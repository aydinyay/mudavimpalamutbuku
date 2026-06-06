<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Yönetim Girişi — Müdavim Restaurant</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{
            min-height:100vh;
            display:flex;
            align-items:center;
            justify-content:center;
            font-family:'Inter',sans-serif;
            background: #1a2e2b;
            background-image:
                radial-gradient(ellipse at 20% 50%, rgba(26,107,94,.4) 0%, transparent 60%),
                radial-gradient(ellipse at 80% 20%, rgba(201,93,63,.15) 0%, transparent 50%);
        }
        .login-card{
            width:100%;
            max-width:420px;
            background:#fff;
            border-radius:20px;
            overflow:hidden;
            box-shadow:0 24px 64px rgba(0,0,0,.35);
            margin:20px;
        }
        .login-header{
            background: linear-gradient(160deg, #1a2e2b 0%, #1a6b5e 100%);
            padding:36px 40px 28px;
            text-align:center;
        }
        .login-header img{height:56px;width:auto;display:block;margin:0 auto 16px;}
        .login-header h1{
            font-family:'Cormorant Garamond',serif;
            color:#fff;font-size:1.5rem;font-weight:600;
            letter-spacing:.05em;margin:0 0 4px;
        }
        .login-header p{color:rgba(255,255,255,.55);font-size:.78rem;margin:0;}
        .login-body{padding:32px 40px 36px;}
        .form-label{font-size:.82rem;font-weight:600;color:#444;margin-bottom:6px;}
        .form-control{
            border:1.5px solid #e0e0e0;
            border-radius:10px;
            padding:11px 14px;
            font-size:.9rem;
            transition:border-color .2s,box-shadow .2s;
        }
        .form-control:focus{
            border-color:#1a6b5e;
            box-shadow:0 0 0 3px rgba(26,107,94,.12);
            outline:none;
        }
        .input-icon-wrap{position:relative;}
        .input-icon-wrap .bi{
            position:absolute;right:14px;top:50%;transform:translateY(-50%);
            color:#aaa;font-size:1rem;pointer-events:none;
        }
        .btn-login{
            width:100%;
            background:linear-gradient(135deg,#1a6b5e,#2ea887);
            color:#fff;border:none;border-radius:10px;
            padding:13px;font-size:.95rem;font-weight:600;
            letter-spacing:.02em;cursor:pointer;
            transition:opacity .2s,transform .1s;
            margin-top:8px;
        }
        .btn-login:hover{opacity:.92;transform:translateY(-1px);}
        .btn-login:active{transform:translateY(0);}
        .error-msg{color:#dc3545;font-size:.78rem;margin-top:5px;}
        .remember-wrap{display:flex;align-items:center;gap:8px;margin-top:4px;}
        .remember-wrap input{width:16px;height:16px;accent-color:#1a6b5e;cursor:pointer;}
        .remember-wrap label{font-size:.82rem;color:#666;cursor:pointer;}
        .login-footer{
            border-top:1px solid #f0f0f0;
            padding:14px 40px;
            text-align:center;
            font-size:.72rem;
            color:#bbb;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <img src="/images/logo-light.png" alt="Müdavim Restaurant">
            <h1>Yönetim Paneli</h1>
            <p>Müdavim Palamutbükü</p>
        </div>
        <div class="login-body">
            {{ $slot }}
        </div>
        <div class="login-footer">
            © {{ date('Y') }} HK Seyahat Turizm ve Dış Tic. Ltd. Şti.
        </div>
    </div>
</body>
</html>
