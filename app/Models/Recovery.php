<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Recovery
 * 
 * @property int $id
 * @property int $users_id
 * @property string|null $code
 * @property Carbon|null $date
 * 
 * @property User $user
 *
 * @package App\Models
 */
class Recovery extends Model
{
	protected $table = 'recovery';
	public $timestamps = false;

	protected $casts = [
		'users_id' => 'int'
	];

	protected $dates = [
		'date'
	];

	protected $fillable = [
		'users_id',
		'code',
		'date'
	];

	public function user()
	{
		return $this->belongsTo(User::class, 'users_id');
	}
}
