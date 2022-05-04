<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Devise
 * 
 * @property int $id
 * @property string|null $devise
 * 
 * @property Collection|Article[] $articles
 *
 * @package App\Models
 */
class Devise extends Model
{
	protected $table = 'devise';
	public $timestamps = false;

	protected $fillable = [
		'devise'
	];

	public function articles()
	{
		return $this->hasMany(Article::class);
	}
}
