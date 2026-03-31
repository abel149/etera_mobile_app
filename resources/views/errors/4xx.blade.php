<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found — etera</title>
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
        .container {
            text-align: center;
            padding: 2rem;
            position: relative;
            z-index: 2;
        }
        .error-code {
            font-size: clamp(6rem, 18vw, 12rem);
            font-weight: 800;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1;
            margin-bottom: 0.5rem;
            animation: float 3s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-15px); }
        }
        .error-title {
            font-size: clamp(1.2rem, 3vw, 1.8rem);
            font-weight: 700;
            margin-bottom: 0.75rem;
            color: #e0e0ff;
        }
        .error-message {
            font-size: 1rem;
            color: rgba(255,255,255,0.6);
            max-width: 420px;
            margin: 0 auto 2rem;
            line-height: 1.6;
        }
        .btn-group {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 14px 32px;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            box-shadow: 0 4px 20px rgba(102, 126, 234, 0.4);
        }
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 30px rgba(102, 126, 234, 0.6);
        }
        .btn-outline {
            background: transparent;
            color: #fff;
            border: 2px solid rgba(255,255,255,0.3);
        }
        .btn-outline:hover {
            border-color: #667eea;
            background: rgba(102, 126, 234, 0.1);
            transform: translateY(-3px);
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
            opacity: 0.05;
            background: #667eea;
        }
        .bg-circles .circle:nth-child(1) { width: 500px; height: 500px; top: -100px; right: -100px; animation: drift 20s linear infinite; }
        .bg-circles .circle:nth-child(2) { width: 300px; height: 300px; bottom: -50px; left: -50px; animation: drift 15s linear infinite reverse; }
        .bg-circles .circle:nth-child(3) { width: 200px; height: 200px; top: 40%; left: 60%; animation: drift 25s linear infinite; }
        @keyframes drift {
            0% { transform: translate(0, 0) rotate(0deg); }
            50% { transform: translate(30px, -30px) rotate(180deg); }
            100% { transform: translate(0, 0) rotate(360deg); }
        }
        .emoji { font-size: 3rem; margin-bottom: 1rem; }
    </style>
</head>
<body>
    <div class="bg-circles">
        <div class="circle"></div>
        <div class="circle"></div>
        <div class="circle"></div>
    </div>
    <div class="container">
        <div class="emoji">🔍</div>
        <h1 class="error-title">We can’t find that page</h1>
        <p class="error-message">
            The page you're looking for doesn't exist or has been moved. 
            Don't worry — let's get you back on track.
        </p>
        <div class="btn-group">
            <a href="/" class="btn btn-primary">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                Go Home
            </a>
            <a href="/login" class="btn btn-outline">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                Login
            </a>
            <button type="button" class="btn btn-outline" onclick="history.back()">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/><line x1="9" y1="12" x2="21" y2="12"/></svg>
                Go Back
            </button>
        </div>
    </div>
</body>
</html>
