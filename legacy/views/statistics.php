<?php
/** @var PDO $pdo */
$points = $pdo->query("SELECT id_point, name FROM payment_points ORDER BY name")->fetchAll();
?>
<div class="container mt-3">
    <h2 class="mb-3">Статистика</h2>
    
    <!-- Панель фильтров -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label">Период</label>
                    <select id="periodSelect" class="form-select">
                        <option value="today">Сегодня</option>
                        <option value="week">Неделя</option>
                        <option value="month" selected>Месяц</option>
                        <option value="custom">Произвольный</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">С</label>
                    <input type="date" id="startDate" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label">По</label>
                    <input type="date" id="endDate" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Пункт оплаты</label>
                    <select id="pointFilter" class="form-select">
                        <option value="">Все</option>
                        <?php foreach ($points as $p): ?>
                            <option value="<?= $p['id_point'] ?>"><?= htmlspecialchars($p['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button id="refreshStats" class="btn btn-primary">Применить</button>
                    <button id="resetFilters" class="btn btn-outline-secondary">Сбросить</button>
                    <button id="exportCSV" class="btn btn-outline-success">CSV</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Карточки с метриками -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="text-muted">Выручка</h6>
                    <h4 id="totalRevenue">0 ₽</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="text-muted">Средний чек</h6>
                    <h4 id="avgCheck">0 ₽</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="text-muted">Транзакций</h6>
                    <h4 id="totalTransactions">0</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="text-muted">Водителей</h6>
                    <h4 id="uniqueDrivers">0</h4>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Графики -->
    <div class="row mb-4">
        <div class="col-md-7">
            <div class="card">
                <div class="card-header bg-light">Выручка по дням</div>
                <div class="card-body">
                    <canvas id="revenueChart" height="250"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="card">
                <div class="card-header bg-light">Способы оплаты</div>
                <div class="card-body">
                    <canvas id="paymentChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">Топ‑5 ПВП</div>
                <div class="card-body">
                    <canvas id="pointsChart" height="250"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">Топ‑5 водителей</div>
                <div class="card-body">
                    <canvas id="driversChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Таблица -->
    <div class="card">
        <div class="card-header bg-light">Детализация по пунктам и способам оплаты</div>
        <div class="card-body p-0">
            <table class="table table-sm table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Пункт</th>
                        <th>Способ оплаты</th>
                        <th class="text-end">Кол‑во</th>
                        <th class="text-end">Сумма (₽)</th>
                        <th class="text-end">Средний чек (₽)</th>
                    </tr>
                </thead>
                <tbody id="detailsTableBody">
                    <tr><td colspan="5" class="text-center">Загрузка...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let revenueChart, paymentChart, pointsChart, driversChart;

// Управление датами при выборе периода
document.getElementById('periodSelect').addEventListener('change', function() {
    const isCustom = this.value === 'custom';
    const startInput = document.getElementById('startDate');
    const endInput = document.getElementById('endDate');
    startInput.disabled = !isCustom;
    endInput.disabled = !isCustom;
    if (!isCustom) {
        const today = new Date();
        let start = new Date();
        switch (this.value) {
            case 'today': start = today; break;
            case 'week': start.setDate(today.getDate() - 7); break;
            case 'month': start.setMonth(today.getMonth() - 1); break;
        }
        startInput.value = start.toISOString().slice(0,10);
        endInput.value = today.toISOString().slice(0,10);
    }
});
document.getElementById('periodSelect').dispatchEvent(new Event('change'));

function loadStatistics() {
    const start = document.getElementById('startDate').value;
    const end = document.getElementById('endDate').value;
    const point = document.getElementById('pointFilter').value;
    fetch(`?page=statistics&action=data&start_date=${start}&end_date=${end}&point_id=${point}`)
        .then(res => res.json())
        .then(data => {
            if (data.error) return alert(data.error);
            // Метрики
            document.getElementById('totalRevenue').innerText = Math.round(data.metrics.total_revenue).toLocaleString() + ' ₽';
            document.getElementById('avgCheck').innerText = Math.round(data.metrics.avg_check).toLocaleString() + ' ₽';
            document.getElementById('totalTransactions').innerText = data.metrics.total_transactions.toLocaleString();
            document.getElementById('uniqueDrivers').innerText = data.metrics.unique_drivers.toLocaleString();
            
            // График выручки
            if (revenueChart) revenueChart.destroy();
            revenueChart = new Chart(document.getElementById('revenueChart'), {
                type: 'line',
                data: {
                    labels: data.daily.map(d => d.date),
                    datasets: [{
                        label: 'Выручка (₽)',
                        data: data.daily.map(d => d.revenue),
                        borderColor: '#0d6efd',
                        fill: false,
                        tension: 0.1
                    }]
                },
                options: { responsive: true, maintainAspectRatio: true }
            });
            
            // Способы оплаты (круговая)
            if (paymentChart) paymentChart.destroy();
            paymentChart = new Chart(document.getElementById('paymentChart'), {
                type: 'pie',
                data: {
                    labels: data.payment_methods.map(p => p.name),
                    datasets: [{
                        data: data.payment_methods.map(p => p.total),
                        backgroundColor: ['#36A2EB','#FFCE56','#FF6384','#4BC0C0']
                    }]
                },
                options: { plugins: { legend: { position: 'bottom' } } }
            });
            
            // Топ ПВП (горизонтальная bar)
            if (pointsChart) pointsChart.destroy();
            pointsChart = new Chart(document.getElementById('pointsChart'), {
                type: 'bar',
                data: {
                    labels: data.top_points.map(p => p.point_name),
                    datasets: [{
                        label: 'Выручка (₽)',
                        data: data.top_points.map(p => p.total_revenue),
                        backgroundColor: '#6c757d'
                    }]
                },
                options: { indexAxis: 'y', responsive: true }
            });
            
            // Топ водителей
            if (driversChart) driversChart.destroy();
            driversChart = new Chart(document.getElementById('driversChart'), {
                type: 'bar',
                data: {
                    labels: data.top_drivers.map(d => d.driver_name),
                    datasets: [{
                        label: 'Оплачено (₽)',
                        data: data.top_drivers.map(d => d.total_paid),
                        backgroundColor: '#17a2b8'
                    }]
                },
                options: { indexAxis: 'y', responsive: true }
            });
            
            // Таблица детализации
            const tbody = document.getElementById('detailsTableBody');
            tbody.innerHTML = '';
            if (!data.details.length) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center">Нет данных</td></tr>';
            } else {
                data.details.forEach(row => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${escapeHtml(row.point_name)}</td>
                        <td>${escapeHtml(row.payment_method_name)}</td>
                        <td class="text-end">${row.total_transactions}</td>
                        <td class="text-end">${Number(row.total_amount).toFixed(2)}</td>
                        <td class="text-end">${Number(row.avg_amount).toFixed(2)}</td>
                    `;
                    tbody.appendChild(tr);
                });
            }
        })
        .catch(err => console.error(err));
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>]/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;'})[m]);
}

document.getElementById('refreshStats').addEventListener('click', loadStatistics);
document.getElementById('exportCSV').addEventListener('click', () => {
    const start = document.getElementById('startDate').value;
    const end = document.getElementById('endDate').value;
    const point = document.getElementById('pointFilter').value;
    window.location.href = `?page=statistics&action=export_csv&start_date=${start}&end_date=${end}&point_id=${point}`;
});
document.getElementById('resetFilters').addEventListener('click', () => {
    document.getElementById('pointFilter').value = '';
    document.getElementById('periodSelect').value = 'month';
    document.getElementById('periodSelect').dispatchEvent(new Event('change'));
    loadStatistics();
});
loadStatistics();
</script>