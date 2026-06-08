@extends('layouts.app')

@section('title', 'Автомобили - ПВП')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2>Автомобили</h2>
            <p class="text-muted mb-0">Реестр транспортных средств</p>
        </div>
        <button class="btn btn-primary btn-modern shadow-sm" onclick="openVehicleModal(null)">
            <i class="bi bi-plus-circle me-2"></i>Добавить
        </button>
    </div>
</div>

<div class="card-modern">
    <div class="card-body p-0">
        <table class="table table-modern mb-0" id="vehiclesTable">
            <thead>
                <tr>
                    <th>Госномер</th>
                    <th>Марка/Модель</th>
                    <th>Тип</th>
                    <th>Владелец</th>
                    <th class="text-end">Действия</th>
                </tr>
            </thead>
            <tbody>
                @foreach($vehicles as $v)
                <tr>
                    <td><code class="bg-light px-2 py-1 rounded fw-bold">{{ $v->plate_number }}</code></td>
                    <td>{{ $v->name ?? '—' }}</td>
                    <td><span class="badge bg-light text-dark border">{{ $v->autoType->name ?? '—' }}</span></td>
                    <td>
                        <a href="{{ route('drivers.index') }}" class="text-decoration-none">{{ $v->driver->full_name ?? '—' }}</a>
                    </td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-outline-warning btn-modern me-1" onclick="openVehicleModal({{ $v->id_vehicle }})">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger btn-modern" onclick="deleteVehicle({{ $v->id_vehicle }}, '{{ addslashes($v->plate_number) }}')">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade modal-modern" id="vehicleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="vehicleModalTitle"><i class="bi bi-truck me-2"></i>Добавить автомобиль</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="vehicleForm">
                <input type="hidden" name="id_vehicle" id="vehicleId">
                <div class="modal-body">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Госномер</label>
                        <input type="text" name="plate_number" id="plateNumber" class="form-control form-control-modern font-monospace" placeholder="А123ВС77" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Марка / Модель</label>
                        <input type="text" name="name" id="vehicleName" class="form-control form-control-modern" placeholder="Lada Vesta">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Тип автомобиля</label>
                        <select name="id_auto_type" id="autoType" class="form-select form-control-modern" required>
                            @foreach($autoTypes as $at)
                            <option value="{{ $at->id_auto_type }}">{{ $at->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Владелец</label>
                        <select name="id_driver" id="driverSelect" class="form-select form-control-modern" required>
                            <option value="">-- Выберите водителя --</option>
                            @foreach($drivers as $dr)
                            <option value="{{ $dr->id_driver }}">{{ $dr->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-modern" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-primary btn-modern">Сохранить</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#vehiclesTable').DataTable({ language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/ru.json' }, pageLength: 25, responsive: true });
    });

    function openVehicleModal(id) {
        const modal = new bootstrap.Modal(document.getElementById('vehicleModal'));
        document.getElementById('vehicleForm').reset();
        document.getElementById('vehicleId').value = '';
        document.getElementById('vehicleModalTitle').innerHTML = '<i class="bi bi-truck me-2"></i>Добавить автомобиль';

        if (id) {
            document.getElementById('vehicleModalTitle').innerHTML = '<i class="bi bi-pencil me-2"></i>Редактировать автомобиль';
            fetch(`{{ url('vehicles') }}/${id}/data`)
                .then(r => r.json())
                .then(d => {
                    document.getElementById('vehicleId').value = d.id_vehicle;
                    document.getElementById('plateNumber').value = d.plate_number;
                    document.getElementById('vehicleName').value = d.name;
                    document.getElementById('autoType').value = d.id_auto_type;
                    document.getElementById('driverSelect').value = d.id_driver;
                    modal.show();
                });
        } else {
            modal.show();
        }
    }

    document.getElementById('vehicleForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const id = document.getElementById('vehicleId').value;
        const url = id ? `{{ url('vehicles') }}/${id}` : '{{ url('vehicles') }}';
        const method = id ? 'PUT' : 'POST';
        const formData = new FormData(this);
        if (id) { formData.append('_method', 'PUT'); }

        fetch(url, { method: 'POST', body: formData, headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
            .then(r => r.json())
            .then(d => {
                if (d.success) { bootstrap.Modal.getInstance(document.getElementById('vehicleModal')).hide(); showToast('Автомобиль сохранен'); setTimeout(() => location.reload(), 500); }
                else showToast(d.error || 'Ошибка', 'error');
            });
    });

    function deleteVehicle(id, plate) {
        if (!confirm(`Удалить автомобиль ${plate}?`)) return;
        fetch(`{{ url('vehicles') }}/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
            .then(r => r.json()).then(d => { if (d.success) { showToast('Автомобиль удален'); location.reload(); } });
    }
</script>
@endpush
