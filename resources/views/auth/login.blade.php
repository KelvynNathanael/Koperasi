{{-- resources/views/auth/login.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login – KoperasiApp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #0f172a;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            border-radius: 1rem;
            border: 1px solid #1e293b;
            background: #1e293b;
        }
    </style>
</head>
<body>
    <div class="login-card p-4 p-md-5">

        {{-- Logo --}}
        <div class="text-center mb-4">
            <div class="mb-3" style="width:56px;height:56px;background:#2563eb;border-radius:12px;
                display:flex;align-items:center;justify-content:center;margin:auto;">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="white" viewBox="0 0 16 16">
                    <path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/>
                </svg>
            </div>
            <h5 class="text-white fw-bold mb-1">KoperasiApp</h5>
            <p class="text-secondary small">Masuk ke sistem manajemen</p>
        </div>

        @if($errors->any())
            <div class="alert alert-danger py-2 small">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label text-light small">Name</label>
                <input type="text" name="name"
                       class="form-control bg-dark border-secondary text-white"
                       value="{{ old('name') }}"
                       placeholder="Admin"
                       autofocus required>
            </div>

            <div class="mb-3">
                <label class="form-label text-light small">Password</label>
                <input type="password" name="password"
                       class="form-control bg-dark border-secondary text-white"
                       placeholder="••••••••" required>
            </div>

            <div class="mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label text-secondary small" for="remember">
                        Ingat saya
                    </label>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 fw-semibold">
                Masuk
            </button>
        </form>

    </div>
</body>
</html>