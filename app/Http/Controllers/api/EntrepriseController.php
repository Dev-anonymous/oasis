<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Entreprise;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class EntrepriseController extends Controller
{
    use ApiResponser;
    /*
    *
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        /** @var \App\Models\User $user **/
        $user = auth()->user();
        $data = [];

        foreach ($user->entreprises()->orderBy('id', 'desc')->get() as $el) {
            $a = (object) $el->toArray();
            unset($a->users_id);
            $a->logo = empty($el->logo) ? asset('storage/user/default.png') : asset('storage/' . $el->logo);
            array_push($data, $a);
        }
        return $this->success($data, "Mes entreprises");
    }

    public function index_all()
    {

        /** @var \App\Models\User $user **/
        $ent = Entreprise::orderBy('id', 'desc')->get();
        $data = [];

        foreach ($ent as $el) {
            $a = (object) $el->toArray();
            unset($a->users_id);
            $a->logo = empty($el->logo) ? asset('storage/user/default.png') : asset('storage/' . $el->logo);
            array_push($data, $a);
        }
        return $this->success($data, "Mes entreprises");
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
            'entreprise' => 'required|max:128|',
            'adresse' => 'sometimes|max:100',
            'telephone' => 'sometimes|string|min:10|numeric|regex:/(\+)[0-9]{10}/',
            'email' => 'sometimes|email',
            'description' => 'sometimes|',
            'localisation' => 'sometimes|',
            'site_web' => 'sometimes|',
            'categorie' => 'sometimes|',
            'logo' => 'sometimes|mimes:jpg,png,jpeg,gif|max:300',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation error', 400, ['errors_msg' => $validator->errors()->all()]);
        }
        $data = $validator->validate();

        if (request()->hasFile('logo')) {
            $logo = request()->file('logo')->store('entreprise', 'public');
            $data['logo'] = $logo;
        }

        $data['users_id'] = auth()->user()->id;

        Entreprise::create($data);
        return $this->success($data, "Entreprise créée.");
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Entreprise $entreprise)
    {
        // if ($entreprise->users_id != auth()->user()->id) {
        //     abort(404);
        // }

        $a = (object) $entreprise->toArray();
        unset($a->users_id);
        $a->logo = empty($entreprise->logo) ? asset('storage/user/default.png') : asset('storage/' . $entreprise->logo);
        return $this->success($a, "Mon entreprise");
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Entreprise $entreprise)
    {
        if ($entreprise->users_id != auth()->user()->id) {
            abort(401);
        }
        $validator = Validator::make(request()->all(), [
            'entreprise' => 'sometimes|min:3|max:128|',
            'adresse' => 'sometimes|max:100',
            'telephone' => 'sometimes|string|min:10|numeric|regex:/(\+)[0-9]{10}/',
            'email' => 'sometimes|email',
            'logo' => 'sometimes|mimes:jpg,png,jpeg,gif|max:300',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation error', 400, ['errors_msg' => $validator->errors()->all()]);
        }
        $data = $validator->validate();

        if (request()->hasFile('logo')) {
            $logo = request()->file('logo')->store('entreprise', 'public');
            File::delete('storage/' . $entreprise->logo);
            $data['logo'] = $logo;
        }

        $entreprise->update($data);
        return $this->success($data, "Entreprise mise à jour.");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Entreprise $entreprise)
    {
        if ($entreprise->users_id != auth()->user()->id) {
            abort(401);
        }
        $entreprise->delete();
        File::delete('storage/' . $entreprise->logo);
        return $this->success([], "Entreprise $entreprise->entreprise a été suprimée");
    }
}
