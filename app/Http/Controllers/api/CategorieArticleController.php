<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\CategorieArticle;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use stdClass;

class CategorieArticleController extends Controller
{
    use ApiResponser;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        /** @var \App\Models\User $user **/
        $user = auth()->user();
        $ide = $user->entreprises()->pluck('id')->all();

        $cat = CategorieArticle::whereIn('entreprise_id', $ide)->get();
        $tab = [];
        foreach ($cat as $e) {
            $a = new stdClass();
            $a->id = $e->id;
            $a->categorie = $e->categorie;
            $a->image = asset('storage/' . $e->image);
            $a->entreprise = $e->entreprise->entreprise;
            array_push($tab, $a);
        }
        return $this->success($tab, 'Vos categories');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'categorie' => 'required|max:45',
            'entreprise_id' => 'required|exists:entreprise,id',
            'image' => 'required|mimes:jpg,png,jpeg,gif|max:300',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation error', 400, ['errors_msg' => $validator->errors()->all()]);
        }

        $ide = request()->entreprise_id;
        /** @var \App\Models\User $user **/
        $user = auth()->user();
        $ent = $user->entreprises()->pluck('id')->all();
        if (!in_array($ide, $ent)) {
            abort(403);
        }

        $fichier = request()->file('image')->store('categorie-article', 'public');

        $data = $validator->validate();
        $data['image'] = $fichier;

        $data['users_id'] = auth()->user()->id;
        CategorieArticle::create($data);
        return $this->success(null, "Categorie ajoutée.");
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
    public function destroy(CategorieArticle $id)
    {
        /** @var \App\Models\CategorieArticle $categorie **/
        $categorie = $id;
        /** @var \App\Models\User $user **/
        $user = auth()->user();
        $ent = $user->entreprises()->pluck('id')->all();
        if (!in_array($categorie->entreprise_id, $ent)) {
            abort(403);
        }
        File::delete('storage/' . $categorie->image);

        $categorie->delete();
        return $this->success('', 'Categorie supprimée');
    }
}
