<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Connexion — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        *,*::before,*::after{box-sizing:border-box}
        html,body{height:100%;margin:0}
        body{
            font-family:'Inter',sans-serif;
            background:#05050c;
            display:flex;align-items:center;justify-content:center;
            min-height:100vh;padding:1.5rem;
            overflow:hidden;
        }
        /* Ambient orbs */
        .orb{position:fixed;border-radius:50%;filter:blur(90px);pointer-events:none;will-change:opacity,transform}
        .orb-1{width:700px;height:700px;background:radial-gradient(circle,rgba(91,33,182,.5),transparent 65%);top:-250px;left:-200px;animation:pulse1 9s ease-in-out infinite}
        .orb-2{width:550px;height:550px;background:radial-gradient(circle,rgba(55,48,163,.45),transparent 65%);bottom:-180px;right:-120px;animation:pulse1 9s ease-in-out infinite 3s}
        .orb-3{width:320px;height:320px;background:radial-gradient(circle,rgba(212,175,55,.18),transparent 65%);top:45%;left:48%;transform:translate(-50%,-50%);animation:pulse3 7s ease-in-out infinite 1.5s}
        @keyframes pulse1{0%,100%{opacity:.4;transform:scale(1)}50%{opacity:.6;transform:scale(1.1)}}
        @keyframes pulse3{0%,100%{opacity:.18;transform:translate(-50%,-50%) scale(1)}50%{opacity:.32;transform:translate(-50%,-50%) scale(1.12)}}
        /* Subtle dot grid */
        body::before{content:'';position:fixed;inset:0;z:0;
            background-image:radial-gradient(circle,rgba(255,255,255,.06) 1px,transparent 1px);
            background-size:28px 28px;pointer-events:none;z-index:0}
        /* Card */
        .login-card{
            position:relative;z-index:10;width:100%;max-width:420px;
            background:rgba(8,8,15,.88);
            backdrop-filter:blur(28px);-webkit-backdrop-filter:blur(28px);
            border-radius:1.5rem;
            border:1px solid rgba(255,255,255,.07);
            box-shadow:0 32px 80px rgba(0,0,0,.75),0 0 0 1px rgba(255,255,255,.03) inset,0 1px 0 rgba(255,255,255,.08) inset;
            padding:2.5rem;
        }
        /* Logo */
        .logo-box{
            width:62px;height:62px;border-radius:14px;
            background:linear-gradient(135deg,#d4af37,#a87820);
            display:flex;align-items:center;justify-content:center;
            margin:0 auto 1.25rem;
            box-shadow:0 0 28px rgba(212,175,55,.35),0 8px 20px rgba(0,0,0,.4);
            animation:logoGlow 4s ease-in-out infinite;
        }
        @keyframes logoGlow{0%,100%{box-shadow:0 0 20px rgba(212,175,55,.28),0 8px 20px rgba(0,0,0,.4)}50%{box-shadow:0 0 42px rgba(212,175,55,.55),0 8px 20px rgba(0,0,0,.4)}}
        .text-gold{background:linear-gradient(135deg,#e8c840,#d4af37,#c8982a);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
        /* Divider */
        .hr{height:1px;background:linear-gradient(to right,transparent,rgba(255,255,255,.08),transparent);margin:1.5rem 0}
        /* Form labels */
        .field-label{display:block;font-size:.75rem;font-weight:600;letter-spacing:.06em;text-transform:uppercase;color:#545470;margin-bottom:.5rem}
        /* Input wrapper */
        .field-wrap{position:relative}
        .field-icon{position:absolute;left:13px;top:50%;transform:translateY(-50%);color:#545470;width:16px;height:16px;pointer-events:none;transition:color .15s;flex-shrink:0}
        .field-input{
            width:100%;padding:.75rem 1rem .75rem 2.75rem;
            background:#0d0d18;border:1px solid rgba(255,255,255,.08);
            border-radius:.625rem;color:#e0e0ee;font-size:.875rem;font-family:inherit;
            transition:border-color .15s,box-shadow .15s;outline:none;
        }
        .field-input::placeholder{color:#3a3a55}
        .field-input:focus{border-color:rgba(139,92,246,.5);box-shadow:0 0 0 3px rgba(139,92,246,.1)}
        /* Autofill dark */
        .field-input:-webkit-autofill,.field-input:-webkit-autofill:hover,.field-input:-webkit-autofill:focus{
            -webkit-box-shadow:0 0 0 1000px #0d0d18 inset !important;
            -webkit-text-fill-color:#e0e0ee !important;
            caret-color:#e0e0ee;border-color:rgba(255,255,255,.08) !important
        }
        /* Password toggle */
        .pw-btn{position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;color:#545470;cursor:pointer;padding:4px;line-height:1;transition:color .15s}
        .pw-btn:hover{color:#88889a}
        /* Submit button */
        .btn-login{
            width:100%;padding:.9rem;border:none;border-radius:.625rem;
            background:linear-gradient(135deg,#5b21b6,#7c3aed);
            color:#fff;font-size:.8125rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;
            cursor:pointer;transition:transform .1s,box-shadow .15s;
            box-shadow:0 4px 24px rgba(109,40,217,.45);
        }
        .btn-login:hover{transform:translateY(-1px);box-shadow:0 8px 32px rgba(109,40,217,.6)}
        .btn-login:active{transform:scale(.98)}
        /* Remember checkbox */
        .check-wrap{display:flex;align-items:center;gap:.5rem;cursor:pointer;user-select:none}
        input[type="checkbox"].custom-cb{width:15px;height:15px;accent-color:#8b5cf6;cursor:pointer;border-radius:3px}
    </style>
</head>
<body>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>

    <div class="login-card">
        <div class="text-center" style="margin-bottom:1.5rem">
            <div class="logo-box">
                <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
            </div>
            <h1 class="text-gold" style="font-size:1.375rem;font-weight:900;letter-spacing:-.01em;margin:0">{{ config('app.name') }}</h1>
            <p style="font-size:.6875rem;color:#3a3a55;letter-spacing:.1em;margin:.3rem 0 0;text-transform:uppercase;font-weight:600">Espace sécurisé — Staff</p>
        </div>

        <div class="hr"></div>

        {{ $slot }}

        <div class="hr"></div>

        <p style="text-align:center;font-size:.6875rem;color:#1e1e30;margin:0">
            © {{ date('Y') }} {{ config('app.name') }}
        </p>
    </div>

    <script>
        function togglePw(id,btn){
            const f=document.getElementById(id);
            const show=f.type==='password';
            f.type=show?'text':'password';
            btn.innerHTML=show
                ?'<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24M1 1l22 22"/></svg>'
                :'<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
        }
    </script>
</body>
</html>
