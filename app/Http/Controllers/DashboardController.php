<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Get all active menus
        $menus = Menu::where('is_active', true)->orderBy('order')->get();

        // Summary cards
        $summaryCards = $this->getSummaryCards($menus);

        // Charts data
        $chartsData = $this->getChartsData($menus);

        // Recent activity (last 10 records across all tables)
        $recentActivity = $this->getRecentActivity($menus);

        // Quick stats per table
        $tableStats = $this->getTableStats($menus);

        return view('dashboard', [
            'summaryCards' => $summaryCards,
            'chartsData' => $chartsData,
            'recentActivity' => $recentActivity,
            'tableStats' => $tableStats,
            'menus' => $menus,
        ]);
    }

    /**
     * Get summary cards data
     */
    private function getSummaryCards($menus)
    {
        $cards = [];

        // Total Users
        $totalUsers = User::count();
        $cards[] = [
            'title' => 'Total Users',
            'value' => number_format($totalUsers),
            'icon' => 'bi-people',
            'color' => 'primary',
            'change' => null,
        ];

        // Get first 3 menus for summary cards
        $topMenus = $menus->take(3);

        foreach ($topMenus as $menu) {
            try {
                $count = DB::table($menu->table_name)->count();

                // Calculate change (compare with last week)
                $lastWeekCount = DB::table($menu->table_name)
                    ->where('created_at', '<=', now()->subWeek())
                    ->count();

                $change = null;
                if ($lastWeekCount > 0) {
                    $percentChange = (($count - $lastWeekCount) / $lastWeekCount) * 100;
                    $change = [
                        'value' => abs(round($percentChange, 1)),
                        'direction' => $percentChange >= 0 ? 'up' : 'down',
                    ];
                }

                $cards[] = [
                    'title' => $menu->name,
                    'value' => number_format($count),
                    'icon' => $menu->icon ?? 'bi-table',
                    'color' => $this->getCardColor(count($cards)),
                    'change' => $change,
                    'menu_id' => $menu->id,
                ];
            } catch (\Exception $e) {
                // Skip if table doesn't have created_at or other issues
                continue;
            }
        }

        return $cards;
    }

    /**
     * Get charts data
     */
    private function getChartsData($menus)
    {
        $charts = [];

        // Line chart: Records created over last 6 months
        $lineChartData = $this->getRecordsOverTime($menus->first());
        if ($lineChartData) {
            $charts['line'] = $lineChartData;
        }

        // Bar chart: Records count by table
        $barChartData = $this->getRecordsByTable($menus);
        if ($barChartData) {
            $charts['bar'] = $barChartData;
        }

        // Pie chart: Status distribution (if any table has is_active field)
        $pieChartData = $this->getStatusDistribution($menus);
        if ($pieChartData) {
            $charts['pie'] = $pieChartData;
        }

        return $charts;
    }

    /**
     * Get records created over time (last 6 months)
     */
    private function getRecordsOverTime($menu)
    {
        if (!$menu) return null;

        try {
            $months = [];
            $counts = [];

            for ($i = 5; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $monthStart = $date->startOfMonth()->toDateString();
                $monthEnd = $date->endOfMonth()->toDateString();

                $count = DB::table($menu->table_name)
                    ->whereBetween('created_at', [$monthStart, $monthEnd])
                    ->count();

                $months[] = $date->format('M Y');
                $counts[] = $count;
            }

            return [
                'title' => $menu->name . ' - Last 6 Months',
                'labels' => $months,
                'data' => $counts,
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get records count by table
     */
    private function getRecordsByTable($menus)
    {
        $labels = [];
        $data = [];

        foreach ($menus->take(5) as $menu) {
            try {
                $count = DB::table($menu->table_name)->count();
                $labels[] = $menu->name;
                $data[] = $count;
            } catch (\Exception $e) {
                continue;
            }
        }

        if (empty($labels)) return null;

        return [
            'title' => 'Records by Table',
            'labels' => $labels,
            'data' => $data,
        ];
    }

    /**
     * Get status distribution (active/inactive)
     */
    private function getStatusDistribution($menus)
    {
        // Find first table with is_active field
        foreach ($menus as $menu) {
            $fields = $menu->getFieldDefinitions();
            $hasActiveField = collect($fields)->contains('name', 'is_active');

            if ($hasActiveField) {
                try {
                    $active = DB::table($menu->table_name)->where('is_active', 1)->count();
                    $inactive = DB::table($menu->table_name)->where('is_active', 0)->count();

                    if ($active + $inactive > 0) {
                        return [
                            'title' => $menu->name . ' - Status Distribution',
                            'labels' => ['Active', 'Inactive'],
                            'data' => [$active, $inactive],
                        ];
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }
        }

        return null;
    }

    /**
     * Get recent activity across all tables
     */
    private function getRecentActivity($menus)
    {
        $activities = [];

        foreach ($menus->take(5) as $menu) {
            try {
                $records = DB::table($menu->table_name)
                    ->orderBy('created_at', 'desc')
                    ->limit(3)
                    ->get();

                foreach ($records as $record) {
                    $recordArray = (array) $record;

                    // Get display value (first non-system field)
                    $displayValue = 'Record #' . $recordArray['id'];
                    $fields = $menu->getFieldDefinitions();
                    foreach ($fields as $field) {
                        if (!in_array($field['name'], ['id', 'created_at', 'updated_at']) && isset($recordArray[$field['name']])) {
                            $displayValue = $recordArray[$field['name']];
                            break;
                        }
                    }

                    $activities[] = [
                        'menu' => $menu->name,
                        'menu_id' => $menu->id,
                        'record_id' => $recordArray['id'],
                        'display' => $displayValue,
                        'created_at' => $recordArray['created_at'] ?? null,
                        'icon' => $menu->icon ?? 'bi-table',
                    ];
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        // Sort by created_at and take 10
        usort($activities, function ($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        return array_slice($activities, 0, 10);
    }

    /**
     * Get quick stats per table
     */
    private function getTableStats($menus)
    {
        $stats = [];

        foreach ($menus as $menu) {
            try {
                $total = DB::table($menu->table_name)->count();

                // Count created today
                $today = DB::table($menu->table_name)
                    ->whereDate('created_at', today())
                    ->count();

                // Count created this week
                $thisWeek = DB::table($menu->table_name)
                    ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                    ->count();

                $stats[] = [
                    'menu' => $menu->name,
                    'menu_id' => $menu->id,
                    'icon' => $menu->icon ?? 'bi-table',
                    'total' => $total,
                    'today' => $today,
                    'this_week' => $thisWeek,
                ];
            } catch (\Exception $e) {
                // If table doesn't have created_at, just show total
                try {
                    $total = DB::table($menu->table_name)->count();
                    $stats[] = [
                        'menu' => $menu->name,
                        'menu_id' => $menu->id,
                        'icon' => $menu->icon ?? 'bi-table',
                        'total' => $total,
                        'today' => 0,
                        'this_week' => 0,
                    ];
                } catch (\Exception $e2) {
                    continue;
                }
            }
        }

        return $stats;
    }

    /**
     * Get card color based on index
     */
    private function getCardColor($index)
    {
        $colors = ['primary', 'success', 'info', 'warning'];
        return $colors[$index % count($colors)];
    }
}
