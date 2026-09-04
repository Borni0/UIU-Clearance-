<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

function notify(int $user_id, string $kind, string $title, ?string $body = null, ?string $link = null): void
{
    $allowed = ['request_submitted', 'status_update', 'admin_remark', 'approval', 'rejection', 'emergency'];
    if (!in_array($kind, $allowed, true)) {
        return;
    }
    $stmt = db()->prepare(
        'INSERT INTO notifications (user_id, kind, title, body, link)
         VALUES (?, ?, ?, ?, ?)'
    );
    $stmt->execute([$user_id, $kind, $title, $body, $link]);
}

function log_activity(?int $actor_id, string $action, ?string $target_type = null, ?int $target_id = null, ?string $detail = null): void
{
    $stmt = db()->prepare(
        'INSERT INTO activity_log (actor_id, action, target_type, target_id, detail)
         VALUES (?, ?, ?, ?, ?)'
    );
    $stmt->execute([$actor_id, $action, $target_type, $target_id, $detail]);
}
