<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ArticleCmd
 * 
 * @property int $id
 * @property int $commande_id
 * @property int|null $article_id
 * @property int|null $boutique_id
 * @property string|null $article
 * @property int|null $qte
 * @property float|null $prix
 * @property string|null $devise
 * @property string|null $image
 * 
 * @property Commande $commande
 *
 * @package App\Models
 */
class ArticleCmd extends Model
{
	protected $table = 'article_cmd';
	public $timestamps = false;

	protected $casts = [
		'commande_id' => 'int',
		'article_id' => 'int',
		'boutique_id' => 'int',
		'qte' => 'int',
		'prix' => 'float'
	];

	protected $fillable = [
		'commande_id',
		'article_id',
		'boutique_id',
		'article',
		'qte',
		'prix',
		'devise',
		'image'
	];

	public function commande()
	{
		return $this->belongsTo(Commande::class);
	}
}
