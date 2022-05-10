<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Compte
 * 
 * @property int $id
 * @property int $users_id
 * @property string|null $numero_compte
 * 
 * @property User $user
 * @property Collection|Approvisionnement[] $approvisionnements
 * @property Collection|Transfert[] $transferts
 *
 * @package App\Models
 */
class Compte extends Model
{
	protected $table = 'compte';
	public $timestamps = false;

	protected $casts = [
		'users_id' => 'int'
	];

	protected $fillable = [
		'users_id',
		'numero_compte'
	];

	public function user()
	{
		return $this->belongsTo(User::class, 'users_id');
	}

	public function approvisionnements()
	{
		return $this->hasMany(Approvisionnement::class);
	}

	public function transferts()
	{
		return $this->hasMany(Transfert::class);
	}
}
