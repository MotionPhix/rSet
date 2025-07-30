<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;

class TestNavigation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-navigation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test that all navigation routes exist';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Testing Navigation Routes');
        $this->info('============================');
        $this->newLine();

        // Routes that should exist for navigation
        $requiredRoutes = [
            // Main navigation
            'dashboard',
            'admin.dashboard',
            'leave-requests.index',
            'leave-requests.create',
            'leave-requests.show',
            'leave-types.index',

            // Admin routes
            'admin.users.index',
            'admin.users.create',
            'admin.users.store',
            'admin.teams.index',
            'admin.teams.create',
            'admin.teams.store',
            'admin.leave-types.index',
            'admin.leave-types.create',
            'admin.leave-types.store',
            'admin.company.profile',
            'admin.company.employees',
            'admin.reports.index',
            'admin.analytics.index',

            // Profile routes
            'profile.show',
            'profile.edit',
            'profile.update',

            // Company routes
            'company.setup',
            'company.store',
            'company.update',
        ];

        $missing = [];
        $existing = [];

        foreach ($requiredRoutes as $routeName) {
            if (Route::has($routeName)) {
                $existing[] = $routeName;
                $this->line("✅ {$routeName}");
            } else {
                $missing[] = $routeName;
                $this->error("❌ {$routeName}");
            }
        }

        $this->newLine();
        $this->info("📊 Summary:");
        $this->line("   Existing routes: " . count($existing));
        $this->line("   Missing routes: " . count($missing));

        if (count($missing) > 0) {
            $this->newLine();
            $this->error("⚠️  Missing routes that need to be added:");
            foreach ($missing as $route) {
                $this->line("   • {$route}");
            }
            return 1;
        }

        $this->newLine();
        $this->info("🎉 All navigation routes exist!");
        return 0;
    }
}
