<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CategorieArticle
 * 
 * @property int $id
 * @property string|null $categorie
 * @property string|null $image
 * @property int $entreprise_id
 * 
 * @property Entreprise $entreprise
 * @property Collection|Article[] $articles
 *
 * @package App\Models
 */
class CategorieArticle extends Model
{
	protected $table = 'categorie_article';
	public $timestamps = false;

	protected $casts = [
		'entreprise_id' => 'int'
	];

	protected $fillable = [
		'categorie',
		'image',
		'entreprise_id'
	];

	public function entreprise()
	{
		return $this->belongsTo(Entreprise::class);
	}

	public function articles()
	{
		return $this->hasMany(Article::class);
	}
}
