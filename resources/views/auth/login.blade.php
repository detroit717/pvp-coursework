<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход — ПВП</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { background: #fff; border-radius: 24px; padding: 2.5rem; width: 100%; max-width: 420px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); }
        .login-card .brand { font-size: 1.5rem; font-weight: 800; text-align: center; margin-bottom: 0.25rem; }
        .login-card .brand span { background: linear-gradient(135deg, #1a1a2e, #0f3460); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .login-card .sub { text-align: center; color: #6c757d; font-size: 0.9rem; margin-bottom: 2rem; }
        .form-control-modern { border-radius: 12px; border: 2px solid #e9ecef; padding: 0.75rem 1rem; transition: all 0.2s; }
        .form-control-modern:focus { border-color: #0d6efd; box-shadow: 0 0 0 3px rgba(13,110,253,0.1); }
        .btn-modern { border-radius: 12px; padding: 0.75rem; font-weight: 600; }
        .error-box { background: #fef2f2; border: 1px solid #fecaca; border-radius: 12px; padding: 0.75rem 1rem; margin-bottom: 1rem; color: #dc2626; font-size: 0.875rem; }
        .driver-hint { margin-top: 1.5rem; padding: 1rem; background: #f8f9fa; border-radius: 12px; font-size: 0.8rem; max-height: 200px; overflow-y: auto; }
        .driver-hint code { display: block; padding: 2px 0; color: #495057; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="brand"><span>ПВП</span></div>
        <div class="sub">Вход в личный кабинет</div>

        @if($errors->any())
        <div class="error-box">
            <i class="bi bi-exclamation-triangle me-1"></i> {{ $errors->first('phone') }}
        </div>
        @endif

        <form method="POST" action="{{ url('/login') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-semibold small">Номер телефона</label>
                <input type="text" name="phone" id="phone" class="form-control form-control-modern" placeholder="XXXXXXXXXX" value="{{ old('phone') }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold small">Пароль</label>
                <input type="password" name="password" class="form-control form-control-modern" placeholder="Пароль" required>
            </div>
            <button type="submit" class="btn btn-primary btn-modern w-100 mb-2">
                <i class="bi bi-box-arrow-in-right me-2"></i>Войти
            </button>
            <div class="text-center">
                <small><a href="{{ url('/register') }}" class="text-muted">Нет аккаунта? Зарегистрироваться</a></small>
            </div>
        </form>

        <div class="driver-hint">
            <div class="fw-bold mb-1 small text-muted">Тестовые аккаунты (пароль: password123):</div>
            <code>+70066564484 — администратор</code>
            @php
                $sampleDrivers = \App\Models\Driver::where('id_driver', '>', 1)->take(5)->get();
            @endphp
            @foreach($sampleDrivers as $d)
            <code>{{ $d->phone_number }} — {{ $d->full_name }}</code>
            @endforeach
        </div>
    </div>

    <script>
        (function() {
            var phoneInput = document.getElementById('phone');
            if (!phoneInput) return;
            phoneInput.addEventListener('input', function() {
                var val = this.value.replace(/[^0-9]/g, '');
                if (val.length > 11) val = val.slice(0, 11);
                if (val.length === 0) { this.value = ''; return; }
                if (val.length === 1 && (val === '8' || val === '7')) { this.value = ''; return; }
                var rest = (val[0] === '7' || val[0] === '8') ? val.slice(1) : val;
                var formatted = '+7';
                if (rest.length > 0) formatted += ' (' + rest.slice(0, 3);
                if (rest.length > 3) formatted += ') ' + rest.slice(3, 6);
                if (rest.length > 6) formatted += '-' + rest.slice(6, 8);
                if (rest.length > 8) formatted += '-' + rest.slice(8, 10);
                this.value = formatted;
            });
        })();
    </script>
</body>
</html>
