<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Compte;
use App\Models\User;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    use ApiResponser;

    public function register(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|string|max:45',
                'email' => 'sometimes|email|max:255|unique:users',
                'phone' => 'sometimes|min:10|numeric|regex:/(\+)[0-9]{10}/|unique:users,phone',
                'password' => 'required|string|min:6|same:cpassword',
                'cpassword' => 'required|string|min:6|',
                'user_role' => 'sometimes|in:marchand,client',
            ]
        );

        if ($validator->fails()) {
            return $this->error('Validation error', 400, ['errors_msg' => $validator->errors()->all()]);
        }

        $em = request('email');
        $ph = request('phone');
        if (empty($em) and empty($ph)) {
            return $this->error('Erreur', 400, ['errors_msg' => ["Vous devez spécifier soit votre email, soit votre numéro de téléphone pour créer un compte."]]);
        }

        $data = $validator->validate();
        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);
        Compte::create([
            'users_id' => $user->id,
            'numero_compte' => numeroCompte()
        ]);

        Auth::login($user);
        return $this->success([
            'token' => $user->createToken('token_' . time())->plainTextToken,
        ], "Account created successfully.");
    }

    public function login(Request $request)
    {
        $attr = $request->all();
        $validator = Validator::make($attr, [
            'login' => 'required|string|',
            'password' => 'required|string|min:6'
        ]);

        if ($validator->fails()) {
            return $this->error('Validation error', 400, ['errors_msg' => $validator->errors()->all()]);
        }

        $success = false;
        $data = $validator->validate();
        $login = $data['login'];
        if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
            $_ = ['password' => $data['password'], 'email' => $login];
            if (Auth::attempt($_, request('remember-me') ? true : false)) {
                $success = true;
            }
        } else if (is_numeric($login)) {
            $login = "+" . (float) $login;
            $_ = ['password' => $data['password'], 'phone' => $login];
            if (Auth::attempt($_, request('remember-me') ? true : false)) {
                $success = true;
            }
        } else {
            return $this->error('Validation error.', 400, ['errors_msg' => ["You must provide your email or phone number to login"]]);
        }

        if (!$success) {
            return $this->error('Login error', 400, ['errors_msg' => ["Credentials not match"]]);
        }

        /** @var \App\Models\User $user **/
        $user = auth()->user();
        User::where(['id' => $user->id])->update(['derniere_connexion' => now()]);
        return $this->success([
            'token' => $user->createToken('token_' . time())->plainTextToken,
        ], "Successful authentication.");
    }

    public function logout()
    {
        if (Auth::check()) {
            /** @var \App\Models\User $user **/
            $user = auth()->user();
            $user->tokens()->delete();
        }
        return $this->success([], "Logout success");
    }
}
