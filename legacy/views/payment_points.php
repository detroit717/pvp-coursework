<?php
/** @var PDO $pdo */

// Получаем расширенный список пунктов
$points_query = $pdo->query("
    SELECT p.*, 
    (SELECT COUNT(*) FROM lanes l WHERE l.id_point = p.id_point) as active_lanes,
    (SELECT COUNT(*) FROM transactions t WHERE t.id_point = p.id_point AND t.status = 'успешно') as total_transactions,
    (SELECT COALESCE(SUM(t.amount), 0) FROM transactions t WHERE t.id_point = p.id_point AND t.status = 'успешно') as total_revenue
    FROM payment_points p 
    ORDER BY p.id_point
");
$all_points = $points_query->fetchAll();

// Получаем все тарифы
$tariffs = $pdo->query("
    SELECT t.*, at.name as auto_type_name 
    FROM tariffs t 
    JOIN auto_types at ON t.id_auto_type = at.id_auto_type 
    ORDER BY at.name, t.time_start
")->fetchAll();

// Получаем типы авто для формы тарифов
$auto_types = $pdo->query("SELECT * FROM auto_types ORDER BY name")->fetchAll();
?>

<div class="container-fluid">
    <!-- Заголовок -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Пункты взимания платы (ПВП)</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="?page=dashboard">Главная</a></li>
                    <li class="breadcrumb-item active">Инфраструктура</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-info" onclick="openTariffsModal()">
                <i class="bi bi-currency-exchange me-2"></i>Тарифы
            </button>
            <button class="btn btn-primary shadow-sm" onclick="openPointModal()">
                <i class="bi bi-plus-circle me-2"></i>Добавить ПВП
            </button>
        </div>
    </div>

    <!-- Карточки пунктов -->
    <div class="row" id="points-grid">
        <?php foreach ($all_points as $p): 
            $limit = $p['lanes_count'] > 0 ? $p['lanes_count'] : 1;
            $percent = min(100, round(($p['active_lanes'] / $limit) * 100));
            $bar_color = $percent < 50 ? 'bg-warning' : ($percent >= 100 ? 'bg-danger' : 'bg-success');
            $revenue = $p['total_revenue'] ?? 0;
        ?>
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card h-100 shadow-sm border-0">
                    <div class="progress" style="height: 5px; border-radius: 0;">
                        <div class="progress-bar <?= $bar_color ?>" style="width: <?= $percent ?>%"></div>
                    </div>
                    
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="card-title text-dark mb-0 fw-bold"><?= htmlspecialchars($p['name']) ?></h5>
                            <span class="badge bg-light text-primary border">ID: <?= $p['id_point'] ?></span>
                        </div>
                        
                        <div class="row g-2 mb-3">
                            <div class="col-4">
                                <div class="p-2 border rounded bg-light text-center">
                                    <div class="small text-muted">Полосы</div>
                                    <div class="fw-bold text-primary"><?= $p['active_lanes'] ?>/<?= $p['lanes_count'] ?></div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 border rounded bg-light text-center">
                                    <div class="small text-muted">Проезды</div>
                                    <div class="fw-bold text-success"><?= number_format($p['total_transactions']) ?></div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 border rounded bg-light text-center">
                                    <div class="small text-muted">Выручка</div>
                                    <div class="fw-bold text-info"><?= number_format($revenue, 0, ',', ' ') ?> ₽</div>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button class="btn btn-dark" onclick="editLanes(<?= $p['id_point'] ?>, '<?= htmlspecialchars($p['name'], ENT_QUOTES) ?>')">
                                <i class="bi bi-diagram-3 me-2"></i>Конфигурация полос
                            </button>
                            <div class="btn-group">
                                <button class="btn btn-outline-secondary btn-sm" onclick='editPoint(<?= json_encode($p, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                                    <i class="bi bi-gear me-2"></i>Настройки
                                </button>
                                <button class="btn btn-outline-info btn-sm" onclick="viewPointStats(<?= $p['id_point'] ?>)">
                                    <i class="bi bi-graph-up me-2"></i>Статистика
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer bg-white border-top-0 d-flex justify-content-between">
                        <button class="btn btn-link btn-sm text-danger text-decoration-none p-0" 
                                onclick="confirmDelete('?page=payment_points&action=delete&id=<?= $p['id_point'] ?>', '<?= htmlspecialchars($p['name'], ENT_QUOTES) ?>')">
                            <i class="bi bi-trash me-1"></i>Удалить
                        </button>
                        <small class="text-muted">
                            <i class="bi bi-check-circle-fill text-success me-1"></i>Активен
                        </small>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Модальное окно: ПВП -->
<div class="modal fade" id="pointModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalTitle">Управление пунктом</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="pointForm">
                <div class="modal-body">
                    <input type="hidden" name="id" id="point_id">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Название пункта</label>
                        <input type="text" name="name" id="point_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Местоположение</label>
                        <input type="text" name="location" id="point_location" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Лимит полос</label>
                        <input type="number" name="lanes_count" id="point_lanes" class="form-control" min="1" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Модальное окно: Полосы -->
<div class="modal fade" id="lanesModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title">
                    <i class="bi bi-diagram-3-fill me-2 text-info"></i>
                    Полосы: <span id="lane_point_name" class="text-info"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Форма добавления/редактирования полосы -->
                <div class="card border-primary bg-light mb-4">
                    <div class="card-body">
                        <h6 class="card-title text-primary fw-bold mb-3" id="laneFormTitle">
                            <i class="bi bi-plus-circle me-1"></i>Добавить полосу
                        </h6>
                        <form id="laneForm">
                            <input type="hidden" id="lane_id" value="">
                            <div class="row g-3">
                                <div class="col-md-2">
                                    <label class="form-label small fw-bold">Номер</label>
                                    <input type="number" id="lane_number" class="form-control" min="1" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">Тип транспорта</label>
                                    <select id="lane_type" class="form-select" required>
                                        <option value="">Выберите</option>
                                        <option value="Легковой">🚗 Легковой</option>
                                        <option value="Грузовой">🚛 Грузовой</option>
                                        <option value="Мотоцикл">🏍️ Мотоцикл</option>
                                        <option value="Универсальный">🔄 Универсальный</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small fw-bold">Цена (₽)</label>
                                    <input type="number" step="0.01" id="lane_price" class="form-control" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">Статус</label>
                                    <select id="lane_status" class="form-select">
                                        <option value="активна">🟢 Активна</option>
                                        <option value="неактивна">🔴 Неактивна</option>
                                    </select>
                                </div>
                                <div class="col-md-2 d-flex align-items-end gap-1">
                                    <button type="submit" class="btn btn-primary flex-grow-1" id="laneSubmitBtn">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" id="laneCancelBtn" onclick="resetLaneForm()" style="display:none;">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Список полос -->
                <h6 class="fw-bold mb-3">Список полос <span class="badge bg-secondary ms-2" id="lanesCount">0</span></h6>
                <div id="lanes_list">
                    <div class="text-center p-4 text-muted">Загрузка...</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно: Тарифы -->
<div class="modal fade" id="tariffsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="bi bi-currency-exchange me-2"></i>Тарифы</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="card border-primary bg-light mb-4">
                    <div class="card-body">
                        <h6 class="card-title text-primary fw-bold"><i class="bi bi-plus-circle me-1"></i>Новый тариф</h6>
                        <form id="tariffForm">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">Тип авто</label>
                                    <select name="id_auto_type" class="form-select" required>
                                        <option value="">Выберите</option>
                                        <?php foreach ($auto_types as $at): ?>
                                            <option value="<?= $at['id_auto_type'] ?>"><?= htmlspecialchars($at['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small fw-bold">Сумма (₽)</label>
                                    <input type="number" step="0.01" name="amount" class="form-control" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small fw-bold">Время с</label>
                                    <input type="time" name="time_start" class="form-control" value="00:00">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small fw-bold">Время по</label>
                                    <input type="time" name="time_end" class="form-control" value="23:59">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small fw-bold">День</label>
                                    <select name="day_of_week" class="form-select">
                                        <option value="">Любой</option>
                                        <option value="1">Пн</option><option value="2">Вт</option>
                                        <option value="3">Ср</option><option value="4">Чт</option>
                                        <option value="5">Пт</option><option value="6">Сб</option>
                                        <option value="7">Вс</option>
                                    </select>
                                </div>
                                <div class="col-md-1 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-plus"></i></button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th><th>Тип авто</th><th>Сумма</th>
                                <th>Время</th><th>День</th><th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tariffs as $t): ?>
                                <tr>
                                    <td><span class="badge bg-secondary">#<?= $t['id_tariff'] ?></span></td>
                                    <td><?= htmlspecialchars($t['auto_type_name']) ?></td>
                                    <td><strong><?= number_format($t['amount'], 2) ?> ₽</strong></td>
                                    <td><?= substr($t['time_start'], 0, 5) ?> - <?= substr($t['time_end'], 0, 5) ?></td>
                                    <td><?php
                                        $days = ['', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'];
                                        echo $t['day_of_week'] ? $days[$t['day_of_week']] : 'Ежедневно';
                                    ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteTariff(<?= $t['id_tariff'] ?>)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let currentPointId = null;

// ==================== ПВП ====================
function openPointModal() {
    document.getElementById('modalTitle').innerHTML = '<i class="bi bi-plus-circle me-2"></i>Новый пункт';
    document.getElementById('pointForm').reset();
    document.getElementById('point_id').value = '';
    document.getElementById('pointForm').dataset.action = 'add';
    new bootstrap.Modal(document.getElementById('pointModal')).show();
}

function editPoint(data) {
    document.getElementById('modalTitle').innerHTML = '<i class="bi bi-gear me-2"></i>' + data.name;
    document.getElementById('point_id').value = data.id_point;
    document.getElementById('point_name').value = data.name;
    document.getElementById('point_location').value = data.location || '';
    document.getElementById('point_lanes').value = data.lanes_count;
    document.getElementById('pointForm').dataset.action = 'edit';
    document.getElementById('pointForm').dataset.id = data.id_point;
    new bootstrap.Modal(document.getElementById('pointModal')).show();
}

document.getElementById('pointForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    let url = '?page=payment_points&action=' + this.dataset.action;
    if (this.dataset.id) url += '&id=' + this.dataset.id;
    
    const btn = this.querySelector('[type="submit"]');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    
    fetch(url, { method: 'POST', body: formData })
        .then(r => r.json())
        .then(d => { if (d.success) location.reload(); else alert(d.error); })
        .catch(e => alert(e.message))
        .finally(() => { btn.disabled = false; btn.innerHTML = 'Сохранить'; });
});

// ==================== ПОЛОСЫ ====================
function editLanes(id, name) {
    currentPointId = id;
    document.getElementById('lane_point_name').innerText = name;
    resetLaneForm();
    loadLanes();
    new bootstrap.Modal(document.getElementById('lanesModal')).show();
}

function resetLaneForm() {
    document.getElementById('laneForm').reset();
    document.getElementById('lane_id').value = '';
    document.getElementById('laneFormTitle').innerHTML = '<i class="bi bi-plus-circle me-1"></i>Новая полоса';
    document.getElementById('laneSubmitBtn').className = 'btn btn-primary flex-grow-1';
    document.getElementById('laneSubmitBtn').innerHTML = '<i class="bi bi-check-lg"></i>';
    document.getElementById('laneCancelBtn').style.display = 'none';
}

function loadLanes() {
    const container = document.getElementById('lanes_list');
    container.innerHTML = '<div class="text-center p-4"><div class="spinner-border text-primary"></div></div>';
    
    fetch(`?page=payment_points&action=get_lanes&point_id=${currentPointId}`)
        .then(r => r.json())
        .then(lanes => {
            document.getElementById('lanesCount').textContent = lanes.length;
            
            if (!lanes.length) {
                container.innerHTML = '<div class="text-center text-muted p-4">Нет полос</div>';
                return;
            }
            
            const icons = { 'Легковой': '🚗', 'Грузовой': '🚛', 'Мотоцикл': '🏍️', 'Универсальный': '🔄' };
            const statusBadge = { 'активна': 'bg-success', 'неактивна': 'bg-secondary' };
            
            container.innerHTML = lanes.map(l => `
                <div class="d-flex justify-content-between align-items-center p-3 border rounded mb-2 bg-white">
                    <div class="d-flex align-items-center gap-3">
                        <span class="fs-4">${icons[l.lane_type] || '🛣️'}</span>
                        <div>
                            <div class="fw-bold">Полоса №${l.lane_number} — ${l.lane_type || 'Стандарт'}</div>
                            <div class="small text-muted">
                                <span class="badge ${statusBadge[l.lane_status] || 'bg-light'} me-2">${l.lane_status || 'активна'}</span>
                                Цена: <strong>${parseFloat(l.lane_price || 0).toFixed(2)} ₽</strong>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-warning" onclick="editLaneForm(${l.id_lane})">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteLane(${l.id_lane})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            `).join('');
        })
        .catch(() => container.innerHTML = '<div class="alert alert-danger">Ошибка загрузки</div>');
}

function editLaneForm(laneId) {
    fetch(`?page=payment_points&action=get_lane_details&id=${laneId}`)
        .then(r => r.json())
        .then(lane => {
            document.getElementById('lane_id').value = lane.id_lane;
            document.getElementById('lane_number').value = lane.lane_number;
            document.getElementById('lane_type').value = lane.lane_type || '';
            document.getElementById('lane_price').value = lane.lane_price || 0;
            document.getElementById('lane_status').value = lane.lane_status || 'активна';
            document.getElementById('laneFormTitle').innerHTML = '<i class="bi bi-pencil me-1"></i>Редактировать полосу №' + lane.lane_number;
            document.getElementById('laneSubmitBtn').className = 'btn btn-warning flex-grow-1';
            document.getElementById('laneSubmitBtn').innerHTML = '<i class="bi bi-check-lg"></i>';
            document.getElementById('laneCancelBtn').style.display = 'inline-block';
        });
}

document.getElementById('laneForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const laneId = document.getElementById('lane_id').value;
    const url = laneId 
        ? `?page=payment_points&action=update_lane&id=${laneId}`
        : '?page=payment_points&action=add_lane';
    
    const formData = new FormData();
    formData.append('id_point', currentPointId);
    formData.append('lane_number', document.getElementById('lane_number').value);
    formData.append('lane_type', document.getElementById('lane_type').value);
    formData.append('lane_price', document.getElementById('lane_price').value);
    formData.append('lane_status', document.getElementById('lane_status').value);
    
    fetch(url, { method: 'POST', body: formData })
        .then(r => r.json())
        .then(d => {
            if (d.success) { resetLaneForm(); loadLanes(); }
            else alert(d.error || 'Ошибка');
        });
});

function deleteLane(laneId) {
    if (!confirm('Удалить полосу?')) return;
    fetch(`?page=payment_points&action=delete_lane&id=${laneId}`, { method: 'POST' })
        .then(r => r.json())
        .then(d => { if (d.success) loadLanes(); else alert(d.error); });
}

// ==================== ТАРИФЫ ====================
function openTariffsModal() {
    new bootstrap.Modal(document.getElementById('tariffsModal')).show();
}

document.getElementById('tariffForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = this.querySelector('[type="submit"]');
    btn.disabled = true;
    
    fetch('?page=tariffs&action=add', { method: 'POST', body: new FormData(this) })
        .then(r => r.json())
        .then(d => { if (d.success) location.reload(); else alert(d.error); })
        .finally(() => btn.disabled = false);
});

function deleteTariff(id) {
    if (!confirm('Удалить тариф?')) return;
    fetch(`?page=tariffs&action=delete&id=${id}`, { method: 'POST' })
        .then(r => r.json())
        .then(d => { if (d.success) location.reload(); else alert(d.error); });
}

function viewPointStats(pointId) {
    window.location.href = `?page=statistics&point_id=${pointId}`;
}
</script>

<style>
.card { transition: all 0.3s; }
.card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.1) !important; }
</style>