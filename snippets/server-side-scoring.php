<?php
/**
 * Server-side lead scoring for the Mohitoo screening form.
 * Scores are always computed on the server — never trusted from the client.
 * Sanitized excerpt: internal configuration and pipeline details removed.
 */

function mohitoo_auto_score(string $seriousness, string $budget, string $why_now): int
{
    $score = 0;

    // Seriousness: ready = 3, thinking = 1, browsing = 0
    if ($seriousness === 'ready') {
        $score += 3;
    } elseif ($seriousness === 'thinking') {
        $score += 1;
    }

    // Budget range: a signal of commitment — not a price field
    if ($budget === 'over10') {
        $score += 3;
    } elseif ($budget === '5to10') {
        $score += 2;
    } elseif ($budget === 'under5') {
        $score += 1;
    }

    // "Why now" quality: specific vs. generic vs. empty (UTF-8 aware)
    $why = trim($why_now);
    $len = mb_strlen($why, 'UTF-8');
    $words = preg_split('/\s+/u', $why, -1, PREG_SPLIT_NO_EMPTY);
    $wordCount = is_array($words) ? count($words) : 0;

    if ($len >= 80 && $wordCount >= 15) {
        $score += 2; // specific
    } elseif ($len >= 25 && $wordCount >= 5) {
        $score += 1; // generic
    }                // below this = empty = 0

    return $score;
}

/** Traffic-light badge derived from the score. */
function mohitoo_badge(int $score): string
{
    if ($score >= 6) {
        return 'green';
    }
    if ($score >= 3) {
        return 'yellow';
    }
    return 'red';
}
