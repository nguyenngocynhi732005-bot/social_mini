<?php

namespace App\ProfilePersonalization\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class InjectAvatarProfileRedirect
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (!method_exists($response, 'getContent')) {
            return $response;
        }

        $contentType = (string) $response->headers->get('Content-Type', '');
        if (stripos($contentType, 'text/html') === false) {
            return $response;
        }

        $content = (string) $response->getContent();
        if ($content === '' || stripos($content, '</body>') === false) {
            return $response;
        }

        if (strpos($content, 'pp-avatar-profile-redirect') !== false) {
            return $response;
        }

        $profileUrl = json_encode(url('/profile'));

        $script = "\n<script id=\"pp-avatar-profile-redirect\">\n(function () {\n  var profileUrl = " . $profileUrl . ";\n\n  var selectors = {\n    composerAvatarImg: '#ajax-content .card.mb-3.shadow-sm .card-body > .d-flex.align-items-center.gap-2.mb-2 > img.rounded-circle'\n  };\n\n  function markFocusable(el) {\n    if (!el) {\n      return;\n    }\n\n    if (!el.hasAttribute('tabindex')) {\n      el.setAttribute('tabindex', '0');\n    }\n\n    if (!el.hasAttribute('role')) {\n      el.setAttribute('role', 'link');\n    }\n\n    el.style.cursor = 'pointer';\n  }\n\n  function refreshA11yHints() {\n    var composerAvatarImg = document.querySelector(selectors.composerAvatarImg);\n    if (composerAvatarImg) {\n      markFocusable(composerAvatarImg);\n    }\n  }\n\n  function shouldGoProfile(target) {\n    if (!target || !target.closest) {\n      return false;\n    }\n\n    var composerAvatar = target.closest(selectors.composerAvatarImg);\n    if (composerAvatar) {\n      return true;\n    }\n\n    return false;\n  }\n\n  function goProfile() {\n    if (window.location.pathname !== '/profile') {\n      window.location.href = profileUrl;\n    }\n  }\n\n  document.addEventListener('click', function (event) {\n    if (!shouldGoProfile(event.target)) {\n      return;\n    }\n\n    event.preventDefault();\n    goProfile();\n  }, true);\n\n  document.addEventListener('keydown', function (event) {\n    if (event.key !== 'Enter' && event.key !== ' ') {\n      return;\n    }\n\n    if (!shouldGoProfile(event.target)) {\n      return;\n    }\n\n    event.preventDefault();\n    goProfile();\n  }, true);\n\n  if (document.readyState === 'loading') {\n    document.addEventListener('DOMContentLoaded', refreshA11yHints);\n  } else {\n    refreshA11yHints();\n  }\n\n  var observer = new MutationObserver(refreshA11yHints);\n  observer.observe(document.documentElement, { childList: true, subtree: true });\n})();\n</script>\n";

        $response->setContent(str_ireplace('</body>', $script . '</body>', $content));

        return $response;
    }
}
