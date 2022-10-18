<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Flexpay
 * 
 * @property int $id
 * @property string $user
 * @property string $cb_code
 * @property string $ref
 * @property string $pay_data
 * @property int $is_saved
 * @property int $callback
 * @property int $transaction_was_failled
 * @property Carbon $date
 *
 * @package App\Models
 */
class Flexpay extends Model
{
	protected $table = 'flexpay';
	public $timestamps = false;

	protected $casts = [
		'is_saved' => 'int',
		'callback' => 'int',
		'transaction_was_failled' => 'int'
	];

	protected $dates = [
		'date'
	];

	protected $fillable = [
		'user',
		'cb_code',
		'ref',
		'pay_data',
		'is_saved',
		'callback',
		'transaction_was_failled',
		'date'
	];
}
