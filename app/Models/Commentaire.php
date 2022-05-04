<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Commentaire
 * 
 * @property int $id
 * @property int $publication_id
 * @property int $users_id
 * @property string|null $contenu
 * @property Carbon|null $date
 * 
 * @property Publication $publication
 * @property User $user
 *
 * @package App\Models
 */
class Commentaire extends Model
{
	protected $table = 'commentaire';
	public $timestamps = false;

	protected $casts = [
		'publication_id' => 'int',
		'users_id' => 'int'
	];

	protected $dates = [
		'date'
	];

	protected $fillable = [
		'publication_id',
		'users_id',
		'contenu',
		'date'
	];

	public function publication()
	{
		return $this->belongsTo(Publication::class);
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'users_id');
	}
}
