<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Publication
 * 
 * @property int $id
 * @property int $users_id
 * @property string|null $contenu
 * @property string|null $fichier
 * @property Carbon|null $date
 * 
 * @property User $user
 * @property Collection|Commentaire[] $commentaires
 *
 * @package App\Models
 */
class Publication extends Model
{
	protected $table = 'publication';
	public $timestamps = false;

	protected $casts = [
		'users_id' => 'int'
	];

	protected $dates = [
		'date'
	];

	protected $fillable = [
		'users_id',
		'contenu',
		'fichier',
		'date'
	];

	public function user()
	{
		return $this->belongsTo(User::class, 'users_id');
	}

	public function commentaires()
	{
		return $this->hasMany(Commentaire::class);
	}
}
