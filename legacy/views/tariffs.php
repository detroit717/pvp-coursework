<?php
/** @var PDO $pdo */
// Получаем типы авто для выпадающего списка
$auto_types = $pdo->query("SELECT * FROM auto_types ORDER BY id_auto_type")->fetchAll();

// Получаем список тарифов с названиями типов авто
$tariffs = $pdo->query("
    SELECT t.*, at.name as auto_type_name 
    FROM tariffs t 
    JOIN auto_types at ON t.id_auto_type = at.id_auto_type 
    ORDER BY t.id_auto_type, t.day_of_week, t.time_start
")->fetchAll();

$days = [
    0 => 'Понедельник', 1 => 'Вторник', 2 => 'Среда', 
    3 => 'Четверг', 4 => 'Пятница', 5 => 'Суббота', 6 => 'Воскресенье'
];
?>

<div class="container mt-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-cash-stack me-2"></i>Сетка тарифов</h2>
        <span class="badge bg-info text-dark">Настройка стоимости проезда</span>
    </div>

    <div class="card shadow-sm mb-4 border-success">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Добавить новое правило цены</h5>
        </div>
        <div class="card-body bg-light">
            <form method="POST" action="?page=tariffs&action=add" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-bold">Тип авто</label>
                    <select name="id_auto_type" class="form-select" required>
                        <?php foreach ($auto_types as $at): ?>
                            <option value="<?= $at['id_auto_type'] ?>"><?= htmlspecialchars($at['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold">Сумма (₽)</label>
                    <input type="number" name="amount" class="form-control" placeholder="0.00" step="0.01" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold">Время С</label>
                    <input type="time" name="time_start" class="form-control" value="00:00" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold">Время ПО</label>
                    <input type="time" name="time_end" class="form-control" value="23:59" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">День недели</label>
                    <select name="day_of_week" class="form-select">
                        <option value="">Ежедневно</option>
                        <?php foreach ($days as $idx => $day): ?>
                            <option value="<?= $idx ?>"><?= $day ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-success px-4 shadow-sm">Создать тариф</button>
                </div>
            </form>
        </div>
    </div>

    <div class="table-responsive shadow-sm">
        <table class="table table-hover table-bordered bg-white">
            <thead class="table-dark">
                <tr>
                    <th>Тип транспорта</th>
                    <th>День</th>
                    <th>Период</th>
                    <th>Стоимость</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tariffs as $t): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($t['auto_type_name']) ?></strong></td>
                    <td>
                        <?php if (isset($t['day_of_week'])): ?>
                            <span class="badge bg-primary"><?= $days[$t['day_of_week']] ?></span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Каждый день</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <i class="bi bi-clock me-1"></i>
                        <?= substr($t['time_start'], 0, 5) ?> — <?= substr($t['time_end'], 0, 5) ?>
                    </td>
                    <td class="fs-5 text-success fw-bold"><?= number_format($t['amount'], 2) ?> ₽</td>
                    <td>
                        <button class="btn btn-outline-danger btn-sm" 
                                onclick="confirmDelete('?page=tariffs&action=delete&id=<?= $t['id_tariff'] ?>', 'тариф <?= $t['amount'] ?> руб.')">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>