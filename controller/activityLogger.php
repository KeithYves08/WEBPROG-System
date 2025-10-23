<?php
/**
 * Activity Logging Helper
 * Provides log_activity($conn, $opts) to write to activity_log safely.
 */

if (!function_exists('log_activity')) {
    function log_activity(PDO $conn, array $opts): bool {
        try {
            // Ensure table exists (best-effort, will do nothing if schema already exists differently)
            // We won't force a specific schema to avoid conflicts with existing installs.
            try {
                $conn->exec("CREATE TABLE IF NOT EXISTS `activity_log` (
                    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `user_id` INT UNSIGNED NULL,
                    `username` VARCHAR(100) NULL,
                    `action` VARCHAR(64) NOT NULL,
                    `entity_type` VARCHAR(64) NULL,
                    `entity_id` BIGINT UNSIGNED NULL,
                    `details` JSON NULL,
                    `ip` VARCHAR(45) NULL,
                    `user_agent` VARCHAR(255) NULL,
                    INDEX `idx_created_at` (`created_at`),
                    INDEX `idx_user` (`user_id`),
                    INDEX `idx_entity` (`entity_type`, `entity_id`),
                    INDEX `idx_action` (`action`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            } catch (Throwable $e) {
                // ignore if already created with another compatible schema
            }

            // Pull session user if available
            if (session_status() === PHP_SESSION_NONE) {
                @session_start();
            }
            $userId = $opts['user_id'] ?? ($_SESSION['admin_id'] ?? null);
            $username = $opts['username'] ?? ($_SESSION['admin_username'] ?? null);

            $action = (string)($opts['action'] ?? 'unknown');
            $entityType = $opts['entity_type'] ?? null;
            $entityId = isset($opts['entity_id']) ? (int)$opts['entity_id'] : null;

            // Build meta/details payload, include description if no dedicated column exists
            $meta = $opts['meta'] ?? ($opts['details'] ?? null);
            $descOpt = isset($opts['description']) ? (string)$opts['description'] : null;
            if (is_array($meta) || is_object($meta)) {
                $metaArr = (array)$meta;
            } elseif (!is_null($meta)) {
                $metaArr = ['details' => (string)$meta];
            } else {
                $metaArr = [];
            }
            // We will decide at runtime whether to place description in a column or inside JSON

            $ipAddr = $_SERVER['REMOTE_ADDR'] ?? null;
            $ua = $_SERVER['HTTP_USER_AGENT'] ?? null;

            // Inspect existing columns to select compatible insert
            static $cols = null;
            if ($cols === null) {
                $cols = [];
                try {
                    $rs = $conn->query("SHOW COLUMNS FROM `activity_log`");
                    foreach ($rs->fetchAll(PDO::FETCH_ASSOC) as $row) {
                        $cols[strtolower($row['Field'])] = true;
                    }
                } catch (Throwable $e) {}
            }

            $has = function($name) use ($cols) { return isset($cols[strtolower($name)]); };
            $insertCols = ['`user_id`','`username`','`action`'];
            $params = [
                ':uid' => $userId,
                ':uname' => $username,
                ':action' => $action,
            ];
            if ($entityType !== null && $has('entity_type')) { $insertCols[]='`entity_type`'; $params[':etype']=$entityType; }
            if ($entityId !== null && $has('entity_id')) { $insertCols[]='`entity_id`'; $params[':eid']=$entityId; }
            // Prefer real description column if it exists; otherwise embed inside details JSON
            if ($descOpt !== null && $has('description')) {
                $insertCols[]='`description`';
                $params[':desc'] = $descOpt;
            } else if ($descOpt !== null) {
                $metaArr['description'] = $descOpt;
            }
            if ($has('ip_address')) { $insertCols[]='`ip_address`'; $params[':ip']=$ipAddr; }
            elseif ($has('ip')) { $insertCols[]='`ip`'; $params[':ip']=$ipAddr; }
            if ($has('user_agent')) { $insertCols[]='`user_agent`'; $params[':ua']=$ua; }

            // details payload if column exists
            if ($has('details')) {
                $detailsStr = !empty($metaArr) ? json_encode($metaArr, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null;
                $insertCols[]='`details`';
                $params[':details'] = $detailsStr;
            }

            $placeholders = [];
            foreach ($insertCols as $c) {
                $key = trim($c, '`');
                $ph = ':' . str_replace('-', '_', $key);
                // We used specific placeholder names above; map them properly
                // Build mapping from column to placeholder
            }
            // Build final SQL with the placeholders in the same order as $insertCols
            $phOrder = [];
            foreach ($insertCols as $c) {
                $col = trim($c,'`');
                if ($col === 'user_id') $phOrder[]=':uid';
                elseif ($col === 'username') $phOrder[]=':uname';
                elseif ($col === 'action') $phOrder[]=':action';
                elseif ($col === 'entity_type') $phOrder[]=':etype';
                elseif ($col === 'entity_id') $phOrder[]=':eid';
                elseif ($col === 'description') $phOrder[]=':desc';
                elseif ($col === 'details') $phOrder[]=':details';
                elseif ($col === 'ip_address' || $col === 'ip') $phOrder[]=':ip';
                elseif ($col === 'user_agent') $phOrder[]=':ua';
            }

            $sql = 'INSERT INTO `activity_log` (' . implode(',', $insertCols) . ') VALUES (' . implode(',', $phOrder) . ')';
            $stmt = $conn->prepare($sql);
            return $stmt->execute($params);
        } catch (Throwable $e) {
            error_log('log_activity failed: ' . $e->getMessage());
            return false;
        }
    }
}

?>
