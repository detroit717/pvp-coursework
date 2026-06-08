<?php
/** @var PDO $pdo */
/** @var array $payment_points */
/** @var array $payment_methods */

// Получаем все автомобили с водителями для формы
$vehicles_with_drivers = $pdo->query("
    SELECT v.id_vehicle, v.plate_number, d.full_name as driver_name 
    FROM vehicles v 
    JOIN drivers d ON v.id_driver = d.id_driver 
    ORDER BY v.plate_number
")->fetchAll();

// Получаем тарифы для формы
$tariffs = $pdo->query("
    SELECT t.id_tariff, t.amount, at.name as auto_type_name 
    FROM tariffs t 
    JOIN auto_types at ON t.id_auto_type = at.id_auto_type 
    ORDER BY at.name
")->fetchAll();
?>

<div class="container mt-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Транзакции</h2>
        <button class="btn btn-primary" onclick="openTransactionModal()">
            <i class="bi bi-plus-circle me-2"></i>Добавить транзакцию
        </button>
    </div>

    <!-- Фильтры -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Пункт оплаты</label>
                    <select id="filterPoint" class="form-select">
                        <option value="">Все пункты</option>
                        <?php foreach ($payment_points as $pt): ?>
                            <option value="<?= $pt['id_point'] ?>"><?= htmlspecialchars($pt['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Способ оплаты</label>
                    <select id="filterPayment" class="form-select">
                        <option value="">Все способы</option>
                        <?php foreach ($payment_methods as $pm): ?>
                            <option value="<?= $pm['id_payment_method'] ?>"><?= htmlspecialchars($pm['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Статус</label>
                    <select id="filterStatus" class="form-select">
                        <option value="">Все статусы</option>
                        <option value="успешно">Успешно</option>
                        <option value="неоплата">Неоплата</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Дата с</label>
                    <input type="date" id="filterDateFrom" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Дата по</label>
                    <input type="date" id="filterDateTo" class="form-control">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button id="resetFilters" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Таблица -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0" id="transactionsTable">
                <thead class="bg-light">
                    <tr>
                        <th>ID</th>
                        <th>Дата</th>
                        <th>Пункт</th>
                        <th>Полоса</th>
                        <th>Автомобиль</th>
                        <th>Водитель</th>
                        <th>Сумма</th>
                        <th>Способ</th>
                        <th>Статус</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $pdo->query("
                        SELECT 
                            t.id_transaction,
                            t.datetime,
                            pp.name as point_name,
                            l.lane_number,
                            v.plate_number,
                            d.full_name as driver_name,
                            t.amount,
                            pm.name as payment_method,
                            t.status
                        FROM transactions t
                        JOIN payment_points pp ON t.id_point = pp.id_point
                        JOIN lanes l ON t.id_lane = l.id_lane
                        JOIN vehicles v ON t.id_vehicle = v.id_vehicle
                        JOIN drivers d ON v.id_driver = d.id_driver
                        JOIN payment_methods pm ON t.id_payment_method = pm.id_payment_method
                        ORDER BY t.datetime DESC
                        LIMIT 500
                    ");
                    while ($row = $stmt->fetch()):
                    ?>
                    <tr>
                        <td><span class="badge bg-secondary">#<?= $row['id_transaction'] ?></span></td>
                        <td><?= date('d.m.Y H:i', strtotime($row['datetime'])) ?></td>
                        <td><?= htmlspecialchars($row['point_name']) ?></td>
                        <td><span class="badge bg-info">Полоса <?= $row['lane_number'] ?></span></td>
                        <td><code><?= htmlspecialchars($row['plate_number']) ?></code></td>
                        <td><?= htmlspecialchars($row['driver_name']) ?></td>
                        <td><strong><?= number_format($row['amount'], 2) ?> ₽</strong></td>
                        <td><?= htmlspecialchars($row['payment_method']) ?></td>
                        <td>
                            <span class="badge <?= $row['status'] == 'успешно' ? 'bg-success' : 'bg-danger' ?>">
                                <?= $row['status'] ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Модальное окно для добавления транзакции -->
<div class="modal fade" id="transactionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-currency-exchange me-2"></i>Регистрация проезда</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="transactionForm">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Пункт оплаты</label>
                            <select name="id_point" id="transPoint" class="form-select" required>
                                <option value="">-- Выберите пункт --</option>
                                <?php foreach ($payment_points as $pt): ?>
                                    <option value="<?= $pt['id_point'] ?>"><?= htmlspecialchars($pt['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Полоса</label>
                            <select name="id_lane" id="transLane" class="form-select" required disabled>
                                <option value="">Сначала выберите пункт</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Автомобиль</label>
                            <select name="id_vehicle" id="transVehicle" class="form-select" required>
                                <option value="">-- Выберите автомобиль --</option>
                                <?php foreach ($vehicles_with_drivers as $v): ?>
                                    <option value="<?= $v['id_vehicle'] ?>">
                                        <?= htmlspecialchars($v['plate_number']) ?> (<?= htmlspecialchars($v['driver_name']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Тариф</label>
                            <select name="id_tariff" id="transTariff" class="form-select">
                                <option value="">Автоматически</option>
                                <?php foreach ($tariffs as $tariff): ?>
                                    <option value="<?= $tariff['id_tariff'] ?>" data-amount="<?= $tariff['amount'] ?>">
                                        <?= htmlspecialchars($tariff['auto_type_name']) ?> - <?= number_format($tariff['amount'], 2) ?> ₽
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Сумма (₽)</label>
                            <input type="number" step="0.01" name="amount" id="transAmount" class="form-control" required placeholder="0.00">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Способ оплаты</label>
                            <select name="id_payment_method" class="form-select" required>
                                <?php foreach ($payment_methods as $pm): ?>
                                    <option value="<?= $pm['id_payment_method'] ?>"><?= htmlspecialchars($pm['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>Зарегистрировать
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Открытие модального окна
function openTransactionModal() {
    const form = document.getElementById('transactionForm');
    form.reset();
    document.getElementById('transLane').disabled = true;
    document.getElementById('transLane').innerHTML = '<option value="">Сначала выберите пункт</option>';
    
    const modal = new bootstrap.Modal(document.getElementById('transactionModal'));
    modal.show();
}

// Загрузка полос при выборе пункта
document.getElementById('transPoint').addEventListener('change', function() {
    const pointId = this.value;
    const laneSelect = document.getElementById('transLane');
    
    if (!pointId) {
        laneSelect.disabled = true;
        laneSelect.innerHTML = '<option value="">Сначала выберите пункт</option>';
        return;
    }
    
    // Показываем индикатор загрузки
    laneSelect.disabled = true;
    laneSelect.innerHTML = '<option value="">⏳ Загрузка полос...</option>';
    
    // Пробуем оба варианта URL
    fetch(`?page=payment_points&action=get_lanes&point_id=${pointId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('HTTP error: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            console.log('Полученные полосы:', data); // Для отладки
            
            // Проверяем, не вернул ли сервер ошибку
            if (data.error) {
                throw new Error(data.error);
            }
            
            const lanes = Array.isArray(data) ? data : [];
            
            if (lanes.length === 0) {
                laneSelect.innerHTML = '<option value="">❌ Нет доступных полос</option>';
                laneSelect.disabled = true;
                // Показываем предупреждение
                alert('Для выбранного пункта нет настроенных полос. Добавьте полосы в разделе "Пункты оплаты".');
                return;
            }
            
            laneSelect.innerHTML = '<option value="">-- Выберите полосу --</option>' +
                lanes.map(lane => 
                    `<option value="${lane.id_lane}">🛣️ Полоса №${lane.lane_number} (ID: ${lane.id_lane})</option>`
                ).join('');
            laneSelect.disabled = false;
        })
        .catch(error => {
            console.error('Ошибка загрузки полос:', error);
            
            // Пробуем альтернативный URL
            fetch(`?page=transactions&action=get_lanes&point_id=${pointId}`)
                .then(response => response.json())
                .then(data => {
                    const lanes = Array.isArray(data) ? data : [];
                    if (lanes.length > 0) {
                        laneSelect.innerHTML = '<option value="">-- Выберите полосу --</option>' +
                            lanes.map(lane => 
                                `<option value="${lane.id_lane}">🛣️ Полоса №${lane.lane_number} (ID: ${lane.id_lane})</option>`
                            ).join('');
                        laneSelect.disabled = false;
                    } else {
                        laneSelect.innerHTML = '<option value="">❌ Нет доступных полос</option>';
                        laneSelect.disabled = true;
                    }
                })
                .catch(() => {
                    laneSelect.innerHTML = '<option value="">❌ Ошибка загрузки</option>';
                    laneSelect.disabled = true;
                });
        });
});

// Автозаполнение суммы при выборе тарифа
document.getElementById('transTariff').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const amount = selectedOption.getAttribute('data-amount');
    if (amount) {
        document.getElementById('transAmount').value = amount;
    }
});

// Отправка формы транзакции
document.getElementById('transactionForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Проверяем, что полоса выбрана
    const laneSelect = document.getElementById('transLane');
    if (!laneSelect.value || laneSelect.disabled) {
        alert('Пожалуйста, выберите пункт оплаты и полосу');
        return;
    }
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Регистрация...';
    
    fetch('?page=transactions&action=add', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('transactionModal'));
            modal.hide();
            location.reload();
        } else {
            alert('Ошибка: ' + (data.error || 'Неизвестная ошибка'));
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    })
    .catch(error => {
        alert('Ошибка отправки: ' + error.message);
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
});

// Инициализация DataTable
$(document).ready(function() {
    if ($.fn.DataTable.isDataTable('#transactionsTable')) {
        $('#transactionsTable').DataTable().destroy();
    }
    
    var table = $('#transactionsTable').DataTable({
        language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/ru.json' },
        pageLength: 25,
        responsive: true,
        order: [[0, 'desc']]
    });
    
    // Правильные фильтры
    function applyFilters() {
        // Фильтр по пункту (точное совпадение в колонке 2)
        var pointText = $('#filterPoint option:selected').text();
        if ($('#filterPoint').val()) {
            table.column(2).search(pointText).draw();
        } else {
            table.column(2).search('').draw();
        }
        
        // Фильтр по способу оплаты (колонка 7)
        var paymentText = $('#filterPayment option:selected').text();
        if ($('#filterPayment').val()) {
            table.column(7).search(paymentText).draw();
        } else {
            table.column(7).search('').draw();
        }
        
        // Фильтр по статусу (колонка 8)
        table.column(8).search($('#filterStatus').val()).draw();
    }
    
    $('#filterPoint, #filterPayment, #filterStatus').on('change', applyFilters);
    
    // Сброс фильтров
    $('#resetFilters').on('click', function() {
        $('#filterPoint, #filterPayment, #filterStatus').val('');
        $('#filterDateFrom, #filterDateTo').val('');
        table.search('').columns().search('').draw();
    });
});
</script>