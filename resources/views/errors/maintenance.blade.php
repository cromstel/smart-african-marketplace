<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <x-seo
        :title="'Site Under Maintenance - ' . config('app.name', 'African Market')"
        :description="'Smart African Marketplace is temporarily unavailable for maintenance'"
        :index="false"
        :follow="false"
    />
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    
    <style>
        :root { 
            --primary: #FF6B35; 
            --primary-dark: #E63946; 
            --secondary: #FF4500; 
            --accent-green: #27AE60; 
            --accent-terracotta: #E17055; 
            --bg-cream: #FFF8F0; 
            --bg-warm: #FFFAF5; 
            --text-dark: #2D3436; 
            --text-gray: #636E72; 
            --text-light: #B2BEC3; 
            --white: #FFFFFF; 
            --shadow-sm: 0 2px 8px rgba(255, 107, 53, 0.08); 
            --shadow-md: 0 8px 24px rgba(255, 107, 53, 0.12); 
            --shadow-lg: 0 16px 48px rgba(255, 107, 53, 0.16); 
            --radius-sm: 8px; 
            --radius-md: 16px; 
            --radius-lg: 24px; 
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body { 
            font-family: 'DM Sans', sans-serif; 
            background: linear-gradient(135deg, var(--bg-warm) 0%, var(--bg-cream) 50%, #FFF5E6 100%); 
            background-size: 200% 200%; 
            animation: gradientShift 8s ease infinite; 
            min-height: 100vh; 
            color: var(--text-dark); 
            overflow-x: hidden; 
            display: flex;
            flex-direction: column;
        }
        
        body::before { 
            content: ''; 
            position: fixed; 
            top: 0; 
            left: 0; 
            right: 0; 
            bottom: 0; 
            background-image: 
                radial-gradient(circle at 20% 80%, rgba(255, 107, 53, 0.05) 0%, transparent 50%), 
                radial-gradient(circle at 80% 20%, rgba(255, 69, 0, 0.05) 0%, transparent 50%), 
                radial-gradient(circle at 40% 40%, rgba(39, 174, 96, 0.03) 0%, transparent 30%); 
            pointer-events: none; 
            z-index: 0; 
        }
        
        .page-container { max-width: 1600px; margin: 0 auto; width: 100%; }
        .page-container-main { flex: 1; display: flex; align-items: center; }
        
        .navbar { background: transparent; padding: 20px 40px; position: relative; z-index: 10; width: 100%; }
        .navbar-content { display: flex; align-items: center; justify-content: flex-start; width: 100%; }
        
        .logo { display: flex; align-items: center; gap: 10px; text-decoration: none; color: var(--text-dark); }
        .logo-icon { width: auto; height: 52px; display: flex; align-items: center; justify-content: center; }
        .logo-icon img { height: 100%; width: auto; max-width: 200px; }
        .logo-text { font-size: 18px; font-weight: 600; display: block; white-space: nowrap; text-transform: uppercase; letter-spacing: 1px; line-height: 1.4; }
        .logo-text span { color: var(--primary); display: block; margin-top: -2px; font-weight: 700; }
        
        .main-content { position: relative; z-index: 1; max-width: 1600px; margin: 0 auto; padding: 60px 40px 32px; text-align: center; width: 100%; }
        
        .badge { 
            display: inline-flex; 
            align-items: center; 
            gap: 8px; 
            background: linear-gradient(135deg, var(--secondary) 0%, #E8930F 100%); 
            color: var(--white); 
            padding: 8px 20px; 
            border-radius: 50px; 
            font-size: 14px; 
            font-weight: 600; 
            text-transform: uppercase; 
            letter-spacing: 1px; 
            margin-bottom: 32px; 
            animation: pulse 2s ease-in-out infinite; 
        }
        
        @keyframes pulse { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.02); } }
        @keyframes float { 0%, 100% { transform: translateY(0) rotate(0deg); } 50% { transform: translateY(-20px) rotate(5deg); } }
        @keyframes floatReverse { 0%, 100% { transform: translateY(0) rotate(0deg); } 50% { transform: translateY(20px) rotate(-5deg); } }
        @keyframes shimmer { 0% { background-position: -200% 0; } 100% { background-position: 200% 0; } }
        @keyframes gradientShift { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
        
        .badge-dot { width: 8px; height: 8px; background: var(--white); border-radius: 50%; animation: blink 1.5s ease-in-out infinite; }
        @keyframes blink { 0%, 100% { opacity: 1; } 50% { opacity: 0.4; } }
        
        .hero-title { font-size: 48px; font-weight: 700; line-height: 1.2; margin-bottom: 24px; color: var(--text-dark); }
        .hero-title span { background: linear-gradient(135deg, var(--primary) 0%, var(--accent-terracotta) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .hero-subtitle { font-size: 18px; color: var(--text-gray); line-height: 1.6; margin-bottom: 40px; max-width: 600px; margin-left: auto; margin-right: auto; }
        
        .status-card { background: var(--white); border-radius: var(--radius-lg); padding: 40px; box-shadow: var(--shadow-md); position: relative; overflow: hidden; flex: 1; }
        .status-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(90deg, var(--primary), var(--secondary), var(--accent-green)); }
        
        .status-icon { width: 80px; height: 80px; background: linear-gradient(135deg, rgba(255, 107, 53, 0.1) 0%, rgba(255, 107, 53, 0.05) 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px; }
        .status-icon svg { width: 40px; height: 40px; color: var(--primary); }
        
        .status-title { font-size: 24px; font-weight: 700; margin-bottom: 12px; color: var(--text-dark); }
        .status-message { font-size: 16px; color: var(--text-gray); line-height: 1.6; }
        
        .info-row { display: flex; gap: 24px; margin-bottom: 40px; flex-wrap: wrap; justify-content: center; }
        
        .contact-section { margin-bottom: 8px; }
        .section-title { font-size: 20px; font-weight: 700; margin-bottom: 24px; color: var(--text-dark); }
        
        .contact-grid { display: flex; justify-content: center; align-items: center; gap: 12px; flex-wrap: wrap; }
        .contact-card { background: var(--white); border-radius: var(--radius-md); padding: 14px 18px; box-shadow: var(--shadow-sm); text-decoration: none; display: flex; align-items: center; gap: 10px; transition: all 0.3s ease; }
        .contact-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }
        
        .contact-icon { width: 48px; height: 48px; border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .contact-icon.email { background: linear-gradient(135deg, rgba(255, 107, 53, 0.1) 0%, rgba(255, 107, 53, 0.05) 100%); color: var(--primary); }
        .contact-icon svg { width: 24px; height: 24px; }
        
        .contact-info { text-align: left; }
        .contact-label { font-size: 12px; color: var(--text-gray); text-transform: uppercase; letter-spacing: 0.5px; }
        .contact-value { font-size: 15px; font-weight: 600; color: var(--text-dark); }
        
        @media (max-width: 768px) { 
            .navbar { padding: 12px 20px; } 
            .logo-text { font-size: 16px; } 
            .main-content { padding: 40px 20px; } 
            .hero-title { font-size: 32px; } 
            .hero-subtitle { font-size: 16px; } 
            .info-row { flex-direction: column; } 
            .status-card { padding: 24px; } 
            .contact-grid { flex-direction: row; flex-wrap: wrap; gap: 8px; } 
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="page-container">
            <div class="navbar-content">
                <a href="{{ route('home') }}" class="logo">
                    <div class="logo-icon">
                        <img src="{{ asset(config('branding.logo')) }}" alt="{{ config('app.name', 'African Market') }} logo">
                    </div>
                    <span class="logo-text">Smart African<br><span>Marketplace</span></span>
                </a>
            </div>
        </div>
    </nav>
    
    <div class="page-container page-container-main">
        <main class="main-content">
            <div class="badge">
                <span class="badge-dot"></span>
                Site Under Maintenance
            </div>
            
            <h1 class="hero-title">We'll Be <span>Back Soon!</span></h1>
            
            <p class="hero-subtitle">
                {{ $message ?? 'The Smart African Marketplace is currently undergoing scheduled maintenance to bring you an even better shopping experience. We appreciate your patience!' }}
            </p>
            
            <div class="info-row">
                <div class="status-card">
                    <div class="status-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                        </svg>
                    </div>
                    <h2 class="status-title">Temporarily Unavailable</h2>
                    <p class="status-message">We're currently performing scheduled maintenance to improve our services.</p>
                </div>
            </div>
            
            <div class="contact-section">
                <h3 class="section-title">Need Assistance?</h3>
                <div class="contact-grid">
                    <a href="mailto:support@smartafricanmarket.com" class="contact-card">
                        <div class="contact-icon email">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div class="contact-info">
                            <div class="contact-label">Email Support</div>
                            <div class="contact-value">support@smartafricanmarket.com</div>
                        </div>
                    </a>
                </div>
            </div>
        </main>
    </div>
    
</body>
</html>
