<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session Expired — etera</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #24243e 100%);
            color: #fff;
            overflow: hidden;
        }
        .bg-circles {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            z-index: 1;
            pointer-events: none;
        }
        .bg-circles .circle {
            position: absolute;
            border-radius: 50%;
            opacity: 0.06;
            background: #667eea;
        }
        .bg-circles .circle:nth-child(1) { width: 520px; height: 520px; top: -120px; right: -120px; animation: drift 20s linear infinite; }
        .bg-circles .circle:nth-child(2) { width: 320px; height: 320px; bottom: -70px; left: -70px; animation: drift 15s linear infinite reverse; }
        .bg-circles .circle:nth-child(3) { width: 200px; height: 200px; top: 45%; left: 60%; animation: drift 25s linear infinite; }
        @keyframes drift {
            0% { transform: translate(0, 0) rotate(0deg); }
            50% { transform: translate(30px, -30px) rotate(180deg); }
            100% { transform: translate(0, 0) rotate(360deg); }
        }
        .card {
            width: min(720px, 92vw);
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.12);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border-radius: 18px;
            padding: 28px;
            position: relative;
            z-index: 2;
            box-shadow: 0 18px 60px rgba(0,0,0,0.35);
        }
        .title {
            font-size: 1.6rem;
            font-weight: 800;
            margin-bottom: 10px;
        }
        .subtitle {
            color: rgba(255,255,255,0.75);
            line-height: 1.7;
            margin-bottom: 18px;
            max-width: 60ch;
        }
        .hint {
            display: grid;
            gap: 10px;
            margin: 18px 0 22px;
            color: rgba(255,255,255,0.75);
            font-size: 0.95rem;
        }
        .hint div {
            display: flex;
            gap: 10px;
            align-items: flex-start;
        }
        .dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-top: 6px;
            background: rgba(255,255,255,0.6);
            flex: 0 0 auto;
        }
        .btn-group {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 14px;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 18px;
            border-radius: 999px;
            font-weight: 700;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.25s ease;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            box-shadow: 0 8px 30px rgba(102, 126, 234, 0.35);
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 12px 40px rgba(102, 126, 234, 0.55); }
        .btn-outline {
            background: transparent;
            color: #fff;
            border: 2px solid rgba(255,255,255,0.22);
        }
        .btn-outline:hover { background: rgba(255,255,255,0.06); transform: translateY(-2px); }
        .emoji { font-size: 2.4rem; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="bg-circles">
        <div class="circle"></div>
        <div class="circle"></div>
        <div class="circle"></div>
    </div>

    <div class="card">
        <div class="emoji">⏱️</div>
        <h1 class="title">Your session expired</h1>
        <p class="subtitle">
            For your security, we logged you out because your session was inactive or opened in multiple tabs.
            Please log in again to continue.
        </p>

        <div class="hint">
            <div><span class="dot"></span><span>Refresh the page if you were filling a form.</span></div>
            <div><span class="dot"></span><span>Log in again to continue where you left off.</span></div>
            <div><span class="dot"></span><span>If it keeps happening, allow cookies for this site.</span></div>
        </div>

        <div class="btn-group">
            <a class="btn btn-primary" href="{{ route('login') }}">Log in again</a>
            <a class="btn btn-outline" href="{{ url('/') }}">Go home</a>
            <button class="btn btn-outline" type="button" onclick="history.back()">Go back</button>
        </div>
    </div>
</body>
</html>
