<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * Class User
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $derniere_connexion
 * @property string|null $phone
 * @property string|null $avatar
 *
 * @property Collection|CategorieArticle[] $categorie_articles
 * @property Collection|Chat[] $chats
 * @property Collection|Commande[] $commandes
 * @property Collection|Commentaire[] $commentaires
 * @property Collection|Compte[] $comptes
 * @property Collection|Panier[] $paniers
 * @property Collection|Publication[] $publications
 *
 * @package App\Models
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';

    protected $dates = [
        'email_verified_at',
        'derniere_connexion'
    ];

    protected $hidden = [
        'password',
        'remember_token'
    ];

    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'remember_token',
        'derniere_connexion',
        'phone',
        'avatar'
    ];

    public function categorie_articles()
    {
        return $this->hasMany(CategorieArticle::class, 'users_id');
    }

    public function chats()
    {
        return $this->hasMany(Chat::class, 'users_id');
    }

    public function commandes()
    {
        return $this->hasMany(Commande::class, 'users_id');
    }

    public function commentaires()
    {
        return $this->hasMany(Commentaire::class, 'users_id');
    }

    public function comptes()
    {
        return $this->hasMany(Compte::class, 'users_id');
    }

    public function paniers()
    {
        return $this->hasMany(Panier::class, 'users_id');
    }

    public function publications()
    {
        return $this->hasMany(Publication::class, 'users_id');
    }
}
