@php $d = $driver; @endphp
<div class="row">
    <div class="col-md-5 border-end pe-4">
        <div class="d-flex align-items-center mb-4">
            <div class="avatar-sq bg-primary text-white me-3" style="width: 60px; height: 60px; font-size: 1.8rem; border-radius: 14px;">
                {{ mb_substr($d->full_name, 0, 1) }}
            </div>
            <div>
                <h4 class="mb-0 fw-bold">{{ $d->full_name }}</h4>
                <small class="text-muted">ID Клиента: #{{ $d->id_driver }}</small>
            </div>
        </div>

        <div class="mb-3">
            <label class="text-uppercase small fw-bold text-muted mb-2">Личные данные</label>
            <div class="d-flex align-items-center mb-2"><i class="bi bi-telephone me-2 text-primary"></i><strong>{{ $d->phone_number ?? 'Не указан' }}</strong></div>
            <div class="d-flex align-items-center mb-2"><i class="bi bi-calendar-event me-2 text-primary"></i><strong>{{ $d->birth_date ? $d->birth_date->format('d.m.Y') : 'Не указана' }}</strong></div>
        </div>

        <div class="mb-3">
            <label class="text-uppercase small fw-bold text-muted mb-2">Финансовое состояние</label>
            <div class="card bg-light border-0 rounded-4 p-3 mb-3">
                <div class="row g-3">
                    <div class="col-6 text-center">
                        <div class="small text-muted mb-1">Баланс</div>
                        <h3 class="mb-0 {{ $d->personal_balance < 100 ? 'text-danger' : 'text-success' }}">{{ number_format($d->personal_balance, 2, ',', ' ') }} ₽</h3>
                    </div>
                    <div class="col-6 text-center">
                        <div class="small text-muted mb-1">Долг по штрафам</div>
                        <h4 class="mb-0 {{ $debt > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($debt, 2, ',', ' ') }} ₽</h4>
                    </div>
                </div>
                <div class="input-group mt-3">
                    <input type="number" id="refillAmount" class="form-control" placeholder="Сумма пополнения" value="500" step="0.01">
                    <button class="btn btn-success" onclick="refillBalance({{ $d->id_driver }})">
                        <i class="bi bi-cash-coin me-1"></i>Пополнить
                    </button>
                </div>
                <div id="refillMessage" class="mt-2 small" style="display: none;"></div>
            </div>
        </div>

        <div>
            <label class="text-uppercase small fw-bold text-muted mb-2">Статистика поездок</label>
            <div class="list-group list-group-flush">
                <div class="list-group-item d-flex justify-content-between px-0 border-0">
                    <span class="text-muted">Всего проездов:</span>
                    <strong>{{ $stats->total_trips ?? 0 }}</strong>
                </div>
                <div class="list-group-item d-flex justify-content-between px-0 border-0">
                    <span class="text-muted">Потрачено всего:</span>
                    <strong>{{ number_format($stats->total_spent ?? 0, 2, ',', ' ') }} ₽</strong>
                </div>
                <div class="list-group-item d-flex justify-content-between px-0 border-0">
                    <span class="text-muted">Последняя поездка:</span>
                    <strong>{{ isset($stats->last_trip) ? \Carbon\Carbon::parse($stats->last_trip)->format('d.m.Y H:i') : 'Нет данных' }}</strong>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-7 ps-4">
        <div class="mb-4">
            <label class="text-uppercase small fw-bold text-muted mb-2">Автомобили ({{ $d->vehicles->count() }})</label>
            @if($d->vehicles->isEmpty())
                <div class="alert alert-light border text-center py-2 small">Нет привязанных ТС</div>
            @else
                <div class="list-group list-group-flush">
                    @foreach($d->vehicles as $v)
                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <div>
                            <span class="badge bg-dark font-monospace me-2">{{ $v->plate_number }}</span>
                            <span class="text-muted small">{{ $v->name ?? 'Марка не указана' }}</span>
                        </div>
                        <span class="badge bg-info-subtle text-info border">{{ $v->autoType->name ?? '—' }}</span>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div>
            <label class="text-uppercase small fw-bold text-muted mb-2">Транспондеры</label>
            @php $transponders = $d->vehicles->flatMap->transponders; @endphp
            @if($transponders->isEmpty())
                <p class="small text-muted fst-italic">Устройства не зарегистрированы</p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm small align-middle">
                        <thead class="table-light"><tr><th>Серийный №</th><th>Автомобиль</th><th>Статус</th></tr></thead>
                        <tbody>
                            @foreach($transponders as $tr)
                            <tr>
                                <td><code class="bg-light px-2 py-1 rounded">{{ $tr->serial_number }}</code></td>
                                <td><span class="fw-semibold">{{ $tr->vehicle->plate_number ?? '—' }}</span></td>
                                <td><span class="badge {{ $tr->status === 'активен' ? 'bg-success' : 'bg-secondary' }}">{{ $tr->status }}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div class="mt-4 pt-3 border-top d-flex justify-content-between">
            <button class="btn btn-sm btn-outline-danger" onclick="deleteDriver({{ $d->id_driver }}, '{{ addslashes($d->full_name) }}')">
                <i class="bi bi-person-x me-1"></i> Удалить
            </button>
            <button class="btn btn-sm btn-outline-secondary" onclick="editDriver({{ $d->id_driver }})">
                <i class="bi bi-gear me-1"></i> Редактировать
            </button>
        </div>
    </div>
</div>

<script>
    function refillBalance(id) {
        const amount = document.getElementById('refillAmount').value;
        const msg = document.getElementById('refillMessage');
        if (!amount || amount <= 0) { msg.style.display = 'block'; msg.className = 'mt-2 small alert alert-danger py-2'; msg.textContent = 'Введите корректную сумму'; return; }
        msg.style.display = 'block'; msg.className = 'mt-2 small alert alert-info py-2'; msg.textContent = 'Пополнение...';
        const formData = new FormData(); formData.append('amount', amount);
        fetch(`{{ url('drivers') }}/${id}/balance`, { method: 'POST', body: formData, headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
            .then(r => r.json())
            .then(d => {
                if (d.success) { msg.className = 'mt-2 small alert alert-success py-2'; msg.textContent = `✅ Баланс пополнен на ${amount} ₽`; setTimeout(() => location.reload(), 1000); }
                else { msg.className = 'mt-2 small alert alert-danger py-2'; msg.textContent = '❌ ' + (d.error || 'Ошибка'); }
            });
    }

    function editDriver(id) {
        const modal = bootstrap.Modal.getInstance(document.getElementById('driverModal'));
        if (modal) modal.hide();
        setTimeout(() => {
            const title = document.getElementById('driverModalTitle');
            const content = document.getElementById('driverModalContent');
            title.innerHTML = '<i class="bi bi-gear me-2"></i>Редактирование профиля';
            fetch(`{{ url('drivers') }}/${id}/edit`)
                .then(r => r.text())
                .then(html => { content.innerHTML = html; new bootstrap.Modal(document.getElementById('driverModal')).show(); });
        }, 300);
    }

    function deleteDriver(id, name) {
        if (!confirm(`Удалить "${name}"?`)) return;
        fetch(`{{ url('drivers') }}/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
            .then(r => r.json())
            .then(d => { if (d.success) { showToast('Водитель удален'); setTimeout(() => location.reload(), 500); } });
    }
</script>
