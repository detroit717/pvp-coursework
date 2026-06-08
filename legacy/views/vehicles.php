<?php
/** @var PDO $pdo */
/** @var array $auto_types */
/** @var array $drivers_list */
?>
<div class="container mt-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Автомобили</h2>
        <button class="btn btn-primary" onclick="openAddVehicleModal()">+ Добавить</button>
    </div>

    <!-- Фильтры -->
    <div class="filter-section">
        <div class="filters-row">
            <div class="filter-item">
                <label>Тип авто</label>
                <select id="filterAutoType" class="form-select">
                    <option value="">Все</option>
                    <?php foreach ($auto_types as $at): ?>
                        <option value="<?= htmlspecialchars($at['name']) ?>"><?= htmlspecialchars($at['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-item">
                <label>Водитель</label>
                <select id="filterDriver" class="form-select">
                    <option value="">Все</option>
                    <?php foreach ($drivers_list as $dr): ?>
                        <option value="<?= htmlspecialchars($dr['full_name']) ?>"><?= htmlspecialchars($dr['full_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-item">
                <label>Поиск (госномер / название)</label>
                <input type="text" id="filterSearch" class="form-control" placeholder="Введите текст...">
            </div>
            <div class="filter-item d-flex align-items-end">
                <button id="resetFilters" class="btn btn-secondary">Сбросить</button>
            </div>
        </div>
    </div>

    <!-- Таблица -->
    <table class="table table-bordered" id="vehiclesTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Госномер</th>
                <th>Название</th>
                <th>Тип авто</th>
                <th>Владелец</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $stmt = $pdo->query("
                SELECT 
                    v.id_vehicle,
                    v.plate_number,
                    v.name,
                    at.name as auto_type_name,
                    d.full_name as driver_name
                FROM vehicles v
                JOIN auto_types at ON v.id_auto_type = at.id_auto_type
                JOIN drivers d ON v.id_driver = d.id_driver
                ORDER BY v.id_vehicle
            ");
            while ($row = $stmt->fetch()):
            ?>
            <tr>
                <td><?= $row['id_vehicle'] ?></td>
                <td><?= htmlspecialchars($row['plate_number']) ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['auto_type_name']) ?></td>
                <td>
                    <a href="?page=drivers&search=<?= urlencode($row['driver_name']) ?>">
                        <?= htmlspecialchars($row['driver_name']) ?>
                    </a>
                </td>
                <td>
                    <button class="btn btn-sm btn-warning" onclick="openEditVehicleModal(<?= $row['id_vehicle'] ?>)">✏️</button>
                    <button class="btn btn-sm btn-danger" onclick="confirmDelete('?page=vehicles&action=delete&id=<?= $row['id_vehicle'] ?>', '<?= htmlspecialchars($row['plate_number']) ?>')">🗑️</button>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Модальное окно для добавления/редактирования автомобиля -->
<div class="modal fade" id="vehicleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="vehicleModalTitle">Добавить автомобиль</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="vehicleForm">
                <input type="hidden" name="id_vehicle" id="vehicleId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Госномер</label>
                        <input type="text" name="plate_number" id="plateNumber" class="form-control font-monospace" placeholder="А123ВС77" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Марка / Модель</label>
                        <input type="text" name="name" id="vehicleName" class="form-control" placeholder="Lada Vesta" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Тип автомобиля</label>
                        <select name="id_auto_type" id="autoType" class="form-select" required>
                            <?php foreach ($auto_types as $at): ?>
                                <option value="<?= $at['id_auto_type'] ?>"><?= htmlspecialchars($at['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Владелец</label>
                        <select name="id_driver" id="driverSelect" class="form-select" required>
                            <option value="">-- Выберите водителя --</option>
                            <?php foreach ($drivers_list as $dr): ?>
                                <option value="<?= $dr['id_driver'] ?>"><?= htmlspecialchars($dr['full_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Открытие модального окна для добавления
function openAddVehicleModal() {
    document.getElementById('vehicleModalTitle').textContent = 'Добавить автомобиль';
    document.getElementById('vehicleForm').action = '?page=vehicles&action=add';
    document.getElementById('vehicleId').value = '';
    document.getElementById('plateNumber').value = '';
    document.getElementById('vehicleName').value = '';
    document.getElementById('autoType').selectedIndex = 0;
    document.getElementById('driverSelect').selectedIndex = 0;
    
    const modal = new bootstrap.Modal(document.getElementById('vehicleModal'));
    modal.show();
}

// Открытие модального окна для редактирования
function openEditVehicleModal(vehicleId) {
    fetch(`?page=vehicles&action=get_vehicle_data&id=${vehicleId}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
                return;
            }
            
            document.getElementById('vehicleModalTitle').textContent = 'Редактировать автомобиль';
            document.getElementById('vehicleForm').action = `?page=vehicles&action=edit&id=${vehicleId}`;
            document.getElementById('vehicleId').value = data.id_vehicle;
            document.getElementById('plateNumber').value = data.plate_number;
            document.getElementById('vehicleName').value = data.name;
            document.getElementById('autoType').value = data.id_auto_type;
            document.getElementById('driverSelect').value = data.id_driver;
            
            const modal = new bootstrap.Modal(document.getElementById('vehicleModal'));
            modal.show();
        })
        .catch(error => {
            alert('Ошибка загрузки данных: ' + error.message);
        });
}

// Обработка отправки формы
document.getElementById('vehicleForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const url = this.action;
    
    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Закрываем модальное окно
            const modal = bootstrap.Modal.getInstance(document.getElementById('vehicleModal'));
            modal.hide();
            // Перезагружаем страницу
            location.reload();
        } else {
            alert('Ошибка: ' + (data.error || 'Неизвестная ошибка'));
        }
    })
    .catch(error => {
        alert('Ошибка отправки: ' + error.message);
    });
});

$(document).ready(function() {
    // Уничтожаем старую инициализацию, если есть
    if ($.fn.DataTable.isDataTable('#vehiclesTable')) {
        $('#vehiclesTable').DataTable().destroy();
    }
    
    var table = $('#vehiclesTable').DataTable({
        language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/ru.json' },
        pageLength: 25,
        responsive: true,
        searching: true
    });
    
    // Функция обновления фильтров
    function applyFilters() {
        var autoType = $('#filterAutoType').val();
        var driver = $('#filterDriver').val();
        var searchText = $('#filterSearch').val();
        
        table.columns().search('');
        
        if (autoType !== '') {
            table.column(3).search('^' + autoType + '$', true, false).draw();
        }
        if (driver !== '') {
            table.column(4).search('^' + driver + '$', true, false).draw();
        }
        if (searchText !== '') {
            table.search(searchText).draw();
        } else {
            table.search('').draw();
        }
    }
    
    $('#filterAutoType, #filterDriver').on('change', applyFilters);
    $('#filterSearch').on('keyup', applyFilters);
    
    $('#resetFilters').on('click', function() {
        $('#filterAutoType').val('');
        $('#filterDriver').val('');
        $('#filterSearch').val('');
        applyFilters();
    });
});
</script>