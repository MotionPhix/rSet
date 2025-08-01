<?php

namespace Database\Seeders;

use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Database\Seeder;

class ShowDemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🎯 Demo Data Overview');
        $this->command->info('===================');
        $this->command->info('');

        // Show Charlie's leave requests (our demo employee)
        $charlie = User::where('email', 'charlie@democompany.com')->first();
        
        if ($charlie) {
            $this->command->info("📋 Leave Requests for {$charlie->name} ({$charlie->email}):");
            $this->command->info('-----------------------------------------------------------');
            
            $requests = LeaveRequest::where('user_id', $charlie->id)
                ->orderBy('start_date', 'desc')
                ->get();

            foreach ($requests as $request) {
                $statusIcon = match($request->status) {
                    'approved' => '✅',
                    'rejected' => '❌',
                    'pending' => '⏳',
                    default => '📄'
                };

                $days = \Carbon\Carbon::parse($request->start_date)
                    ->diffInDays(\Carbon\Carbon::parse($request->end_date)) + 1;

                $this->command->info(sprintf(
                    '%s %s %s | %s to %s (%d days) | %s',
                    $statusIcon,
                    $request->status,
                    $request->type,
                    \Carbon\Carbon::parse($request->start_date)->format('M j, Y'),
                    \Carbon\Carbon::parse($request->end_date)->format('M j, Y'),
                    $days,
                    \Str::limit($request->reason, 50)
                ));
            }
        }

        $this->command->info('');
        $this->command->info('🎮 How to Test:');
        $this->command->info('1. Start your development server: php artisan serve');
        $this->command->info('2. Visit the application in your browser');
        $this->command->info('3. Login with: charlie@democompany.com / password');
        $this->command->info('4. Navigate to Leave Requests to see the sample data');
        $this->command->info('5. Try creating a new leave request');
        $this->command->info('6. Check the balance calculations and validation');
        $this->command->info('');
        $this->command->info('💡 Pro tip: You can also login as manager or admin to test approval workflows!');
    }
}
