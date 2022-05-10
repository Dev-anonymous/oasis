<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Approvisionnement;
use App\Models\Compte;
use App\Models\Devise;
use App\Models\Operateur;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PayementController extends Controller
{
    use ApiResponser;

    public function solde($devise = null)
    {
        /** @var \App\Models\User $user **/
        $user = auth()->user();
        $compte = $user->comptes()->first();

        $solde = Devise::selectRaw('COALESCE(sum(montant), 0) as montant, devise.devise')
            ->leftJoin('approvisionnement', 'approvisionnement.devise_id', '=', 'devise.id')->groupBy('devise.id')
            ->where('compte_id', $compte->id)
            ->get();

        if (count($solde)) {
            $r = $solde;
        } else {
            $r = [['montant' => 0, 'devise' => 'CDF'], ['montant' => 0, 'devise' => 'USD']];
        }

        $devise = strtoupper($devise);
        if ($devise and !in_array($devise, ['USD', 'CDF'])) {
            return  $this->error("Devise nom valide : $devise", 400, []);
        }

        if ($devise) {
            $find = false;
            foreach ($r as $sol) {
                if ($sol->devise == $devise) {
                    $find = true;
                    $s[] = $sol;
                    $r = $s;
                    break;
                }
            }

            if (!$find) {
                $r = [
                    ['montant' => 0, 'devise' => $devise]
                ];
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
                'source' => 'sometimes|in:appro,transfert'
            ]
        );

        if ($validator->fails()) {
            return $this->error('Validation error', 400, ['errors_msg' => $validator->errors()->all()]);
        }

        $data = $validator->validated();
        $data['compte_id'] = $compte->id;
        Approvisionnement::create($data);

        $dev = Devise::where('id', request()->devise_id)->first();
        $op = Operateur::where('id', request()->operateur_id)->first();
        $m = request()->montant . ' ' . $dev->devise;

        $msg = "Vous venez d'approvissionner votre votre d'un montant de $m, par {$op->operateur}.";
        return $this->success([], $msg);
    }
}
