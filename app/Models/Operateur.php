<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Operateur
 * 
 * @property int $id
 * @property string|null $operateur
 * @property string|null $image
 * 
 * @property Collection|Transaction[] $transactions
 *
 * @package App\Models
 */
class Operateur extends Model
{
	protected $table = 'operateur';
	public $timestamps = false;

	protected $fillable = [
		'operateur',
		'image'
	];

	public function transactions()
	{
		return $this->hasMany(Transaction::class);
	}
}
