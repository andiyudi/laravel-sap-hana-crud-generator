<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use App\Models\Menu;
use App\Models\User;

class ActivityLogController extends Controller
{
    /**
     * Display activity log page
     */
    public function index(Request $request)
    {
        $query = Activity::with(['causer', 'subject'])
            ->orderBy('created_at', 'desc');

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('causer_id', $request->user_id);
        }

        // Filter by action
        if ($request->filled('action')) {
            $query->where('description', $request->action);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search in description
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('description', 'like', "%{$search}%");
        }

        // Get activities
        $activities = $query->paginate(20);

        // Filter by table/menu if specified (done in PHP since HANA doesn't support JSON operations)
        if ($request->filled('menu_id')) {
            $menu = Menu::find($request->menu_id);
            if ($menu) {
                $activities->getCollection()->transform(function ($activity) use ($menu) {
                    $props = $activity->properties;
                    if (!isset($props['table']) || $props['table'] !== $menu->table_name) {
                        return null;
                    }
                    return $activity;
                })->filter()->values();
            }
        }

        // Get users for filter
        $users = User::orderBy('name')->get();

        // Get menus for filter
        $menus = Menu::orderBy('name')->get();

        return view('activity-log.index', [
            'activities' => $activities,
            'users' => $users,
            'menus' => $menus,
        ]);
    }

    /**
     * Get activity log for specific record
     */
    public function show($subjectType, $subjectId)
    {
        $activities = Activity::where('subject_type', $subjectType)
            ->where('subject_id', $subjectId)
            ->with('causer')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('activity-log.show', [
            'activities' => $activities,
        ]);
    }
}
