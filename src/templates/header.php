<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= $pageDescription ?? 'Data-driven infographics' ?>">

    <title><?= $pageTitle ?? 'Infographics' ?></title>

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="<?= isset($post) ? 'article' : 'website' ?>">
    <meta property="og:url" content="https://<?= $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ?>">
    <meta property="og:title" content="<?= $pageTitle ?? 'Infographics' ?>">
    <meta property="og:description" content="<?= $pageDescription ?? 'Data-driven infographics' ?>">
    <?php if (isset($ogImage)): ?>
        <meta property="og:image" content="<?= $ogImage ?>">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="1200">
    <?php endif; ?>

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= $pageTitle ?? 'Infographics' ?>">
    <meta name="twitter:description" content="<?= $pageDescription ?? 'Data-driven infographics' ?>">
    <?php if (isset($ogImage)): ?>
        <meta name="twitter:image" content="<?= $ogImage ?>">
    <?php endif; ?>

    <!-- Canonical URL -->
    <link rel="canonical" href="https://<?= $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ?>">

    <!-- Favicon -->
    <link rel="icon" href="/assets/img/favicon.ico">

    <!-- Main CSS -->
    <link rel="stylesheet" href="/assets/css/main.css">

    <!-- Preload critical assets -->
    <link rel="preload" href="/assets/css/main.css" as="style">

    <!-- Google Analytics (with Consent Mode V2) -->
    <script>
        // Default consent state (denied until user accepts)
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}

        gtag('consent', 'default', {
            'analytics_storage': 'denied',
            'ad_storage': 'denied',
            'ad_user_data': 'denied',
            'ad_personalization': 'denied',
            'wait_for_update': 500
        });

        // Google Analytics - replace GA_MEASUREMENT_ID with your actual ID
        gtag('js', new Date());
        gtag('config', 'GA_MEASUREMENT_ID', {
            'anonymize_ip': true,
            'cookie_flags': 'SameSite=None;Secure'
        });
    </script>
    <script async src="https://www.googletagmanager.com/gtag/js?id=GA_MEASUREMENT_ID"></script>
</head>
<body>
    <nav class="main-nav">
        <div class="container">
            <a href="/" class="logo">EconVisuals</a>

            <ul class="nav-menu">
                <li><a href="/">Home</a></li>
                <?php
                // Load categories for menu
                $categoryModel = new App\Category();
                $navCategories = $categoryModel->getAll();
                foreach (array_slice($navCategories, 0, 5) as $cat):
                ?>
                    <li><a href="/category/<?= htmlspecialchars($cat['slug']) ?>">
                        <?= htmlspecialchars($cat['name']) ?>
                    </a></li>
                <?php endforeach; ?>
            </ul>

            <!-- Search Bar -->
            <div class="search-container">
                <form action="/search" method="GET" class="search-form">
                    <input type="search"
                           name="q"
                           placeholder="Search infographics..."
                           aria-label="Search"
                           value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                    <button type="submit" aria-label="Search">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="m21 21-4.35-4.35"></path>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <!-- GDPR Cookie Consent Banner -->
    <div id="cookie-consent" class="cookie-consent" style="display: none;">
        <div class="cookie-content">
            <div class="cookie-text">
                <h3>🍪 Cookie Consent</h3>
                <p>We use cookies to analyze our traffic and improve your experience. We respect your privacy and comply with GDPR, CCPA, and other global privacy laws.</p>
                <p class="cookie-details">
                    <strong>Analytics cookies:</strong> Help us understand how visitors interact with our website.
                    <a href="#" id="cookie-learn-more">Learn more</a>
                </p>
            </div>
            <div class="cookie-buttons">
                <button id="cookie-accept" class="cookie-btn cookie-btn-accept">Accept All</button>
                <button id="cookie-necessary" class="cookie-btn cookie-btn-necessary">Necessary Only</button>
                <button id="cookie-decline" class="cookie-btn cookie-btn-decline">Decline All</button>
            </div>
        </div>
    </div>

    <!-- Cookie Consent Script -->
    <script>
        (function() {
            const COOKIE_NAME = 'econvisuals_cookie_consent';
            const COOKIE_DURATION = 365; // days

            function setCookie(name, value, days) {
                const date = new Date();
                date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                document.cookie = name + '=' + value + ';expires=' + date.toUTCString() + ';path=/;SameSite=Strict;Secure';
            }

            function getCookie(name) {
                const nameEQ = name + '=';
                const ca = document.cookie.split(';');
                for(let i = 0; i < ca.length; i++) {
                    let c = ca[i];
                    while (c.charAt(0) == ' ') c = c.substring(1, c.length);
                    if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
                }
                return null;
            }

            function updateConsent(analyticsAllowed) {
                if (typeof gtag !== 'function') return;

                gtag('consent', 'update', {
                    'analytics_storage': analyticsAllowed ? 'granted' : 'denied',
                    'ad_storage': 'denied', // We don't use ads
                    'ad_user_data': 'denied',
                    'ad_personalization': 'denied'
                });
            }

            function hideConsentBanner() {
                document.getElementById('cookie-consent').style.display = 'none';
            }

            function showConsentBanner() {
                document.getElementById('cookie-consent').style.display = 'block';
            }

            // Check existing consent
            const consent = getCookie(COOKIE_NAME);

            if (consent === 'accepted') {
                updateConsent(true);
            } else if (consent === 'declined' || consent === 'necessary') {
                updateConsent(false);
            } else {
                // No consent yet - show banner
                showConsentBanner();
            }

            // Accept all cookies
            document.getElementById('cookie-accept')?.addEventListener('click', function() {
                setCookie(COOKIE_NAME, 'accepted', COOKIE_DURATION);
                updateConsent(true);
                hideConsentBanner();
            });

            // Necessary only (same as decline for analytics)
            document.getElementById('cookie-necessary')?.addEventListener('click', function() {
                setCookie(COOKIE_NAME, 'necessary', COOKIE_DURATION);
                updateConsent(false);
                hideConsentBanner();
            });

            // Decline all
            document.getElementById('cookie-decline')?.addEventListener('click', function() {
                setCookie(COOKIE_NAME, 'declined', COOKIE_DURATION);
                updateConsent(false);
                hideConsentBanner();
            });

            // Learn more
            document.getElementById('cookie-learn-more')?.addEventListener('click', function(e) {
                e.preventDefault();
                alert('We only use Google Analytics to understand visitor behavior. No personal data is collected or sold. You can change your preferences at any time by clearing your cookies.');
            });
        })();
    </script>
</body>
</html>
