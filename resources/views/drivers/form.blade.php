@php $isEdit = isset($driver) && $driver; @endphp
<div class="p-2">
    <form id="driverForm" method="POST" action="{{ $isEdit ? route('drivers.update', $driver) : route('drivers.store') }}">
        @csrf
        @if($isEdit) @method('PUT') @endif
        <div class="row g-4">
            <div class="col-md-6">
                <label class="form-label fw-semibold">ФИО <span class="text-danger">*</span></label>
                <input type="text" name="full_name" class="form-control form-control-modern" required value="{{ $driver->full_name ?? '' }}">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Телефон</label>
                <input type="text" name="phone_number" class="form-control form-control-modern" value="{{ $driver->phone_number ?? '' }}">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Дата рождения</label>
                <input type="date" name="birth_date" class="form-control form-control-modern" value="{{ isset($driver) && $driver->birth_date ? $driver->birth_date->format('Y-m-d') : '' }}">
            </div>
            @if(!$isEdit)
            <div class="col-md-6">
                <label class="form-label fw-semibold">Начальный баланс (₽)</label>
                <input type="number" step="0.01" name="personal_balance" class="form-control form-control-modern" value="0">
            </div>
            @endif
        </div>
        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
            <button type="button" class="btn btn-outline-secondary btn-modern" data-bs-dismiss="modal">Отмена</button>
            <button type="submit" class="btn btn-primary btn-modern px-4">
                <i class="bi bi-check-lg me-1"></i>{{ $isEdit ? 'Сохранить' : 'Зарегистрировать' }}
            </button>
        </div>
    </form>
</div>

<script>
    document.getElementById('driverForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = this.querySelector('[type="submit"]');
        btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Сохранение...';
        fetch(this.action, { method: 'POST', body: new FormData(this), headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
            .then(r => r.json())
            .then(d => {
                if (d.success) { showToast('{{ $isEdit ? "Данные обновлены" : "Водитель зарегистрирован" }}'); setTimeout(() => location.reload(), 500); }
                else { showToast(d.error || 'Ошибка', 'error'); btn.disabled = false; btn.textContent = '{{ $isEdit ? "Сохранить" : "Зарегистрировать" }}'; }
            })
            .catch(() => { showToast('Ошибка соединения', 'error'); btn.disabled = false; btn.textContent = '{{ $isEdit ? "Сохранить" : "Зарегистрировать" }}'; });
    });
</script>
