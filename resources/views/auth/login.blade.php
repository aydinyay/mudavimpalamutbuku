<x-guest-layout>

    @if(session('status'))
    <div style="background:#d1fae5;color:#065f46;border-radius:8px;padding:10px 14px;font-size:.82rem;margin-bottom:16px;">
        {{ session('status') }}
    </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">E-posta Adresi</label>
            <div class="input-icon-wrap">
                <input id="email" type="email" name="email" class="form-control"
                       value="{{ old('email') }}" required autofocus autocomplete="username"
                       placeholder="ornek@mudavimpalamutbuku.com">
                <i class="bi bi-envelope"></i>
            </div>
            @error('email')
            <div class="error-msg">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Şifre</label>
            <div class="input-icon-wrap">
                <input id="password" type="password" name="password" class="form-control"
                       required autocomplete="current-password" placeholder="••••••••">
                <i class="bi bi-lock"></i>
            </div>
            @error('password')
            <div class="error-msg">{{ $message }}</div>
            @enderror
        </div>

        <div class="remember-wrap mb-3">
            <input id="remember_me" type="checkbox" name="remember">
            <label for="remember_me">Beni hatırla</label>
        </div>

        <button type="submit" class="btn-login">
            <i class="bi bi-box-arrow-in-right me-2"></i>Giriş Yap
        </button>

        @if(Route::has('password.request'))
        <div class="text-center mt-3">
            <a href="{{ route('password.request') }}"
               style="font-size:.78rem;color:#aaa;text-decoration:none;">
                Şifremi unuttum
            </a>
        </div>
        @endif
    </form>

</x-guest-layout>
