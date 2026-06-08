<?php
/** @var PDO $pdo */
/** @var array $auto_types */
/** @var array $drivers_list */
/** @var array|null $vehicle */
$isEdit = $vehicle !== null;
$action_url = $isEdit 
    ? "?page=vehicles&action=edit&id={$vehicle['id_vehicle']}" 
    : "?page=vehicles&action=add";
?>
<div class="modal fade" id="vehicleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title"><?= $isEdit ? 'Редактировать автомобиль' : 'Добавить автомобиль' ?></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="vehicleForm" method="POST" action="<?= $action_url ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Госномер</label>
                        <input type="text" name="plate_number" class="form-control font-monospace" 
                               placeholder="А123ВС77" required 
                               value="<?= htmlspecialchars($vehicle['plate_number'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Марка / Модель</label>
                        <input type="text" name="name" class="form-control" 
                               placeholder="Lada Vesta" required
                               value="<?= htmlspecialchars($vehicle['name'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Тип автомобиля</label>
                        <select name="id_auto_type" class="form-select" required>
                            <?php foreach ($auto_types as $at): ?>
                                <option value="<?= $at['id_auto_type'] ?>" 
                                    <?= ($vehicle['id_auto_type'] ?? '') == $at['id_auto_type'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($at['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Владелец</label>
                        <select name="id_driver" class="form-select" required>
                            <option value="">-- Выберите водителя --</option>
                            <?php foreach ($drivers_list as $dr): ?>
                                <option value="<?= $dr['id_driver'] ?>"
                                    <?= ($vehicle['id_driver'] ?? '') == $dr['id_driver'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($dr['full_name']) ?>
                                </option>
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