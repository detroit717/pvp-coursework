@extends('layouts.app')

@section('title', 'Водители - ПВП')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2>Реестр водителей</h2>
            <p class="text-muted mb-0">Управление учетными записями и финансовыми балансами</p>
        </div>
        <button class="btn btn-primary btn-modern shadow-sm" onclick="openDriverModal(null)">
            <i class="bi bi-person-plus-fill me-2"></i>Новый водитель
        </button>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(13,110,253,0.1); color: var(--primary);">
                <i class="bi bi-people"></i>
            </div>
            <div class="stat-label">Всего активных</div>
            <div class="stat-value text-primary">{{ number_format($stats->total) }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(25,135,84,0.1); color: var(--success);">
                <i class="bi bi-cash-coin"></i>
            </div>
            <div class="stat-label">Средства клиентов</div>
            <div class="stat-value text-success">{{ number_format($stats->total_money ?? 0, 2, ',', ' ') }} ₽</div>
        </div>
    </div>
</div>

<div class="card-modern">
    <div class="card-body p-0">
        <table class="table table-modern mb-0" id="driversTable">
            <thead>
                <tr>
                    <th>Водитель</th>
                    <th>Телефон</th>
                    <th>Баланс</th>
                    <th class="text-end">Управление</th>
                </tr>
            </thead>
            <tbody>
                @foreach($drivers as $d)
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar-sq bg-primary text-white me-3">{{ mb_substr($d->full_name, 0, 1) }}</div>
                            <div>
                                <div class="fw-semibold">{{ $d->full_name }}</div>
                                <div class="text-muted" style="font-size: 0.8rem;">ID: {{ $d->id_driver }}</div>
                            </div>
                        </div>
                    </td>
                    <td>{{ $d->phone_number ?? '—' }}</td>
                    <td data-order="{{ $d->personal_balance }}">
                        @php $isLow = $d->personal_balance < 100; @endphp
                        <span class="badge badge-modern {{ $isLow ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' }}">
                            {{ number_format($d->personal_balance, 2, ',', ' ') }} ₽
                        </span>
                    </td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-outline-primary btn-modern me-1" onclick="viewDriverCard({{ $d->id_driver }})">
                            <i class="bi bi-eye"></i> Профиль
                        </button>
                        <button class="btn btn-sm btn-outline-danger btn-modern" onclick="deleteDriver({{ $d->id_driver }}, '{{ addslashes($d->full_name) }}')">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade modal-modern" id="driverModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="driverModalTitle"><i class="bi bi-person-vcard me-2"></i>Карточка водителя</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="driverModalContent">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#driversTable').DataTable({
            language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/ru.json' },
            pageLength: 25, responsive: true
        });
    });

    function openDriverModal(id) {
        const modal = new bootstrap.Modal(document.getElementById('driverModal'));
        const title = document.getElementById('driverModalTitle');
        const content = document.getElementById('driverModalContent');

        if (id) {
            title.innerHTML = '<i class="bi bi-person-vcard me-2"></i>Карточка водителя';
            fetch(`{{ url('drivers') }}/${id}/card`)
                .then(r => r.text())
                .then(html => { content.innerHTML = html; modal.show(); });
        } else {
            title.innerHTML = '<i class="bi bi-person-plus-fill me-2"></i>Регистрация нового водителя';
            fetch(`{{ url('drivers/create') }}`)
                .then(r => r.text())
                .then(html => { content.innerHTML = html; modal.show(); });
        }
    }

    function viewDriverCard(id) { openDriverModal(id); }

    function deleteDriver(id, name) {
        if (!confirm(`Удалить водителя "${name}"?`)) return;
        fetch(`{{ url('drivers') }}/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
            .then(r => r.json())
            .then(d => { if (d.success) { showToast('Водитель удален'); location.reload(); } else showToast(d.error, 'error'); });
    }
</script>
@endpush
