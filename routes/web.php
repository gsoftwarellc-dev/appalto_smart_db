<?php

use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\Artisan;

Route::get('/deploy-setup', function () {
    try {
        // Clear caches to ensure .env changes are picked up
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('route:clear');
        
        Artisan::call('migrate:fresh', ['--seed' => true, '--force' => true]);
        return "Database migration and seeding completed successfully! <br><br> <a href='/'>Go to Home</a>";
    } catch (\Exception $e) {
        return "Error during setup: " . $e->getMessage();
    }
});

Route::get('/{any}', function () {
    return file_get_contents(public_path('index.html'));
})->where('any', '.*');
