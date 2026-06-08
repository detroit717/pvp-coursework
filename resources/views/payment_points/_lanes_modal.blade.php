<div class="modal fade modal-modern" id="lanesModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title">
                    <i class="bi bi-diagram-3-fill me-2 text-info"></i>
                    Полосы: <span id="lane_point_name" class="text-info"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-4">
                    <div class="col-md-5">
                        <div class="card border-primary bg-light rounded-4">
                            <div class="card-body">
                                <h6 class="fw-bold text-primary mb-3" id="laneFormTitle">
                                    <i class="bi bi-plus-circle me-1"></i>Добавить полосу
                                </h6>
                                <form id="laneForm">
                                    <input type="hidden" id="lane_id" value="">
                                    <div class="row g-3">
                                        <div class="col-4">
                                            <label class="form-label small fw-bold">Номер</label>
                                            <input type="number" id="lane_number" class="form-control form-control-modern" min="1" required>
                                        </div>
                                        <div class="col-8">
                                            <label class="form-label small fw-bold">Тип полосы</label>
                                            <select id="id_lane_type" class="form-select form-control-modern" required>
                                                @foreach($laneTypes as $lt)
                                                <option value="{{ $lt->id_lane_type }}">{{ $lt->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12 d-flex gap-2">
                                            <button type="submit" class="btn btn-primary flex-grow-1 btn-modern" id="laneSubmitBtn">
                                                <i class="bi bi-check-lg me-1"></i>Сохранить
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary btn-modern" id="laneCancelBtn" onclick="resetLaneForm()" style="display:none;">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold mb-0">Список полос</h6>
                            <span class="badge bg-secondary rounded-pill" id="lanesCount">0</span>
                        </div>
                        <div id="lanes_list">
                            <div class="text-center p-5 text-muted">
                                <div class="spinner-border text-primary"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary btn-modern" data-bs-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>

<script>
function editLanes(id, name) {
    currentPointId = id;
    document.getElementById('lane_point_name').innerText = name;
    resetLaneForm();
    loadLanes();
    new bootstrap.Modal(document.getElementById('lanesModal')).show();
}

function resetLaneForm() {
    document.getElementById('laneForm').reset();
    document.getElementById('lane_id').value = '';
    document.getElementById('laneFormTitle').innerHTML = '<i class="bi bi-plus-circle me-1"></i>Добавить полосу';
    document.getElementById('laneSubmitBtn').className = 'btn btn-primary flex-grow-1 btn-modern';
    document.getElementById('laneSubmitBtn').innerHTML = '<i class="bi bi-check-lg me-1"></i>Сохранить';
    document.getElementById('laneCancelBtn').style.display = 'none';
}

function loadLanes() {
    const container = document.getElementById('lanes_list');
    container.innerHTML = '<div class="text-center p-4"><div class="spinner-border text-primary"></div></div>';

    fetch(`{{ url('payment-points') }}/${currentPointId}/lanes`)
        .then(r => r.json())
        .then(lanes => {
            document.getElementById('lanesCount').textContent = lanes.length;
            if (!lanes.length) {
                container.innerHTML = '<div class="empty-state"><i class="bi bi-layers"></i><p>Нет полос</p><p class="small">Добавьте полосу слева</p></div>';
                return;
            }

            const icons = { 'Легковой': '🚗', 'Грузовой': '🚛', 'Мотоцикл': '🏍️', 'Автобус': '🚌', 'Универсальный': '🔄' };

            container.innerHTML = lanes.map(l => `
                <div class="lane-card d-flex justify-content-between align-items-center mb-2">
                    <div class="d-flex align-items-center gap-3">
                        <div class="lane-icon" style="background: rgba(13,110,253,0.08);">
                            ${icons[l.lane_type?.name] || '🛣️'}
                        </div>
                        <div>
                            <div class="fw-bold fs-6">Полоса №${l.lane_number}</div>
                            <div class="d-flex align-items-center gap-2 mt-1">
                                <span class="text-muted small">${l.lane_type?.name || 'Стандарт'}</span>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <div class="d-flex gap-1">
                            <button class="btn btn-sm btn-outline-warning rounded-circle" style="width:36px;height:36px;" onclick="editLaneForm(${l.id_lane})" title="Редактировать">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger rounded-circle" style="width:36px;height:36px;" onclick="deleteLane(${l.id_lane})" title="Удалить">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `).join('');
        })
        .catch(() => container.innerHTML = '<div class="alert alert-danger">Ошибка загрузки</div>');
}

function editLaneForm(laneId) {
    fetch(`{{ url('payment-points/lanes') }}/${laneId}`)
        .then(r => r.json())
        .then(lane => {
            document.getElementById('lane_id').value = lane.id_lane;
            document.getElementById('lane_number').value = lane.lane_number;
            document.getElementById('id_lane_type').value = lane.id_lane_type;
            document.getElementById('laneFormTitle').innerHTML = '<i class="bi bi-pencil me-1"></i>Редактировать полосу №' + lane.lane_number;
            document.getElementById('laneSubmitBtn').className = 'btn btn-warning flex-grow-1 btn-modern';
            document.getElementById('laneSubmitBtn').innerHTML = '<i class="bi bi-check-lg me-1"></i>Обновить';
            document.getElementById('laneCancelBtn').style.display = 'inline-block';
        });
}

document.getElementById('laneForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const laneId = document.getElementById('lane_id').value;
    const url = laneId ? `{{ url('payment-points/lanes') }}/${laneId}` : '{{ url('payment-points/lanes') }}';
    const method = laneId ? 'PUT' : 'POST';

    const formData = new FormData();
    formData.append('id_point', currentPointId);
    formData.append('lane_number', document.getElementById('lane_number').value);
    formData.append('id_lane_type', document.getElementById('id_lane_type').value);
    if (laneId) formData.append('_method', 'PUT');

    fetch(url, { method: 'POST', body: formData, headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
        .then(r => r.json())
        .then(d => {
            if (d.success) { resetLaneForm(); loadLanes(); showToast(laneId ? 'Полоса обновлена' : 'Полоса добавлена'); }
            else showToast(d.error || 'Ошибка', 'error');
        });
});

function deleteLane(laneId) {
    if (!confirm('Удалить полосу?')) return;
    fetch(`{{ url('payment-points/lanes') }}/${laneId}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
        .then(r => r.json())
        .then(d => { if (d.success) { loadLanes(); showToast('Полоса удалена'); } else showToast(d.error, 'error'); });
}
</script>
