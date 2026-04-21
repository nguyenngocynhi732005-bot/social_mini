<div class="auth-screen-shell">
    <div class="auth-screen-orb auth-screen-orb-left"></div>
    <div class="auth-screen-orb auth-screen-orb-right"></div>
    <div class="auth-screen-orb auth-screen-orb-bottom"></div>
    <div class="auth-screen-orb auth-screen-orb-top"></div>

    <div class="auth-screen-card">
        <div class="auth-screen-logo">
            {{ $logo }}
        </div>

        <div class="auth-screen-panel">
            {{ $slot }}
        </div>
    </div>
</div>

<style>
    .auth-screen-shell {
        position: relative;
        min-height: 100vh;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 36px 18px;
        background:
            radial-gradient(circle at 14% 12%, rgba(255, 255, 255, 0.7) 0%, rgba(255, 255, 255, 0.18) 16%, transparent 34%),
            radial-gradient(circle at 88% 16%, rgba(255, 255, 255, 0.42) 0%, rgba(255, 255, 255, 0.1) 14%, transparent 34%),
            radial-gradient(circle at 16% 84%, rgba(255, 177, 217, 0.45) 0%, rgba(255, 177, 217, 0.12) 16%, transparent 40%),
            radial-gradient(circle at 82% 74%, rgba(255, 220, 153, 0.46) 0%, rgba(255, 220, 153, 0.12) 16%, transparent 38%),
            linear-gradient(135deg, #f7d0d5 0%, #f4b1d2 34%, #ffd8ac 68%, #fff2bf 100%);
    }

    .auth-screen-shell::before,
    .auth-screen-shell::after {
        content: '';
        position: absolute;
        inset: 0;
        pointer-events: none;
    }

    .auth-screen-shell::before {
        background:
            linear-gradient(135deg, rgba(255, 255, 255, 0.35) 0%, rgba(255, 255, 255, 0.08) 48%, rgba(255, 255, 255, 0.22) 100%);
        opacity: 0.9;
    }

    .auth-screen-shell::after {
        background-image:
            radial-gradient(circle at 18% 22%, rgba(255, 255, 255, 0.22) 0 1px, transparent 1px),
            radial-gradient(circle at 82% 24%, rgba(255, 255, 255, 0.2) 0 1px, transparent 1px),
            radial-gradient(circle at 60% 78%, rgba(255, 255, 255, 0.18) 0 1px, transparent 1px);
        background-size: 180px 180px, 240px 240px, 200px 200px;
        opacity: 0.62;
        mix-blend-mode: screen;
    }

    .auth-screen-orb {
        position: absolute;
        border-radius: 50%;
        filter: blur(0px);
        opacity: 0.96;
        pointer-events: none;
    }

    .auth-screen-orb-left {
        width: min(34vw, 360px);
        height: min(34vw, 360px);
        left: -8vw;
        top: 13vh;
        background: radial-gradient(circle at 30% 30%, #ff76c1 0%, #ff62ad 38%, #ffca67 100%);
        box-shadow: 0 24px 80px rgba(255, 118, 193, 0.25);
    }

    .auth-screen-orb-right {
        width: min(20vw, 220px);
        height: min(20vw, 220px);
        right: 8vw;
        top: 9vh;
        background: radial-gradient(circle at 35% 35%, #ffd96d 0%, #ff8dc4 52%, #f05cc1 100%);
        box-shadow: 0 18px 55px rgba(255, 141, 196, 0.22);
    }

    .auth-screen-orb-bottom {
        width: min(28vw, 320px);
        height: min(28vw, 320px);
        left: 12vw;
        bottom: -10vh;
        background: radial-gradient(circle at 38% 35%, #a7e8ff 0%, #f4b6de 56%, #ffd789 100%);
        box-shadow: 0 22px 70px rgba(255, 182, 222, 0.22);
    }

    .auth-screen-orb-top {
        width: min(12vw, 150px);
        height: min(12vw, 150px);
        left: 48%;
        top: 5vh;
        transform: translateX(-50%);
        background: radial-gradient(circle at 35% 35%, #ffffff 0%, #ffdde9 52%, #ffa7d3 100%);
        box-shadow: 0 10px 30px rgba(255, 221, 233, 0.24);
    }

    .auth-screen-card {
        position: relative;
        z-index: 1;
        width: min(430px, calc(100vw - 28px));
        max-width: 100%;
        border-radius: 32px;
        padding: 24px;
        box-sizing: border-box;
        background: rgba(255, 255, 255, 0.18);
        border: 1px solid rgba(255, 255, 255, 0.32);
        box-shadow: 0 30px 80px rgba(255, 157, 196, 0.18);
        backdrop-filter: blur(22px);
        -webkit-backdrop-filter: blur(22px);
    }

    .auth-screen-logo {
        display: flex;
        justify-content: center;
        margin-bottom: 20px;
    }

    .auth-screen-logo svg {
        width: 82px;
        height: 82px;
        color: rgba(255, 255, 255, 0.96);
        filter: drop-shadow(0 10px 18px rgba(255, 117, 181, 0.18));
    }

    .auth-screen-logo img.login-logo-image {
        width: 64px;
        height: 64px;
        border-radius: 18px;
        object-fit: cover;
        box-shadow: 0 8px 16px rgba(124, 77, 255, 0.18);
        display: block;
    }

    .auth-screen-panel {
        border-radius: 30px;
        padding: 26px 24px 22px;
        box-sizing: border-box;
        background: rgba(255, 255, 255, 0.22);
        border: 1px solid rgba(255, 255, 255, 0.34);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.35);
    }

    .auth-screen-panel form {
        margin: 0;
    }

    .auth-screen-panel label {
        color: rgba(39, 67, 140, 0.9);
        font-weight: 800;
        letter-spacing: 0.01em;
    }

    .auth-screen-panel input[type='email'],
    .auth-screen-panel input[type='password'],
    .auth-screen-panel input[type='text'],
    .auth-screen-panel input[type='date'],
    .auth-screen-panel select {
        display: block;
        width: 100%;
        max-width: 100%;
        min-height: 54px;
        border-radius: 999px;
        border: 0;
        background: rgba(255, 255, 255, 0.96);
        color: #1f2937;
        padding: 15px 18px;
        box-sizing: border-box;
        box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.78), 0 10px 24px rgba(16, 24, 40, 0.08);
    }

    .auth-screen-panel select {
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        background-image: linear-gradient(45deg, transparent 50%, #6b7280 50%), linear-gradient(135deg, #6b7280 50%, transparent 50%);
        background-position: calc(100% - 24px) calc(50% - 2px), calc(100% - 18px) calc(50% - 2px);
        background-size: 6px 6px, 6px 6px;
        background-repeat: no-repeat;
        padding-right: 44px;
    }

    .auth-screen-panel .password-toggle-wrap {
        position: relative;
    }

    .auth-screen-panel .password-toggle-wrap input[type='password'] {
        padding-right: 46px;
    }

    .auth-screen-panel input:focus {
        outline: none;
        box-shadow: 0 0 0 4px rgba(255, 174, 216, 0.26), 0 10px 24px rgba(16, 24, 40, 0.08);
    }

    .auth-screen-panel input[type='checkbox'] {
        width: 18px;
        height: 18px;
        border-radius: 4px;
        border: 1px solid rgba(255, 182, 216, 0.8);
        background: rgba(255, 255, 255, 0.9);
    }

    .auth-screen-panel .text-gray-600,
    .auth-screen-panel .text-gray-700,
    .auth-screen-panel .text-sm {
        color: rgba(57, 71, 122, 0.8) !important;
    }

    .auth-screen-panel a {
        color: #203a89;
        text-decoration: none;
        font-weight: 700;
    }

    .auth-screen-panel a:hover {
        text-decoration: underline;
    }

    .auth-screen-panel button[type='submit'] {
        border: 0;
        border-radius: 999px;
        padding: 14px 24px;
        background: linear-gradient(90deg, #f7df64 0%, #ff9aa7 45%, #d0a5ff 100%);
        color: #203a89;
        font-family: Tahoma, sans-serif;
        font-size: 13px;
        font-weight: 700;
        letter-spacing: 0.12em;
        box-shadow: 0 14px 30px rgba(255, 154, 167, 0.34);
    }

    .auth-screen-panel button[type='submit']:hover {
        filter: brightness(1.02);
        transform: translateY(-1px);
    }

    .auth-screen-panel .flex.items-center.justify-end.mt-4 {
        gap: 14px;
    }

    .auth-screen-panel .mt-4.text-sm.text-gray-600,
    .auth-screen-panel .mt-4.text-sm.text-gray-600 * {
        color: rgba(57, 71, 122, 0.86) !important;
    }

    .auth-screen-panel .mb-4 {
        margin-bottom: 1rem;
    }

    @media (max-width: 640px) {
        .auth-screen-shell {
            padding: 18px 12px;
        }

        .auth-screen-card {
            width: calc(100vw - 20px);
            padding: 18px;
            border-radius: 24px;
        }

        .auth-screen-panel {
            padding: 20px 16px 18px;
            border-radius: 20px;
        }

        .auth-screen-orb-left {
            left: -22vw;
            top: 8vh;
        }

        .auth-screen-orb-right {
            right: -8vw;
            top: 4vh;
        }

        .auth-screen-orb-bottom {
            left: -8vw;
            bottom: -10vh;
        }

        .auth-screen-orb-top {
            top: 3vh;
        }
    }
</style>