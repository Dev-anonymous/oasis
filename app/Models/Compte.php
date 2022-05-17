<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Compte
 *
 * @property int $id
 * @property string|null $numero_compte
 * @property int $users_id
 *
 * @property User $user
 * @property Collection|Solde[] $soldes
 * @property Collection|Transaction[] $transactions
 *
 * @package App\Models
 */
class Compte extends Model
{
    protected $table = 'compte';
    public $timestamps = false;

    protected $casts = [
        'users_id' => 'int'
    ];

    protected $fillable = [
        'numero_compte',
        'users_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    public function soldes()
    {
        return $this->hasMany(Solde::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
