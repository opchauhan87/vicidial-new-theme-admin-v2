<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Content-type" content="text/html; charset=utf-8">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta http-equiv="Pragma" content="no-cache">
  <title>HN Connect · AI Login</title>

  <!-- Bootstrap 5 + Icons + Google Font -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Space Grotesk', sans-serif;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 1.5rem;
      margin: 0;
      background: #0a0e1a;
      overflow: hidden;
    }

    /* Animated background particles */
    .bg-particles {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: 0;
      background: 
        radial-gradient(ellipse at 20% 50%, rgba(72, 49, 212, 0.15) 0%, transparent 50%),
        radial-gradient(ellipse at 80% 50%, rgba(0, 200, 255, 0.1) 0%, transparent 50%),
        radial-gradient(ellipse at 50% 100%, rgba(120, 50, 200, 0.08) 0%, transparent 50%);
    }

    .bg-particles::before {
      content: '';
      position: absolute;
      width: 200%;
      height: 200%;
      top: -50%;
      left: -50%;
      background-image: 
        radial-gradient(2px 2px at 20px 30px, rgba(255,255,255,0.1), transparent),
        radial-gradient(2px 2px at 40px 70px, rgba(255,255,255,0.08), transparent),
        radial-gradient(2px 2px at 50px 160px, rgba(255,255,255,0.12), transparent),
        radial-gradient(2px 2px at 90px 40px, rgba(255,255,255,0.06), transparent),
        radial-gradient(2px 2px at 130px 80px, rgba(255,255,255,0.1), transparent),
        radial-gradient(2px 2px at 160px 30px, rgba(255,255,255,0.07), transparent);
      background-size: 200px 200px;
      animation: floatParticles 20s linear infinite;
      opacity: 0.5;
    }

    @keyframes floatParticles {
      0% { transform: translate(0, 0) rotate(0deg); }
      100% { transform: translate(-50px, -30px) rotate(5deg); }
    }

    /* Glowing orbs */
    .glow-orb {
      position: fixed;
      border-radius: 50%;
      filter: blur(80px);
      z-index: 0;
    }
    .glow-orb-1 {
      width: 400px;
      height: 400px;
      background: rgba(72, 49, 212, 0.2);
      top: -100px;
      right: -100px;
      animation: orbFloat 8s ease-in-out infinite;
    }
    .glow-orb-2 {
      width: 300px;
      height: 300px;
      background: rgba(0, 200, 255, 0.15);
      bottom: -50px;
      left: -50px;
      animation: orbFloat 10s ease-in-out infinite reverse;
    }
    .glow-orb-3 {
      width: 200px;
      height: 200px;
      background: rgba(255, 100, 200, 0.1);
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      animation: orbFloat 12s ease-in-out infinite;
    }

    @keyframes orbFloat {
      0%, 100% { transform: translate(0, 0) scale(1); }
      50% { transform: translate(30px, -30px) scale(1.1); }
    }

    .login-container {
      position: relative;
      z-index: 1;
      width: 100%;
      max-width: 520px;
    }

    /* Main card - completely different design */
    .ai-card {
      background: rgba(255, 255, 255, 0.03);
      backdrop-filter: blur(40px) saturate(200%);
      -webkit-backdrop-filter: blur(40px) saturate(200%);
      border-radius: 32px;
      padding: 2.8rem 2.5rem 2.5rem;
      border: 1px solid rgba(255, 255, 255, 0.06);
      box-shadow: 
        0 40px 80px rgba(0, 0, 0, 0.6),
        0 0 0 1px rgba(255, 255, 255, 0.03) inset,
        0 0 40px rgba(72, 49, 212, 0.05);
      position: relative;
      overflow: hidden;
    }

    /* AI gradient line */
    .ai-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 3px;
      background: linear-gradient(90deg, #4831d4, #00c8ff, #ff64c8, #4831d4);
      background-size: 300% 100%;
      animation: gradientMove 4s ease-in-out infinite;
    }

    @keyframes gradientMove {
      0%, 100% { background-position: 0% 0%; }
      50% { background-position: 100% 0%; }
    }

    /* AI chip badge */
    .ai-badge {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      background: rgba(72, 49, 212, 0.15);
      border: 1px solid rgba(72, 49, 212, 0.2);
      padding: 0.3rem 1rem 0.3rem 0.8rem;
      border-radius: 100px;
      color: rgba(255, 255, 255, 0.7);
      font-size: 0.7rem;
      font-weight: 600;
      letter-spacing: 0.5px;
      text-transform: uppercase;
      margin-bottom: 1.5rem;
    }

    .ai-badge .pulse-dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: #00c8ff;
      animation: pulseDot 2s ease-in-out infinite;
      display: inline-block;
    }

    @keyframes pulseDot {
      0%, 100% { opacity: 1; transform: scale(1); }
      50% { opacity: 0.3; transform: scale(0.7); }
    }

    .brand-section {
      text-align: center;
      margin-bottom: 2.5rem;
    }

    .logo-container {
      width: 100px;
      height: 100px;
      margin: 0 auto 1rem;
      border-radius: 28px;
      background: linear-gradient(135deg, rgba(72, 49, 212, 0.2), rgba(0, 200, 255, 0.1));
      padding: 16px;
      border: 1px solid rgba(255, 255, 255, 0.06);
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
      position: relative;
    }

    .logo-container::after {
      content: '';
      position: absolute;
      inset: -2px;
      border-radius: 30px;
      padding: 2px;
      background: linear-gradient(135deg, rgba(72, 49, 212, 0.3), rgba(0, 200, 255, 0.2));
      -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
      -webkit-mask-composite: xor;
      mask-composite: exclude;
      pointer-events: none;
    }

    .logo-container img {
      width: 100%;
      height: 100%;
      object-fit: contain;
      filter: drop-shadow(0 4px 12px rgba(0,0,0,0.3));
    }

    .brand-title-ai {
      font-size: 2.4rem;
      font-weight: 700;
      background: linear-gradient(135deg, #ffffff 0%, #a0c4ff 50%, #7abfff 100%);
      -webkit-background-clip: text;
      background-clip: text;
      color: transparent;
      letter-spacing: -0.02em;
      margin-bottom: 0.25rem;
    }

    .brand-sub {
      color: rgba(255, 255, 255, 0.4);
      font-size: 0.85rem;
      font-weight: 400;
      letter-spacing: 0.3px;
    }

    .brand-sub span {
      color: rgba(0, 200, 255, 0.6);
    }

    /* Role buttons - completely different style */
    .role-grid {
      display: grid;
      gap: 1rem;
      margin: 1.8rem 0;
    }

    .role-btn {
      display: flex;
      align-items: center;
      gap: 1rem;
      padding: 0.9rem 1.5rem;
      border-radius: 16px;
      background: rgba(255, 255, 255, 0.03);
      border: 1px solid rgba(255, 255, 255, 0.06);
      color: rgba(255, 255, 255, 0.8);
      font-weight: 500;
      font-size: 1rem;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      cursor: pointer;
      width: 100%;
      text-align: left;
      position: relative;
      overflow: hidden;
      font-family: 'Space Grotesk', sans-serif;
    }

    .role-btn::before {
      content: '';
      position: absolute;
      inset: 0;
      border-radius: 16px;
      padding: 1px;
      background: linear-gradient(135deg, rgba(72, 49, 212, 0.2), rgba(0, 200, 255, 0.1));
      -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
      -webkit-mask-composite: xor;
      mask-composite: exclude;
      pointer-events: none;
      opacity: 0;
      transition: opacity 0.3s;
    }

    .role-btn:hover::before {
      opacity: 1;
    }

    .role-btn:hover {
      background: rgba(255, 255, 255, 0.06);
      border-color: rgba(255, 255, 255, 0.1);
      transform: translateY(-2px) scale(1.01);
      box-shadow: 0 12px 40px rgba(0, 0, 0, 0.3);
      color: white;
    }

    .role-btn:active {
      transform: scale(0.97);
    }

    .role-btn .icon-box {
      width: 44px;
      height: 44px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.2rem;
      flex-shrink: 0;
      transition: all 0.3s;
    }

    .role-btn .icon-box.admin {
      background: rgba(72, 49, 212, 0.2);
      color: #7a5cff;
    }
    .role-btn .icon-box.agent {
      background: rgba(0, 200, 255, 0.15);
      color: #00c8ff;
    }
    .role-btn .icon-box.clock {
      background: rgba(255, 100, 200, 0.15);
      color: #ff64c8;
    }

    .role-btn:hover .icon-box.admin {
      background: rgba(72, 49, 212, 0.3);
      box-shadow: 0 0 30px rgba(72, 49, 212, 0.15);
    }
    .role-btn:hover .icon-box.agent {
      background: rgba(0, 200, 255, 0.25);
      box-shadow: 0 0 30px rgba(0, 200, 255, 0.15);
    }
    .role-btn:hover .icon-box.clock {
      background: rgba(255, 100, 200, 0.25);
      box-shadow: 0 0 30px rgba(255, 100, 200, 0.15);
    }

    .role-btn .btn-label {
      flex: 1;
    }
    .role-btn .btn-label small {
      display: block;
      font-size: 0.7rem;
      color: rgba(255, 255, 255, 0.3);
      font-weight: 400;
      margin-top: 0.1rem;
    }

    .role-btn .arrow-icon {
      opacity: 0.3;
      transition: all 0.3s;
      font-size: 0.9rem;
    }
    .role-btn:hover .arrow-icon {
      opacity: 1;
      transform: translateX(4px);
      color: #00c8ff;
    }

    /* Divider with AI text */
    .ai-divider {
      display: flex;
      align-items: center;
      gap: 1rem;
      margin: 1.8rem 0 1.5rem;
    }

    .ai-divider .line {
      flex: 1;
      height: 1px;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.06), transparent);
    }

    .ai-divider .ai-text {
      color: rgba(255, 255, 255, 0.2);
      font-size: 0.65rem;
      font-weight: 600;
      letter-spacing: 1.5px;
      text-transform: uppercase;
      white-space: nowrap;
    }

    .ai-divider .ai-text .sparkle {
      display: inline-block;
      animation: sparkle 2s ease-in-out infinite;
    }

    @keyframes sparkle {
      0%, 100% { opacity: 0.3; }
      50% { opacity: 1; }
    }

    /* WhatsApp section - new style */
    .connect-section {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      justify-content: center;
      gap: 0.8rem;
      padding: 0.8rem 1.2rem;
      border-radius: 16px;
      background: rgba(255, 255, 255, 0.02);
      border: 1px solid rgba(255, 255, 255, 0.03);
    }

    .wa-link {
      display: flex;
      align-items: center;
      gap: 0.6rem;
      color: rgba(255, 255, 255, 0.6);
      text-decoration: none;
      font-weight: 500;
      font-size: 0.9rem;
      transition: all 0.3s;
      padding: 0.3rem 0.8rem;
      border-radius: 100px;
    }

    .wa-link:hover {
      color: white;
      background: rgba(37, 211, 102, 0.1);
    }

    .wa-link img {
      width: 24px;
      height: 24px;
    }

    .contact-chip {
      color: rgba(255, 255, 255, 0.25);
      font-size: 0.75rem;
      padding: 0.2rem 1rem;
      border-radius: 100px;
      background: rgba(255, 255, 255, 0.03);
      border: 1px solid rgba(255, 255, 255, 0.03);
    }

    .contact-chip i {
      margin-right: 4px;
    }

    /* Footer */
    .footer-ai {
      margin-top: 2rem;
      text-align: center;
      color: rgba(255, 255, 255, 0.12);
      font-size: 0.7rem;
      letter-spacing: 0.3px;
      line-height: 1.8;
    }

    .footer-ai strong {
      color: rgba(255, 255, 255, 0.2);
      font-weight: 500;
    }

    .footer-ai .phone-link {
      color: rgba(255, 255, 255, 0.15);
      text-decoration: none;
      transition: color 0.3s;
    }
    .footer-ai .phone-link:hover {
      color: rgba(255, 255, 255, 0.3);
    }

    /* AI assistant floating indicator */
    .ai-assistant {
      position: fixed;
      bottom: 2rem;
      right: 2rem;
      width: 56px;
      height: 56px;
      border-radius: 50%;
      background: linear-gradient(135deg, #4831d4, #00c8ff);
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 1.4rem;
      box-shadow: 0 8px 32px rgba(72, 49, 212, 0.3);
      cursor: pointer;
      z-index: 100;
      border: none;
      transition: all 0.3s;
      animation: floatBtn 3s ease-in-out infinite;
    }

    .ai-assistant:hover {
      transform: scale(1.1);
      box-shadow: 0 12px 48px rgba(72, 49, 212, 0.5);
    }

    @keyframes floatBtn {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-6px); }
    }

    .ai-assistant .tooltip-bubble {
      position: absolute;
      bottom: 70px;
      right: 0;
      background: rgba(0,0,0,0.8);
      backdrop-filter: blur(12px);
      padding: 0.5rem 1rem;
      border-radius: 12px;
      font-size: 0.7rem;
      color: rgba(255,255,255,0.7);
      white-space: nowrap;
      border: 1px solid rgba(255,255,255,0.05);
      opacity: 0;
      transform: translateY(10px);
      transition: all 0.3s;
      pointer-events: none;
    }

    .ai-assistant:hover .tooltip-bubble {
      opacity: 1;
      transform: translateY(0);
    }

    @media (max-width: 480px) {
      .ai-card {
        padding: 2rem 1.5rem 2rem;
        border-radius: 24px;
      }
      .brand-title-ai {
        font-size: 1.8rem;
      }
      .logo-container {
        width: 80px;
        height: 80px;
        padding: 12px;
      }
      .role-btn {
        padding: 0.7rem 1.2rem;
        font-size: 0.9rem;
      }
      .role-btn .icon-box {
        width: 38px;
        height: 38px;
        font-size: 1rem;
      }
      .ai-assistant {
        width: 48px;
        height: 48px;
        font-size: 1.2rem;
        bottom: 1rem;
        right: 1rem;
      }
      .connect-section {
        flex-direction: column;
        align-items: stretch;
        text-align: center;
        gap: 0.5rem;
      }
      .wa-link {
        justify-content: center;
      }
    }

    /* ========================================
       HN CONNECT MODERN LIGHT-GREEN THEME
       ======================================== */
    body {
      background: #f4faf6 !important;
      color: #294f3d !important;
      overflow-x: hidden !important;
      overflow-y: auto !important;
    }

    .bg-particles {
      background:
        radial-gradient(ellipse at 15% 50%, rgba(47, 174, 112, 0.10) 0%, transparent 50%),
        radial-gradient(ellipse at 85% 40%, rgba(28, 184, 126, 0.08) 0%, transparent 50%),
        radial-gradient(ellipse at 50% 100%, rgba(111, 190, 140, 0.08) 0%, transparent 50%) !important;
    }

    .bg-particles::before {
      background-image:
        radial-gradient(2px 2px at 20px 30px, rgba(44, 126, 82, 0.10), transparent),
        radial-gradient(2px 2px at 40px 70px, rgba(44, 126, 82, 0.08), transparent),
        radial-gradient(2px 2px at 90px 40px, rgba(44, 126, 82, 0.06), transparent) !important;
      opacity: .35 !important;
    }

    .glow-orb {
      filter: blur(90px) !important;
    }

    .glow-orb-1 {
      background: rgba(75, 190, 120, 0.10) !important;
    }

    .glow-orb-2 {
      background: rgba(32, 180, 120, 0.08) !important;
    }

    .glow-orb-3 {
      background: rgba(120, 210, 150, 0.06) !important;
    }

    .login-container {
      max-width: 680px !important;
    }

    .ai-card {
      background: rgba(255, 255, 255, 0.96) !important;
      backdrop-filter: blur(20px) !important;
      -webkit-backdrop-filter: blur(20px) !important;
      border: 1px solid #dceee3 !important;
      border-radius: 28px !important;
      box-shadow: 0 20px 60px rgba(35, 91, 61, 0.10), 0 2px 10px rgba(35, 91, 61, 0.05) !important;
    }

    .ai-card::before {
      height: 3px !important;
      background: linear-gradient(90deg, #20b486, #64c88b, #20b486) !important;
      background-size: 200% 100% !important;
    }

    .ai-badge {
      background: #eaf8ef !important;
      border: 1px solid #ccebd7 !important;
      color: #267149 !important;
    }

    .ai-badge .pulse-dot {
      background: #20b76b !important;
    }

    .logo-container {
      background: #ffffff !important;
      border: 1px solid #dceee3 !important;
      box-shadow: 0 10px 30px rgba(35, 91, 61, 0.10) !important;
    }

    .logo-container::after {
      background: linear-gradient(135deg, rgba(32,180,134,.25), rgba(100,200,139,.15)) !important;
    }

    .logo-container img {
      filter: none !important;
    }

    .brand-title-ai {
      background: none !important;
      -webkit-background-clip: initial !important;
      background-clip: initial !important;
      color: #245b40 !important;
    }

    .brand-sub {
      color: #668071 !important;
    }

    .brand-sub span {
      color: #20a96f !important;
    }

    .role-grid {
      gap: 14px !important;
    }

    .role-btn {
      background: #ffffff !important;
      border: 1px solid #dceee3 !important;
      color: #28573f !important;
      border-radius: 16px !important;
      box-shadow: 0 4px 14px rgba(35, 91, 61, 0.05) !important;
    }

    .role-btn:hover {
      background: #f5fbf7 !important;
      border-color: #8fd3ad !important;
      color: #1f6040 !important;
      transform: translateY(-2px) !important;
      box-shadow: 0 10px 25px rgba(35, 91, 61, 0.10) !important;
    }

    .role-btn .icon-box.admin,
    .role-btn .icon-box.agent,
    .role-btn .icon-box.clock {
      background: #e8f7ee !important;
      color: #168052 !important;
    }

    .role-btn:active {
      transform: scale(.98) !important;
    }

  </style>
</head>
<body>

<!-- Background -->
<div class="bg-particles"></div>
<div class="glow-orb glow-orb-1"></div>
<div class="glow-orb glow-orb-2"></div>
<div class="glow-orb glow-orb-3"></div>

<!-- AI Assistant Floating Button -->
<button class="ai-assistant" onclick="alert('🤖 AI Assistant: How can I help you today?')">
  <i class="bi bi-robot"></i>
  <span class="tooltip-bubble">🤖 AI Assistant</span>
</button>

<div class="login-container">
  <div class="ai-card">

    <!-- AI Badge -->
    <div class="ai-badge">
      <span class="pulse-dot"></span>
      AI Powered · v2.0
    </div>

    <!-- Brand -->
    <div class="brand-section">
      <div class="logo-container">
        <img src="logoHNC.png" alt="HN Connect">
      </div>
      <div class="brand-title-ai">HN Connect</div>
      <div class="brand-sub">
        <span>✦</span> Seamless Communication. Smart Future. <span>✦</span>
      </div>
    </div>

    <!-- Role Buttons -->
    <div class="role-grid">
      <button class="role-btn" onclick="window.location.href='../admin/admin.php'">
        <span class="icon-box admin"><i class="bi bi-shield-fill-check"></i></span>
        <span class="btn-label">
          ADMIN
          <small>Full system access &amp; control</small>
        </span>
        <span class="arrow-icon"><i class="bi bi-chevron-right"></i></span>
      </button>

      <button class="role-btn" onclick="window.location.href='../agent/index.php'">
        <span class="icon-box agent"><i class="bi bi-person-badge"></i></span>
        <span class="btn-label">
          AGENT
          <small>Manage tickets &amp; clients</small>
        </span>
        <span class="arrow-icon"><i class="bi bi-chevron-right"></i></span>
      </button>

      <button class="role-btn" onclick="window.location.href='../agent/timeclock.php'">
        <span class="icon-box clock"><i class="bi bi-clock-history"></i></span>
        <span class="btn-label">
          TIME CLOCK
          <small>Track attendance &amp; hours</small>
        </span>
        <span class="arrow-icon"><i class="bi bi-chevron-right"></i></span>
      </button>
    </div>

    <!-- AI Divider -->
    <div class="ai-divider">
      <span class="line"></span>
      <span class="ai-text"><span class="sparkle">✦</span> AI Connect <span class="sparkle">✦</span></span>
      <span class="line"></span>
    </div>

    <!-- Connect Section -->
    <div class="connect-section">
      <a href="https://wa.me/919999999999" target="_blank" class="wa-link">
        <img src="WhatsApp.svg" alt="WhatsApp">
        Chat with us
      </a>
      <span class="contact-chip">
        <i class="bi bi-envelope"></i> info@harapnilai.com
      </span>
    </div>

    <!-- Footer -->
    <div class="footer-ai">
      <strong>© 2025 HN Connect</strong> · All Rights Reserved.<br>
      <a href="tel:+60104031983" class="phone-link">
        <i class="bi bi-telephone"></i> +6010-403-1983
      </a>
    </div>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
