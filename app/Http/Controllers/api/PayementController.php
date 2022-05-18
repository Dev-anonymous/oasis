<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Approvisionnement;
use App\Models\Compte;
use App\Models\Devise;
use App\Models\Operateur;
use App\Models\Solde;
use App\Models\Transaction;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use stdClass;

class PayementController extends Controller
{
    use ApiResponser;

    public function solde($devise = null)
    {
        /** @var \App\Models\User $user **/
        $user = auth()->user();
        $solde = $user->comptes()->first()->soldes()->get();

        $tab = [];
        foreach ($solde as $e) {
            array_push($tab, (object) [
                'devise' => $e->devise->devise,
                'montant' => (float) $e->montant
            ]);
        }

        $devise = strtoupper($devise);
        if ($devise and !in_array($devise, ['USD', 'CDF'])) {
            return  $this->error("Devise nom valide : $devise", 400, []);
        }

        $r = $tab;
        if ($devise) {
            foreach ($r as $sol) {
                if ($sol->devise == $devise) {
                    $s[] = $sol;
                    $r = $s;
                    break;
                }
            }
        }

        return $this->success($r, 'Votre solde');
    }

    public function appro()
    {
        /** @var \App\Models\User $user **/
        $user = auth()->user();
        $compte = $user->comptes()->first();

        $validator = Validator::make(
            request()->all(),
            [
                'operateur_id' => 'required|exists:operateur,id',
                'devise_id' => 'required|exists:devise,id',
                'montant' => 'required|numeric|min:1',
            ]
        );

        if ($validator->fails()) {
            return $this->error('Validation error', 400, ['errors_msg' => $validator->errors()->all()]);
        }

        $data = $validator->validated();
        $data['compte_id'] = $compte->id;
        $data['trans_id'] = $transid = trans_id();


        DB::beginTransaction();
        Transaction::create($data);
        $solde = $compte->soldes()->where(['devise_id' => $data['devise_id']]);
        $solde->increment('montant', $data['montant']);
        DB::commit();

        $dev = Devise::where('id', request()->devise_id)->first();
        $op = Operateur::where('id', request()->operateur_id)->first();
        $m = request()->montant . ' ' . $dev->devise;

        $msg = "Vous venez d'approvissionner votre votre d'un montant de $m, par {$op->operateur}. TransID : $transid";
        return $this->success([], $msg);
    }

    public function transfert()
    {
        /** @var \App\Models\User $user **/
        $user = auth()->user();

        $validator = Validator::make(
            request()->all(),
            [
                'numero_compte' => 'required|exists:compte,numero_compte',
                'devise_id' => 'required|exists:devise,id',
                'montant' => 'required|numeric|min:1',
            ]
        );

        if ($validator->fails()) {
            return $this->error('Validation error', 400, ['errors_msg' => $validator->errors()->all()]);
        }

        $data = $validator->validated();
        $cmpt = $data['numero_compte'];
        $mont = $data['montant'];

        $compte = $user->comptes()->first();
        if ($cmpt == $compte->numero_compte) {
            return $this->error('Echec de transaction', 400, ['errors_msg' => ["Numéro de compte non autorisé."]]);
        }

        $solde = $compte->soldes()->where(['devise_id' => $data['devise_id']])->first();
        $montant_solde = $solde->montant;

        if ($montant_solde < $mont) {
            return $this->error('Echec de transaction', 400, ['errors_msg' => ["Vous disposez de $montant_solde {$solde->devise->devise} dans votre compte, votre transaction de $mont {$solde->devise->devise} ne peut etre effectuée, merci de recharger votre compte."]]);
        }

        DB::beginTransaction();
        $solde->decrement('montant', $mont);

        $d['compte_id'] = $compte->id;
        $d['devise_id'] = $data['devise_id'];
        $d['montant'] = $data['montant'];
        $d['trans_id'] = $transid = trans_id();
        $d['type'] = 'transfert';
        $d['data'] = json_encode([
            'to' => $data['numero_compte']
        ]);
        Transaction::create($d);

        $comptBenficiaire = Compte::where('numero_compte', $data['numero_compte'])->first();
        $solde2 = $comptBenficiaire->soldes()->where(['devise_id' => $data['devise_id']])->first();
        $solde2->increment('montant', $mont);

        $d['compte_id'] = $comptBenficiaire->id;
        $d['source'] = 'client';
        $d['data'] = json_encode([
            'from' => $compte->numero_compte
        ]);
        Transaction::create($d);
        DB::commit();

        $msg = "Vous venez d'effectuer un tranfert de $mont {$solde->devise->devise} vers le compte {$comptBenficiaire->numero_compte}({$comptBenficiaire->user->name}). TransID : $transid";
        return $this->success([], $msg);
    }


    public function transaction($limte = null)
    {
        /** @var \App\Models\User $user **/
        $user = auth()->user();
        $compte = $user->comptes()->first();
        $trans = Transaction::where('compte_id', $compte->id);
        $limte = (int) $limte;
        if ($limte) {
            $trans = $trans->limit($limte);
        }

        $trans = $trans->orderBy('id', 'desc')->get();

        $tab = [];

        foreach ($trans as $e) {
            $a = new stdClass();
            $a->id = $e->id;
            $a->trans_id = $e->trans_id;
            $a->montant = "$e->montant {$e->devise->devise}";
            $a->type = $e->type;
            $a->source = $e->source;
            $op =  $e->operateur;
            if ($op) {
                $op = ['operateur' => $op->operateur, 'image' => asset('storage/' . $op->image)];
            }
            $a->operateur = $op;

            $a->date = $e->date->format('Y-m-d H:i:s');

            array_push($tab, $a);
        }

        $m = "Vos transactions";
        return $this->success($tab, "$m");
    }

    public function numero_compte()
    {
        /** @var \App\Models\User $user **/
        $user = auth()->user();
        $compte = $user->comptes()->first();
        $n = $compte->numero_compte;
        return $this->success($n, "Mon numero de compte");
    }

    public function devise()
    {
        $dev = Devise::get(['id', 'devise']);
        return $this->success($dev, "Liste devises");
    }

    public function operateur()
    {
        $dev = Operateur::get(['id', 'operateur']);
        return $this->success($dev, "Liste operateurs");
    }
}
