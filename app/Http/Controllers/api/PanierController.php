<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Panier;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use stdClass;

class PanierController extends Controller
{
    use ApiResponser;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $panier = Panier::where(['users_id' => auth()->user()->id])->get();
        $tab = [];
        $total = $nb = 0;
        $dev = '';
        foreach ($panier as $pa) {
            $a = new stdClass();
            $a->id = $pa->article->id;
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

        if (count($tab)) {
            return $this->success([
                'panier' => $tab,
                'total' => "$total $dev",
                'nb' => $nb,
            ], "Article dans votre panier");
        } else {
            return $this->error('Votre panier est vide', 200, ['panier' => []]);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $qte = request('qte');
        $no_inc = request('no_inc');
        $validator = Validator::make($request->all(), ['id' => 'required|int|exists:article,id']);
        if ($validator->fails()) {
            return $this->error('Validation error', 200, ['errors_msg' => $validator->errors()->all()]);
        }
        $data = $validator->validate();
        $id = $data['id'];

        $article = Article::find($id);
        if (!$article) {
            return $this->error('Article non valide', 200, ['errors_msg' => []]);
        }
        $user = auth()->user();
        if ($article->categorie_article->entreprise->users_id == $user->id) {
            return $this->error('Action non autorisée', 403, ['errors_msg' => ["Vous ne pouvez pas ajouter votre propre article au panier."]]);
        }

        if ($qte && $qte <= 0) {
            return $this->error('Quantité non valide', 200, ['errors_msg' => []]);
        }
        $panier = Panier::with('article')->where(['users_id' => $user->id, 'article_id' => $id])->first();


        $panierTest = Panier::with('article')->where(['users_id' => $user->id])->first();
        if ($panierTest) {
            $d1 = $panierTest->article->devise;
            $d2 = $article->devise;
            if ($d1->id != $d2->id) {
                return $this->error('Erreur', 200, ['errors_msg' => ["Vous devez continuer à ajouter les articles en $d1->devise et passer votre commande avant d'ajouter les articles en $d2->devise."]]);
            }
        }

        $qte = (int) $qte;
        $qte = $qte <= 0 ? 1 : $qte;

        if ($panier) {

            if (is_null($no_inc)) {
                $qte = $qte + $panier->qte;
            }
            Panier::where([
                'users_id' => $user->id,
                'article_id' => $id,
            ])->update(['qte' => $qte, 'date' => date('Y-m-d H:i:s')]);
            return $this->success([], "Article mis à jour.\nQte : {$qte}\nPrix : " . $qte * $article->prix . " {$article->devise->devise}" . "\n{$article->article}");
        } else {
            Panier::create([
                'users_id' => $user->id,
                'article_id' => $id,
                'qte' => $qte
            ]);
            return $this->success([], "Article ajouté au panier.\n{$article->article}");
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = auth()->user();
        $panier = Panier::where(['users_id' => $user->id, 'article_id' => $id])->get()->first();
        if (!$panier) {
            return $this->error("Action non autorisée.", 403);
        }
        $panier->delete();
        return $this->success(null, "L'article a été supprimé du panier.");
    }
}
