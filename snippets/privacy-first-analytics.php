<?php
/**
 * Privacy-first, first-party analytics patterns.
 * Principles: no cookies, no PII, no raw IPs. Session keys rotate daily
 * and cannot be replayed across days. All events stay on first-party storage.
 * Sanitized excerpt: internal configuration details removed.
 */

/** Server-side Do-Not-Track respect; honoring DNT must never break the user's funnel. */
function mohitoo_dnt_enabled(): bool
{
    $dnt = isset($_SERVER['HTTP_DNT'])
        ? mb_strtolower(trim((string) $_SERVER['HTTP_DNT']), 'UTF-8')
        : '';
    return $dnt === '1' || $dnt === 'yes';
}

/**
 * Anonymous daily session key: no cookies, no cross-day traceability.
 * Built from a pre-hashed IP (raw IPs are never stored), the user agent,
 * and the UTC date — so the key rotates automatically every day.
 */
function mohitoo_session_key(string $ipHash, string $userAgent, string $appSecret): string
{
    $seed = $ipHash . '|' . $userAgent . '|' . gmdate('Ymd');
    return hash_hmac('sha256', 'evt-session|' . $seed, $appSecret);
}

/** Raw journey id is validated, then only its HMAC is persisted — never the raw value. */
function mohitoo_journey_key(string $raw, string $appSecret): string
{
    $raw = trim($raw);
    if (!preg_match('/^[A-Za-z0-9_-]{20,80}$/', $raw)) {
        return '';
    }
    return hash_hmac('sha256', 'evt-journey|' . $raw, $appSecret);
}

/** Only these event types may ever be recorded from the client side. */
function mohitoo_event_client_allowed(): array
{
    return array('page_view', 'cta_click', 'apply_start');
}
