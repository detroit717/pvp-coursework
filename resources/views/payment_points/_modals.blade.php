<div class="modal fade modal-modern" id="pointModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalTitle">Управление пунктом</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="pointForm">
                <div class="modal-body">
                    <input type="hidden" name="id" id="point_id">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Название пункта</label>
                        <input type="text" name="name" id="point_name" class="form-control form-control-modern" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Местоположение</label>
                        <input type="text" name="location" id="point_location" class="form-control form-control-modern">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Лимит полос</label>
                        <input type="number" name="lanes_count" id="point_lanes" class="form-control form-control-modern" min="1" required>
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
