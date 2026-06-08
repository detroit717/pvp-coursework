@extends('layouts.app')

@section('title', 'Пункты оплаты - ПВП')

@push('styles')
<style>
    .point-card {
        background: #fff;
        border-radius: 16px;
        border: 1px solid rgba(0,0,0,0.04);
        overflow: hidden;
        transition: all 0.3s;
        height: 100%;
    }
    .point-card:hover { transform: translateY(-4px); box-shadow: 0 12px 40px rgba(0,0,0,0.08); }
    .point-card .progress-top { height: 4px; border-radius: 0; }
    .point-card .card-body { padding: 1.25rem; }
    .point-card .metric-box { padding: 0.75rem; border-radius: 10px; background: var(--gray-50); text-align: center; }
    .point-card .metric-box .num { font-weight: 700; font-size: 1.1rem; }
    .point-card .metric-box .lbl { font-size: 0.7rem; color: #6c757d; text-transform: uppercase; }
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2>Пункты взимания платы (ПВП)</h2>
            <p class="text-muted mb-0">Управление инфраструктурой, полосами и тарифами</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-info btn-modern" onclick="openTariffsModal()">
                <i class="bi bi-currency-exchange me-2"></i>Тарифы
            </button>
            <button class="btn btn-primary btn-modern shadow-sm" onclick="openPointModal(null)">
                <i class="bi bi-plus-circle me-2"></i>Добавить ПВП
            </button>
        </div>
    </div>
</div>

<div class="row g-4" id="pointsGrid">
    @foreach($points as $p)
    @php
        $limit = max($p->lanes_count, 1);
        $percent = min(100, round(($p->active_lanes / $limit) * 100));
        $barColor = $percent < 50 ? 'bg-warning' : ($percent >= 100 ? 'bg-danger' : 'bg-success');
        $icons = ['Легковой'=>'🚗', 'Грузовой'=>'🚛', 'Мотоцикл'=>'🏍️', 'Автобус'=>'🚌', 'Универсальный'=>'🔄'];
    @endphp
    <div class="col-xl-4 col-md-6">
        <div class="point-card">
            <div class="progress progress-top"><div class="progress-bar {{ $barColor }}" style="width: {{ $percent }}%"></div></div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <h5 class="fw-bold mb-0">{{ $p->name }}</h5>
                    <span class="badge bg-light text-primary border">ID: {{ $p->id_point }}</span>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-4"><div class="metric-box"><div class="lbl">Полосы</div><div class="num text-primary">{{ $p->active_lanes }}/{{ $p->lanes_count }}</div></div></div>
                    <div class="col-4"><div class="metric-box"><div class="lbl">Проезды</div><div class="num text-success">{{ number_format($p->total_transactions) }}</div></div></div>
                    <div class="col-4"><div class="metric-box"><div class="lbl">Выручка</div><div class="num text-info">{{ number_format($p->total_revenue ?? 0, 0, ',', ' ') }} ₽</div></div></div>
                </div>
                <button class="btn btn-dark w-100 btn-modern mb-2" onclick="editLanes({{ $p->id_point }}, '{{ addslashes($p->name) }}')">
                    <i class="bi bi-diagram-3 me-2"></i>Конфигурация полос
                </button>
                <div class="btn-group w-100">
                    <button class="btn btn-outline-secondary btn-modern" onclick="editPoint({{ $p }})">
                        <i class="bi bi-gear me-2"></i>Настройки
                    </button>
                    <button class="btn btn-outline-info btn-modern" onclick="window.location.href='{{ route("statistics.index") }}?point_id={{ $p->id_point }}'">
                        <i class="bi bi-graph-up me-2"></i>Статистика
                    </button>
                </div>
            </div>
            <div class="card-footer bg-white border-top-0 d-flex justify-content-between px-3 py-2">
                <button class="btn btn-link btn-sm text-danger text-decoration-none p-0" onclick="deletePoint({{ $p->id_point }}, '{{ addslashes($p->name) }}')">
                    <i class="bi bi-trash me-1"></i>Удалить
                </button>
                <small class="text-muted"><i class="bi bi-check-circle-fill text-success me-1"></i>Активен</small>
            </div>
        </div>
    </div>
    @endforeach
</div>

@include('payment_points._modals')
@include('payment_points._lanes_modal')
@include('payment_points._tariffs_modal')
@endsection

@push('scripts')
<script>
    let currentPointId = null;

    function openPointModal(data) {
        const modal = new bootstrap.Modal(document.getElementById('pointModal'));
        if (data) {
            document.getElementById('modalTitle').innerHTML = '<i class="bi bi-gear me-2"></i>' + data.name;
            document.getElementById('point_id').value = data.id_point;
            document.getElementById('point_name').value = data.name;
            document.getElementById('point_location').value = data.location || '';
            document.getElementById('point_lanes').value = data.lanes_count;
            document.getElementById('pointForm').dataset.action = 'edit';
        } else {
            document.getElementById('modalTitle').innerHTML = '<i class="bi bi-plus-circle me-2"></i>Новый пункт';
            document.getElementById('pointForm').reset();
            document.getElementById('point_id').value = '';
            document.getElementById('pointForm').dataset.action = 'add';
        }
        modal.show();
    }

    document.getElementById('pointForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const isEdit = this.dataset.action === 'edit';
        const id = document.getElementById('point_id').value;
        const url = isEdit ? `{{ url('payment-points') }}/${id}` : '{{ url('payment-points') }}';
        const formData = new FormData(this);
        if (isEdit) formData.append('_method', 'PUT');

        fetch(url, { method: 'POST', body: formData, headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
            .then(r => r.json())
            .then(d => { if (d.success) { showToast(isEdit ? 'Пункт обновлен' : 'Пункт создан'); setTimeout(() => location.reload(), 500); } else showToast(d.error, 'error'); });
    });

    function deletePoint(id, name) {
        if (!confirm(`Удалить пункт "${name}"?`)) return;
        fetch(`{{ url('payment-points') }}/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
            .then(r => r.json()).then(d => { if (d.success) { showToast('Пункт удален'); location.reload(); } });
    }

    function openTariffsModal() { new bootstrap.Modal(document.getElementById('tariffsModal')).show(); }
</script>
@endpush
