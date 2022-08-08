<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Entreprise
 * 
 * @property int $id
 * @property string|null $entreprise
 * @property string|null $adresse
 * @property string|null $logo
 * @property string|null $telephone
 * @property string|null $email
 * @property int $users_id
 * 
 * @property User $user
 * @property Collection|CategorieArticle[] $categorie_articles
 *
 * @package App\Models
 */
class Entreprise extends Model
{
	protected $table = 'entreprise';
	public $timestamps = false;

	protected $casts = [
		'users_id' => 'int'
	];

	protected $fillable = [
		'entreprise',
		'adresse',
		'logo',
		'telephone',
		'email',
		'users_id'
	];

	public function user()
	{
		return $this->belongsTo(User::class, 'users_id');
	}

	public function categorie_articles()
	{
		return $this->hasMany(CategorieArticle::class);
	}
}
