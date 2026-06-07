<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Self-healing symlink for public/storage
        $storagePath = public_path('storage');
        
        // Check if symlink is broken
        if (is_link($storagePath) && !file_exists($storagePath)) {
            @unlink($storagePath);
        }
        
        // Recreate symlink if it doesn't exist
        if (!file_exists($storagePath) && !is_link($storagePath)) {
            try {
                \Illuminate\Support\Facades\Artisan::call('storage:link');
            } catch (\Exception $e) {
                // Fail silently if permissions prevent link creation
            }
        }
    }
}
