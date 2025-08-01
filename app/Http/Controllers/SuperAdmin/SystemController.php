<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class SystemController extends Controller
{
    /**
     * Show the system settings page.
     */
    public function settings()
    {
        $this->authorize('manage_system_settings');

        $settings = [
            'app_name' => config('app.name'),
            'app_url' => config('app.url'),
            'maintenance_mode' => app()->isDownForMaintenance(),
            'debug_mode' => config('app.debug'),
            'cache_driver' => config('cache.default'),
            'queue_driver' => config('queue.default'),
            'mail_driver' => config('mail.default'),
            'database_driver' => config('database.default'),
            'timezone' => config('app.timezone'),
            'locale' => config('app.locale'),
            'default_currency' => config('app.currency', 'USD'),
            'max_companies' => config('app.max_companies', 10),
            'leave_request_expiry_days' => config('app.leave_request_expiry_days', 30),
        ];

        $systemInfo = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'max_execution_time' => ini_get('max_execution_time'),
            'memory_limit' => ini_get('memory_limit'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'disk_usage' => $this->getDiskUsage(),
        ];

        return Inertia::render('super-admin/system/Settings', [
            'settings' => $settings,
            'systemInfo' => $systemInfo,
        ]);
    }

    /**
     * Update system settings.
     */
    public function updateSettings(Request $request, string $section)
    {
        $this->authorize('manage_system_settings');

        $request->validate([
            'value' => 'required',
        ]);

        // Here you would typically update environment variables
        // or configuration files based on the section
        
        return redirect()->route('super-admin.system.settings')
            ->with('success', 'Settings updated successfully.');
    }

    /**
     * Display system logs.
     */
    public function logs()
    {
        $this->authorize('manage_system_settings');
        
        $logFile = storage_path('logs/laravel.log');
        $logs = [];
        
        if (File::exists($logFile)) {
            $content = File::get($logFile);
            $lines = explode("\n", $content);
            
            // Get last 100 lines and parse them
            $recentLines = array_slice($lines, -100);
            
            foreach ($recentLines as $line) {
                if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\].*\.(\w+):/', $line, $matches)) {
                    $logs[] = [
                        'timestamp' => Carbon::parse($matches[1]),
                        'level' => strtoupper($matches[2]),
                        'message' => $line,
                    ];
                }
            }
        }

        $logStats = [
            'total_size' => File::exists($logFile) ? File::size($logFile) : 0,
            'last_modified' => File::exists($logFile) ? Carbon::createFromTimestamp(File::lastModified($logFile)) : null,
            'total_lines' => File::exists($logFile) ? count(file($logFile)) : 0,
        ];

        return Inertia::render('super-admin/system/Logs', [
            'logs' => array_reverse($logs), // Show newest first
            'logStats' => $logStats,
        ]);
    }

    /**
     * Download log file.
     */
    public function downloadLogs()
    {
        $this->authorize('manage_system_settings');
        
        $logFile = storage_path('logs/laravel.log');
        
        if (!File::exists($logFile)) {
            return redirect()->route('super-admin.system.logs')
                ->with('error', 'Log file not found.');
        }

        return response()->download($logFile, 'laravel-' . now()->format('Y-m-d') . '.log');
    }

    /**
     * Clear application cache.
     */
    public function clearCache()
    {
        $this->authorize('manage_system_settings');
        
        try {
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');
            
            return redirect()->route('super-admin.system.settings')
                ->with('success', 'Cache cleared successfully.');
        } catch (\Exception $e) {
            return redirect()->route('super-admin.system.settings')
                ->with('error', 'Failed to clear cache: ' . $e->getMessage());
        }
    }

    /**
     * Create database backup.
     */
    public function createBackup()
    {
        $this->authorize('manage_system_settings');
        
        try {
            $filename = 'backup-' . now()->format('Y-m-d-H-i-s') . '.sql';
            
            // For SQLite, we can simply copy the database file
            if (config('database.default') === 'sqlite') {
                $dbPath = database_path('database.sqlite');
                $backupPath = storage_path('app/backups/' . $filename);
                
                // Create backups directory if it doesn't exist
                if (!File::exists(dirname($backupPath))) {
                    File::makeDirectory(dirname($backupPath), 0755, true);
                }
                
                File::copy($dbPath, $backupPath);
            } else {
                // For other database types, you would use mysqldump or similar
                Artisan::call('backup:run');
            }
            
            return redirect()->route('super-admin.system.settings')
                ->with('success', 'Database backup created successfully.');
        } catch (\Exception $e) {
            return redirect()->route('super-admin.system.settings')
                ->with('error', 'Failed to create backup: ' . $e->getMessage());
        }
    }

    /**
     * Toggle maintenance mode.
     */
    public function toggleMaintenance()
    {
        $this->authorize('manage_system_settings');
        
        try {
            if (app()->isDownForMaintenance()) {
                Artisan::call('up');
                $message = 'Maintenance mode disabled.';
            } else {
                Artisan::call('down', ['--secret' => 'super-admin-secret']);
                $message = 'Maintenance mode enabled.';
            }
            
            return redirect()->route('super-admin.system.settings')
                ->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->route('super-admin.system.settings')
                ->with('error', 'Failed to toggle maintenance mode: ' . $e->getMessage());
        }
    }

    /**
     * Get disk usage information.
     */
    private function getDiskUsage(): array
    {
        $totalSpace = disk_total_space(base_path());
        $freeSpace = disk_free_space(base_path());
        $usedSpace = $totalSpace - $freeSpace;

        return [
            'total' => $this->formatBytes($totalSpace),
            'used' => $this->formatBytes($usedSpace),
            'free' => $this->formatBytes($freeSpace),
            'percentage' => round(($usedSpace / $totalSpace) * 100, 2),
        ];
    }

    /**
     * Format bytes to human readable format.
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
