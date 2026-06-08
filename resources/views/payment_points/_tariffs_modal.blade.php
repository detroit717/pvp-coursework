<div class="modal fade modal-modern" id="tariffsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="bi bi-currency-exchange me-2"></i>Тарифы</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="card border-primary bg-light rounded-4 mb-4">
                    <div class="card-body">
                        <h6 class="fw-bold text-primary mb-3"><i class="bi bi-plus-circle me-1"></i>Новый тариф</h6>
                        <form id="tariffForm">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">Тип авто</label>
                                    <select name="id_auto_type" class="form-select form-control-modern" required>
                                        <option value="">Выберите</option>
                                        @foreach($autoTypes as $at)
                                        <option value="{{ $at->id_auto_type }}">{{ $at->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small fw-bold">Сумма (₽)</label>
                                    <input type="number" step="0.01" name="amount" class="form-control form-control-modern" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small fw-bold">Время с</label>
                                    <input type="time" name="time_start" class="form-control form-control-modern" value="00:00">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small fw-bold">Время по</label>
                                    <input type="time" name="time_end" class="form-control form-control-modern" value="23:59">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small fw-bold">День</label>
                                    <select name="day_of_week" class="form-select form-control-modern">
                                        <option value="">Любой</option>
                                        <option value="1">Пн</option><option value="2">Вт</option>
                                        <option value="3">Ср</option><option value="4">Чт</option>
                                        <option value="5">Пт</option><option value="6">Сб</option>

                                    </select>
                                </div>
                                <div class="col-md-1 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary btn-modern w-100"><i class="bi bi-plus"></i></button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-modern">
                        <thead>
                            <tr><th>Тип авто</th><th>Сумма</th><th>Время</th><th>День</th><th class="text-end"></th></tr>
                        </thead>
                        <tbody>
                            @foreach($tariffs as $t)
                            <tr>
                                <td><span class="fw-semibold">{{ $t->autoType->name ?? '—' }}</span></td>
                                <td><strong class="text-success">{{ number_format($t->amount, 2) }} ₽</strong></td>
                                <td>{{ substr($t->time_start, 0, 5) }} - {{ substr($t->time_end, 0, 5) }}</td>
                                <td>
                                    @php $days = ['', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб']; @endphp
                                    <span class="badge bg-light text-dark">{{ $t->day_of_week ? $days[$t->day_of_week] : 'Ежедневно' }}</span>
                                </td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-danger rounded-circle" style="width:32px;height:32px;" onclick="deleteTariff({{ $t->id_tariff }})">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('tariffForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = this.querySelector('[type="submit"]');
    btn.disabled = true;
    fetch('{{ route("tariffs.store") }}', { method: 'POST', body: new FormData(this), headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
        .then(r => r.json())
        .then(d => { if (d.success) { showToast('Тариф создан'); setTimeout(() => location.reload(), 500); } else showToast(d.error, 'error'); });
});

function deleteTariff(id) {
    if (!confirm('Удалить тариф?')) return;
    fetch(`{{ url('tariffs') }}/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
        .then(r => r.json()).then(d => { if (d.success) { showToast('Тариф удален'); location.reload(); } });
}
</script>
