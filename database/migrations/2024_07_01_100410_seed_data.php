<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $adminUsername = env('ADMIN_USERNAME');
        $adminEmail = env('ADMIN_EMAIL');
        $adminPassword = env('ADMIN_PASSWORD');

        if (empty($adminUsername) || empty($adminEmail) || empty($adminPassword)) {
            throw new \Exception('Please set ADMIN_USERNAME, ADMIN_EMAIL, and ADMIN_PASSWORD in your .env file');
        }

        \Illuminate\Support\Facades\Artisan::call(
            'db:seed --class=DatabaseSeeder --force'
        );

        \Illuminate\Support\Facades\Artisan::call(
            "make:filament-user --name=$adminUsername --email=$adminEmail --password=$adminPassword --quiet"
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
