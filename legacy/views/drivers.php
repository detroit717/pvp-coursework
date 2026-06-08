<?php
/** @var PDO $pdo */
/** @var array $payment_methods */

// Статистика для верхних карточек
$stats = $pdo->query("SELECT COUNT(*) as total, SUM(personal_balance) as total_money FROM drivers")->fetch();
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1 text-dark">Реестр водителей</h2>
            <p class="text-muted small mb-0">Управление учетными записями и финансовыми балансами</p>
        </div>
        <button class="btn btn-primary shadow-sm" onclick="openAddDriverModal()">
            <i class="bi bi-person-plus-fill me-2"></i>Новый водитель
        </button>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-white">
                <div class="card-body">
                    <div class="text-muted small fw-bold text-uppercase">Всего активных</div>
                    <h3 class="mb-0 text-primary"><?= number_format($stats['total']) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-white">
                <div class="card-body">
                    <div class="text-muted small fw-bold text-uppercase">Средства клиентов</div>
                    <h3 class="mb-0 text-success"><?= number_format($stats['total_money'] ?? 0, 2, ',', ' ') ?> ₽</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0" id="driversTable">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Водитель</th>
                        <th>Телефон</th>
                        <th>Баланс</th>
                        <th class="text-end pe-4">Управление</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $pdo->query("SELECT * FROM drivers ORDER BY full_name");
                    while ($row = $stmt->fetch()):
                        $isLowBalance = ($row['personal_balance'] < 100);
                    ?>
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center">
                                <div class="avatar-sq me-3"><?= mb_substr($row['full_name'], 0, 1) ?></div>
                                <div>
                                    <div class="fw-bold"><?= htmlspecialchars($row['full_name']) ?></div>
                                    <div class="text-muted tiny">ID: <?= $row['id_driver'] ?></div>
                                </div>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($row['phone_number']) ?></td>
                        <td>
                            <span class="badge <?= $isLowBalance ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' ?> px-2 py-2">
                                <?= number_format($row['personal_balance'], 2, ',', ' ') ?> ₽
                            </span>
                        </td>
                        <td class="text-end pe-4">
                            <button class="btn btn-sm btn-outline-primary me-1" 
                                    onclick="viewDriverCard(<?= $row['id_driver'] ?>)" 
                                    title="Открыть профиль">
                                <i class="bi bi-eye-fill me-1"></i> Профиль
                            </button>
                            
                            <button class="btn btn-sm btn-light text-danger" 
                                    onclick="confirmDelete('?page=drivers&action=delete&id=<?= $row['id_driver'] ?>', '<?= htmlspecialchars($row['full_name']) ?>')"
                                    title="Удалить">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- МОДАЛЬНОЕ ОКНО: Карточка водителя (Профиль) -->
<div class="modal fade" id="driverCardModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-person-vcard me-2"></i>Карточка водителя</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" id="driverCardContent">
                <div class="text-center p-5">
                    <div class="spinner-border text-primary"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Открытие модалки для нового водителя
function openAddDriverModal() {
    const modalEl = document.getElementById('driverCardModal');
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    
    // Меняем заголовок
    modalEl.querySelector('.modal-title').innerHTML = '<i class="bi bi-person-plus-fill me-2"></i>Регистрация нового водителя';
    
    // Загружаем форму
    fetch('?page=drivers&action=add_form')
        .then(r => r.text())
        .then(html => {
            document.getElementById('driverCardContent').innerHTML = html;
            modal.show();
        });
}
// Глобальная функция пополнения баланса (доступна из карточки водителя)
window.refillBalance = function(driverId) {
    const amountInput = document.getElementById('refillAmount');
    const amount = parseFloat(amountInput.value);
    const messageDiv = document.getElementById('refillMessage');
    
    if (!amount || amount <= 0) {
        if (messageDiv) {
            messageDiv.style.display = 'block';
            messageDiv.className = 'alert alert-danger py-2 small mt-2';
            messageDiv.textContent = 'Пожалуйста, введите корректную сумму';
        }
        if (amountInput) amountInput.focus();
        return;
    }

    const btn = document.querySelector('#driverCardContent .btn-success');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Пополнение...';
    }
    
    if (messageDiv) {
        messageDiv.style.display = 'block';
        messageDiv.className = 'alert alert-info py-2 small mt-2';
        messageDiv.textContent = 'Выполняется пополнение...';
    }

    const formData = new FormData();
    formData.append('amount', amount);

    fetch(`?page=drivers&action=add_balance&id=${driverId}`, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            if (messageDiv) {
                messageDiv.className = 'alert alert-success py-2 small mt-2';
                messageDiv.textContent = '✅ Баланс успешно пополнен на ' + amount.toFixed(2) + ' ₽';
            }
            
            // Обновляем и карточку, и страницу
            setTimeout(() => {
                // Сначала обновляем карточку водителя
                viewDriverCard(driverId);
                // Затем обновляем данные на странице
                refreshDriversTable();
            }, 1000);
        } else {
            if (messageDiv) {
                messageDiv.className = 'alert alert-danger py-2 small mt-2';
                messageDiv.textContent = '❌ Ошибка: ' + (data.error || 'Неизвестная ошибка');
            }
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-cash-coin me-1"></i>Пополнить';
            }
        }
    })
    .catch(err => {
        if (messageDiv) {
            messageDiv.className = 'alert alert-danger py-2 small mt-2';
            messageDiv.textContent = '❌ Ошибка соединения с сервером';
        }
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-cash-coin me-1"></i>Пополнить';
        }
    });
};

// Функция обновления таблицы водителей без перезагрузки страницы
function refreshDriversTable() {
    // Получаем обновленные данные через AJAX
    fetch('?page=drivers&action=get_table_data')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Обновляем статистику в карточках
                const statsCards = document.querySelectorAll('.col-md-3 .card-body h3');
                if (statsCards.length >= 2) {
                    statsCards[0].textContent = data.total_drivers;
                    statsCards[1].textContent = data.total_balance + ' ₽';
                }
                
                // Обновляем таблицу через DataTables API
                const table = $('#driversTable').DataTable();
                table.clear();
                
                data.drivers.forEach(function(driver) {
                    const isLowBalance = driver.personal_balance < 100;
                    const badgeClass = isLowBalance ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success';
                    
                    table.row.add([
                        `<div class="d-flex align-items-center">
                            <div class="avatar-sq me-3">${driver.full_name.charAt(0)}</div>
                            <div>
                                <div class="fw-bold">${driver.full_name}</div>
                                <div class="text-muted tiny">ID: ${driver.id_driver}</div>
                            </div>
                        </div>`,
                        driver.phone_number,
                        `<span class="badge ${badgeClass} px-2 py-2">${parseFloat(driver.personal_balance).toLocaleString('ru-RU', {minimumFractionDigits: 2, maximumFractionDigits: 2})} ₽</span>`,
                        `<button class="btn btn-sm btn-outline-primary me-1" onclick="viewDriverCard(${driver.id_driver})" title="Открыть профиль">
                            <i class="bi bi-eye-fill me-1"></i> Профиль
                        </button>
                        <button class="btn btn-sm btn-light text-danger" onclick="confirmDelete('?page=drivers&action=delete&id=${driver.id_driver}', '${driver.full_name}')" title="Удалить">
                            <i class="bi bi-trash"></i>
                        </button>`
                    ]);
                });
                
                table.draw();
            }
        })
        .catch(error => {
            console.error('Ошибка обновления таблицы:', error);
            // Если AJAX не удался, просто перезагружаем страницу
            location.reload();
        });
}

// Открытие карточки водителя
function viewDriverCard(id) {
    const modalEl = document.getElementById('driverCardModal');
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    
    // Возвращаем стандартный заголовок
    modalEl.querySelector('.modal-title').innerHTML = '<i class="bi bi-person-vcard me-2"></i>Карточка водителя';
    
    // Загружаем карточку
    fetch(`?page=drivers&action=get_card&id=${id}`)
        .then(r => r.text())
        .then(html => {
            document.getElementById('driverCardContent').innerHTML = html;
            modal.show();
        });
}

// Открытие модалки для нового водителя
function openAddDriverModal() {
    const modalEl = document.getElementById('driverCardModal');
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    
    modalEl.querySelector('.modal-title').innerHTML = '<i class="bi bi-person-plus-fill me-2"></i>Регистрация нового водителя';
    
    fetch('?page=drivers&action=add_form')
        .then(r => r.text())
        .then(html => {
            document.getElementById('driverCardContent').innerHTML = html;
            modal.show();
        });
}

window.parentViewDriverCard = function(id) {
    viewDriverCard(id);
};

// Открытие карточки водителя
function viewDriverCard(id) {
    const modalEl = document.getElementById('driverCardModal');
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    
    // Возвращаем стандартный заголовок
    modalEl.querySelector('.modal-title').innerHTML = '<i class="bi bi-person-vcard me-2"></i>Карточка водителя';
    
    // Загружаем карточку
    fetch(`?page=drivers&action=get_card&id=${id}`)
        .then(r => r.text())
        .then(html => {
            document.getElementById('driverCardContent').innerHTML = html;
            modal.show();
        });
}

// Инициализация DataTable
$(document).ready(function() {
    if ($.fn.DataTable.isDataTable('#driversTable')) {
        $('#driversTable').DataTable().destroy();
    }
    
    if ($('#driversTable').length > 0) {
        $('#driversTable').DataTable({
            language: { 
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/ru.json' 
            },
            pageLength: 25,
            responsive: true
        });
    }
});
</script>