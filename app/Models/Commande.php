<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Commande
 * 
 * @property int $id
 * @property int $users_id
 * @property string|null $status
 * @property string|null $numero
 * @property float|null $total
 * @property string|null $devise
 * @property Carbon|null $date
 * 
 * @property User $user
 * @property Collection|ArticleCmd[] $article_cmds
 *
 * @package App\Models
 */
class Commande extends Model
{
	protected $table = 'commande';
	public $timestamps = false;

	protected $casts = [
		'users_id' => 'int',
		'total' => 'float'
	];

	protected $dates = [
		'date'
	];

	protected $fillable = [
		'users_id',
		'status',
		'numero',
		'total',
		'devise',
		'date'
	];

	public function user()
	{
		return $this->belongsTo(User::class, 'users_id');
	}

	public function article_cmds()
	{
		return $this->hasMany(ArticleCmd::class);
	}
}
