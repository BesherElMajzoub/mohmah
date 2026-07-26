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
 * Scrolling collapses the utility strip and shrinks the mark, so the sticky
 * bar stays compact while reading.
 *
 * Two thresholds rather than one, and this is the whole point of the code
 * below: collapsing the strip removes about 56px from a header that sits in
 * the document flow, so everything under it moves up. With a single
 * threshold the page could land back above it, expand, push the content
 * down, cross it again — and the bar visibly shook on slow scrolls near the
 * top. The gap between collapse and expand is wider than the height the
 * header loses, which makes that loop impossible.
 *
 * Passive listener, and the read is deferred to a frame so a fast scroll
 * cannot queue a layout read per event.
 */
const header = document.querySelector('[data-site-header]');

if (header) {
    const COLLAPSE_AT = 96;
    const EXPAND_AT = 24;

    let collapsed = null;
    let queued = false;

    const applyScrollState = () => {
        queued = false;

        const y = window.scrollY;
        const next = collapsed ? y > EXPAND_AT : y > COLLAPSE_AT;

        if (next === collapsed) {
            return;
        }

        collapsed = next;
        header.classList.toggle('is-scrolled', next);
    };

    const queueScrollState = () => {
        if (queued) {
            return;
        }

        queued = true;
        requestAnimationFrame(applyScrollState);
    };

    applyScrollState();
    window.addEventListener('scroll', queueScrollState, { passive: true });
}
