<?php
/** @var PDO $pdo */
/** @var array $auto_types */
/** @var array $drivers_list */
?>

<div class="container mt-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Штрафы</h2>
        <button class="btn btn-primary" onclick="openAddFineModal()">Назначить штраф</button>
    </div>

    <table class="table table-bordered" id="finesTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Дата</th>
                <th>Водитель</th>
                <th>Автомобиль</th>
                <th>Тип штрафа</th>
                <th>Сумма</th>
                <th>Статус</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php
            $stmt = $pdo->query("
                SELECT 
                    f.id_fine,
                    f.datetime,
                    d.full_name as driver_name,
                    v.plate_number,
                    ft.name as fine_type,
                    f.amount,
                    f.payment_status,
                    f.id_fine
                FROM fines f
                JOIN drivers d ON f.id_driver = d.id_driver
                LEFT JOIN vehicles v ON f.id_vehicle = v.id_vehicle
                JOIN fine_types ft ON f.id_fine_type = ft.id_fine_type
                ORDER BY f.datetime DESC
            ");
            while ($row = $stmt->fetch()):
            ?>
            <tr>
                <td><?= $row['id_fine'] ?></td>
                <td><?= date('d.m.Y H:i', strtotime($row['datetime'])) ?></td>
                <td><?= htmlspecialchars($row['driver_name']) ?></td>
                <td><?= htmlspecialchars($row['plate_number'] ?? '—') ?></td>
                <td><?= htmlspecialchars($row['fine_type']) ?></td>
                <td><?= number_format($row['amount'], 2) ?> ₽</td>
                <td>
                    <span class="badge <?= $row['payment_status'] == 'оплачен' ? 'bg-success' : 'bg-danger' ?>">
                        <?= $row['payment_status'] ?>
                    </span>
                </td>
                <td>
                    <?php if ($row['payment_status'] == 'неоплачен'): ?>
                    <?php endif; ?>
                    <div class="button-delete">
                        <button class="btn btn-sm btn-danger" onclick="deleteFine(<?= $row['id_fine'] ?>)">🗑️</button>
                    </div>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Модальное окно для начисления штрафа -->
<div class="modal fade" id="fineModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-danger">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Выписать штраф</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="fineForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Автомобиль</label>
                        <select name="id_vehicle" id="fineVehicle" class="form-select">
                            <option value="">Без привязки к авто</option>
                            <?php 
                            $v_stmt = $pdo->query("
                                SELECT v.id_vehicle, v.plate_number, d.full_name as driver_name, d.id_driver
                                FROM vehicles v 
                                JOIN drivers d ON v.id_driver = d.id_driver 
                                ORDER BY v.plate_number
                            ");
                            while($v = $v_stmt->fetch()): 
                            ?>
                                <option value="<?= $v['id_vehicle'] ?>" 
                                        data-driver-id="<?= $v['id_driver'] ?>"
                                        data-driver-name="<?= htmlspecialchars($v['driver_name']) ?>">
                                    <?= htmlspecialchars($v['plate_number']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <small class="text-muted">При выборе авто водитель заполнится автоматически</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Владелец</label>
                        <select name="id_driver" id="fineDriver" class="form-select" required>
                            <option value="">-- Выберите водителя --</option>
                            <?php foreach ($drivers_list as $dr): ?>
                                <option value="<?= $dr['id_driver'] ?>"><?= htmlspecialchars($dr['full_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
            
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Тип штрафа</label>
                        <select name="id_fine_type" class="form-select" required>
                            <?php
                            $ft_stmt = $pdo->query("SELECT id_fine_type, name FROM fine_types ORDER BY id_fine_type");
                            while($ft = $ft_stmt->fetch()):
                            ?>
                                <option value="<?= $ft['id_fine_type'] ?>"><?= htmlspecialchars($ft['name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Сумма (₽)</label>
                        <input type="number" step="0.01" name="amount" id="fineAmount" class="form-control" required min="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Комментарий</label>
                        <textarea name="comment" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-danger">Подтвердить штраф</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Объект с данными автомобилей (для JavaScript)
const vehiclesData = {
    <?php 
    $v_stmt = $pdo->query("
        SELECT v.id_vehicle, v.plate_number, d.full_name, d.id_driver
        FROM vehicles v 
        JOIN drivers d ON v.id_driver = d.id_driver 
        ORDER BY v.plate_number
    ");
    $vehicles = [];
    while($v = $v_stmt->fetch()) {
        $vehicles[] = $v;
    }
    foreach ($vehicles as $v): 
    ?>
    <?= $v['id_vehicle'] ?>: {
        driverId: <?= $v['id_driver'] ?>,
        driverName: '<?= htmlspecialchars($v['full_name'], ENT_QUOTES) ?>',
        plateNumber: '<?= htmlspecialchars($v['plate_number'], ENT_QUOTES) ?>'
    },
    <?php endforeach; ?>
};

// Функция открытия модального окна
function openAddFineModal() {
    // Сбрасываем форму
    document.getElementById('fineForm').reset();
    
    // Включаем поле водителя (на случай, если было заблокировано)
    document.getElementById('fineDriver').disabled = false;
    
    const modal = new bootstrap.Modal(document.getElementById('fineModal'));
    modal.show();
}

// Автоматическое заполнение водителя при выборе автомобиля
document.getElementById('fineVehicle').addEventListener('change', function() {
    const vehicleId = this.value;
    const driverSelect = document.getElementById('fineDriver');
    
    if (vehicleId === '') {
        // Если выбрано "Без привязки к авто" - разблокируем выбор водителя
        driverSelect.disabled = false;
        driverSelect.value = '';
        // Убираем подсветку
        driverSelect.classList.remove('border-success');
    } else {
        // Получаем данные автомобиля
        const vehicle = vehiclesData[vehicleId];
        if (vehicle) {
            // Автоматически выбираем водителя
            driverSelect.value = vehicle.driverId;
            // Блокируем изменение водителя
            driverSelect.disabled = true;
            // Визуально показываем, что поле автоматически заполнено
            driverSelect.classList.add('border-success');
            
            // Показываем подсказку
            showAutoFillMessage('Водитель автоматически выбран: ' + vehicle.driverName);
        }
    }
});



// Отправка формы штрафа через AJAX
document.getElementById('fineForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // ВАЖНО: Разблокируем поле водителя ДО создания FormData
    const driverSelect = document.getElementById('fineDriver');
    const wasDisabled = driverSelect.disabled;
    
    if (wasDisabled) {
        driverSelect.disabled = false;
    }
    
    // Теперь создаем FormData (после разблокировки)
    const formData = new FormData(this);
    
    // Проверяем, что водитель выбран
    if (!formData.get('id_driver')) {
        alert('Пожалуйста, выберите водителя');
        // Возвращаем блокировку обратно
        if (wasDisabled) {
            driverSelect.disabled = true;
        }
        return;
    }
    
    // Отправляем запрос
    fetch('?page=fines&action=add', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('fineModal'));
            modal.hide();
            location.reload();
        } else {
            alert('Ошибка: ' + (data.error || 'Неизвестная ошибка'));
        }
    })
    .catch(error => {
        alert('Ошибка отправки: ' + error.message);
    })
    .finally(() => {
        // В любом случае возвращаем блокировку обратно, если она была
        if (wasDisabled) {
            driverSelect.disabled = true;
        }
    });
});


// Удаление штрафа
function deleteFine(fineId) {
    if (!confirm('Удалить штраф №' + fineId + '? Это действие нельзя отменить.')) {
        return;
    }
    
    fetch('?page=fines&action=delete&id=' + fineId, {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Ошибка: ' + (data.error || 'Не удалось удалить штраф'));
        }
    })
    .catch(error => {
        alert('Ошибка: ' + error.message);
    });
}

// Инициализация DataTable
$(document).ready(function() {
    if ($.fn.DataTable.isDataTable('#finesTable')) {
        $('#finesTable').DataTable().destroy();
    }
    
    if ($('#finesTable').length > 0) {
        $('#finesTable').DataTable({
            language: { 
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/ru.json' 
            },
            pageLength: 25,
            responsive: true,
            order: [[0, 'desc']]
        });
    }
});
</script>