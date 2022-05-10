<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Approvisionnement
 * 
 * @property int $id
 * @property int $compte_id
 * @property int $operateur_id
 * @property int $devise_id
 * @property float|null $montant
 * @property string|null $source
 * @property Carbon|null $date
 * 
 * @property Compte $compte
 * @property Devise $devise
 * @property Operateur $operateur
 *
 * @package App\Models
 */
class Approvisionnement extends Model
{
	protected $table = 'approvisionnement';
	public $timestamps = false;

	protected $casts = [
		'compte_id' => 'int',
		'operateur_id' => 'int',
		'devise_id' => 'int',
		'montant' => 'float'
	];

	protected $dates = [
		'date'
	];

	protected $fillable = [
		'compte_id',
		'operateur_id',
		'devise_id',
		'montant',
		'source',
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

	public function operateur()
	{
		return $this->belongsTo(Operateur::class);
	}
}
