@extends('layouts.app')

@section('title', 'Статистика - ПВП')

@push('styles')
<style>
    .stat-card-lg {
        background: #fff;
        border-radius: 16px;
        padding: 1.5rem;
        border: 1px solid rgba(0,0,0,0.04);
        transition: all 0.3s;
        height: 100%;
    }
    .stat-card-lg:hover { box-shadow: 0 8px 30px rgba(0,0,0,0.06); transform: translateY(-2px); }
    .stat-card-lg .label { font-size: 0.8rem; color: #6c757d; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
    .stat-card-lg .value { font-size: 2rem; font-weight: 800; margin: 0; }
    .stat-card-lg .icon-bg {
        width: 48px; height: 48px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.4rem;
    }
    .stat-card-lg .trend { font-size: 0.8rem; font-weight: 600; }
    .chart-wrapper { position: relative; height: 280px; }
    .stat-table thead th { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; color: #6c757d; background: var(--gray-50); border-bottom: 2px solid var(--gray-200); }
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2>Статистика и аналитика</h2>
            <p class="text-muted mb-0">Детальный анализ работы системы ПВП</p>
        </div>
        <button id="exportCSV" class="btn btn-outline-success btn-modern">
            <i class="bi bi-download me-2"></i>CSV
        </button>
    </div>
</div>

<div class="card-modern mb-4">
    <div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label small fw-bold">Период</label>
                <select id="periodSelect" class="form-select form-control-modern">
                    <option value="today">Сегодня</option>
                    <option value="week">Неделя</option>
                    <option value="month" selected>Месяц</option>
                    <option value="custom">Произвольный</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">С</label>
                <input type="date" id="startDate" class="form-control form-control-modern">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">По</label>
                <input type="date" id="endDate" class="form-control form-control-modern">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold">Пункт оплаты</label>
                <select id="pointFilter" class="form-select form-control-modern">
                    <option value="">Все пункты</option>
                    @foreach($points as $p)
                    <option value="{{ $p->id_point }}">{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button id="refreshStats" class="btn btn-primary btn-modern flex-grow-1">
                    <i class="bi bi-arrow-clockwise me-1"></i>Применить
                </button>
                <button id="resetFilters" class="btn btn-outline-secondary btn-modern">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card-lg d-flex flex-column justify-content-between">
            <div>
                <div class="icon-bg" style="background: rgba(13,110,253,0.1); color: var(--primary);"><i class="bi bi-cash-stack"></i></div>
                <div class="label mt-2">Выручка</div>
            </div>
            <div>
                <div class="value" id="totalRevenue">0 ₽</div>
                <div class="trend text-success"><i class="bi bi-arrow-up me-1"></i>За выбранный период</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card-lg d-flex flex-column justify-content-between">
            <div>
                <div class="icon-bg" style="background: rgba(25,135,84,0.1); color: var(--success);"><i class="bi bi-receipt"></i></div>
                <div class="label mt-2">Средний чек</div>
            </div>
            <div>
                <div class="value" id="avgCheck">0 ₽</div>
                <div class="trend text-muted">За одну транзакцию</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card-lg d-flex flex-column justify-content-between">
            <div>
                <div class="icon-bg" style="background: rgba(111,66,193,0.1); color: #6f42c1;"><i class="bi bi-arrow-left-right"></i></div>
                <div class="label mt-2">Транзакций</div>
            </div>
            <div>
                <div class="value" id="totalTransactions">0</div>
                <div class="trend text-muted">Всего операций</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card-lg d-flex flex-column justify-content-between">
            <div>
                <div class="icon-bg" style="background: rgba(13,202,240,0.1); color: #0dcaf0;"><i class="bi bi-people"></i></div>
                <div class="label mt-2">Водителей</div>
            </div>
            <div>
                <div class="value" id="uniqueDrivers">0</div>
                <div class="trend text-muted">Уникальных</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-7">
        <div class="card-modern">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="bi bi-graph-up"></i>
                <span>Выручка по дням</span>
            </div>
            <div class="card-body">
                <div class="chart-wrapper">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="card-modern" style="height: 100%;">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="bi bi-pie-chart"></i>
                <span>Способы оплаты</span>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
                <div class="chart-wrapper" style="height: 240px; width: 100%;">
                    <canvas id="paymentChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card-modern">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="bi bi-trophy"></i>
                <span>Топ-5 ПВП</span>
            </div>
            <div class="card-body">
                <div class="chart-wrapper">
                    <canvas id="pointsChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card-modern">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="bi bi-trophy"></i>
                <span>Топ-5 водителей</span>
            </div>
            <div class="card-body">
                <div class="chart-wrapper">
                    <canvas id="driversChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card-modern">
    <div class="card-header d-flex align-items-center gap-2">
        <i class="bi bi-table"></i>
        <span>Детализация по пунктам и способам оплаты</span>
    </div>
    <div class="card-body p-0">
        <table class="table table-modern mb-0 stat-table" id="detailsTable">
            <thead>
                <tr>
                    <th>Пункт</th>
                    <th>Способ оплаты</th>
                    <th class="text-end">Кол-во</th>
                    <th class="text-end">Сумма (₽)</th>
                    <th class="text-end">Средний чек (₽)</th>
                </tr>
            </thead>
            <tbody id="detailsTableBody">
                <tr><td colspan="5" class="text-center text-muted py-4">Загрузка...</td></tr>
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let revenueChart, paymentChart, pointsChart, driversChart;

    document.getElementById('periodSelect').addEventListener('change', function() {
        const isCustom = this.value === 'custom';
        document.getElementById('startDate').disabled = !isCustom;
        document.getElementById('endDate').disabled = !isCustom;
        if (!isCustom) {
            const today = new Date();
            let start = new Date();
            switch (this.value) {
                case 'today': start = today; break;
                case 'week': start.setDate(today.getDate() - 7); break;
                case 'month': start.setMonth(today.getMonth() - 1); break;
            }
            document.getElementById('startDate').value = start.toISOString().slice(0,10);
            document.getElementById('endDate').value = today.toISOString().slice(0,10);
        }
    });
    document.getElementById('periodSelect').dispatchEvent(new Event('change'));

    const colors = ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#6f42c1', '#0dcaf0', '#fd7e14', '#20c997'];

    function loadStatistics() {
        const start = document.getElementById('startDate').value;
        const end = document.getElementById('endDate').value;
        const point = document.getElementById('pointFilter').value;
        fetch(`{{ route('statistics.data') }}?start_date=${start}&end_date=${end}&point_id=${point}`)
            .then(r => r.json())
            .then(data => {
                if (data.error) return showToast(data.error, 'error');

                document.getElementById('totalRevenue').innerText = Math.round(data.metrics.total_revenue).toLocaleString() + ' ₽';
                document.getElementById('avgCheck').innerText = Math.round(data.metrics.avg_check).toLocaleString() + ' ₽';
                document.getElementById('totalTransactions').innerText = data.metrics.total_transactions.toLocaleString();
                document.getElementById('uniqueDrivers').innerText = data.metrics.unique_drivers.toLocaleString();

                if (revenueChart) revenueChart.destroy();
                revenueChart = new Chart(document.getElementById('revenueChart'), {
                    type: 'line',
                    data: {
                        labels: data.daily.map(d => d.date),
                        datasets: [{
                            label: 'Выручка (₽)',
                            data: data.daily.map(d => d.revenue),
                            borderColor: '#0d6efd',
                            backgroundColor: 'rgba(13,110,253,0.08)',
                            fill: true,
                            tension: 0.4,
                            pointRadius: 4,
                            pointHoverRadius: 7,
                            borderWidth: 3
                        }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true, ticks: { callback: v => v.toLocaleString() + ' ₽' } } }
                    }
                });

                if (paymentChart) paymentChart.destroy();
                paymentChart = new Chart(document.getElementById('paymentChart'), {
                    type: 'doughnut',
                    data: {
                        labels: data.payment_methods.map(p => p.name),
                        datasets: [{
                            data: data.payment_methods.map(p => p.total),
                            backgroundColor: colors.slice(0, data.payment_methods.length),
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom', labels: { padding: 15, usePointStyle: true, font: { size: 12 } } },
                            tooltip: {
                                callbacks: {
                                    label: ctx => {
                                        const total = ctx.dataset.data.reduce((a,b) => a+b, 0);
                                        const pct = ((ctx.raw / total) * 100).toFixed(1);
                                        return ` ${ctx.label}: ${Math.round(ctx.raw).toLocaleString()} ₽ (${pct}%)`;
                                    }
                                }
                            }
                        },
                        cutout: '60%'
                    }
                });

                if (pointsChart) pointsChart.destroy();
                pointsChart = new Chart(document.getElementById('pointsChart'), {
                    type: 'bar',
                    data: {
                        labels: data.top_points.map(p => p.point_name),
                        datasets: [{
                            label: 'Выручка (₽)',
                            data: data.top_points.map(p => p.total_revenue),
                            backgroundColor: colors.slice(0, data.top_points.length),
                            borderRadius: 6
                        }]
                    },
                    options: {
                        indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: { x: { beginAtZero: true, ticks: { callback: v => v.toLocaleString() + ' ₽' } } }
                    }
                });

                if (driversChart) driversChart.destroy();
                driversChart = new Chart(document.getElementById('driversChart'), {
                    type: 'bar',
                    data: {
                        labels: data.top_drivers.map(d => d.driver_name.length > 12 ? d.driver_name.substring(0,12) + '...' : d.driver_name),
                        datasets: [{
                            label: 'Оплачено (₽)',
                            data: data.top_drivers.map(d => d.total_paid),
                            backgroundColor: '#17a2b8',
                            borderRadius: 6
                        }]
                    },
                    options: {
                        indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: { x: { beginAtZero: true, ticks: { callback: v => v.toLocaleString() + ' ₽' } } }
                    }
                });

                const tbody = document.getElementById('detailsTableBody');
                tbody.innerHTML = '';
                if (!data.details.length) {
                    tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">Нет данных</td></tr>';
                } else {
                    data.details.forEach(row => {
                        tbody.innerHTML += `<tr>
                            <td class="fw-semibold">${escapeHtml(row.point_name)}</td>
                            <td>${escapeHtml(row.payment_method_name)}</td>
                            <td class="text-end">${row.total_transactions}</td>
                            <td class="text-end fw-bold">${Number(row.total_amount).toFixed(2)}</td>
                            <td class="text-end">${Number(row.avg_amount).toFixed(2)}</td>
                        </tr>`;
                    });
                }
            })
            .catch(err => console.error(err));
    }

    function escapeHtml(str) { if (!str) return ''; return str.replace(/[&<>]/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;'})[m]); }

    document.getElementById('refreshStats').addEventListener('click', loadStatistics);
    document.getElementById('exportCSV').addEventListener('click', () => {
        const start = document.getElementById('startDate').value;
        const end = document.getElementById('endDate').value;
        const point = document.getElementById('pointFilter').value;
        window.location.href = `{{ route('statistics.export') }}?start_date=${start}&end_date=${end}&point_id=${point}`;
    });
    document.getElementById('resetFilters').addEventListener('click', () => {
        document.getElementById('pointFilter').value = '';
        document.getElementById('periodSelect').value = 'month';
        document.getElementById('periodSelect').dispatchEvent(new Event('change'));
        loadStatistics();
    });
    loadStatistics();
</script>
@endpush
