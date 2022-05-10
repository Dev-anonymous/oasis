<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Transfert
 * 
 * @property int $id
 * @property int $compte_id
 * @property int $devise_id
 * @property string|null $numero_compte_destination
 * @property float|null $montant
 * @property Carbon|null $date
 * 
 * @property Compte $compte
 * @property Devise $devise
 *
 * @package App\Models
 */
class Transfert extends Model
{
	protected $table = 'transfert';
	public $timestamps = false;

	protected $casts = [
		'compte_id' => 'int',
		'devise_id' => 'int',
		'montant' => 'float'
	];

	protected $dates = [
		'date'
	];

	protected $fillable = [
		'compte_id',
		'devise_id',
		'numero_compte_destination',
		'montant',
		'date'
	];

	public function compte()
	{
		return $this->belongsTo(Compte::class);
	}

	public function devise()
	{
		return $this->belongsTo(Devise::class);
	}
}
