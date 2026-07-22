import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

// Alpine drives the mega menu, the mobile accordion and the FAQ disclosures —
// small amounts of state that would otherwise need hand-rolled listeners on
// every page. Collapse gives the accordions a height transition that respects
// prefers-reduced-motion.
Alpine.plugin(collapse);
window.Alpine = Alpine;
Alpine.start();

/**
 * Conversion tracking.
 *
 * Two independent sinks, both fire-and-forget:
 *   1. Our own /t/click endpoint, via sendBeacon.
 *   2. GA4 / Google Ads, when a measurement ID is configured.
 *
 * Nothing here calls preventDefault. The call and WhatsApp anchors are real
 * links; this module observes them. If the script fails to load, is blocked
 * by an extension, or the endpoint is unreachable, the visitor still reaches
 * the phone — tracking must never sit between someone and the office.
 */

const TRACK_ENDPOINT = '/t/click';

/**
 * Where the click happened, so the office can see which pages convert.
 * page_type is set by the layout; it falls back to null when absent.
 */
function pageContext() {
    return {
        page_path: window.location.pathname,
        page_type: document.body?.dataset.pageType ?? null,
    };
}

/**
 * Post to our own endpoint.
 *
 * sendBeacon is used rather than fetch because the browser is about to
 * navigate away to tel: or wa.me — a normal request would be cancelled
 * mid-flight. A beacon is queued by the browser and delivered regardless.
 */
function sendToServer(type, placement) {
    const payload = { ...pageContext(), type, placement };
    const body = new Blob([JSON.stringify(payload)], { type: 'application/json' });

    if (navigator.sendBeacon && navigator.sendBeacon(TRACK_ENDPOINT, body)) {
        return;
    }

    // Older browsers, or a beacon the UA refused to queue. keepalive gives
    // fetch the same survive-navigation behaviour.
    fetch(TRACK_ENDPOINT, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
        keepalive: true,
    }).catch(() => {
        // Swallowed deliberately: a failed analytics write must never surface
        // as a console error on a client-facing page.
    });
}

/**
 * Mirror the event into GA4 and, when configured, Google Ads.
 *
 * Both are no-ops when the site has no measurement ID, which is the default
 * until the client supplies real ones.
 */
function sendToAnalytics(type, placement) {
    if (typeof window.gtag !== 'function') {
        return;
    }

    const eventNames = {
        call: 'click_call',
        whatsapp: 'click_whatsapp',
        contact_form: 'submit_contact_form',
    };

    window.gtag('event', eventNames[type] ?? type, {
        placement,
        page_path: window.location.pathname,
    });

    const conversion = window.__adsConversions?.[type];

    if (conversion) {
        window.gtag('event', 'conversion', { send_to: conversion });
    }
}

/**
 * One delegated listener for the whole document.
 *
 * Delegation matters here because the sticky bar, header, hero and footer all
 * render conversion buttons, and anything rendered later stays covered
 * without re-binding.
 */
document.addEventListener('click', (event) => {
    const trigger = event.target.closest('[data-track]');

    if (!trigger) {
        return;
    }

    const type = trigger.dataset.track;
    const placement = trigger.dataset.placement || 'unknown';

    sendToServer(type, placement);
    sendToAnalytics(type, placement);
});

/**
 * Header treatment on scroll.
 *
 * The header starts transparent over the hero and gains a solid background
 * once the page scrolls, so the hero is never cropped by a bar. Passive
 * listener, class toggle only — no layout is read during the scroll.
 */
const header = document.querySelector('[data-site-header]');

if (header) {
    const applyScrollState = () => {
        header.classList.toggle('is-scrolled', window.scrollY > 24);
    };

    applyScrollState();
    window.addEventListener('scroll', applyScrollState, { passive: true });
}
