<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use stdClass;

class MessageController extends Controller
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

        $toList = Chat::where(['users_id' => $user->id])->groupBy('with_uid')->pluck('with_uid')->all();
        $fromList = Chat::where(['with_uid' => $user->id])->groupBy('users_id')->pluck('users_id')->all();

        $chat = array_unique(array_merge($toList, $fromList));
        $chat = array_reverse($chat);

        $tab = [];
        foreach ($chat as $e) {
            $ch = Chat::where('users_id', $e)->orWhere('with_uid', $e)->first();
            $u = User::find($e);

            $el = new stdClass();
            $el->id = $u->id;
            $el->name = $u->name;
            $el->image = empty($u->avatar) ? asset('storage/users/default.png') : asset('storage/' . $u->avatar);
            $el->chat_id = @$ch->id;

            array_push($tab, $el);
        }

        return $this->success($tab, 'Chat récents');
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
            'message' => 'required|max:600',
            'with_uid' => 'required|exists:users,id',
            'chat_id' => 'sometimes|exists:chat,id'
        ]);

        if ($validator->fails()) {
            return $this->error('Validation error', 400, ['errors_msg' => $validator->errors()->all()]);
        }

        $user = auth()->user();

        $data = $validator->validate();
        if ($data['with_uid'] == $user->id) {
            return $this->error('Validation error', 400, ['errors_msg' => ['Invalide to_uid']]);
        }

        $chat_id = (int) @$data['chat_id'];
        $sentbyuser = 1;
        if ($chat_id) {
            $chat = Chat::where(['id' => $chat_id])->first();
            if ($chat) {
                if ($chat->users_id != $user->id) {
                    $sentbyuser = 0;
                }
            } else {
                $chat = Chat::create(['users_id' => $user->id, 'with_uid' => request()->with_uid]);
            }
        } else {
            $chat = Chat::create(['users_id' => $user->id, 'with_uid' => request()->with_uid]);
        }

        $data['chat_id'] = $chat->id;
        $data['sentbyuser'] = $sentbyuser;
        Message::create($data);
        return $this->success($data, "Message envoyé.");
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($with_uid)
    {
        $user = auth()->user();
        $chat = Chat::where(['users_id' => $user->id, 'with_uid' => $with_uid])->first();
        if (!$chat) {
            return $this->error('Aucune conversation', 200, []);
        }
        $tab = [];
        $mess = Message::where(['chat_id' => $chat->id])->get();
        foreach ($mess as $e) {
            $a = new stdClass();
            $a->id = $e->id;
            $a->sentbyuser = (bool) $e->sentbyuser;
            $a->message = $e->message;
            $a->read = $e->read;
            if ($a->sentbyuser) {
                $u = User::find($e->chat->user->id);
                $a->to = [
                    'name' => $u->name,
                    'image' => empty($u->avatar) ? asset('storage/users/default.png') : asset('storage/' . $u->avatar),
                ];
            } else {
                $u = User::find($e->chat->user->id);
                $a->from = [
                    'name' => $u->name,
                    'image' => empty($u->avatar) ? asset('storage/users/default.png') : asset('storage/' . $u->avatar),
                ];
            }
            $a->date = $e->date->format('Y-m-d H:i:s');
            array_push($tab, $a);
        }

        return $this->success($tab, 'Messages');
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
    public function destroy(Message $id)
    {
        /** @var \App\Models\Message $message **/
        $message = $id;
        if (auth()->user()->id != $message->chat->user->id) {
            abort(403);
        }

        $message->delete();
        return $this->success('', 'Message supprimé');
    }
}
