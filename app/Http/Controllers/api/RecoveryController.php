<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Mail\AppMail;
use App\Models\Recovery;
use App\Models\User;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class RecoveryController extends Controller
{
    use ApiResponser;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function recovery()
    {
        $validator = Validator::make(request()->all(), [
            'login' => 'required|',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation error', 200, ['errors_msg' => $validator->errors()->all()]);
        }
        $login = trim(request()->login);

        if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
            $data['email'] = $login;
        } else {
            $data['phone'] = $login;
        }
        $u =  User::where($data)->first();
        if ($u) {
            $email = $u->email;
            $phone = $u->phone;
            if (empty($email)) {
                return $this->error("Aucune adresse email trouvée, il est impossible de vous envoyer le code de réinitialisation du mot de passe.", 200, ['errors_msg' => []]);
            }

            $code = makeRand(6);
            while (1) {
                $code = makeRand(6);
                if (!Recovery::where(['code' => $code])->first()) {
                    break;
                }
            }

            try {
                $d = "Cher(e) $u->name, votre code de réinitialisation du mot de passe est $code";
                Mail::to($email)->send(new AppMail($d));
                Recovery::where('users_id', $u->id)->delete();
                Recovery::create(['date' => now('Africa/Lubumbashi'), 'code' => $code, 'users_id' => $u->id]);
            } catch (\Throwable $th) {
                return $this->error("Impossible d'envoyer le code, veuiller réessayer.", 200, ['errors_msg' => []]);
            }
        } else {
            return $this->error("Aucun compte trouvé pour $login", 200, ['errors_msg' => []]);
        }

        return $this->success([], "Le code de réinitialisation du mot de passe a été envoyé à l'adresse $email.");
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function check()
    {
        $validator = Validator::make(request()->all(), [
            // 'code' => 'required|',
            'phone' => 'required|',
            'newpassword' => 'required|string|min:3|',
        ]);
        if ($validator->fails()) {
            return $this->error('Validation error', 200, ['errors_msg' => $validator->errors()->all()]);
        }

        $code = request()->code;
        $pass = request()->newpassword;
        // $rec  = Recovery::where('code', $code)->first();
        // if (!$rec) {
        //     return $this->error("Le code $code est incorrect", 200, ['errors_msg' => []]);
        // }

        // $rec->user->update(['password' => Hash::make($pass)]);
        // $rec->user->tokens()->delete();
        // $rec->delete();
        // return $this->success(['token' => $rec->user->createToken('token_' . time())->plainTextToken,], "Votre mot de passe a été réinitialisisé.");

        $user = User::where('phone', request()->phone)->first();
        if ($user) {
            $user->update(['password' => Hash::make($pass)]);
            return $this->success(['token' => $user->createToken('token_' . time())->plainTextToken,], "Votre mot de passe a été réinitialisisé.");
        } else {
            return $this->error("Aucun compte trouvé", 200, ['errors_msg' => []]);
        }
    }
}
