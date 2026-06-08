@extends('layouts.app')

@section('title', 'Штрафы - ПВП')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2>Штрафы</h2>
            <p class="text-muted mb-0">Управление штрафными санкциями</p>
        </div>
        <button class="btn btn-danger btn-modern shadow-sm" onclick="openFineModal()">
            <i class="bi bi-exclamation-triangle me-2"></i>Назначить штраф
        </button>
    </div>
</div>

<div class="card-modern">
    <div class="card-body p-0">
        <table class="table table-modern mb-0" id="finesTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Дата</th>
                    <th>Водитель</th>
                    <th>Автомобиль</th>
                    <th>Тип</th>
                    <th>Сумма</th>
                    <th>Статус</th>
                    <th class="text-end"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($fines as $fine)
                <tr>
                    <td data-order="{{ $fine->id_fine }}"><span class="badge bg-secondary">#{{ $fine->id_fine }}</span></td>
                    <td data-order="{{ $fine->datetime->format('Y-m-d H:i') }}">{{ $fine->datetime->format('d.m.Y H:i') }}</td>
                    <td class="fw-semibold">{{ $fine->driver->full_name }}</td>
                    <td>{{ $fine->vehicle->plate_number ?? '—' }}</td>
                    <td>{{ $fine->fineType->name ?? '—' }}</td>
                    <td class="fw-bold text-danger" data-order="{{ $fine->amount }}">{{ number_format($fine->amount, 2, ',', ' ') }} ₽</td>
                    <td>
                        <span class="badge badge-modern {{ $fine->payment_status === 'оплачен' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }}">
                            {{ $fine->payment_status }}
                        </span>
                    </td>
                    <td class="text-end">
                        @if($fine->payment_status === 'неоплачен')
                        <button class="btn btn-sm btn-outline-success btn-modern me-1" onclick="payFine({{ $fine->id_fine }})">
                            <i class="bi bi-check-lg"></i> Оплатить
                        </button>
                        @endif
                        <button class="btn btn-sm btn-outline-danger btn-modern" onclick="deleteFine({{ $fine->id_fine }})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade modal-modern" id="fineModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-2"></i>Выписать штраф</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="fineForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Автомобиль</label>
                        <select name="id_vehicle" id="fineVehicle" class="form-select form-control-modern">
                            <option value="">Без привязки к авто</option>
                            @foreach($vehicles as $v)
                            <option value="{{ $v->id_vehicle }}" data-driver-id="{{ $v->id_driver }}" data-driver-name="{{ $v->driver->full_name }}">
                                {{ $v->plate_number }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Водитель <span class="text-danger">*</span></label>
                        <select name="id_driver" id="fineDriver" class="form-select form-control-modern" required>
                            <option value="">-- Выберите водителя --</option>
                            @foreach($drivers as $dr)
                            <option value="{{ $dr->id_driver }}">{{ $dr->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Тип штрафа</label>
                        <select name="id_fine_type" class="form-select form-control-modern" required>
                            @foreach($fineTypes as $ft)
                            <option value="{{ $ft->id_fine_type }}">{{ $ft->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Сумма (₽)</label>
                        <input type="number" step="0.01" name="amount" class="form-control form-control-modern" required min="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Комментарий</label>
                        <textarea name="comment" class="form-control form-control-modern" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-modern" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-danger btn-modern">Подтвердить штраф</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#finesTable').DataTable({ language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/ru.json' }, pageLength: 25, responsive: true, order: [[1, 'desc']] });
    });

    function openFineModal() {
        document.getElementById('fineForm').reset();
        document.getElementById('fineDriver').disabled = false;
        new bootstrap.Modal(document.getElementById('fineModal')).show();
    }

    document.getElementById('fineVehicle').addEventListener('change', function() {
        const driverSelect = document.getElementById('fineDriver');
        if (this.value === '') { driverSelect.disabled = false; driverSelect.value = ''; return; }
        const opt = this.options[this.selectedIndex];
        driverSelect.value = opt.getAttribute('data-driver-id');
        driverSelect.disabled = true;
    });

    document.getElementById('fineForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const driverSelect = document.getElementById('fineDriver');
        const wasDisabled = driverSelect.disabled;
        if (wasDisabled) driverSelect.disabled = false;
        const formData = new FormData(this);
        fetch('{{ route("fines.store") }}', { method: 'POST', body: formData, headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
            .then(r => r.json())
            .then(d => {
                if (d.success) { bootstrap.Modal.getInstance(document.getElementById('fineModal')).hide(); showToast('Штраф назначен'); setTimeout(() => location.reload(), 500); }
                else showToast(d.error || 'Ошибка', 'error');
            })
            .finally(() => { if (wasDisabled) driverSelect.disabled = true; });
    });

    function payFine(id) {
        if (!confirm('Отметить штраф как оплаченный?')) return;
        fetch(`{{ url('fines') }}/${id}/pay`, { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
            .then(r => r.json()).then(d => { if (d.success) { showToast('Штраф оплачен'); location.reload(); } });
    }

    function deleteFine(id) {
        if (!confirm('Удалить штраф?')) return;
        fetch(`{{ url('fines') }}/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
            .then(r => r.json()).then(d => { if (d.success) { showToast('Штраф удален'); location.reload(); } });
    }
</script>
@endpush
