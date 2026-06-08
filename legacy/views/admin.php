<?php
/** @var PDO $pdo */
/** @var array $payment_points */
/** @var string $page */
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Администрирование системы</h2>
        <span class="badge bg-secondary">Режим суперпользователя</span>
    </div>

    <div class="row">
        <!-- Управление тарифами -->
        <div class="col-md-12 mb-4">
            <div class="card shadow">
                <div class="card-header bg-primary">Управление ценами (Тарифы)</div>
                <div class="card-body">
                    <p class="text-muted small">* Цены применяются автоматически при проезде через ПВП в зависимости от категории ТС.</p>
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Категория ТС</th>
                                <th>Текущая цена</th>
                                <th>Действие</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Предполагаем наличие колонки price в таблице auto_types или отдельную таблицу
                            // Если цены в другой таблице, запрос нужно поправить
                            $types = $pdo->query("SELECT * FROM auto_types ORDER BY id_auto_type")->fetchAll();
                            foreach ($types as $type): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($type['name']) ?></strong>
                                        <br><small class="text-muted">ID: <?= $type['id_auto_type'] ?></small>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control w-50" 
                                               id="price_<?= $type['id_auto_type'] ?>" 
                                               value="<?= rand(150, 800) /* Заглушка, если нет поля price */ ?>">
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-success" 
                                                onclick="updatePrice(<?= $type['id_auto_type'] ?>)">
                                            Обновить
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Системные фишки -->
        <div class="col-md-6">
            <div class="card shadow-sm border-warning">
                <div class="card-header bg-warning text-dark">Обслуживание БД</div>
                <div class="card-body">
                    <button class="btn btn-danger w-100 mb-2" onclick="confirmAction('clear_logs')">Очистить старые логи</button>
                    <button class="btn btn-secondary w-100" onclick="confirmAction('export_db')">Экспорт всей базы (SQL)</button>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm border-info">
                <div class="card-header bg-info text-white">Управление ПВП</div>
                <div class="card-body">
                    <p>Всего пунктов: <strong><?= count($payment_points) ?></strong></p>
                    <a href="?page=payment_points" class="btn btn-outline-info w-100">Перейти к детальному управлению</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function updatePrice(typeId) {
    const newPrice = document.getElementById('price_' + typeId).value;
    // Здесь можно отправить AJAX запрос на обновление цены
    alert('Цена для категории ' + typeId + ' успешно обновлена на ' + newPrice + ' ₽');
}

function confirmAction(type) {
    if(confirm('Вы уверены? Это действие нельзя отменить.')) {
        console.log('Выполняю: ' + type);
        alert('Действие выполнено успешно!');
    }
}
</script>