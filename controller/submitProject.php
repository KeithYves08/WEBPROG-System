<?php
// Handles project creation from creation.php via JSON POST
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/activityLogger.php';
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

// Server-side sanity check for date range
try {
	$sd = new DateTime($project['start_date']);
	$ed = new DateTime($project['end_date']);
	if ($ed <= $sd) {
		http_response_code(400);
		echo json_encode(['status' => 'error', 'message' => 'End date must be after start date']);
		exit;
	}
} catch (Throwable $e) {
	http_response_code(400);
	echo json_encode(['status' => 'error', 'message' => 'Invalid date format']);
	exit;
}

// Ensure core tables exist (non-destructive; uses IF NOT EXISTS)
function ensureCoreTables(PDO $conn) {
	$ddl = [];
	$ddl[] = "CREATE TABLE IF NOT EXISTS agreements (\n        id INT AUTO_INCREMENT PRIMARY KEY,\n        funding_source VARCHAR(100),\n        budget_amount DECIMAL(12,2),\n        document_path VARCHAR(255),\n        contract_type VARCHAR(50)\n    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
	$ddl[] = "CREATE TABLE IF NOT EXISTS academe_information (\n        id INT AUTO_INCREMENT PRIMARY KEY,\n        department_program VARCHAR(255),\n        faculty_coordinator VARCHAR(100),\n        contact_number VARCHAR(50),\n        email_academe VARCHAR(100),\n        students_involved INT,\n        unit_attach_document VARCHAR(255)\n    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
	$ddl[] = "CREATE TABLE IF NOT EXISTS deliverables (\n        id INT AUTO_INCREMENT PRIMARY KEY,\n        expected_outputs TEXT,\n        kpi_success_metrics TEXT,\n        objectives TEXT\n    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
	$ddl[] = "CREATE TABLE IF NOT EXISTS projects (\n        id INT AUTO_INCREMENT PRIMARY KEY,\n        title VARCHAR(255) NOT NULL,\n        description TEXT,\n        project_type VARCHAR(50),\n        start_date DATE,\n        end_date DATE,\n        agreement_id INT,\n        academe_id INT,\n        industry_partner_id INT,\n        deliverable_id INT,\n        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n        FOREIGN KEY (agreement_id) REFERENCES agreements(id),\n        FOREIGN KEY (academe_id) REFERENCES academe_information(id),\n        FOREIGN KEY (industry_partner_id) REFERENCES companies(id),\n        FOREIGN KEY (deliverable_id) REFERENCES deliverables(id)\n    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
	foreach ($ddl as $sql) {
		$conn->exec($sql);
	}
}

try {
	// Ensure tables exist so inserts won't fail on missing schema
	ensureCoreTables($conn);

	// Upgrade schema non-destructively if older table is missing expected columns
	$ensureAcademeColumns = function(PDO $conn) {
		$cols = [];
		$stmt = $conn->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'academe_information'");
		foreach ($stmt->fetchAll(PDO::FETCH_COLUMN, 0) as $c) { $cols[$c] = true; }
		if (!isset($cols['students_involved'])) {
			$conn->exec("ALTER TABLE academe_information ADD COLUMN students_involved INT NULL");
		}
		if (!isset($cols['unit_attach_document'])) {
			$conn->exec("ALTER TABLE academe_information ADD COLUMN unit_attach_document VARCHAR(255) NULL");
		}
	};
	$ensureAcademeColumns($conn);
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
		':ip' => ($industryPartnerId !== null ? $industryPartnerId : null),
		':deliv' => $deliverableId,
	]);
	$projectId = (int)$conn->lastInsertId();

	$conn->commit();

	// Log project creation (best-effort)
	try {
		log_activity($conn, [
			'action' => 'project_create',
			'entity_type' => 'project',
			'entity_id' => $projectId,
			'description' => 'Created project: ' . ($project['project_title'] ?? ('ID ' . $projectId)),
			'meta' => [
				'project_type' => $project['project_type'] ?? null,
				'start_date' => $project['start_date'] ?? null,
				'end_date' => $project['end_date'] ?? null,
				'industry_partner_id' => $industryPartnerId,
			],
		]);
	} catch (Throwable $e) { /* no-op */ }

	echo json_encode(['status' => 'ok', 'project_id' => $projectId]);
} catch (Throwable $e) {
	if ($conn->inTransaction()) { $conn->rollBack(); }
	error_log('submitProject failed: ' . $e->getMessage());
	// Surface the underlying message to aid troubleshooting in this environment
	http_response_code(500);
	echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
