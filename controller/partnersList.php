<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';
checkLogin();

header('Content-Type: application/json');

try {
    // Return companies that have at least one non-terminated partnership
    $stmt = $conn->prepare(
        "SELECT DISTINCT c.id, c.name
         FROM companies c
         INNER JOIN partnerships p ON p.company_id = c.id
         WHERE COALESCE(p.status, '') != 'terminated'
         ORDER BY c.name ASC"
    );
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['status' => 'ok', 'companies' => $rows]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
}

exit;
