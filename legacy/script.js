// Глобальные переменные
let currentChart = null;

// Открытие модального окна
// Глобальный обработчик для открытия модальных окон с загрузкой контента
function openModal(url, title = 'Загрузка...') {
    // 1. Удаляем старую модалку, если она осталась в DOM
    const oldModal = document.getElementById('ajaxModal');
    if (oldModal) {
        const inst = bootstrap.Modal.getInstance(oldModal);
        if (inst) inst.hide();
        oldModal.remove();
    }

    // 2. Создаем скелет модалки с индикатором загрузки
    const modalHtml = `
        <div class="modal fade" id="ajaxModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg shadow-lg">
                <div class="modal-content">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title">${title}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="ajaxModalBody">
                        <div class="text-center p-5">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="mt-2 text-muted">Пожалуйста, подождите...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>`;

    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modalElem = document.getElementById('ajaxModal');
    const modalInstance = new bootstrap.Modal(modalElem);
    modalInstance.show();

    // 3. Загружаем HTML контент
    fetch(url)
        .then(response => {
            if (!response.ok) throw new Error('Ошибка загрузки: ' + response.status);
            return response.text();
        })
        .then(html => {
            const body = document.getElementById('ajaxModalBody');
            body.innerHTML = html;

            // 4. Автоматическая привязка AJAX к формам внутри модалки
            body.querySelectorAll('form').forEach(form => {
                form.onsubmit = function (e) {
                    e.preventDefault();
                    submitAjaxForm(this);
                };
            });
        })
        .catch(error => {
            document.getElementById('ajaxModalBody').innerHTML =
                `<div class="alert alert-danger">Ошибка: ${error.message}</div>`;
        });

    modalElem.addEventListener('hidden.bs.modal', () => modalElem.remove());
}

// Универсальная функция отправки форм через AJAX
function submitAjaxForm(form) {
    const formData = new FormData(form);
    const url = form.getAttribute('action') || window.location.href;
    const btn = form.querySelector('[type="submit"]');

    if (btn) btn.disabled = true; // Защита от двойного клика

    fetch(url, {
        method: 'POST',
        body: formData
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                location.reload(); // Перезагружаем для обновления таблиц
            } else {
                alert('Ошибка: ' + (data.error || 'Неизвестная ошибка'));
                if (btn) btn.disabled = false;
            }
        })
        .catch(err => {
            console.error(err);
            alert('Ошибка соединения с сервером');
            if (btn) btn.disabled = false;
        });
}

// Подтверждение удаления
function confirmDelete(url, name) {
    if (confirm('Вы уверены, что хотите удалить ' + name + '?')) {
        window.location.href = url;
    }
}

// Отправка формы через AJAX
function submitForm(formId, callback) {
    const form = document.getElementById(formId);
    if (!form) return;

    const formData = new FormData(form);
    const url = form.action || window.location.href;

    fetch(url, {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.error || 'Ошибка при сохранении');
            }
        })
        .catch(error => {
            console.error('Ошибка:', error);
            alert('Ошибка соединения');
        });

    return false;
}

// Пополнение баланса
function addBalance(driverId, currentBalance) {
    const amount = prompt('Введите сумму пополнения (руб):', '100');
    if (amount && !isNaN(amount) && amount > 0) {
        fetch(window.location.href, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=add_balance&id=' + driverId + '&amount=' + amount
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.error || 'Ошибка пополнения');
                }
            });
    }
}

// Оплата штрафа
function payFine(fineId) {
    if (confirm('Отметить штраф как оплаченный?')) {
        fetch(window.location.href, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=pay&id=' + fineId
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.error || 'Ошибка');
                }
            });
    }
}

// Инициализация DataTable
function initDataTable(tableId) {
    if ($.fn.DataTable) {
        $(document).ready(function () {
            if ($('#' + tableId).length) {
                $('#' + tableId).DataTable({
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/ru.json'
                    },
                    pageLength: 25,
                    responsive: true
                });
            }
        });
    }
}

// Фильтрация таблицы
function filterTable(tableId, columnIndex, value) {
    const table = $('#' + tableId).DataTable();
    if (table) {
        table.column(columnIndex).search(value).draw();
    }
}

// Экспорт таблицы в CSV
function exportToCSV(tableId, filename) {
    const table = $('#' + tableId).DataTable();
    const data = table.rows().data().toArray();
    let csv = '';

    // Заголовки
    $('#' + tableId + ' thead th').each(function () {
        csv += $(this).text() + ',';
    });
    csv = csv.slice(0, -1) + '\n';

    // Данные
    data.forEach(row => {
        row.forEach(cell => {
            let cellText = String(cell).replace(/[",]/g, '');
            csv += '"' + cellText + '",';
        });
        csv = csv.slice(0, -1) + '\n';
    });

    const blob = new Blob(["\uFEFF" + csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.href = url;
    link.setAttribute('download', filename + '.csv');
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
}

// Инициализация графиков
function initChart(chartId, type, labels, data, label) {
    const ctx = document.getElementById(chartId);
    if (!ctx) return;

    if (currentChart) {
        currentChart.destroy();
    }

    currentChart = new Chart(ctx, {
        type: type,
        data: {
            labels: labels,
            datasets: [{
                label: label,
                data: data,
                backgroundColor: type === 'pie' ? [
                    '#0d6efd', '#198754', '#ffc107', '#dc3545', '#6f42c1', '#fd7e14'
                ] : '#0d6efd',
                borderColor: '#0d6efd',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            let label = context.dataset.label || '';
                            let value = context.raw;
                            if (type === 'pie') {
                                let total = context.dataset.data.reduce((a, b) => a + b, 0);
                                let percent = ((value / total) * 100).toFixed(1);
                                return label + ': ' + value.toLocaleString() + ' руб. (' + percent + '%)';
                            }
                            return label + ': ' + value.toLocaleString() + ' руб.';
                        }
                    }
                }
            },
            scales: type !== 'pie' ? {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function (value) {
                            return value.toLocaleString() + ' ₽';
                        }
                    }
                }
            } : {}
        }
    });
}

// Загрузка данных для графиков через AJAX
function loadChartData(url, chartId, type, label) {
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.labels && data.values) {
                initChart(chartId, type, data.labels, data.values, label);
            }
        })
        .catch(error => console.error('Ошибка загрузки данных:', error));
}

// Обновление выпадающих списков (зависимые)
function updateLanesByPoint(pointSelectId, laneSelectId) {
    const pointId = document.getElementById(pointSelectId).value;
    const laneSelect = document.getElementById(laneSelectId);

    if (!pointId || !laneSelect) return;

    fetch('get_lanes.php?point_id=' + pointId)
        .then(response => response.json())
        .then(data => {
            laneSelect.innerHTML = '<option value="">Выберите полосу</option>';
            data.forEach(lane => {
                laneSelect.innerHTML += '<option value="' + lane.id_lane + '">Полоса ' + lane.lane_number + '</option>';
            });
        });
}

// Автоматическая инициализация при загрузке страницы
$(document).ready(function () {
    // Инициализация всех таблиц с классом datatable
    $('.datatable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/ru.json'
        },
        pageLength: 25,
        responsive: true
    });

    // Обработка зависимых списков
    $(document).on('change', '.point-select', function () {
        const laneSelect = $(this).closest('form').find('.lane-select');
        if (laneSelect.length) {
            const pointId = $(this).val();
            if (pointId) {
                fetch('/get_lanes.php?point_id=' + pointId)
                    .then(response => response.json())
                    .then(data => {
                        laneSelect.html('<option value="">Выберите полосу</option>');
                        data.forEach(lane => {
                            laneSelect.append('<option value="' + lane.id_lane + '">Полоса ' + lane.lane_number + '</option>');
                        });
                    });
            }
        }
    });
});