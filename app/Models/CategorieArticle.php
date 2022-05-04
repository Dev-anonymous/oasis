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
 * @property int $users_id
 * 
 * @property User $user
 * @property Collection|Article[] $articles
 *
 * @package App\Models
 */
class CategorieArticle extends Model
{
	protected $table = 'categorie_article';
	public $timestamps = false;

	protected $casts = [
		'users_id' => 'int'
	];

	protected $fillable = [
		'categorie',
		'image',
		'users_id'
	];

	public function user()
	{
		return $this->belongsTo(User::class, 'users_id');
	}

	public function articles()
	{
		return $this->hasMany(Article::class);
	}
}
