<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация — ПВП</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { background: #fff; border-radius: 24px; padding: 2.5rem; width: 100%; max-width: 440px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); }
        .login-card .brand { font-size: 1.5rem; font-weight: 800; text-align: center; margin-bottom: 0.25rem; }
        .login-card .brand span { background: linear-gradient(135deg, #1a1a2e, #0f3460); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .login-card .sub { text-align: center; color: #6c757d; font-size: 0.9rem; margin-bottom: 2rem; }
        .form-control-modern { border-radius: 12px; border: 2px solid #e9ecef; padding: 0.75rem 1rem; transition: all 0.2s; }
        .form-control-modern:focus { border-color: #0d6efd; box-shadow: 0 0 0 3px rgba(13,110,253,0.1); }
        .btn-modern { border-radius: 12px; padding: 0.75rem; font-weight: 600; }
        .error-box { background: #fef2f2; border: 1px solid #fecaca; border-radius: 12px; padding: 0.75rem 1rem; margin-bottom: 1rem; color: #dc2626; font-size: 0.875rem; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="brand"><span>ПВП</span></div>
        <div class="sub">Регистрация нового аккаунта</div>

        @if($errors->any())
        <div class="error-box">
            @foreach($errors->all() as $e)
            <div><i class="bi bi-exclamation-triangle me-1"></i> {{ $e }}</div>
            @endforeach
        </div>
        @endif

        <form method="POST" action="{{ url('/register') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-semibold small">ФИО</label>
                <input type="text" name="full_name" class="form-control form-control-modern" placeholder="Иванов Иван Иванович" value="{{ old('full_name') }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold small">Номер телефона</label>
                <input type="text" name="phone" id="phone" class="form-control form-control-modern" placeholder="XXXXXXXXXX" value="{{ old('phone') }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold small">Пароль</label>
                <input type="password" name="password" class="form-control form-control-modern" placeholder="Не менее 4 символов" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold small">Подтверждение пароля</label>
                <input type="password" name="password_confirmation" class="form-control form-control-modern" placeholder="Повторите пароль" required>
            </div>
            <button type="submit" class="btn btn-primary btn-modern w-100 mb-2">
                <i class="bi bi-person-plus me-2"></i>Зарегистрироваться
            </button>
            <div class="text-center">
                <small><a href="{{ url('/login') }}" class="text-muted">Уже есть аккаунт? Войти</a></small>
            </div>
        </form>
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
