@extends('layouts.app')

@section('title', 'Транзакции - ПВП')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2>Транзакции</h2>
            <p class="text-muted mb-0">Регистрация проездов и история операций</p>
        </div>
        <button class="btn btn-primary btn-modern shadow-sm" onclick="openTransactionModal()">
            <i class="bi bi-plus-circle me-2"></i>Новая транзакция
        </button>
    </div>
</div>

<div class="card-modern mb-4">
    <div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-bold">Пункт оплаты</label>
                <select id="filterPoint" class="form-select form-control-modern">
                    <option value="">Все пункты</option>
                    @foreach($points as $pt)
                    <option value="{{ $pt->id_point }}">{{ $pt->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold">Способ оплаты</label>
                <select id="filterPayment" class="form-select form-control-modern">
                    <option value="">Все способы</option>
                    @foreach($methods as $pm)
                    <option value="{{ $pm->id_payment_method }}">{{ $pm->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">Статус</label>
                <select id="filterStatus" class="form-select form-control-modern">
                    <option value="">Все</option>
                    <option value="успешно">Успешно</option>
                    <option value="неоплата">Неоплата</option>
                </select>
            </div>
            <div class="col-md-2">
                <button id="resetFilters" class="btn btn-outline-secondary btn-modern w-100 mt-3">Сбросить</button>
            </div>
        </div>
    </div>
</div>

<div class="card-modern">
    <div class="card-body p-0">
        <table class="table table-modern mb-0" id="transactionsTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Дата</th>
                    <th>Пункт</th>
                    <th>Полоса</th>
                    <th>Автомобиль</th>
                    <th>Водитель</th>
                    <th>Сумма</th>
                    <th>Способ</th>
                    <th>Статус</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transactions as $t)
                <tr>
                    <td data-order="{{ $t->id_transaction }}"><span class="badge bg-secondary">#{{ $t->id_transaction }}</span></td>
                    <td data-order="{{ $t->datetime->format('Y-m-d H:i') }}">{{ $t->datetime->format('d.m.Y H:i') }}</td>
                    <td>{{ $t->paymentPoint->name ?? '—' }}</td>
                    <td><span class="badge bg-info">Полоса {{ $t->lane->lane_number ?? '—' }}</span></td>
                    <td><code class="bg-light px-2 py-1 rounded">{{ $t->vehicle->plate_number ?? '—' }}</code></td>
                    <td>{{ $t->vehicle->driver->full_name ?? '—' }}</td>
                    <td class="fw-bold" data-order="{{ $t->amount }}">{{ number_format($t->amount, 2, ',', ' ') }} ₽</td>
                    <td>{{ $t->paymentMethod->name ?? '—' }}</td>
                    <td>
                        <span class="badge badge-modern {{ $t->status === 'успешно' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                            {{ $t->status }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade modal-modern" id="transactionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-currency-exchange me-2"></i>Регистрация проезда</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="transactionForm">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Пункт оплаты</label>
                            <select name="id_point" id="transPoint" class="form-select form-control-modern" required>
                                <option value="">-- Выберите пункт --</option>
                                @foreach($points as $pt)
                                <option value="{{ $pt->id_point }}">{{ $pt->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Полоса</label>
                            <select name="id_lane" id="transLane" class="form-select form-control-modern" required disabled>
                                <option value="">Сначала выберите пункт</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Автомобиль</label>
                            <select name="id_vehicle" id="transVehicle" class="form-select form-control-modern" required>
                                <option value="">-- Выберите автомобиль --</option>
                                @foreach($vehicles as $v)
                                 @php $vt = $tariffs->firstWhere('id_auto_type', $v->id_auto_type); @endphp
                                 <option value="{{ $v->id_vehicle }}" data-tariff-id="{{ $vt?->id_tariff ?? '' }}" data-tariff-amount="{{ $vt?->amount ?? '' }}">
                                     {{ $v->plate_number }} ({{ $v->driver->full_name }})
                                 </option>
                                 @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Тариф</label>
                            <select name="id_tariff" id="transTariff" class="form-select form-control-modern">
                                <option value="">Автоматически</option>
                                @foreach($tariffs as $tariff)
                                <option value="{{ $tariff->id_tariff }}" data-amount="{{ $tariff->amount }}">
                                    {{ $tariff->autoType->name ?? '—' }} - {{ number_format($tariff->amount, 2) }} ₽
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Сумма (₽)</label>
                            <input type="number" step="0.01" name="amount" id="transAmount" class="form-control form-control-modern" required placeholder="0.00">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Способ оплаты</label>
                            <select name="id_payment_method" class="form-select form-control-modern" required>
                                @foreach($methods as $pm)
                                <option value="{{ $pm->id_payment_method }}">{{ $pm->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-modern" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-primary btn-modern">
                        <i class="bi bi-check-lg me-1"></i>Зарегистрировать
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
        const table = $('#transactionsTable').DataTable({
            language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/ru.json' },
            pageLength: 25, responsive: true, order: [[1, 'desc']]
        });

        function applyFilters() {
            const pointText = $('#filterPoint option:selected').text();
            $('#filterPoint').val() ? table.column(2).search(pointText).draw() : table.column(2).search('').draw();
            const paymentText = $('#filterPayment option:selected').text();
            $('#filterPayment').val() ? table.column(7).search(paymentText).draw() : table.column(7).search('').draw();
            table.column(8).search($('#filterStatus').val()).draw();
        }
        $('#filterPoint, #filterPayment, #filterStatus').on('change', applyFilters);
        $('#resetFilters').on('click', function() { $('#filterPoint, #filterPayment, #filterStatus').val(''); table.columns().search('').draw(); });
    });

    function openTransactionModal() {
        document.getElementById('transactionForm').reset();
        document.getElementById('transLane').disabled = true;
        document.getElementById('transLane').innerHTML = '<option value="">Сначала выберите пункт</option>';
        new bootstrap.Modal(document.getElementById('transactionModal')).show();
    }

    document.getElementById('transPoint').addEventListener('change', function() {
        const pointId = this.value;
        const laneSelect = document.getElementById('transLane');
        if (!pointId) { laneSelect.disabled = true; laneSelect.innerHTML = '<option value="">Сначала выберите пункт</option>'; return; }
        laneSelect.disabled = true;
        laneSelect.innerHTML = '<option value="">⏳ Загрузка...</option>';
        fetch(`{{ url('transactions/lanes-by-point') }}/${pointId}`)
            .then(r => r.json())
            .then(data => {
                if (data.length === 0) { laneSelect.innerHTML = '<option value="">❌ Нет полос</option>'; laneSelect.disabled = true; return; }
                laneSelect.innerHTML = '<option value="">-- Выберите полосу --</option>' +
                    data.map(l => `<option value="${l.id_lane}">🚗 Полоса №${l.lane_number} - ${l.lane_type?.name || 'Стандарт'}</option>`).join('');
                laneSelect.disabled = false;
            })
            .catch(() => { laneSelect.innerHTML = '<option value="">❌ Ошибка</option>'; laneSelect.disabled = true; });
    });

    document.getElementById('transTariff').addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        const amount = opt.getAttribute('data-amount');
        if (amount) document.getElementById('transAmount').value = amount;
    });

    document.getElementById('transVehicle').addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        const tariffId = opt.getAttribute('data-tariff-id');
        const tariffAmount = opt.getAttribute('data-tariff-amount');
        const tariffSelect = document.getElementById('transTariff');
        if (tariffId) {
            tariffSelect.value = tariffId;
            tariffSelect.dispatchEvent(new Event('change'));
        } else {
            tariffSelect.value = '';
            document.getElementById('transAmount').value = '';
        }
    });

    document.getElementById('transactionForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = this.querySelector('[type="submit"]');
        btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Регистрация...';
        fetch('{{ route("transactions.store") }}', { method: 'POST', body: new FormData(this), headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
            .then(r => r.json())
            .then(d => {
                if (d.success) {
                    bootstrap.Modal.getInstance(document.getElementById('transactionModal')).hide();
                    showToast('Транзакция зарегистрирована');
                    setTimeout(() => location.reload(), 500);
                } else {
                    showToast(d.error || 'Ошибка', 'error');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-check-lg me-1"></i>Зарегистрировать';
                }
            });
    });
</script>
@endpush
