<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Commentaire;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use stdClass;

class CommentaireController extends Controller
{
    use ApiResponser;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'publication_id' => 'required|exists:publication,id',
            'contenu' => 'required|max:600'
        ]);

        if ($validator->fails()) {
            return $this->error('Validation error', 400, ['errors_msg' => $validator->errors()->all()]);
        }
        $data = $validator->validated();
        $data['users_id'] = auth()->user()->id;
        Commentaire::create($data);
        return $this->success(null, "Commentaire publié.");
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Commentaire $publication_id)
    {
        $a = new stdClass();
        $a->commentaire = $publication_id->contenu;
        $a->user = $publication_id->user->name;
        $user = $publication_id->user;
        $a->user_image = empty($user->avatar) ? asset('storage/users/default.png') : asset('storage/' . $user->avatar);
        $a->date = $publication_id->date->format('Y-m-d H:i:s');
        return $this->success($a, "Commentaire");
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
    public function destroy(Commentaire $id)
    {
        /** @var \App\Models\Commentaire $commentaire **/
        $commentaire = $id;
        if (auth()->user()->id != $commentaire->user->id) {
            abort(403);
        }
        $commentaire->delete();
        return $this->success('', 'Commentaire supprimé');
    }
}
