<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Article
 * 
 * @property int $id
 * @property int $devise_id
 * @property int $categorie_article_id
 * @property string|null $article
 * @property string|null $description
 * @property float|null $prix
 * @property Carbon|null $date
 * @property string|null $image
 * 
 * @property CategorieArticle $categorie_article
 * @property Devise $devise
 * @property Collection|Panier[] $paniers
 *
 * @package App\Models
 */
class Article extends Model
{
	protected $table = 'article';
	public $timestamps = false;

	protected $casts = [
		'devise_id' => 'int',
		'categorie_article_id' => 'int',
		'prix' => 'float'
	];

	protected $dates = [
		'date'
	];

	protected $fillable = [
		'devise_id',
		'categorie_article_id',
		'article',
		'description',
		'prix',
		'date',
		'image'
	];

	public function categorie_article()
	{
		return $this->belongsTo(CategorieArticle::class);
	}

	public function devise()
	{
		return $this->belongsTo(Devise::class);
	}

	public function paniers()
	{
		return $this->hasMany(Panier::class);
	}
}
