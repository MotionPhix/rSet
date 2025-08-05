<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;

class HolidayController extends Controller
{
    /**
     * Display a listing of holidays.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $year = (int) $request->get('year', now()->year);
        
        // Get holiday instances for the year (including recurring)
        $holidayInstances = Holiday::getHolidaysForYear($year, $user->company_id);
        
        // Sort by date
        usort($holidayInstances, function ($a, $b) {
            return strcmp($a['date'], $b['date']);
        });

        return Inertia::render('admin/holidays/Index', [
            'holidays' => $holidayInstances,
            'currentYear' => $year,
            'years' => range(now()->year - 2, now()->year + 5),
        ]);
    }

    /**
     * Get holidays for calendar display.
     */
    public function getHolidaysForCalendar(Request $request)
    {
        $user = Auth::user();
        $year = (int) $request->get('year', now()->year);
        $month = $request->get('month'); // Optional month filter
        
        $holidayInstances = Holiday::getHolidaysForYear($year, $user->company_id);
        
        // Filter by month if specified
        if ($month) {
            $holidayInstances = array_filter($holidayInstances, function ($holiday) use ($month) {
                return (int) date('m', strtotime($holiday['date'])) === (int) $month;
            });
        }
        
        // Transform to calendar event format
        $calendarEvents = array_map(function ($holiday) {
            return [
                'id' => $holiday['id'],
                'title' => $holiday['name'],
                'start' => $holiday['date'],
                'end' => $holiday['date'],
                'allDay' => true,
                'color' => $holiday['color'],
                'backgroundColor' => $holiday['color'] . '20', // Add transparency
                'borderColor' => $holiday['color'],
                'isHoliday' => true,
                'extendedProps' => [
                    'description' => $holiday['description'],
                    'isRecurring' => $holiday['is_recurring'],
                    'recurrenceType' => $holiday['recurrence_type'],
                    'createdBy' => $holiday['creator']['name'] ?? 'System',
                ],
            ];
        }, $holidayInstances);
        
        return response()->json($calendarEvents);
    }

    /**
     * Store a newly created holiday.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'date' => 'required|date',
            'is_recurring' => 'boolean',
            'color' => 'required|string|regex:/^#[a-fA-F0-9]{6}$/',
        ]);

        $user = Auth::user();
        $requestDate = new \DateTime($validated['date']);

        // Check for conflicts with existing holidays
        if (Holiday::conflictsWithHoliday($requestDate, $user->company_id)) {
            return back()->withErrors(['date' => 'A holiday already exists on this date.']);
        }

        // Create the holiday
        $holiday = Holiday::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'date' => $validated['date'],
            'is_recurring' => $validated['is_recurring'] ?? false,
            'recurrence_type' => ($validated['is_recurring'] ?? false) ? 'yearly' : 'none',
            'color' => $validated['color'],
            'company_id' => $user->company_id,
            'created_by' => $user->id,
            'is_active' => true,
        ]);

        return redirect()->route('admin.holidays.index')
            ->with('success', 'Holiday created successfully!' . 
                   ($holiday->is_recurring ? ' This holiday will repeat yearly.' : ''));
    }

    /**
     * Update the specified holiday.
     */
    public function update(Request $request, Holiday $holiday)
    {
        // Ensure user can only update holidays in their company
        if ($holiday->company_id !== Auth::user()->company_id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'date' => 'required|date',
            'is_recurring' => 'boolean',
            'recurrence_type' => 'in:yearly,none',
            'color' => 'required|string|regex:/^#[a-fA-F0-9]{6}$/',
            'is_active' => 'boolean',
        ]);

        // Check for existing holiday on the same date (excluding current)
        $existingHoliday = Holiday::where('company_id', Auth::user()->company_id)
            ->where('date', $validated['date'])
            ->where('id', '!=', $holiday->id)
            ->first();

        if ($existingHoliday) {
            return back()->withErrors(['date' => 'A holiday already exists on this date.']);
        }

        $holiday->update([
            ...$validated,
            'recurrence_type' => $validated['is_recurring'] ? 'yearly' : 'none',
        ]);

        return redirect()->route('admin.holidays.index')
            ->with('success', 'Holiday updated successfully!');
    }

    /**
     * Remove the specified holiday.
     */
    public function destroy(Holiday $holiday)
    {
        // Ensure user can only delete holidays in their company
        if ($holiday->company_id !== Auth::user()->company_id) {
            abort(403);
        }

        $holiday->delete();

        return redirect()->route('admin.holidays.index')
            ->with('success', 'Holiday deleted successfully!');
    }
}
