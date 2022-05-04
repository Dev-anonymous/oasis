<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Panier
 * 
 * @property int $id
 * @property int $article_id
 * @property int $users_id
 * @property int|null $qte
 * @property Carbon|null $date
 * 
 * @property Article $article
 * @property User $user
 *
 * @package App\Models
 */
class Panier extends Model
{
	protected $table = 'panier';
	public $timestamps = false;

	protected $casts = [
		'article_id' => 'int',
		'users_id' => 'int',
		'qte' => 'int'
	];

	protected $dates = [
		'date'
	];

	protected $fillable = [
		'article_id',
		'users_id',
		'qte',
		'date'
	];

	public function article()
	{
		return $this->belongsTo(Article::class);
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'users_id');
	}
}
