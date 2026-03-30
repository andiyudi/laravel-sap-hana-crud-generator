@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="row">
    <div class="col-12">
        <h1 class="mb-4">
            <i class="bi bi-speedometer2 me-2"></i>
            Dashboard
        </h1>
    </div>
</div>

{{-- Summary Cards --}}
<div class="row mb-4">
    @foreach($summaryCards as $card)
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-{{ $card['color'] }} shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-{{ $card['color'] }} text-uppercase mb-1">
                                {{ $card['title'] }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $card['value'] }}
                            </div>
                            @if($card['change'])
                                <div class="mt-2">
                                    <span class="badge bg-{{ $card['change']['direction'] === 'up' ? 'success' : 'danger' }}">
                                        <i class="bi bi-arrow-{{ $card['change']['direction'] }}"></i>
                                        {{ $card['change']['value'] }}%
                                    </span>
                                    <small class="text-muted ms-1">vs last week</small>
                                </div>
                            @endif
                        </div>
                        <div class="col-auto">
                            <i class="bi {{ $card['icon'] }} text-{{ $card['color'] }}" style="font-size: 2rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                    @if(isset($card['menu_id']))
                        <div class="mt-2">
                            <a href="{{ route('dynamic.index', $card['menu_id']) }}" class="btn btn-sm btn-outline-{{ $card['color'] }}">
                                View All <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="row">
    {{-- Charts Column --}}
    <div class="col-lg-8 mb-4">
        {{-- Line Chart --}}
        @if(isset($chartsData['line']))
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-graph-up me-2"></i>
                        {{ $chartsData['line']['title'] }}
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="lineChart" height="80"></canvas>
                </div>
            </div>
        @endif

        {{-- Bar Chart --}}
        @if(isset($chartsData['bar']))
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="bi bi-bar-chart me-2"></i>
                        {{ $chartsData['bar']['title'] }}
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="barChart" height="80"></canvas>
                </div>
            </div>
        @endif
    </div>

    {{-- Sidebar Column --}}
    <div class="col-lg-4 mb-4">
        {{-- Pie Chart --}}
        @if(isset($chartsData['pie']))
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="bi bi-pie-chart me-2"></i>
                        {{ $chartsData['pie']['title'] }}
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="pieChart"></canvas>
                </div>
            </div>
        @endif

        {{-- Recent Activity --}}
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-warning">
                    <i class="bi bi-clock-history me-2"></i>
                    Recent Activity
                </h6>
            </div>
            <div class="card-body">
                @if(!empty($recentActivity))
                    <div class="list-group list-group-flush">
                        @foreach($recentActivity as $activity)
                            <a href="{{ route('dynamic.show', [$activity['menu_id'], $activity['record_id']]) }}"
                               class="list-group-item list-group-item-action px-0">
                                <div class="d-flex align-items-center">
                                    <i class="bi {{ $activity['icon'] }} me-3 text-primary"></i>
                                    <div class="flex-grow-1">
                                        <div class="fw-bold">{{ $activity['menu'] }}</div>
                                        <small class="text-muted">{{ Str::limit($activity['display'], 30) }}</small>
                                    </div>
                                    <small class="text-muted">
                                        {{ $activity['created_at'] ? \Carbon\Carbon::parse($activity['created_at'])->diffForHumans() : '-' }}
                                    </small>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted text-center py-3">No recent activity</p>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Table Stats --}}
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="bi bi-table me-2"></i>
                    Quick Stats
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Table</th>
                                <th class="text-end">Total Records</th>
                                <th class="text-end">Created Today</th>
                                <th class="text-end">Created This Week</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tableStats as $stat)
                                <tr>
                                    <td>
                                        <i class="bi {{ $stat['icon'] }} me-2"></i>
                                        <strong>{{ $stat['menu'] }}</strong>
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-primary">{{ number_format($stat['total']) }}</span>
                                    </td>
                                    <td class="text-end">
                                        @if($stat['today'] > 0)
                                            <span class="badge bg-success">+{{ $stat['today'] }}</span>
                                        @else
                                            <span class="text-muted">0</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if($stat['this_week'] > 0)
                                            <span class="badge bg-info">+{{ $stat['this_week'] }}</span>
                                        @else
                                            <span class="text-muted">0</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('dynamic.index', $stat['menu_id']) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye me-1"></i>
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}
.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}
.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}
.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Line Chart
    @if(isset($chartsData['line']))
    const lineCtx = document.getElementById('lineChart');
    if (lineCtx) {
        new Chart(lineCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($chartsData['line']['labels']) !!},
                datasets: [{
                    label: 'Records Created',
                    data: {!! json_encode($chartsData['line']['data']) !!},
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    }
    @endif

    // Bar Chart
    @if(isset($chartsData['bar']))
    const barCtx = document.getElementById('barChart');
    if (barCtx) {
        new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($chartsData['bar']['labels']) !!},
                datasets: [{
                    label: 'Total Records',
                    data: {!! json_encode($chartsData['bar']['data']) !!},
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(153, 102, 255, 0.8)'
                    ],
                    borderColor: [
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 99, 132, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    }
    @endif

    // Pie Chart
    @if(isset($chartsData['pie']))
    const pieCtx = document.getElementById('pieChart');
    if (pieCtx) {
        new Chart(pieCtx, {
            type: 'pie',
            data: {
                labels: {!! json_encode($chartsData['pie']['labels']) !!},
                datasets: [{
                    data: {!! json_encode($chartsData['pie']['data']) !!},
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(255, 99, 132, 0.8)'
                    ],
                    borderColor: [
                        'rgba(75, 192, 192, 1)',
                        'rgba(255, 99, 132, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }
    @endif
});
</script>
@endpush
@endsection
