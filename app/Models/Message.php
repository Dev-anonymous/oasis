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
 * @property int $chat_id
 * @property string|null $message
 * @property int|null $read
 * @property Carbon|null $date
 * @property int|null $sentbyuser
 * 
 * @property Chat $chat
 *
 * @package App\Models
 */
class Message extends Model
{
	protected $table = 'message';
	public $timestamps = false;

	protected $casts = [
		'chat_id' => 'int',
		'read' => 'int',
		'sentbyuser' => 'int'
	];

	protected $dates = [
		'date'
	];

	protected $fillable = [
		'chat_id',
		'message',
		'read',
		'date',
		'sentbyuser'
	];

	public function chat()
	{
		return $this->belongsTo(Chat::class);
	}
}
