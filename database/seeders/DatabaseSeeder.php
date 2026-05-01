<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\Farmer;
use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Utilisateurs
        User::create(['name' => 'Admin', 'email' => 'admin@test.com', 'password' => Hash::make('123456'), 'role' => 'admin']);
        User::create(['name' => 'Superviseur 1', 'email' => 'supervisor@test.com', 'password' => Hash::make('123456'), 'role' => 'supervisor']);
        User::create(['name' => 'Operateur 1', 'email' => 'operator@test.com', 'password' => Hash::make('123456'), 'role' => 'operator', 'supervisor_id' => 2]);

        // Catégories
        $pesticides = Category::create(['name' => 'Pesticides', 'parent_id' => null, 'level' => 1]);
        $engrais = Category::create(['name' => 'Engrais', 'parent_id' => null, 'level' => 1]);
        $semences = Category::create(['name' => 'Semences', 'parent_id' => null, 'level' => 1]);

        $herbicides = Category::create(['name' => 'Herbicides', 'parent_id' => $pesticides->id, 'level' => 2]);
        $insecticides = Category::create(['name' => 'Insecticides', 'parent_id' => $pesticides->id, 'level' => 2]);
        $engraisOrga = Category::create(['name' => 'Engrais Organiques', 'parent_id' => $engrais->id, 'level' => 2]);

        // Produits
        Product::create(['name' => 'Roundup', 'category_id' => $herbicides->id, 'price_fcfa' => 5000, 'description' => 'Herbicide total']);
        Product::create(['name' => 'Décis', 'category_id' => $insecticides->id, 'price_fcfa' => 3500, 'description' => 'Insecticide polyvalent']);
        Product::create(['name' => 'Compost Bio', 'category_id' => $engraisOrga->id, 'price_fcfa' => 2000, 'description' => 'Engrais organique naturel']);
        Product::create(['name' => 'Urée', 'category_id' => $engraisOrga->id, 'price_fcfa' => 4500, 'description' => 'Engrais azoté']);

        // Fermiers
        Farmer::create(['identifier' => 'FARM001', 'firstname' => 'Kouame', 'lastname' => 'Koffi', 'phone' => '0708090001', 'credit_limit_fcfa' => 50000]);
        Farmer::create(['identifier' => 'FARM002', 'firstname' => 'Adjoua', 'lastname' => 'Bamba', 'phone' => '0708090002', 'credit_limit_fcfa' => 75000]);
        Farmer::create(['identifier' => 'FARM003', 'firstname' => 'Konan', 'lastname' => 'Yao', 'phone' => '0708090003', 'credit_limit_fcfa' => 30000]);

        // Paramètres
        Setting::create(['key' => 'interest_rate', 'value' => 30]);
        Setting::create(['key' => 'commodity_rate', 'value' => 1000]);
    }
}