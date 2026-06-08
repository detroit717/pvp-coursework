<?php
/** @var array|null $driver */
$isEdit = $driver !== null;
$action_url = $isEdit 
    ? "?page=drivers&action=edit&id={$driver['id_driver']}" 
    : "?page=drivers&action=add";
?>

<div class="p-1">
    <form id="driverForm" method="POST" action="<?= $action_url ?>">
        <div class="mb-3">
            <label class="form-label">ФИО</label>
            <input type="text" name="full_name" class="form-control" required
                   value="<?= htmlspecialchars($driver['full_name'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Телефон</label>
            <input type="text" name="phone_number" class="form-control" required
                   value="<?= htmlspecialchars($driver['phone_number'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Дата рождения</label>
            <input type="date" name="birth_date" class="form-control"
                   value="<?= htmlspecialchars($driver['birth_date'] ?? '') ?>">
        </div>
        <?php if (!$isEdit): ?>
        <div class="mb-3">
            <label class="form-label">Начальный баланс (₽)</label>
            <input type="number" step="0.01" name="personal_balance" class="form-control" value="0">
        </div>
        <?php endif; ?>
        <div class="d-flex justify-content-end gap-2 mt-3">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
            <button type="submit" class="btn btn-primary">Сохранить</button>
        </div>
    </form>
</div>

<script>
// Отправка формы через AJAX
document.getElementById('driverForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Сохранение...';
    
    fetch(this.action, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Ошибка: ' + (data.error || 'Неизвестная ошибка'));
            submitBtn.disabled = false;
            submitBtn.textContent = 'Сохранить';
        }
    })
    .catch(error => {
        alert('Ошибка отправки: ' + error.message);
        submitBtn.disabled = false;
        submitBtn.textContent = 'Сохранить';
    });
});
</script>