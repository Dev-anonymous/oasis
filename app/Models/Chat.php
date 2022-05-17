<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Chat
 * 
 * @property int $id
 * @property int $users_id
 * @property int $with_uid
 * 
 * @property User $user
 * @property Collection|Message[] $messages
 *
 * @package App\Models
 */
class Chat extends Model
{
	protected $table = 'chat';
	public $timestamps = false;

	protected $casts = [
		'users_id' => 'int',
		'with_uid' => 'int'
	];

	protected $fillable = [
		'users_id',
		'with_uid'
	];

	public function user()
	{
		return $this->belongsTo(User::class, 'users_id');
	}

	public function messages()
	{
		return $this->hasMany(Message::class);
	}
}
