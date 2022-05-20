<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\ArticleCmd;
use App\Models\Commande;
use App\Models\Devise;
use App\Models\Panier;
use App\Models\Transaction;
use App\Traits\ApiResponser;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use stdClass;

class CommandeController extends Controller
{
    use ApiResponser;

    public function commande($id = null)
    {
        $user = auth()->user();
        $commades = Commande::with('article_cmds')->orderBy('id', 'desc');
        $commades->where('users_id', $user->id);

        if ($id) {
            $commades->where(['id' => $id]);
            $commades = $commades->get(['*', 'numero', 'status', 'total', 'devise', 'date'])->first();
            if ($commades) {
                $tab = [];
                $obj = new stdClass();
                $obj->id = $commades->id;
                $obj->numero = $commades->numero;
                $obj->status = $commades->status;
                $obj->date = Carbon::parse($commades->date)->format('d-m-Y, à H:i:s');
                $obj->total = "$commades->total $commades->devise";
                $q = 0;

                $tab2 = [];
                foreach ($commades->article_cmds as $art) {
                    $q += $art->qte;
                    $article = new stdClass();
                    $article->article = $art->article;
                    $article->qte = $art->qte;
                    $article->prix = $art->prix;
                    $article->total = $art->prix * $art->qte;
                    $article->devise = $art->devise;
                    $article->image = $art->image;
                    array_push($tab2, $article);
                }
                $obj->tot_article = $q;
                $tab['commande'] = $obj;
                $tab['articles'] = $tab2;
                return $this->success($tab, "Détails de la commande : " . $commades->numero);
            }
            return $this->error("Aucune commande trouvée.", 200, []);
        }

        $commades = $commades->get(['*', 'numero', 'status', 'total', 'devise', 'date']);

        if (count($commades)) {
            $tab = [];
            foreach ($commades as $cmd) {
                $obj = new stdClass();
                $obj->id = $cmd->id;
                $obj->numero = $cmd->numero;
                $obj->status = $cmd->status;
                $obj->date = Carbon::parse($cmd->date)->format('d-m-Y, à H:i:s');
                $obj->total = "$cmd->total $cmd->devise";
                $q = 0;
                foreach ($cmd->article_cmds as $art) {
                    $q += $art->qte;
                }
                $obj->tot_article = $q;
                array_push($tab, $obj);
            }
            return $this->success($tab, "Vos commandes (" . count($commades) . ")");
        }
        return $this->error("Aucune commande trouvée.", 200, []);
    }

    function passerCommande()
    {
        /** @var \App\Models\User $user **/
        $user = auth()->user();
        $panier = Panier::where(['users_id' => $user->id])->get();
        $tab = [];
        $total = $nb = 0;
        $dev = '';
        foreach ($panier as $pa) {
            $a = new stdClass();
            $a->id = $pa->article->id;
            $a->categorie_article_id = $pa->article->categorie_article->id;
            $a->article = $pa->article->article;
            $a->qte = $pa->qte;
            $a->prix = $pa->article->prix;
            $a->total = $a->qte * $a->prix;
            $total += $a->total;
            $a->total = "$a->total {$pa->article->devise->devise}";
            $a->prix = "$a->prix {$pa->article->devise->devise}";
            $a->image = asset('storage/' . $pa->article->image);
            array_push($tab, $a);
            $nb += $pa->qte;
            $dev = $pa->article->devise->devise;
        }

        $pdata['total'] = $total;
        $pdata['panier'] = $tab;
        $pdata['devise'] = $dev;

        if (count($tab)) {

            $iddev = Devise::where('devise', $dev)->first();
            $compte = $user->comptes()->first();
            $solde = $compte->soldes()->where(['devise_id' => @$iddev->id])->first();
            if (!$solde) {
                return $this->error("Erreur aucun solde dans votre compte.", 200, []);
            }
            $montant_solde = $solde->montant;
            if ($montant_solde < $total) {
                return $this->error('Echec de payement', 200, ['errors_msg' => ["Vous disposez de $montant_solde {$solde->devise->devise} dans votre compte, votre commande de $total; {$solde->devise->devise} ne peut etre effectuée, merci de recharger votre compte."]]);
            }

            $pdata['solde'] = $solde;
            $pdata['compte'] = $compte;
            $pdata['devise_id'] = @$iddev->id;

            DB::transaction(function () use ($pdata) {
                $panier = $pdata['panier'];
                $devise = $pdata['devise'];
                $devise_id = $pdata['devise_id'];
                $total = $pdata['total'];
                $solde = $pdata['solde'];
                $compte = $pdata['compte'];

                $user = auth()->user();
                $cmd = [
                    'users_id' => $user->id,
                    'status' => 'En attente',
                    'devise' => $devise,
                    'total' => $total,
                ];
                $command = makeUserCommande($user->id, $cmd);
                foreach ($panier as $pa) {
                    ArticleCmd::create([
                        'article_id' => $pa->id,
                        'categorie_article_id' => $pa->categorie_article_id,
                        'article' => $pa->article,
                        'commande_id' => $command->id,
                        'qte' => $pa->qte,
                        'devise' => $devise,
                        'prix' => $pa->prix,
                        'image' => encodeFile('storage/' . $pa->image)
                    ]);
                }
                Panier::where('users_id', $user->id)->delete();

                $solde->decrement('montant', $total);

                $d['compte_id'] = $compte->id;
                $d['devise_id'] = $devise_id;
                $d['montant'] = $total;
                $d['trans_id'] = trans_id();
                $d['type'] = 'commande';
                Transaction::create($d);
            });
            return $this->success([], "Vous venez de passer une commande de " . $total . " $dev");
        } else {
            return $this->error('Impossible de passer votre commande, le panier est vide', 403,);
        }
    }
}
