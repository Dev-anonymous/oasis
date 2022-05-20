<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\CategorieArticle;
use App\Models\Commentaire;
use App\Models\Compte;
use App\Models\Devise;
use App\Models\Operateur;
use App\Models\Publication;
use App\Models\Solde;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AppSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (['Mastercard', 'Visa', 'M-Pesa', 'Airtel Money', 'Orange Money'] as $e) {
            if (!Operateur::where('operateur', $e)->first()) {
                Operateur::create(['operateur' => $e]);
            }
        }

        $faker = Faker::create('fr_FR');
        foreach (['CDF', 'USD'] as $e) {
            if (!Devise::where('devise', $e)->first()) {
                Devise::create(['devise' => $e]);
            }
        }

        foreach (range(1, 3) as $i) {
            DB::transaction(function () {
                $faker = Faker::create('fr_FR');

                $user = new User();
                $user->name = $faker->name();
                $user->phone = $faker->e164PhoneNumber();
                $user->email = $faker->email();
                $user->password = '$2y$10$1xF4empii1JvxtLZSzYQ6eFz2y.xhuUetX8pjWN5f/kH9XoYePTfO'; // 123456
                $user->avatar = '';
                $user->save();

                $cmpt =  Compte::create([
                    'users_id' => $user->id,
                    'numero_compte' => numeroCompte()
                ]);
                $dev = Devise::all();
                foreach ($dev as $d) {
                    Solde::create(['montant' => 0, 'devise_id' => $d->id, 'compte_id' => $cmpt->id]);
                }

                foreach (range(1, 50) as $c) {
                    $publication = new Publication();
                    $publication->users_id = $user->id;
                    $publication->contenu = $faker->text(600);
                    $publication->fichier = 'tmp/' . rand(1, 20) . ".jpg";
                    $publication->save();

                    foreach (range(1, 50) as $art) {
                        $commentaire = new Commentaire();
                        $commentaire->publication_id = $publication->id;
                        $commentaire->users_id = $user->id;
                        $commentaire->contenu = $faker->text(120);
                        $commentaire->save();
                    }
                }

                foreach (range(1, 3) as $c) {
                    $categorie = new CategorieArticle();
                    $categorie->users_id = $user->id;
                    $categorie->categorie = $faker->text(10);
                    $categorie->image = 'tmp/' . rand(1, 20) . ".jpg";
                    $categorie->save();

                    foreach (range(1, 50) as $art) {
                        $article = new Article();
                        $article->devise_id = rand(1, 2);
                        $article->prix = rand(100, 900);
                        $article->categorie_article_id = $categorie->id;
                        $article->article = $faker->text(15);
                        $article->description = $faker->text(100);
                        $article->image = 'tmp/' . rand(1, 20) . ".jpg";
                        $article->save();
                    }
                }
            });
        }
    }
}
