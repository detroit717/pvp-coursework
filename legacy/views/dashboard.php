<div class="container-fluid">
    <h2 class="mb-4">Панель управления</h2>
    
    <!-- Карточки с быстрой статистикой -->
    <div class="row">
        <div class="col-md-3">
            <div class="stat-card shadow-sm">
                <p>Транзакции (30 дн.)</p>
                <h3 id="dash-trans-count">...</h3>
                <span class="small-text text-success">Всего операций</span>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card shadow-sm">
                <p>Выручка (30 дн.)</p>
                <h3 id="dash-revenue">...</h3>
                <span class="small-text text-primary">Российский рубль (₽)</span>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card shadow-sm">
                <p>Активные водители</p>
                <h3 id="dash-drivers">...</h3>
                <span class="small-text text-muted">Уникальные пользователи</span>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card shadow-sm">
                <p>Средний чек</p>
                <h3 id="dash-avg">...</h3>
                <span class="small-text text-warning">За одну поездку</span>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <!-- Мини-таблица последних транзакций -->
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">Последние транзакции</div>
                <div class="card-body p-0">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Время</th>
                                <th>Авто</th>
                                <th>Сумма</th>
                                <th>Статус</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $recent = $pdo->query("SELECT t.*, v.plate_number 
                                                 FROM transactions t 
                                                 JOIN vehicles v ON t.id_vehicle = v.id_vehicle 
                                                 ORDER BY t.datetime DESC LIMIT 5")->fetchAll();
                            foreach ($recent as $row): ?>
                                <tr>
                                    <td><?= date('H:i d.m', strtotime($row['datetime'])) ?></td>
                                    <td><?= htmlspecialchars($row['plate_number']) ?></td>
                                    <td><?= number_format($row['amount'], 2) ?> ₽</td>
                                    <td><span class="badge badge-success"><?= $row['status'] ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Кнопки быстрого доступа -->
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header">Быстрые действия</div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="?page=transactions&action=add" class="btn btn-outline-primary">Новая оплата</a>
                        <a href="?page=fines&action=add" class="btn btn-outline-danger">Выписать штраф</a>
                        <a href="?page=statistics" class="btn btn-outline-dark">Полный отчет</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Загружаем данные для дашборда из вашего API в index.php
fetch('?page=statistics&action=data')
    .then(r => r.json())
    .then(data => {
        if(data.metrics) {
            document.getElementById('dash-trans-count').innerText = data.metrics.total_transactions;
            document.getElementById('dash-revenue').innerText = Math.round(data.metrics.total_revenue).toLocaleString() + ' ₽';
            document.getElementById('dash-drivers').innerText = data.metrics.unique_drivers;
            document.getElementById('dash-avg').innerText = Math.round(data.metrics.avg_check) + ' ₽';
        }
    });
</script>