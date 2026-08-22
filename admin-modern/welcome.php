<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="theme-color" content="#0b5d3b">
<meta name="robots" content="noindex,nofollow">

<title>HNC Connect · AI Portal</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>

* {
    box-sizing: border-box;
}

html,
body {
    width: 100%;
    height: 100%;
    margin: 0;
}

body {
    font-family: "Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", Arial, sans-serif;
    background: #f4f8f5;
    color: #173c2c;
    overflow: hidden;
}

/* =========================================================
   BACKGROUND
   ========================================================= */

.hnc-page {
    position: relative;
    width: 100%;
    height: 100vh;
    min-height: 620px;
    overflow: hidden;
    background:
        radial-gradient(circle at 8% 10%, rgba(90,190,130,.16), transparent 27%),
        radial-gradient(circle at 92% 85%, rgba(100,190,140,.14), transparent 30%),
        linear-gradient(135deg, #f8fcf9 0%, #eef7f1 100%);
}

.hnc-page::before {
    content: "";
    position: absolute;
    width: 650px;
    height: 650px;
    left: -330px;
    bottom: -360px;
    border: 1px solid rgba(22,128,82,.08);
    border-radius: 50%;
    box-shadow:
        0 0 0 70px rgba(22,128,82,.025),
        0 0 0 140px rgba(22,128,82,.018);
}

.hnc-page::after {
    content: "";
    position: absolute;
    width: 520px;
    height: 520px;
    right: -270px;
    top: -280px;
    border: 1px solid rgba(22,128,82,.08);
    border-radius: 50%;
    box-shadow:
        0 0 0 60px rgba(22,128,82,.025),
        0 0 0 120px rgba(22,128,82,.015);
}

/* =========================================================
   TOP BAR
   ========================================================= */

.hnc-topbar {
    position: relative;
    z-index: 5;

    height: 76px;
    padding: 0 42px;

    display: flex;
    align-items: center;
    justify-content: space-between;

    background: rgba(255,255,255,.88);
    border-bottom: 1px solid #dceae2;

    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
}

.hnc-brand {
    display: flex;
    align-items: center;
    gap: 13px;
}

.hnc-brand-logo {
    height: 42px;
    width: 132px;

    object-fit: contain;
    object-position: left center;
}

.hnc-brand-divider {
    width: 1px;
    height: 28px;
    background: #d5e5dc;
}

.hnc-brand-text {
    font-size: 12px;
    font-weight: 600;
    color: #668073;
    letter-spacing: .3px;
}

.hnc-status {
    display: flex;
    align-items: center;
    gap: 10px;

    padding: 9px 14px;

    background: #eff9f3;
    border: 1px solid #d7eee0;
    border-radius: 30px;

    color: #267149;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .4px;
    text-transform: uppercase;
}

.hnc-status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #20b76b;

    box-shadow: 0 0 0 4px rgba(32,183,107,.12);
}

/* =========================================================
   MAIN LAYOUT
   ========================================================= */

.hnc-main {
    position: relative;
    z-index: 2;

    height: calc(100vh - 76px);

    display: grid;
    grid-template-columns: minmax(400px, .85fr) minmax(520px, 1.15fr);

    max-width: 1380px;
    margin: 0 auto;

    padding: 48px 55px 38px;

    gap: 70px;
}

/* =========================================================
   LEFT BRAND AREA
   ========================================================= */

.hnc-hero {
    display: flex;
    flex-direction: column;
    justify-content: center;

    padding-left: 20px;
    padding-bottom: 20px;
}

.hnc-ai-label {
    width: fit-content;

    display: inline-flex;
    align-items: center;
    gap: 9px;

    padding: 8px 13px;

    border-radius: 30px;

    background: #eaf8f0;
    border: 1px solid #ccebd8;

    color: #197a4e;

    font-size: 10px;
    font-weight: 800;
    letter-spacing: 1px;
    text-transform: uppercase;

    margin-bottom: 25px;
}

.hnc-ai-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #20b76b;

    box-shadow: 0 0 0 4px rgba(32,183,107,.10);
}

.hnc-hero-logo {
    width: min(320px, 70%);
    max-height: 130px;

    object-fit: contain;
    object-position: left center;

    margin-bottom: 28px;

    filter: drop-shadow(0 12px 22px rgba(20,80,55,.12));
}

.hnc-hero h1 {
    margin: 0;

    font-size: clamp(42px, 5vw, 70px);
    line-height: .98;

    font-weight: 800;
    letter-spacing: -3px;

    color: #14553c;
}

.hnc-hero h1 span {
    display: block;
    color: #79bd36;
}

.hnc-hero-description {
    max-width: 520px;

    margin: 23px 0 0;

    font-size: 16px;
    line-height: 1.7;

    color: #6b8276;
}

.hnc-hero-description strong {
    color: #245b40;
}

.hnc-feature-row {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;

    margin-top: 28px;
}

.hnc-feature {
    padding: 9px 13px;

    background: rgba(255,255,255,.72);

    border: 1px solid #dceae2;
    border-radius: 9px;

    color: #507063;

    font-size: 11px;
    font-weight: 600;
}

/* =========================================================
   RIGHT ACCESS AREA
   ========================================================= */

.hnc-access {
    display: flex;
    align-items: center;
    justify-content: center;
}

.hnc-access-inner {
    width: 100%;
    max-width: 620px;
}

/* Header */

.hnc-access-heading {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;

    margin-bottom: 18px;
}

.hnc-access-title h2 {
    margin: 0;

    color: #173c2c;

    font-size: 27px;
    line-height: 1.2;
    font-weight: 800;

    letter-spacing: -.8px;
}

.hnc-access-title p {
    margin: 6px 0 0;

    color: #789086;

    font-size: 12px;
}

.hnc-access-badge {
    padding: 7px 10px;

    border-radius: 8px;

    background: #ffffff;
    border: 1px solid #dceae2;

    color: #6b8176;

    font-size: 10px;
    font-weight: 700;

    white-space: nowrap;
}

/* =========================================================
   ACCESS TILES
   ========================================================= */

.hnc-roles {
    display: grid;
    gap: 12px;
}

.hnc-role {
    position: relative;

    width: 100%;
    min-height: 92px;

    display: flex;
    align-items: center;

    padding: 16px 20px;

    border: 1px solid #dceae2;
    border-radius: 17px;

    background: rgba(255,255,255,.92);

    box-shadow:
        0 6px 20px rgba(24,76,53,.045);

    color: #173c2c;

    cursor: pointer;

    text-align: left;

    transition:
        transform .18s ease,
        border-color .18s ease,
        box-shadow .18s ease,
        background .18s ease;
}

.hnc-role:hover {
    transform: translateY(-3px);

    border-color: #9bd5b4;

    background: #ffffff;

    box-shadow:
        0 14px 30px rgba(24,76,53,.10);
}

.hnc-role:active {
    transform: translateY(-1px);
}

.hnc-role-icon {
    width: 52px;
    height: 52px;

    flex: 0 0 52px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 14px;

    background: #eaf8f0;
    color: #168052;

    font-size: 22px;
    font-weight: 800;
}

.hnc-role-content {
    min-width: 0;
    padding-left: 16px;
}

.hnc-role-title {
    display: block;

    color: #245b40;

    font-size: 16px;
    font-weight: 800;

    letter-spacing: .1px;
}

.hnc-role-description {
    display: block;

    margin-top: 4px;

    color: #7a8e83;

    font-size: 11px;
}

.hnc-role-arrow {
    margin-left: auto;

    width: 34px;
    height: 34px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 50%;

    background: #f3f9f5;

    color: #77a28c;

    font-size: 17px;

    transition:
        background .18s ease,
        color .18s ease,
        transform .18s ease;
}

.hnc-role:hover .hnc-role-arrow {
    background: #e6f5ed;
    color: #168052;
    transform: translateX(3px);
}

/* =========================================================
   QUICK INFO
   ========================================================= */

.hnc-info {
    display: flex;
    align-items: center;
    gap: 10px;

    margin-top: 17px;

    padding: 12px 14px;

    background: #f7fbf8;
    border: 1px solid #e2eee7;
    border-radius: 11px;

    color: #6e8479;

    font-size: 10px;
}

.hnc-info-icon {
    width: 24px;
    height: 24px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 7px;

    background: #e3f4ea;

    color: #168052;

    font-size: 12px;
    font-weight: 800;
}

/* =========================================================
   FOOTER
   ========================================================= */

.hnc-footer {
    position: absolute;

    left: 55px;
    bottom: 22px;

    color: #8aa097;

    font-size: 10px;
}

.hnc-footer strong {
    color: #5d786b;
}

/* =========================================================
   AI FLOATING BUTTON
   ========================================================= */

.hnc-ai-button {
    position: fixed;

    right: 27px;
    bottom: 24px;

    z-index: 50;

    width: 54px;
    height: 54px;

    border: 0;
    border-radius: 17px;

    background:
        linear-gradient(145deg, #78c837, #138052);

    color: #ffffff;

    font-size: 21px;
    font-weight: 800;

    cursor: pointer;

    box-shadow:
        0 12px 28px rgba(22,128,82,.28),
        0 0 0 6px rgba(22,128,82,.07);

    transition:
        transform .18s ease,
        box-shadow .18s ease;
}

.hnc-ai-button:hover {
    transform: translateY(-4px);

    box-shadow:
        0 17px 32px rgba(22,128,82,.34),
        0 0 0 8px rgba(22,128,82,.08);
}

.hnc-ai-button::after {
    content: "AI Assistant";

    position: absolute;

    right: 65px;
    top: 50%;

    transform: translateY(-50%);

    padding: 7px 10px;

    border-radius: 8px;

    background: #173c2c;
    color: #ffffff;

    font-size: 10px;
    font-weight: 600;

    white-space: nowrap;

    opacity: 0;
    pointer-events: none;

    transition: opacity .18s ease;
}

.hnc-ai-button:hover::after {
    opacity: 1;
}

/* =========================================================
   RESPONSIVE
   ========================================================= */

@media (max-width: 1050px) {

    body {
        overflow: auto;
    }

    .hnc-page {
        height: auto;
        min-height: 100vh;
        overflow: visible;
    }

    .hnc-main {
        height: auto;

        grid-template-columns: 1fr;

        padding: 45px 30px 80px;

        gap: 40px;
    }

    .hnc-hero {
        padding-left: 0;
        align-items: center;
        text-align: center;
    }

    .hnc-hero-logo {
        object-position: center;
    }

    .hnc-hero-description {
        margin-left: auto;
        margin-right: auto;
    }

    .hnc-feature-row {
        justify-content: center;
    }

    .hnc-footer {
        position: static;
        text-align: center;
        padding: 0 20px 25px;
    }
}

@media (max-width: 650px) {

    .hnc-topbar {
        height: 64px;
        padding: 0 18px;
    }

    .hnc-brand-logo {
        width: 105px;
        height: 34px;
    }

    .hnc-brand-divider,
    .hnc-brand-text {
        display: none;
    }

    .hnc-status {
        padding: 7px 10px;
        font-size: 9px;
    }

    .hnc-main {
        padding: 32px 18px 55px;
        gap: 32px;
    }

    .hnc-hero h1 {
        font-size: 43px;
        letter-spacing: -2px;
    }

    .hnc-hero-logo {
        width: 220px;
        margin-bottom: 20px;
    }

    .hnc-hero-description {
        font-size: 13px;
    }

    .hnc-access-title h2 {
        font-size: 22px;
    }

    .hnc-access-heading {
        align-items: flex-start;
        gap: 10px;
    }

    .hnc-access-badge {
        display: none;
    }

    .hnc-role {
        min-height: 82px;
        padding: 13px;
    }

    .hnc-role-icon {
        width: 45px;
        height: 45px;
        flex-basis: 45px;
    }

    .hnc-role-content {
        padding-left: 12px;
    }

    .hnc-role-title {
        font-size: 14px;
    }

    .hnc-role-description {
        font-size: 10px;
    }

    .hnc-ai-button {
        right: 16px;
        bottom: 16px;
        width: 48px;
        height: 48px;
    }
}

</style>
</head>

<body>

<div class="hnc-page">

    <!-- =====================================================
         TOP BAR
         ===================================================== -->

    <header class="hnc-topbar">

        <div class="hnc-brand">

            <img
                src="logoHNC.png"
                alt="HNC Connect"
                class="hnc-brand-logo"
            >

            <span class="hnc-brand-divider"></span>

            <span class="hnc-brand-text">
                Intelligent Communication Platform
            </span>

        </div>

        <div class="hnc-status">
            <span class="hnc-status-dot"></span>
            System Online
        </div>

    </header>


    <!-- =====================================================
         MAIN
         ===================================================== -->

    <main class="hnc-main">

        <!-- LEFT -->

        <section class="hnc-hero">

            <div class="hnc-ai-label">
                <span class="hnc-ai-dot"></span>
                AI Powered Platform
            </div>

            <img
                src="logoHNC.png"
                alt="HNC Connect"
                class="hnc-hero-logo"
            >

            <h1>
                Connect.
                <span>Communicate.</span>
            </h1>

            <p class="hnc-hero-description">
                Welcome to <strong>HNC Connect</strong> —
                your intelligent communication and contact-center
                workspace designed for faster, smarter operations.
            </p>

            <div class="hnc-feature-row">

                <span class="hnc-feature">
                    AI Assisted
                </span>

                <span class="hnc-feature">
                    Secure Access
                </span>

                <span class="hnc-feature">
                    Smart Operations
                </span>

            </div>

        </section>


        <!-- RIGHT -->

        <section class="hnc-access">

            <div class="hnc-access-inner">

                <div class="hnc-access-heading">

                    <div class="hnc-access-title">

                        <h2>
                            Choose your workspace
                        </h2>

                        <p>
                            Select an option to continue
                        </p>

                    </div>

                    <div class="hnc-access-badge">
                        HNC CONNECT
                    </div>

                </div>


                <div class="hnc-roles">

                    <!-- ADMIN -->

                    <button
                        type="button"
                        class="hnc-role"
                        onclick="window.location.href='../admin/admin.php'"
                    >

                        <span class="hnc-role-icon">
                            ✓
                        </span>

                        <span class="hnc-role-content">

                            <span class="hnc-role-title">
                                Administration
                            </span>

                            <span class="hnc-role-description">
                                Manage users, campaigns, systems and platform settings
                            </span>

                        </span>

                        <span class="hnc-role-arrow">
                            →
                        </span>

                    </button>


                    <!-- AGENT -->

                    <button
                        type="button"
                        class="hnc-role"
                        onclick="window.location.href='../agent/index.php'"
                    >

                        <span class="hnc-role-icon">
                            ◉
                        </span>

                        <span class="hnc-role-content">

                            <span class="hnc-role-title">
                                Agent Workspace
                            </span>

                            <span class="hnc-role-description">
                                Access your customer, calling and communication workspace
                            </span>

                        </span>

                        <span class="hnc-role-arrow">
                            →
                        </span>

                    </button>


                    <!-- TIME CLOCK -->

                    <button
                        type="button"
                        class="hnc-role"
                        onclick="window.location.href='../agent/timeclock.php'"
                    >

                        <span class="hnc-role-icon">
                            ◷
                        </span>

                        <span class="hnc-role-content">

                            <span class="hnc-role-title">
                                Time Clock
                            </span>

                            <span class="hnc-role-description">
                                Track attendance, shifts and working hours
                            </span>

                        </span>

                        <span class="hnc-role-arrow">
                            →
                        </span>

                    </button>

                </div>


                <div class="hnc-info">

                    <span class="hnc-info-icon">
                        AI
                    </span>

                    <span>
                        HNC AI services are being integrated into the platform.
                    </span>

                </div>

            </div>

        </section>

    </main>


    <!-- =====================================================
         FOOTER
         ===================================================== -->

    <div class="hnc-footer">
        <strong>© 2025 HN Connect</strong>
        · Intelligent Communication Platform
    </div>


    <!-- =====================================================
         AI BUTTON
         ===================================================== -->

    <button
        type="button"
        class="hnc-ai-button"
        aria-label="HNC AI Assistant"
        onclick="alert('HNC AI Assistant is coming soon.')"
    >
        AI
    </button>

</div>

</body>
</html>
