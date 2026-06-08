<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Профиль — ПВП</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background: #f8f9fa; padding: 2rem; }
        .profile-header { background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%); border-radius: 24px; padding: 2rem; color: #fff; margin-bottom: 1.5rem; }
        .profile-header .avatar { width: 72px; height: 72px; border-radius: 18px; background: rgba(255,255,255,0.15); display: flex; align-items: center; justify-content: center; font-size: 2rem; }
        .card-modern { background: #fff; border-radius: 16px; border: 1px solid rgba(0,0,0,0.04); overflow: hidden; margin-bottom: 1.5rem; }
        .card-modern .card-header { background: none; border-bottom: 1px solid #e9ecef; padding: 1rem 1.25rem; font-weight: 600; font-size: 0.95rem; }
        .card-modern .card-body { padding: 1.25rem; }
        .badge-modern { padding: 0.35em 0.85em; border-radius: 20px; font-weight: 500; font-size: 0.75rem; }
        .stat-box { padding: 1rem; border-radius: 12px; background: #f8f9fa; text-align: center; }
        .stat-box .num { font-size: 1.5rem; font-weight: 700; }
        .stat-box .lbl { font-size: 0.75rem; color: #6c757d; text-transform: uppercase; }
        .table-modern thead th { background: #f1f3f5; font-weight: 600; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px; color: #495057; border-bottom: none; padding: 0.75rem 1rem; }
        .table-modern td { padding: 0.75rem 1rem; vertical-align: middle; border-color: #f1f3f5; font-size: 0.9rem; }
        .vehicle-chip { display: inline-flex; align-items: center; gap: 0.5rem; background: #f1f3f5; border-radius: 10px; padding: 0.5rem 0.75rem; margin: 0.25rem; font-size: 0.85rem; font-weight: 500; }
        .logout-btn { position: fixed; top: 1rem; right: 1rem; z-index: 100; border-radius: 12px; padding: 0.5rem 1.25rem; font-weight: 600; font-size: 0.875rem; }
        .fine-row { border-left: 4px solid transparent; }
        .fine-row.unpaid { border-left-color: #dc3545; background: #fff5f5; }
        .fine-row.paid { border-left-color: #198754; }
    </style>
</head>
<body>
    <a href="{{ url('/logout') }}" class="btn btn-outline-secondary logout-btn">
        <i class="bi bi-box-arrow-right me-1"></i>Выйти
    </a>

    <div class="container" style="max-width: 900px;">
        <div class="profile-header">
            <div class="d-flex align-items-center gap-3">
                <div class="avatar">
                    <i class="bi bi-person-fill"></i>
                </div>
                <div>
                    <h3 class="fw-bold mb-1">{{ $driver->full_name }}</h3>
                    <div class="text-white-50">
                        <i class="bi bi-telephone me-1"></i>{{ $driver->phone_number }}
                        <span class="mx-2">|</span>
                        <i class="bi bi-cash-coin me-1"></i>{{ number_format($driver->personal_balance, 2) }} ₽
                    </div>
                </div>
                <div class="ms-auto d-flex gap-2 align-items-center">
                    <span class="badge bg-white text-dark rounded-pill px-3 py-2 fs-6 d-none d-md-inline">
                        <i class="bi bi-wallet2 me-1"></i>{{ number_format($driver->personal_balance, 2) }} ₽
                    </span>
                    <button class="btn btn-light btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#topupModal">
                        <i class="bi bi-plus-circle me-1"></i>
                        <span class="d-none d-md-inline">Пополнить</span>
                    </button>
                </div>
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success border-0 rounded-4 shadow-sm mb-4">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        </div>
        @endif

        <div class="row g-3 mb-4">
            <div class="col-4"><div class="stat-box"><div class="num text-primary">{{ $driver->vehicles->count() }}</div><div class="lbl">Авто</div></div></div>
            <div class="col-4"><div class="stat-box"><div class="num text-success">{{ $transactions->count() }}</div><div class="lbl">Поездок</div></div></div>
            <div class="col-4"><div class="stat-box"><div class="num text-danger">{{ $unpaidFines->count() }}</div><div class="lbl">Штрафы</div></div></div>
        </div>

        @if($driver->vehicles->isNotEmpty())
        <div class="card-modern">
            <div class="card-header"><i class="bi bi-truck me-2"></i>Мои автомобили</div>
            <div class="card-body">
                @foreach($driver->vehicles as $v)
                <div class="vehicle-chip">
                    <i class="bi bi-car-front"></i>
                    <code>{{ $v->plate_number }}</code>
                    <span class="text-muted">— {{ $v->name ?? 'без названия' }}</span>
                    @if($v->transponders->where('status', 'активен')->first())
                    <span class="badge bg-success rounded-pill">
                        <i class="bi bi-wifi me-1"></i>ESP
                    </span>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        @if($fines->isNotEmpty())
        <div class="card-modern">
            <div class="card-header"><i class="bi bi-exclamation-octagon me-2 text-danger"></i>Штрафы</div>
            <div class="card-body p-0">
                <table class="table table-modern mb-0">
                    <thead><tr><th>Дата</th><th>Тип</th><th>Сумма</th><th>Статус</th></tr></thead>
                    <tbody>
                        @foreach($fines as $f)
                        <tr class="fine-row {{ $f->payment_status === 'неоплачен' ? 'unpaid' : 'paid' }}">
                            <td class="small">{{ $f->datetime instanceof \Carbon\Carbon ? $f->datetime->format('d.m.Y H:i') : $f->datetime }}</td>
                            <td>{{ $f->fineType->name ?? '—' }}</td>
                            <td class="fw-bold">{{ number_format($f->amount, 2) }} ₽</td>
                            <td>
                                @if($f->payment_status === 'неоплачен')
                                <span class="badge badge-modern bg-danger">Неоплачен</span>
                                @else
                                <span class="badge badge-modern bg-success">Оплачен</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        @if($transactions->isNotEmpty())
        <div class="card-modern">
            <div class="card-header"><i class="bi bi-clock-history me-2"></i>Последние поездки</div>
            <div class="card-body p-0">
                <table class="table table-modern mb-0">
                    <thead><tr><th>Дата</th><th>ПВП</th><th>Сумма</th><th>Статус</th></tr></thead>
                    <tbody>
                        @foreach($transactions as $t)
                        <tr>
                            <td class="small">{{ $t->datetime instanceof \Carbon\Carbon ? $t->datetime->format('d.m.Y H:i') : $t->datetime }}</td>
                            <td>{{ $t->paymentPoint->name ?? '—' }}</td>
                            <td class="fw-bold text-success">{{ number_format($t->amount, 2) }} ₽</td>
                            <td>
                                @if($t->status === 'успешно')
                                <span class="badge badge-modern bg-success">Успешно</span>
                                @else
                                <span class="badge badge-modern bg-warning text-dark">{{ $t->status }}</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @else
        <div class="card-modern">
            <div class="card-body text-center text-muted py-4">
                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                <p class="mb-0">Нет поездок</p>
            </div>
        </div>
        @endif
    </div>

    <div class="modal fade" id="topupModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow">
                <form method="POST" action="{{ url('/profile/balance') }}">
                    @csrf
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold"><i class="bi bi-wallet2 me-2"></i>Пополнение баланса</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted small">Текущий баланс: <strong>{{ number_format($driver->personal_balance, 2) }} ₽</strong></p>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Сумма пополнения</label>
                            <div class="input-group">
                                <input type="number" name="amount" class="form-control form-control-lg rounded-3" min="10" max="100000" step="0.01" placeholder="1000" required>
                                <span class="input-group-text bg-light fw-semibold rounded-3">₽</span>
                            </div>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" onclick="this.form.amount.value=500">500 ₽</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" onclick="this.form.amount.value=1000">1 000 ₽</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" onclick="this.form.amount.value=2000">2 000 ₽</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" onclick="this.form.amount.value=5000">5 000 ₽</button>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="submit" class="btn btn-primary rounded-pill px-4 w-100">
                            <i class="bi bi-plus-circle me-2"></i>Пополнить
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
