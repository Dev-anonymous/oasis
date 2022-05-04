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
        $user = auth()->user();
        $cat = $user->categorie_articles;

        $tab = [];
        foreach ($cat as $e) {
            $a = new stdClass();
            $a->categorie = $e->categorie;
            $a->image = asset('storage/' . $e->image);
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
            'image' => 'required|mimes:jpg,png,jpeg,gif|max:300',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation error', 400, ['errors_msg' => $validator->errors()->all()]);
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
        if (auth()->user()->id != $categorie->user->id) {
            abort(403);
        }
        File::delete('storage/' . $categorie->image);

        $categorie->delete();
        return $this->success('', 'Categorie supprimée');
    }
}
