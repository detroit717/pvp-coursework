@extends('layouts.app')

@section('title', 'Транспондеры - ПВП')

@push('styles')
<style>
    .serial-display {
        background: linear-gradient(135deg, #1a1a2e, #16213e);
        border-radius: 10px;
        padding: 0.5rem 1rem;
        font-family: 'Courier New', monospace;
        font-size: 1.2rem;
        letter-spacing: 2px;
        color: #00ff88;
        text-align: center;
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2>Транспондеры</h2>
            <p class="text-muted mb-0">Управление устройствами для автоматической оплаты</p>
        </div>
        <button class="btn btn-primary btn-modern shadow-sm" onclick="openTransponderModal()">
            <i class="bi bi-plus-circle me-2"></i>Назначить транспондер
        </button>
    </div>
</div>

<div class="card-modern">
    <div class="card-body p-0">
        <table class="table table-modern mb-0" id="transpondersTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Серийный номер</th>
                    <th>Автомобиль</th>
                    <th>Владелец</th>
                    <th>Статус</th>
                    <th class="text-end">Действия</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transponders as $t)
                <tr>
                    <td data-order="{{ $t->id_transponder }}"><span class="badge bg-secondary">#{{ $t->id_transponder }}</span></td>
                    <td><code class="serial-display d-inline-block px-3 py-1">{{ $t->serial_number }}</code></td>
                    <td>{{ $t->vehicle->plate_number ?? '—' }}</td>
                    <td>{{ $t->vehicle->driver->full_name ?? '—' }}</td>
                    <td>
                        <span class="badge badge-modern {{ $t->status === 'активен' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                            <i class="bi {{ $t->status === 'активен' ? 'bi-wifi' : 'bi-wifi-off' }} me-1"></i>
                            {{ $t->status }}
                        </span>
                    </td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-outline-warning btn-modern me-1" onclick="editTransponder({{ $t->id_transponder }}, '{{ $t->serial_number }}', {{ $t->id_vehicle }}, '{{ $t->status }}')">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger btn-modern" onclick="deleteTransponder({{ $t->id_transponder }})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade modal-modern" id="transponderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="transponderModalTitle">
                    <i class="bi bi-wifi me-2"></i>Назначить транспондер
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="transponderForm">
                <input type="hidden" name="id_transponder" id="transponderId">
                <div class="modal-body">
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Серийный номер (генерируется автоматически)</label>
                        <div class="input-group">
                            <input type="text" name="serial_number" id="serialNumber" class="form-control form-control-modern font-monospace" readonly required>
                            <button type="button" class="btn btn-outline-info" onclick="generateSerial()" title="Сгенерировать новый">
                                <i class="bi bi-arrow-repeat"></i>
                            </button>
                        </div>
                        <small class="text-muted">Уникальный идентификатор устройства. Генерируется системой.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Автомобиль</label>
                        <select name="id_vehicle" id="transponderVehicle" class="form-select form-control-modern" required>
                            <option value="">-- Выберите автомобиль --</option>
                            @foreach($vehicles as $v)
                            <option value="{{ $v->id_vehicle }}">{{ $v->plate_number }} ({{ $v->driver->full_name }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Статус</label>
                        <select name="status" id="transponderStatus" class="form-select form-control-modern" required>
                            <option value="активен">🟢 Активен</option>
                            <option value="неактивен">🔴 Неактивен</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-modern" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-primary btn-modern">
                        <i class="bi bi-check-lg me-1"></i>Сохранить
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#transpondersTable').DataTable({ language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/ru.json' }, pageLength: 25, responsive: true, order: [[0, 'desc']] });
    });

    function generateSerial() {
        fetch('{{ route("transponders.generate") }}')
            .then(r => r.json())
            .then(d => { document.getElementById('serialNumber').value = d.serial_number; });
    }

    function openTransponderModal() {
        document.getElementById('transponderForm').reset();
        document.getElementById('transponderId').value = '';
        document.getElementById('transponderModalTitle').innerHTML = '<i class="bi bi-wifi me-2"></i>Назначить транспондер';
        generateSerial();
        new bootstrap.Modal(document.getElementById('transponderModal')).show();
    }

    function editTransponder(id, serial, vehicleId, status) {
        document.getElementById('transponderModalTitle').innerHTML = '<i class="bi bi-pencil me-2"></i>Редактировать транспондер';
        document.getElementById('transponderId').value = id;
        document.getElementById('serialNumber').value = serial;
        document.getElementById('transponderVehicle').value = vehicleId;
        document.getElementById('transponderStatus').value = status;
        new bootstrap.Modal(document.getElementById('transponderModal')).show();
    }

    document.getElementById('transponderForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const id = document.getElementById('transponderId').value;
        const url = id ? `{{ url('transponders') }}/${id}` : '{{ url('transponders') }}';
        const formData = new FormData(this);
        if (id) formData.append('_method', 'PUT');
        else if (!document.getElementById('serialNumber').value) { showToast('Сгенерируйте серийный номер', 'error'); return; }

        fetch(url, { method: 'POST', body: formData, headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
            .then(r => r.json())
            .then(d => {
                if (d.success) { bootstrap.Modal.getInstance(document.getElementById('transponderModal')).hide(); showToast('Транспондер сохранен'); setTimeout(() => location.reload(), 500); }
                else showToast(d.error || 'Ошибка', 'error');
            });
    });

    function deleteTransponder(id) {
        if (!confirm('Удалить транспондер?')) return;
        fetch(`{{ url('transponders') }}/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
            .then(r => r.json()).then(d => { if (d.success) { showToast('Транспондер удален'); location.reload(); } });
    }
</script>
@endpush
