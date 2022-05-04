<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Publication;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use stdClass;

class PublicationController extends Controller
{
    use ApiResponser;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $publications  = Publication::orderBy('id', 'desc');
        $publications = $publications->paginate(20);

        $data = $publications;
        $tab = [];
        foreach ($publications as $e) {
            $a = new stdClass();
            $a->id = $e->id;
            $a->contenu = $e->contenu;
            $f = $e->fichier;
            $a->fichier = empty($f) ? null : (object) ['url' => asset('storage/' . $f), 'type' => getMimeType('storage/' . $f)];
            $user = $e->user;
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
        return $this->success($data, 'Publications');
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
            'contenu' => 'required|max:600',
            'fichier' => 'sometimes|mimes:jpg,png,jpeg,gif,mp3,mp4|max:5120',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation error', 400, ['errors_msg' => $validator->errors()->all()]);
        }

        $fichier = null;
        if (request()->hasFile('fichier')) {
            $fichier = request()->file('fichier')->store('publications', 'public');
        }

        $data = $validator->validate();
        if ($fichier) {
            $data['fichier'] = $fichier;
        }
        $data['users_id'] = auth()->user()->id;
        Publication::create($data);
        return $this->success(null, "Publication reusie.");
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Publication $id)
    {
        /** @var \App\Models\Publication $publication **/
        $publication = $id;

        $a = new stdClass();
        $a->id = $publication->id;
        $a->contenu = $publication->contenu;
        $a->image = asset('storage/' . $publication->fichier);
        $user = $publication->user;
        $a->user = $user->name;
        $a->user_image = empty($user->avatar) ? asset('storage/users/default.png') : asset('storage/' . $user->avatar);
        $a->date = $publication->date->format('Y-m-d H:i:s');
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
    public function destroy(Publication $id)
    {
        /** @var \App\Models\Publication $publication **/
        $publication = $id;
        if (auth()->user()->id != $publication->user->id) {
            abort(403);
        }
        File::delete('storage/' . $publication->image);

        $publication->delete();
        return $this->success('', 'Publication supprimée');
    }
}
