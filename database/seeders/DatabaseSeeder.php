<?php

namespace Database\Seeders;

use App\Models\Utility;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(NotificationSeeder::class);
        $this->call(UsersTableSeeder::class);
        Artisan::call('module:migrate LandingPage');
        Artisan::call('module:seed LandingPage');

        $route = \Request::route();
if ($route && $route->getName() !== 'LaravelUpdater::database') {
    $this->call(UsersTableSeeder::class);
    $this->call(AiTemplateSeeder::class);
}
else{
            Utility::languagecreate();

        }

    }
}
