<?php
// SupplierMdb.php
header('Content-Type: application/json');

$host = "localhost";
$user = "root";
$password = "";
$config = "mis_coffe";

$conn = new mysqli($host, $user, $password, $config);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

// ----------------- HANDLE NEW SUPPLIER REGISTRATION -----------------
if (isset($_POST['action']) && $_POST['action'] === 'registerSupplier') {

    $company_name = trim($_POST['company_name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $contact_person = trim($_POST['contact_person'] ?? '');
    $position = trim($_POST['position'] ?? '');
    $contact_number = trim($_POST['contact_number'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $status = 'Active';
    $created_date = date('Y-m-d');
    $expiry_date = date('Y-m-d', strtotime('+1 year'));

    // Validate required fields
    if (!$company_name || !$address || !$contact_person || !$position || !$contact_number || !$email) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit;
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        exit;
    }

    // Validate contact number (ensure it's numeric)
    if (!is_numeric($contact_number)) {
        echo json_encode(['success' => false, 'message' => 'Contact number must be numeric']);
        exit;
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        // Generate Supplier ID
        $sqlLastSupplier = "SELECT supplier_id FROM tblsupplier ORDER BY supplier_id DESC LIMIT 1";
        $res = $conn->query($sqlLastSupplier);
        if ($res && $res->num_rows > 0) {
            $row = $res->fetch_assoc();
            $num = intval(substr($row['supplier_id'], 3)) + 1;
            $supplier_id = 'SLR' . str_pad($num, 4, '0', STR_PAD_LEFT);
        } else {
            $supplier_id = 'SLR0001';
        }

        // Generate Contract ID
        $sqlLastContract = "SELECT contract_id FROM tblcontract ORDER BY contract_id DESC LIMIT 1";
        $res = $conn->query($sqlLastContract);
        if ($res && $res->num_rows > 0) {
            $row = $res->fetch_assoc();
            $num = intval(substr($row['contract_id'], 3)) + 1;
            $contract_id = 'CNT' . str_pad($num, 4, '0', STR_PAD_LEFT);
        } else {
            $contract_id = 'CNT0001';
        }

        // First, insert into tblcontract
        $stmt = $conn->prepare("INSERT INTO tblcontract (contract_id, supplier_id, created_date, expiry_date, status) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception('Prepare failed for contract insert: ' . $conn->error);
        }
        $stmt->bind_param("sssss", $contract_id, $supplier_id, $created_date, $expiry_date, $status);
        if (!$stmt->execute()) {
            throw new Exception('Failed to insert contract: ' . $stmt->error);
        }
        $stmt->close();

        // Then insert into tblsupplier with contract_id
        // Note: Make sure your tblsupplier table has a contract_id column
        $stmt = $conn->prepare("INSERT INTO tblsupplier (supplier_id, company_name, contact_person, contact_number, position, address, email, contract_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception('Prepare failed for supplier insert: ' . $conn->error);
        }
        // Convert contact_number to integer as per your table structure
        $contact_number_int = intval($contact_number);
        $stmt->bind_param("sssissss", $supplier_id, $company_name, $contact_person, $contact_number_int, $position, $address, $email, $contract_id);
        if (!$stmt->execute()) {
            throw new Exception('Failed to insert supplier: ' . $stmt->error);
        }
        $stmt->close();

        // Commit transaction
        $conn->commit();

        echo json_encode([
            'success' => true, 
            'message' => 'Supplier registered successfully', 
            'supplier_id' => $supplier_id, 
            'contract_id' => $contract_id
        ]);

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        error_log("Supplier registration error: " . $e->getMessage()); // Log the error
        echo json_encode(['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()]);
    }
    exit;
}

// ----------------- UPDATE SUPPLIER CONTRACT -----------------
if (isset($_POST['action']) && $_POST['action'] === 'updateContract') {

    $supplier_id = $_POST['supplier_id'] ?? '';
    $status = $_POST['status'] ?? '';
    $expiry_date = $_POST['expiry_date'] ?? '';

    if (!$supplier_id || !$status) {
        echo json_encode(['success' => false, 'message' => 'Supplier ID and status are required']);
        exit;
    }

    // Get current contract_id from supplier
    $sql = "SELECT contract_id FROM tblsupplier WHERE supplier_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $supplier_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $contract_id = $row['contract_id'];
        
        // Update contract status and expiry date
        if ($expiry_date) {
            $updateSql = "UPDATE tblcontract SET status = ?, expiry_date = ? WHERE contract_id = ?";
            $stmt = $conn->prepare($updateSql);
            $stmt->bind_param("sss", $status, $expiry_date, $contract_id);
        } else {
            $updateSql = "UPDATE tblcontract SET status = ? WHERE contract_id = ?";
            $stmt = $conn->prepare($updateSql);
            $stmt->bind_param("ss", $status, $contract_id);
        }
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Contract updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update contract']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Supplier not found']);
    }
    $stmt->close();
    exit;
}

// ----------------- RENEW SUPPLIER CONTRACT -----------------
if (isset($_POST['action']) && $_POST['action'] === 'renewContract') {

    $supplier_id = $_POST['supplier_id'] ?? '';
    $new_expiry_date = $_POST['new_expiry_date'] ?? '';

    if (!$supplier_id || !$new_expiry_date) {
        echo json_encode(['success' => false, 'message' => 'Supplier ID and new expiry date are required']);
        exit;
    }

    // Get current contract_id from supplier
    $sql = "SELECT contract_id FROM tblsupplier WHERE supplier_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $supplier_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $contract_id = $row['contract_id'];
        
        // Update contract with new expiry date and set status to Active
        $updateSql = "UPDATE tblcontract SET status = 'Active', expiry_date = ? WHERE contract_id = ?";
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param("ss", $new_expiry_date, $contract_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Contract renewed successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to renew contract']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Supplier not found']);
    }
    $stmt->close();
    exit;
}

// ----------------- GET SUPPLIER CONTRACT DETAILS -----------------
if (isset($_POST['action']) && $_POST['action'] === 'getContractDetails') {

    $supplier_id = $_POST['supplier_id'] ?? '';

    if (!$supplier_id) {
        echo json_encode(['success' => false, 'message' => 'Supplier ID is required']);
        exit;
    }

    $sql = "
        SELECT s.supplier_id, s.company_name, s.contract_id, 
               c.created_date, c.expiry_date, c.status
        FROM tblsupplier s
        LEFT JOIN tblcontract c ON s.contract_id = c.contract_id
        WHERE s.supplier_id = ?
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $supplier_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $details = $result->fetch_assoc();
        echo json_encode(['success' => true, 'data' => $details]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Supplier not found']);
    }
    $stmt->close();
    exit;
}

// ----------------- FETCH SUPPLIER DATA & SUMMARY -----------------

// Total Active Suppliers
$sqlActive = "
    SELECT COUNT(DISTINCT s.supplier_id) AS totalActive
    FROM tblsupplier s
    JOIN tblcontract c ON s.contract_id = c.contract_id
    WHERE c.status = 'Active'
";
$resultActive = $conn->query($sqlActive);
$totalActive = ($resultActive && $row = $resultActive->fetch_assoc()) ? $row['totalActive'] : 0;

// Need Renewal Suppliers (Expired)
$sqlRenewal = "
    SELECT COUNT(DISTINCT s.supplier_id) AS needRenewal
    FROM tblsupplier s
    JOIN tblcontract c ON s.contract_id = c.contract_id
    WHERE c.status = 'Expired'
";
$resultRenewal = $conn->query($sqlRenewal);
$needRenewal = ($resultRenewal && $row = $resultRenewal->fetch_assoc()) ? $row['needRenewal'] : 0;

// Expiring Soon Contracts (within 7 days)
$sqlExpiring = "
    SELECT COUNT(DISTINCT s.supplier_id) AS expiringSoon
    FROM tblsupplier s
    JOIN tblcontract c ON s.contract_id = c.contract_id
    WHERE c.status = 'Active'
      AND c.expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
";
$resultExpiring = $conn->query($sqlExpiring);
$expiringSoon = ($resultExpiring && $row = $resultExpiring->fetch_assoc()) ? $row['expiringSoon'] : 0;

// Supplier List with Contract Info
$sqlSuppliers = "
    SELECT s.supplier_id, s.company_name, s.contact_person, 
           s.contact_number, s.email, s.address, s.position,
           c.status, c.expiry_date, c.created_date, s.contract_id
    FROM tblsupplier s
    LEFT JOIN tblcontract c ON s.contract_id = c.contract_id
    ORDER BY s.supplier_id ASC
";
$resultSuppliers = $conn->query($sqlSuppliers);

$suppliers = [];
if ($resultSuppliers && $resultSuppliers->num_rows > 0) {
    while ($row = $resultSuppliers->fetch_assoc()) {
        // Calculate days until expiry for active contracts
        $daysUntilExpiry = null;
        if ($row['status'] === 'Active' && $row['expiry_date']) {
            $expiry = new DateTime($row['expiry_date']);
            $today = new DateTime();
            $interval = $today->diff($expiry);
            $daysUntilExpiry = $interval->days;
        }
        
        $suppliers[] = [
            'supplier_id' => $row['supplier_id'],
            'company_name' => $row['company_name'],
            'contact_person' => $row['contact_person'],
            'contact_number' => $row['contact_number'],
            'email' => $row['email'],
            'position' => $row['position'],
            'address' => $row['address'],
            'status' => $row['status'] ?? 'N/A',
            'contract_id' => $row['contract_id'] ?? 'N/A',
            'expiry_date' => $row['expiry_date'],
            'created_date' => $row['created_date'],
            'days_until_expiry' => $daysUntilExpiry
        ];
    }
}

// Output JSON
echo json_encode([
    'success' => true,
    'totalActive' => $totalActive,
    'needRenewal' => $needRenewal,
    'almostOver' => $expiringSoon,
    'suppliers' => $suppliers
]);

$conn->close();
?>