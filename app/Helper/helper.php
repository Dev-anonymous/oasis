<?php

use App\Models\Commande;
use App\Models\Compte;
use App\Models\Flexpay;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

define('FLEXPAY_HEADERS', [
    "Content-Type: application/json",
    "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJcL2xvZ2luIiwicm9sZXMiOlsiTUVSQ0hBTlQiXSwiZXhwIjoxNzI1NTMwNTA2LCJzdWIiOiI0MDNiMmJiODc5MGFhYzA2NTIzNzY4MjJmMmRkMmY5NSJ9.WgAwvlzgXPBueAmX2dh0LFqiE6LR_Ri4IzZUGnkgppY"
]);

define('BASE_U', "http://41.243.7.46:3006/flexpay/api");

function getMimeType($filename)
{
    if (!file_exists($filename)) return '';
    $mimetype = mime_content_type($filename);
    if (strpos($mimetype, 'image') !== false) {
        $mimetype = 'image';
    } else if (strpos($mimetype, 'audio') !== false) {
        $mimetype = 'audio';
    } else if (strpos($mimetype, 'video') !== false) {
        $mimetype = 'video';
    }
    return $mimetype;
}

function numeroCompte()
{
    $compte = Compte::all();
    $n = $compte->count() + 1;

    if ($n < 10) {
        $c = "C00$n";
    } else if ($n >= 10 and $n < 100) {
        $c = "C0$n";
    } else {
        $c = "C$n";
    }
    $c = $c . '.' . makeRand() . '.' . makeRand();
    return $c;
}

function makeRand($max = 5)
{
    $max = (int) $max;
    if (!$max or $max <= 0) {
        return 0;
    }

    $num = '';
    while ($max > 0) {
        $max--;
        $num .= rand(1, 9);
    }
    return $num;
}

function trans_id()
{
    $tr = Transaction::where('type', 'transfert')->get();
    $n = $tr->count() + 1;

    if ($n < 10) {
        $c = "TRANS-00$n";
    } else if ($n >= 10 and $n < 100) {
        $c = "TRANS-0$n";
    } else {
        $c = "TRANS-$n";
    }
    $c = $c . '.' . makeRand() . '.' . makeRand();
    return $c;
}

function makeUserCommande($iduser, array $data)
{
    $nb_cmds = Commande::where('users_id', $iduser)->get()->count() + 1;
    $data['numero'] = numeroCmd($iduser, $nb_cmds);
    return Commande::create($data);
}

function numeroCmd($iduser, $n)
{
    switch ($n) {
        case $n <= 9:
            $n = "00$n";
            break;
        case $n >= 10 and $n <= 99:
            $n = "0$n";
            break;
        default:
            break;
    }
    return "C-$iduser-" . $n . '-' . strtoupper(Str::random(10));
}

function encodeFile($file)
{
    if (!is_file($file)) return;
    return 'data:' . mime_content_type($file) . ';base64,' . base64_encode(file_get_contents($file));
}

function startFlexPay($devise, $montant, $telephone, $ref, $cb_code)
{
    $_api_headers = FLEXPAY_HEADERS;
    $marchand = 'OASISAPP';

    $telephone = (float) $telephone;
    $data = array(
        "merchant" => $marchand,
        "type" => "1",
        "phone" => "$telephone",
        "reference" => "$ref",
        "amount" => "$montant",
        "currency" => "$devise",
        "callbackUrl" => route('payment.callback.web', $cb_code),
    );


    $data = json_encode($data);
    $gateway = BASE_U . "/rest/v1/paymentService";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $gateway);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $_api_headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 300);
    $response = curl_exec($ch);
    $rep['status'] = false;
    if (curl_errno($ch)) {
        $rep['message'] = "Erreur, veuillez reessayer.";
    } else {
        $jsonRes = json_decode($response);
        $code = $jsonRes->code ?? '';
        if ($code != "0") {
            $rep['message'] = "Erreur, veuillez reessayer : " . @$jsonRes->message;
            $rep['data'] = $jsonRes;
        } else {
            $rep['status'] = true;
            $rep['message'] = "Transaction initialisée avec succès. Veuillez saisir votre code Mobile Money pour confirmer la transaction.";
            $rep['data'] = $jsonRes;
        }
    }
    curl_close($ch);
    return $rep;
}

function completeFlexpayTrans()
{
    $pendingPayments = Flexpay::where(['callback' => '1', 'is_saved' => '0', 'transaction_was_failled' => '0'])->get();
    foreach ($pendingPayments as $e) {
        $payedata = json_decode($e->pay_data);
        $orderNumber = $payedata->apiresponse->orderNumber;

        if (transaction_was_success($orderNumber) == true) {
            $user = User::find(json_decode($e->user)->id);
            if ($user) {
                $compte = $user->comptes()->first();
                $trans_data = (array) $payedata->paydata->trans_data;
                DB::beginTransaction();
                Transaction::create($trans_data);
                $solde = $compte->soldes()->where(['devise_id' => $trans_data['devise_id']]);
                $solde->increment('montant', $trans_data['montant']);
                DB::commit();
                $e->update(['is_saved' => 1]);
            }
        } else {
            $e->update(['transaction_was_failled' => 1]);
        }
    }
}

function transaction_was_success($orderNumber)
{
    $_api_headers = FLEXPAY_HEADERS;

    $gateway = BASE_U . "/rest/v1/check/" . $orderNumber;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $gateway);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $_api_headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 300);
    $response = curl_exec($ch);
    $status = false;
    if (!curl_errno($ch)) {
        curl_close($ch);
        $jsonRes = json_decode($response);
        $code = $jsonRes->code ?? '';
        if ($code == "0") {
            if ($jsonRes->transaction->status == '0') {
                $status = true;
            }
        }
    }
    return $status;
}
