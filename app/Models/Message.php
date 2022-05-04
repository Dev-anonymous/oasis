<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Message
 * 
 * @property int $id
 * @property int $users_id
 * @property string|null $message
 * @property int|null $to_uid
 * @property int|null $read
 * @property Carbon|null $date
 * 
 * @property User $user
 *
 * @package App\Models
 */
class Message extends Model
{
	protected $table = 'message';
	public $timestamps = false;

	protected $casts = [
		'users_id' => 'int',
		'to_uid' => 'int',
		'read' => 'int'
	];

	protected $dates = [
		'date'
	];

	protected $fillable = [
		'users_id',
		'message',
		'to_uid',
		'read',
		'date'
	];

	public function user()
	{
		return $this->belongsTo(User::class, 'users_id');
	}
}
