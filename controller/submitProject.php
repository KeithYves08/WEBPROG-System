<?php
// Handles project creation from creation.php via JSON POST
require_once __DIR__ . '/config.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	http_response_code(405);
	echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
	exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) {
	http_response_code(400);
	echo json_encode(['status' => 'error', 'message' => 'Invalid JSON']);
	exit;
}

// Extract and validate required fields
$project = $data['project'] ?? [];
$academe = $data['academe'] ?? [];
$agreements = $data['agreements'] ?? [];
$deliverables = $data['deliverables'] ?? [];

$required = [
	$project['project_title'] ?? '',
	$project['project_description'] ?? '',
	$project['project_type'] ?? '',
	$project['start_date'] ?? '',
	$project['end_date'] ?? '',
	$academe['department_program'] ?? '',
	$academe['faculty_coordinator'] ?? '',
	$academe['contact_number'] ?? '',
	$academe['contact_email'] ?? '',
	$agreements['funding_source'] ?? '',
];

foreach ($required as $v) {
	if (!is_string($v) || trim($v) === '') {
		echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
		exit;
	}
}

// Optional: industry partner id from payload
$industryPartnerId = null;
if (isset($data['industry_partner_id']) && is_numeric($data['industry_partner_id'])) {
	$industryPartnerId = (int)$data['industry_partner_id'];
}

try {
	$conn->beginTransaction();

	// Insert into agreements
	$stmt = $conn->prepare("INSERT INTO agreements (funding_source, budget_amount, document_path, contract_type) VALUES (:fs, :budget, NULL, NULL)");
	$budget = isset($agreements['budget']) && $agreements['budget'] !== '' ? (float)$agreements['budget'] : null;
	$stmt->execute([
		':fs' => $agreements['funding_source'],
		':budget' => $budget,
	]);
	$agreementId = (int)$conn->lastInsertId();

	// Insert academe_information
	$stmt = $conn->prepare("INSERT INTO academe_information (department_program, faculty_coordinator, contact_number, email_academe, students_involved, unit_attach_document) VALUES (:dept, :coord, :phone, :email, NULL, NULL)");
	$stmt->execute([
		':dept' => $academe['department_program'],
		':coord' => $academe['faculty_coordinator'],
		':phone' => $academe['contact_number'],
		':email' => $academe['contact_email'],
	]);
	$academeId = (int)$conn->lastInsertId();

	// Insert deliverables as plain text (no JSON brackets)
	$toPlain = function($val){
		if (is_array($val)) {
			// Trim, remove empties, and join with newlines for readability
			$vals = array_filter(array_map(function($s){ return trim((string)$s); }, $val), function($s){ return $s !== ''; });
			return implode("\n", $vals);
		}
		return trim((string)($val ?? ''));
	};
	$exp = $toPlain($deliverables['expected_outputs'] ?? []);
	$kpi = $toPlain($deliverables['kpis'] ?? []);
	$obj = $toPlain($deliverables['objectives'] ?? []);
	$stmt = $conn->prepare("INSERT INTO deliverables (expected_outputs, kpi_success_metrics, objectives) VALUES (:exp, :kpi, :obj)");
	$stmt->execute([
		':exp' => $exp,
		':kpi' => $kpi,
		':obj' => $obj,
	]);
	$deliverableId = (int)$conn->lastInsertId();

	// Insert project
	$stmt = $conn->prepare("INSERT INTO projects (title, description, project_type, start_date, end_date, agreement_id, academe_id, industry_partner_id, deliverable_id) VALUES (:title, :desc, :ptype, :sd, :ed, :aid, :acad, :ip, :deliv)");
	$stmt->execute([
		':title' => $project['project_title'],
		':desc' => $project['project_description'],
		':ptype' => $project['project_type'],
		':sd' => $project['start_date'],
		':ed' => $project['end_date'],
		':aid' => $agreementId,
		':acad' => $academeId,
		':ip' => $industryPartnerId,
		':deliv' => $deliverableId,
	]);
	$projectId = (int)$conn->lastInsertId();

	$conn->commit();

	echo json_encode(['status' => 'ok', 'project_id' => $projectId]);
} catch (Throwable $e) {
	if ($conn->inTransaction()) { $conn->rollBack(); }
	error_log('submitProject failed: ' . $e->getMessage());
	http_response_code(500);
	echo json_encode(['status' => 'error', 'message' => 'Server error while creating project']);
}
?>
