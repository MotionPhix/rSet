<?php

namespace Tests\Feature\Employee;

use App\Models\User;
use App\Models\Team;
use App\Models\Company;
use App\Models\LeaveRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Tests\TestCase;

class LeaveRequestTest extends TestCase
{
    use RefreshDatabase;

    protected $employee;
    protected $company;
    protected $team;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test company
        $this->company = Company::factory()->create();

        // Create test team
        $this->team = Team::factory()->create([
            'company_id' => $this->company->id,
        ]);

        // Create test employee with sufficient employment history
        $this->employee = User::factory()->create([
            'company_id' => $this->company->id,
            'team_id' => $this->team->id,
            'role' => 'employee',
            'created_at' => Carbon::now()->subMonths(6), // 6 months employment
        ]);

        // Fake storage for file uploads
        Storage::fake('public');
    }

    /** @test */
    public function employee_can_view_create_leave_request_form()
    {
        $response = $this->actingAs($this->employee)
            ->get(route('employee.leave-requests.create'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => 
            $page->component('employee/leave-requests/Create')
                ->has('leaveTypes')
                ->has('userLeaveBalance')
        );
    }

    /** @test */
    public function employee_can_create_valid_leave_request()
    {
        $startDate = Carbon::now()->addDays(7);
        $endDate = Carbon::now()->addDays(9);

        $response = $this->actingAs($this->employee)
            ->post(route('employee.leave-requests.store'), [
                'leave_type' => 'annual',
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'reason' => 'Family vacation trip',
                'documentation' => UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'),
            ]);

        $response->assertRedirect(route('employee.leave-requests.index'));
        $response->assertSessionHas('message', 'Leave request submitted successfully');

        $this->assertDatabaseHas('leave_requests', [
            'user_id' => $this->employee->id,
            'leave_type' => 'annual',
            'reason' => 'Family vacation trip',
            'status' => 'pending',
        ]);

        // Check file was uploaded
        $leaveRequest = LeaveRequest::where('user_id', $this->employee->id)->first();
        $this->assertNotNull($leaveRequest->documentation_path);
        Storage::disk('public')->assertExists($leaveRequest->documentation_path);
    }

    /** @test */
    public function employee_cannot_create_leave_request_with_insufficient_employment()
    {
        // Create employee with only 1 month employment
        $newEmployee = User::factory()->create([
            'company_id' => $this->company->id,
            'team_id' => $this->team->id,
            'role' => 'employee',
            'created_at' => Carbon::now()->subMonth(),
        ]);

        $startDate = Carbon::now()->addDays(7);
        $endDate = Carbon::now()->addDays(9);

        $response = $this->actingAs($newEmployee)
            ->post(route('employee.leave-requests.store'), [
                'leave_type' => 'annual',
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'reason' => 'Test reason',
            ]);

        $response->assertSessionHasErrors(['employment' => 'You must be employed for at least 3 months to request annual leave.']);
    }

    /** @test */
    public function employee_cannot_create_leave_request_with_insufficient_balance()
    {
        // Create some existing leave requests to use up balance
        LeaveRequest::factory()->count(25)->create([
            'user_id' => $this->employee->id,
            'leave_type' => 'annual',
            'status' => 'approved',
            'start_date' => Carbon::now()->subDays(30),
            'end_date' => Carbon::now()->subDays(29),
        ]);

        $startDate = Carbon::now()->addDays(7);
        $endDate = Carbon::now()->addDays(9);

        $response = $this->actingAs($this->employee)
            ->post(route('employee.leave-requests.store'), [
                'leave_type' => 'annual',
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'reason' => 'Test reason',
            ]);

        $response->assertSessionHasErrors(['leave_balance']);
    }

    /** @test */
    public function employee_cannot_create_overlapping_leave_requests()
    {
        // Create existing leave request
        LeaveRequest::factory()->create([
            'user_id' => $this->employee->id,
            'start_date' => Carbon::now()->addDays(8),
            'end_date' => Carbon::now()->addDays(10),
            'status' => 'pending',
        ]);

        // Try to create overlapping request
        $startDate = Carbon::now()->addDays(7);
        $endDate = Carbon::now()->addDays(9);

        $response = $this->actingAs($this->employee)
            ->post(route('employee.leave-requests.store'), [
                'leave_type' => 'annual',
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'reason' => 'Test reason',
            ]);

        $response->assertSessionHasErrors(['dates' => 'You already have a leave request for overlapping dates.']);
    }

    /** @test */
    public function employee_cannot_create_leave_request_exceeding_maximum_duration()
    {
        $startDate = Carbon::now()->addDays(7);
        $endDate = Carbon::now()->addDays(37); // 31 days - exceeds 30 day limit

        $response = $this->actingAs($this->employee)
            ->post(route('employee.leave-requests.store'), [
                'leave_type' => 'annual',
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'reason' => 'Long vacation',
            ]);

        $response->assertSessionHasErrors(['duration' => 'Annual leave cannot exceed 30 days per request.']);
    }

    /** @test */
    public function employee_cannot_create_leave_request_below_minimum_duration()
    {
        $startDate = Carbon::now()->addDays(7);
        $endDate = $startDate; // Same day - 0 days

        $response = $this->actingAs($this->employee)
            ->post(route('employee.leave-requests.store'), [
                'leave_type' => 'annual',
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'reason' => 'Short break',
            ]);

        $response->assertSessionHasErrors(['duration' => 'Annual leave must be at least 1 day.']);
    }

    /** @test */
    public function employee_cannot_create_past_dated_leave_request()
    {
        $startDate = Carbon::now()->subDays(1);
        $endDate = Carbon::now()->addDays(1);

        $response = $this->actingAs($this->employee)
            ->post(route('employee.leave-requests.store'), [
                'leave_type' => 'annual',
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'reason' => 'Past dated request',
            ]);

        $response->assertSessionHasErrors(['start_date']);
    }

    /** @test */
    public function employee_can_view_leave_requests_index()
    {
        // Create some leave requests
        LeaveRequest::factory()->count(3)->create([
            'user_id' => $this->employee->id,
        ]);

        $response = $this->actingAs($this->employee)
            ->get(route('employee.leave-requests.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => 
            $page->component('employee/leave-requests/Index')
                ->has('leaveRequests.data', 3)
        );
    }
}
