<?php
// Returns companies that have at least one non-terminated partnership.
// Response shape: { status: 'ok', companies: [{ id, name }, ...] }

require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

try {
	// Select distinct companies with active/non-terminated partnerships
	$sql = "
		SELECT DISTINCT c.id, c.name
		FROM partnerships p
		INNER JOIN companies c ON c.id = p.company_id
		WHERE (
			(p.status IS NULL OR LOWER(p.status) <> 'terminated')
			AND (p.agreement_end_date IS NULL OR p.agreement_end_date >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR))
		)
		ORDER BY c.name ASC
	";
	$stmt = $conn->prepare($sql);
	$stmt->execute();
	$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

	// Normalize output
	$companies = array_map(function($r){
		return [
			'id' => (int)$r['id'],
			'name' => $r['name']
		];
	}, $rows ?: []);

	echo json_encode(['status' => 'ok', 'companies' => $companies]);
} catch (Throwable $e) {
	error_log('partnersList error: ' . $e->getMessage());
	http_response_code(500);
	echo json_encode(['status' => 'error', 'message' => 'Unable to load partners']);
}
?>
