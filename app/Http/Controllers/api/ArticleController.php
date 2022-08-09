<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\CategorieArticle;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use stdClass;

class ArticleController extends Controller
{
    use ApiResponser;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $articles  = Article::orderBy('id', 'desc');
        $articles = $articles->paginate(20);

        $data = $articles;
        $tab = [];
        foreach ($articles as $e) {
            $a = new stdClass();
            $a->id = $e->id;
            $a->article = $e->article;
            $a->description = $e->description;
            $a->categorie = $e->categorie_article->categorie;
            $f = $e->image;
            $a->image = asset('storage/' . $f);

            $user = $e->categorie_article->entreprise->user;
            $a->user = $user->name;
            $a->user_image = empty($user->avatar) ? asset('storage/users/default.png') : asset('storage/' . $user->avatar);
            $a->date = $e->date->format('Y-m-d H:i:s');
            array_push($tab, $a);
        }
        $data = $data->toArray();
        $data['data'] = $tab;
        unset($data['total']);
        unset($data['last_page']);
        unset($data['links']);
        unset($data['first_page_url']);
        unset($data['last_page_url']);
        unset($data['path']);
        unset($data['from']);
        unset($data['to']);
        return $this->success($data, 'Articles');
    }

    public function userArticles()
    {
        $articles  = Article::orderBy('id', 'desc')->whereIn('categorie_article_id', CategorieArticle::where('users_id', auth()->user()->id)->get()->pluck('id')->all());
        $articles = $articles->paginate(20);

        $data = $articles;
        $tab = [];
        foreach ($articles as $e) {
            $a = new stdClass();
            $a->id = $e->id;
            $a->article = $e->article;
            $a->description = $e->description;
            $a->categorie = $e->categorie_article->categorie;
            $f = $e->image;
            $a->image = asset('storage/' . $f);

            $user = $e->categorie_article->user;
            $a->user = $user->name;
            $a->user_image = empty($user->avatar) ? asset('storage/users/default.png') : asset('storage/' . $user->avatar);
            $a->date = $e->date->format('Y-m-d H:i:s');
            array_push($tab, $a);
        }
        $data = $data->toArray();
        $data['data'] = $tab;
        unset($data['total']);
        unset($data['last_page']);
        unset($data['links']);
        unset($data['first_page_url']);
        unset($data['last_page_url']);
        unset($data['path']);
        unset($data['from']);
        unset($data['to']);
        return $this->success($data, 'Articles');
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
            'article' => 'required|max:45',
            'categorie_article_id' => 'required|exists:categorie_article,id',
            'devise_id' => 'required|exists:devise,id',
            'description' => 'required|max:300',
            'prix' => 'required|numeric|min:1',
            'image' => 'required|mimes:jpg,png,jpeg,gif|max:300',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation error', 400, ['errors_msg' => $validator->errors()->all()]);
        }

        $cat = CategorieArticle::where(['users_id' => auth()->user()->id, 'id' => request()->categorie_article_id])->first();
        if (!$cat) {
            abort(403);
        }

        $fichier = request()->file('image')->store('article', 'public');

        $data = $validator->validate();
        $data['image'] = $fichier;

        Article::create($data);
        return $this->success(null, "Article ajouté.");
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Article $id)
    {
        /** @var \App\Models\Article $article **/
        $article = $id;

        $a = new stdClass();
        $a->id = $article->id;
        $a->article = $article->article;
        $a->description = $article->description;
        $a->categorie = $article->categorie_article->categorie;
        $f = $article->image;
        $a->image = asset('storage/' . $f);

        $user = $article->categorie_article->user;
        $a->user = $user->name;
        $a->user_image = empty($user->avatar) ? asset('storage/users/default.png') : asset('storage/' . $user->avatar);
        $a->date = $article->date->format('Y-m-d H:i:s');
        $data = $a;

        return $this->success($data, '');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update()
    {
        $validator = Validator::make(request()->all(), [
            'article' => 'required|max:45',
            'categorie_article_id' => 'required|exists:categorie_article,id',
            'article_id' => 'required|exists:article,id',
            'devise_id' => 'required|exists:devise,id',
            'description' => 'required|max:300',
            'prix' => 'required|numeric|min:1',
            'image' => 'sometimes|mimes:jpg,png,jpeg,gif|max:300',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation error', 400, ['errors_msg' => $validator->errors()->all()]);
        }

        $cat = CategorieArticle::where(['users_id' => auth()->user()->id, 'id' => request()->categorie_article_id])->first();
        if (!$cat) {
            abort(403);
        }

        $article = Article::where(['id' => request()->article_id])->first();
        if (!$article) {
            abort(403);
        }

        $user = auth()->user();
        if ($user->id != $article->categorie_article->users_id) {
            abort(403);
        }

        $data = $validator->validated();
        if (request()->hasFile('image')) {
            $image = request()->file('image')->store('article', 'public');
            File::delete('storage/' . $article->image);
            $data['image'] = $image;
        }

        $article->update($data);
        return $this->success($data, "Article mis à jour.");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Article $id)
    {
        /** @var \App\Models\Article $article **/
        $article = $id;
        if (auth()->user()->id != $article->categorie_article->user->id) {
            abort(403);
        }
        File::delete('storage/' . $article->image);

        $article->delete();
        return $this->success('', 'Article supprimé');
    }
}
