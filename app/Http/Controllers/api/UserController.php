<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use stdClass;

class UserController extends Controller
{
    use ApiResponser;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::orderBy('id', 'desc');
        $users = $users->paginate(100);

        $data = $users;
        $tab = [];
        foreach ($users as $e) {
            $a = new stdClass();
            $a->id = $e->id;
            $a->name = $e->name;
            $a->email = $e->email;
            $a->phone = $e->phone;
            $a->user_image = empty($e->avatar) ? asset('storage/users/default.png') : asset('storage/' . $e->avatar);
            array_push($tab, $a);
        }
        $data = $data->toArray();
        $data['data'] = $tab;
        // unset($data['total']);
        unset($data['last_page']);
        unset($data['links']);
        unset($data['first_page_url']);
        unset($data['last_page_url']);
        unset($data['path']);
        unset($data['from']);
        unset($data['to']);
        return $this->success($data, 'Users');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
    public function update()
    {
        $user = auth()->user();
        $validator = Validator::make(request()->all(), [
            'name' => 'sometimes|string|max:45|min:6',
            'email' => 'sometimes|string|email|max:255|min:6|unique:users,email,' . $user->id,
            'phone' => 'sometimes|string|min:10|numeric|regex:/(\+)[0-9]{10}/|unique:users,phone,' . $user->id,
            'avatar' => 'sometimes|mimes:jpg,png,jpeg,gif|max:800|dimensions:min_width=300,min_height=300,max_width=500,max_height=500',
        ]);
        if ($validator->fails()) {
            return $this->error('Validation error', 400, ['errors_msg' => $validator->errors()->all()]);
        }
        $data = $validator->validate();
        if (request()->hasFile('avatar')) {
            $image = request()->file('avatar')->store('avatar', 'public');
            File::delete('storage/' . $user->avatar);
            $data['avatar'] = $image;
        }
        User::where('id', $user->id)->update($data);
        return $this->success([], "Vos données ont été mises à jour.");
    }

    public function update_pass()
    {
        $user = auth()->user();
        $validator = Validator::make(request()->all(), [
            'password' => 'required|string',
            'newpassword' => 'required|string|min:3|',
        ]);
        if ($validator->fails()) {
            return $this->error('Validation error', 400, ['msg' => $validator->errors()->all()]);
        }

        $cp = request()->password;
        $np = request()->newpassword;

        if (!(Hash::check($cp, $user->password))) {
            return $this->error('Validation error', 400, ['msg' => ['Le mot de passe actuel que vous avez saisi est incorrect.']]);
        }

        User::where('id', $user->id)->update(['password' => Hash::make($np)]);
        return $this->success(null, "Votre mot de passe a été modifié.");
    }

    public function me()
    {
        $user = auth()->user();
        return $this->success($user, "Profil");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
