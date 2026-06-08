@extends('layouts.app')

@section('title', 'Главная - ПВП')

@push('styles')
<style>
    .dash-card { background: #fff; border-radius: 16px; padding: 1.5rem; border: 1px solid rgba(0,0,0,0.04); transition: all 0.3s; height: 100%; }
    .dash-card:hover { transform: translateY(-4px); box-shadow: 0 12px 40px rgba(0,0,0,0.08); }
    .dash-card .icon-wrap { width: 56px; height: 56px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; margin-bottom: 1rem; }
    .dash-card .label { font-size: 0.8rem; color: #6c757d; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
    .dash-card .value { font-size: 2rem; font-weight: 800; line-height: 1.2; }
    .dash-card .sub { font-size: 0.8rem; color: #adb5bd; margin-top: 0.25rem; }
    .quick-btn { border-radius: 12px; padding: 0.75rem 1.25rem; font-weight: 600; transition: all 0.2s; }
    .quick-btn:hover { transform: translateX(4px); }
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2>Панель управления</h2>
            <p class="text-muted mb-0">Сводка по системе ПВП на {{ now()->format('d.m.Y') }}</p>
        </div>
        <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill">
            <i class="bi bi-check-circle me-1"></i>Система активна
        </span>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="dash-card">
            <div class="icon-wrap" style="background: rgba(13,110,253,0.1); color: var(--primary);">
                <i class="bi bi-arrow-left-right"></i>
            </div>
            <div class="label">Транзакции (30 дн.)</div>
            <div class="value" id="dash-trans-count">{{ number_format($stats->total_transactions ?? 0) }}</div>
            <div class="sub">Всего операций</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="dash-card">
            <div class="icon-wrap" style="background: rgba(25,135,84,0.1); color: var(--success);">
                <i class="bi bi-cash-stack"></i>
            </div>
            <div class="label">Выручка (30 дн.)</div>
            <div class="value" id="dash-revenue">{{ number_format($stats->total_revenue ?? 0, 0, ',', ' ') }} ₽</div>
            <div class="sub">Российский рубль</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="dash-card">
            <div class="icon-wrap" style="background: rgba(111,66,193,0.1); color: #6f42c1;">
                <i class="bi bi-people"></i>
            </div>
            <div class="label">Активные водители</div>
            <div class="value" id="dash-drivers">{{ number_format($stats->unique_drivers ?? 0) }}</div>
            <div class="sub">Уникальные пользователи</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="dash-card">
            <div class="icon-wrap" style="background: rgba(255,193,7,0.1); color: #ffc107;">
                <i class="bi bi-receipt"></i>
            </div>
            <div class="label">Средний чек</div>
            <div class="value" id="dash-avg">{{ number_format($stats->avg_check ?? 0, 0, ',', ' ') }} ₽</div>
            <div class="sub">За одну поездку</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-8">
        <div class="card-modern">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-clock-history me-2"></i>Последние транзакции</span>
                <a href="{{ route('transactions.index') }}" class="btn btn-sm btn-outline-primary rounded-pill">Все транзакции</a>
            </div>
            <div class="card-body p-0">
                <table class="table table-modern mb-0">
                    <thead>
                        <tr>
                            <th>Время</th>
                            <th>Автомобиль</th>
                            <th>Сумма</th>
                            <th>Статус</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentTransactions as $t)
                        <tr>
                            <td>{{ $t->datetime->format('H:i d.m') }}</td>
                            <td><code class="bg-light px-2 py-1 rounded">{{ $t->vehicle->plate_number ?? '—' }}</code></td>
                            <td class="fw-bold">{{ number_format($t->amount, 2, ',', ' ') }} ₽</td>
                            <td>
                                <span class="badge badge-modern {{ $t->status === 'успешно' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                                    {{ $t->status }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">Нет транзакций</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card-modern">
            <div class="card-header"><i class="bi bi-lightning me-2"></i>Быстрые действия</div>
            <div class="card-body">
                <div class="d-grid gap-3">
                    <a href="{{ route('transactions.index') }}" class="btn btn-primary quick-btn">
                        <i class="bi bi-plus-circle me-2"></i>Новая оплата
                    </a>
                    <a href="{{ route('fines.index') }}" class="btn btn-outline-danger quick-btn">
                        <i class="bi bi-exclamation-triangle me-2"></i>Выписать штраф
                    </a>
                    <a href="{{ route('transponders.index') }}" class="btn btn-outline-info quick-btn">
                        <i class="bi bi-wifi me-2"></i>Назначить транспондер
                    </a>
                    <a href="{{ route('statistics.index') }}" class="btn btn-outline-dark quick-btn">
                        <i class="bi bi-bar-chart me-2"></i>Полный отчет
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
