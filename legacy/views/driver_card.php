<?php
/** @var PDO $pdo */
/** @var int $id */

// 1. Получаем расширенные данные водителя
$stmt = $pdo->prepare("SELECT * FROM drivers WHERE id_driver = ?");
$stmt->execute([$id]);
$d = $stmt->fetch();

if (!$d) {
    echo '<div class="alert alert-danger">Водитель не найден</div>';
    exit;
}

// 2. Получаем список автомобилей этого водителя
$carsStmt = $pdo->prepare("
    SELECT v.*, at.name as type_name 
    FROM vehicles v 
    LEFT JOIN auto_types at ON v.id_auto_type = at.id_auto_type 
    WHERE v.id_driver = ?
");
$carsStmt->execute([$id]);
$vehicles = $carsStmt->fetchAll();

// 3. Получаем транспондеры через привязку к автомобилям водителя
$transpStmt = $pdo->prepare("
    SELECT t.*, v.plate_number, v.name as vehicle_name
    FROM transponders t
    JOIN vehicles v ON t.id_vehicle = v.id_vehicle
    WHERE v.id_driver = ?
");
$transpStmt->execute([$id]);
$transponders = $transpStmt->fetchAll();

// 4. Получаем долг по штрафам
$debtStmt = $pdo->prepare("SELECT get_driver_debt(?) as debt");
$debtStmt->execute([$id]);
$debt = $debtStmt->fetchColumn() ?: 0;

// 5. Статистика проездов
$statsStmt = $pdo->prepare("
    SELECT 
        COUNT(t.id_transaction) as total_trips,
        COALESCE(SUM(t.amount), 0) as total_spent,
        MAX(t.datetime) as last_trip
    FROM transactions t
    JOIN vehicles v ON t.id_vehicle = v.id_vehicle
    WHERE v.id_driver = ? AND t.status = 'успешно'
");
$statsStmt->execute([$id]);
$stats = $statsStmt->fetch();
?>

<div class="row">
    <div class="col-md-5 border-end">
        <div class="d-flex align-items-center mb-4">
            <div class="avatar-sq-lg me-3 bg-primary text-white">
                <?= mb_substr($d['full_name'], 0, 1) ?>
            </div>
            <div>
                <h4 class="mb-0"><?= htmlspecialchars($d['full_name']) ?></h4>
                <small class="text-muted">ID Клиента: #<?= $d['id_driver'] ?></small>
            </div>
        </div>

        <h6 class="text-uppercase small fw-bold text-muted mb-3">Личные данные</h6>
        <div class="mb-2">
            <i class="bi bi-telephone me-2 text-primary"></i>
            <strong><?= htmlspecialchars($d['phone_number']) ?></strong>
        </div>
        <div class="mb-4">
            <i class="bi bi-calendar-event me-2 text-primary"></i>
            <strong><?= $d['birth_date'] ? date('d.m.Y', strtotime($d['birth_date'])) : 'Не указана' ?></strong>
        </div>

        <h6 class="text-uppercase small fw-bold text-muted mb-3">Финансовое состояние</h6>
        <div class="card bg-light border-0 mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="small text-muted">Личный баланс:</span>
                    <span class="small text-muted">Неоплаченные штрафы:</span>
                </div>
                <div class="d-flex justify-content-between align-items-end">
                    <h3 class="<?= $d['personal_balance'] < 100 ? 'text-danger' : 'text-success' ?> mb-0">
                        <?= number_format($d['personal_balance'], 2, ',', ' ') ?> ₽
                    </h3>
                    <h4 class="<?= $debt > 0 ? 'text-danger' : 'text-success' ?> mb-0">
                        <?= number_format($debt, 2, ',', ' ') ?> ₽
                    </h4>
                </div>
                <!-- Форма пополнения -->
                <div class="input-group mt-3 shadow-sm">
                    <input type="number" id="refillAmount" class="form-control" placeholder="Сумма" value="500" min="1" step="0.01">
                    <button class="btn btn-success" onclick="window.parent.refillBalance(<?= $id ?>)">
                        <i class="bi bi-cash-coin me-1"></i>Пополнить
                    </button>
                </div>
                <div id="refillMessage" class="mt-2" style="display: none;"></div>
            </div>
        </div>

        <h6 class="text-uppercase small fw-bold text-muted mb-3">Статистика поездок</h6>
        <ul class="list-group list-group-flush small">
            <li class="list-group-item d-flex justify-content-between px-0">
                <span>Всего проездов:</span>
                <strong><?= $stats['total_trips'] ?></strong>
            </li>
            <li class="list-group-item d-flex justify-content-between px-0">
                <span>Потрачено всего:</span>
                <strong><?= number_format($stats['total_spent'], 2, ',', ' ') ?> ₽</strong>
            </li>
            <li class="list-group-item d-flex justify-content-between px-0">
                <span>Последняя поездка:</span>
                <strong><?= $stats['last_trip'] ? date('d.m.Y H:i', strtotime($stats['last_trip'])) : 'Нет данных' ?></strong>
            </li>
        </ul>
    </div>

    <div class="col-md-7 ps-4">
        <h6 class="text-uppercase small fw-bold text-muted mb-3">Зарегистрированные авто (<?= count($vehicles) ?>)</h6>
        <?php if (empty($vehicles)): ?>
            <div class="alert alert-light border small text-center py-2">Нет привязанных ТС</div>
        <?php else: ?>
            <div class="list-group mb-4">
                <?php foreach ($vehicles as $v): ?>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge bg-dark font-monospace"><?= htmlspecialchars($v['plate_number']) ?></span>
                            <div class="small text-muted mt-1"><?= htmlspecialchars($v['name'] ?? 'Марка не указана') ?></div>
                        </div>
                        <span class="badge bg-info-subtle text-info border border-info-subtle small"><?= $v['type_name'] ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <h6 class="text-uppercase small fw-bold text-muted mb-3">Транспондеры в автомобилях</h6>
        <?php if (empty($transponders)): ?>
            <p class="small text-muted fst-italic">Устройства не зарегистрированы</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm small align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Серийный №</th>
                            <th>Автомобиль</th>
                            <th>Статус</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transponders as $tr): ?>
                        <tr>
                            <td><code><?= htmlspecialchars($tr['serial_number']) ?></code></td>
                            <td>
                                <span class="fw-bold"><?= htmlspecialchars($tr['plate_number']) ?></span>
                            </td>
                            <td>
                                <span class="badge <?= $tr['status'] === 'активен' ? 'bg-success' : 'bg-secondary' ?>">
                                    <?= htmlspecialchars($tr['status']) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        
        <div class="mt-4 pt-3 border-top d-flex justify-content-between">
            <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete('?page=drivers&action=delete&id=<?= $id ?>', '<?= htmlspecialchars($d['full_name']) ?>')">
                <i class="bi bi-person-x me-1"></i> Удалить аккаунт
            </button>
            <button class="btn btn-sm btn-outline-secondary" onclick="editDriver(<?= $id ?>)">
                <i class="bi bi-gear me-1"></i> Настройки
            </button>
        </div>
    </div>
</div>

<style>
    .avatar-sq-lg {
        width: 60px; height: 60px; 
        display: flex; align-items: center; justify-content: center;
        border-radius: 12px; font-size: 1.8rem; font-weight: bold;
    }
    .font-monospace { font-family: 'Courier New', Courier, monospace; letter-spacing: 1px; }
</style>

<script>
// Функция пополнения баланса
function refillBalance(driverId) {
    const amountInput = document.getElementById('refillAmount');
    const amount = parseFloat(amountInput.value);
    const messageDiv = document.getElementById('refillMessage');
    
    if (!amount || amount <= 0) {
        messageDiv.style.display = 'block';
        messageDiv.className = 'alert alert-danger py-2 small mt-2';
        messageDiv.textContent = 'Пожалуйста, введите корректную сумму';
        amountInput.focus();
        return;
    }

    const btn = event.target;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Пополнение...';
    
    messageDiv.style.display = 'block';
    messageDiv.className = 'alert alert-info py-2 small mt-2';
    messageDiv.textContent = 'Выполняется пополнение...';

    const formData = new FormData();
    formData.append('amount', amount);

    fetch(`?page=drivers&action=add_balance&id=${driverId}`, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            messageDiv.className = 'alert alert-success py-2 small mt-2';
            messageDiv.textContent = '✅ Баланс успешно пополнен на ' + amount + ' ₽';
            
            // Обновляем карточку через 1 секунду
            setTimeout(() => {
                // Вызываем родительскую функцию viewDriverCard
                if (typeof parentViewDriverCard === 'function') {
                    parentViewDriverCard(driverId);
                } else {
                    // Если функция не найдена, перезагружаем страницу
                    location.reload();
                }
            }, 1000);
        } else {
            messageDiv.className = 'alert alert-danger py-2 small mt-2';
            messageDiv.textContent = '❌ Ошибка: ' + (data.error || 'Неизвестная ошибка');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-cash-coin me-1"></i>Пополнить';
        }
    })
    .catch(err => {
        messageDiv.className = 'alert alert-danger py-2 small mt-2';
        messageDiv.textContent = '❌ Ошибка соединения с сервером';
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-cash-coin me-1"></i>Пополнить';
    });
}

// Функция редактирования водителя
function editDriver(driverId) {
    // Закрываем текущее модальное окно
    const currentModal = bootstrap.Modal.getInstance(document.getElementById('driverCardModal'));
    currentModal.hide();
    
    // После скрытия открываем форму редактирования
    document.getElementById('driverCardModal').addEventListener('hidden.bs.modal', function handler() {
        this.removeEventListener('hidden.bs.modal', handler);
        
        const modalEl = document.getElementById('driverCardModal');
        modalEl.querySelector('.modal-title').innerHTML = '<i class="bi bi-gear me-2"></i>Редактирование профиля';
        
        fetch(`?page=drivers&action=edit_form&id=${driverId}`)
            .then(r => r.text())
            .then(html => {
                document.getElementById('driverCardContent').innerHTML = html;
                const modal = new bootstrap.Modal(modalEl);
                modal.show();
            });
    });
}
</script>