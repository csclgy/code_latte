<?php 
// Procurementdb.php

$host = "localhost";
$user = "root";
$password = "";
$db = "mis_coffe";

// Create connection with error reporting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn = mysqli_connect($host, $user, $password, $db);

if (!$conn) {
    die(json_encode(["status" => "error", "message" => "Connection Failed: " . mysqli_connect_error()]));
}

if (isset($_POST['action'])) {
    try {
        if ($_POST['action'] == "get_procurement_summary") {
            // TOTAL REQUEST
            $stmt = mysqli_prepare($conn, "SELECT COUNT(*) as total FROM request");
            mysqli_stmt_execute($stmt);
            $totalResult = mysqli_stmt_get_result($stmt);
            $total = mysqli_fetch_assoc($totalResult)['total'];
            mysqli_stmt_close($stmt);
            
            // PENDING REQUEST
            $stmt = mysqli_prepare($conn, "SELECT COUNT(*) as total FROM request WHERE status='Pending'");
            mysqli_stmt_execute($stmt);
            $pendingResult = mysqli_stmt_get_result($stmt);
            $pending = mysqli_fetch_assoc($pendingResult)['total'];
            mysqli_stmt_close($stmt);
            
            // FULFILLED REQUEST
            $stmt = mysqli_prepare($conn, "SELECT COUNT(*) as total FROM request WHERE status='Received'");
            mysqli_stmt_execute($stmt);
            $fulfilledResult = mysqli_stmt_get_result($stmt);
            $fulfilled = mysqli_fetch_assoc($fulfilledResult)['total'];
            mysqli_stmt_close($stmt);
            
            // REJECTED REQUEST
            $stmt = mysqli_prepare($conn, "SELECT COUNT(*) as total FROM request WHERE status='Rejected'");
            mysqli_stmt_execute($stmt);
            $rejectedResult = mysqli_stmt_get_result($stmt);
            $rejected = mysqli_fetch_assoc($rejectedResult)['total'];
            mysqli_stmt_close($stmt);
            
            echo json_encode([
                "total" => $total,
                "pending" => $pending,
                "fulfilled" => $fulfilled,
                "rejected" => $rejected
            ]);
        }
        
       elseif ($_POST['action'] == "get_requisition_table") {
    $sql = "SELECT 
            p.request_id,
            r.request_date,
            p.total_amount,
            p.quotation_status,
            p.quotation_approval_date,
            p.inventory_approval,
            p.inventory_approval_date,
            p.finance_approval,
            p.finance_approval_date,
            p.verification,
            p.verification_date,
            s.supplier_id,
            s.company_name
        FROM tblprocurement p
        LEFT JOIN request r ON p.request_id = r.request_id
        LEFT JOIN tblpurchased_items pi ON p.procurement_id = pi.procurement_id
        LEFT JOIN tblsupplier s ON pi.supplier_id = s.supplier_id
        ORDER BY r.request_date DESC";

    $result = mysqli_query($conn, $sql);

    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }

    echo json_encode($data);
}

elseif ($_POST['action'] == "get_verified_report") {
    if (!isset($_POST['request_id'])) {
        throw new Exception("Request ID is required");
    }

    $request_id = $_POST['request_id'];

    $stmt = mysqli_prepare($conn, "
        SELECT r.request_id, r.request_date,
               p.finance_approval_date,
               p.inventory_approval_date,
               p.verification_date,
               s.supplier_id, s.company_name,
               ri.item_id, i.item_name, ri.item_quantity, ri.price
        FROM request r
        JOIN tblprocurement p ON r.request_id = p.request_id
        JOIN tblpurchased_items pi ON p.procurement_id = pi.procurement_id
        JOIN tblsupplier s ON pi.supplier_id = s.supplier_id
        JOIN request_items ri ON r.request_id = ri.request_id
        JOIN items i ON ri.item_id = i.item_id
        WHERE r.request_id = ?
    ");

    mysqli_stmt_bind_param($stmt, "s", $request_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }

    echo json_encode($data);
    mysqli_stmt_close($stmt);
}
        
       /* GET TRACE PURCHASED ORDERS */
elseif ($_POST['action'] == "get_trace_orders") {
    $sql = "SELECT r.request_id, 
                   r.status,  -- ✅ changed here
                   s.company_name, 
                   pi.purchase_status, 
                   pi.purchase_date, 
                   d.delivery_id, 
                   d.delivery_status, 
                   d.delivery_date, 
                   i.inspection_id, 
                   i.inspection_status, 
                   i.inspection_date, 
                   pi.recieved_date 
            FROM request r 
            LEFT JOIN tblprocurement pr ON r.request_id = pr.request_id 
            LEFT JOIN tblpurchased_items pi ON pr.procurement_id = pi.procurement_id 
            LEFT JOIN tblsupplier s ON pi.supplier_id = s.supplier_id 
            LEFT JOIN tbldelivery d ON pi.delivery_id = d.delivery_id 
            LEFT JOIN tblinspection i ON pi.inspection_id = i.inspection_id 
            WHERE pr.verification = 'Verified' 
            ORDER BY r.request_id DESC";
    
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        throw new Exception("Query failed: " . mysqli_error($conn));
    }
    
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    echo json_encode($data);
}
        
        /* MARK DELIVERY ARRIVED */
        elseif ($_POST['action'] == "mark_delivery") {
            if (!isset($_POST['delivery_id'])) {
                throw new Exception("Delivery ID is required");
            }
            
            $delivery_id = $_POST['delivery_id'];
            $stmt = mysqli_prepare($conn, "UPDATE tbldelivery SET delivery_status='Delivered', delivery_date = NOW() WHERE delivery_id=?");
            mysqli_stmt_bind_param($stmt, "s", $delivery_id);
            mysqli_stmt_execute($stmt);
            
            if (mysqli_stmt_affected_rows($stmt) > 0) {
                echo json_encode(["status" => "success", "message" => "Delivery marked as delivered"]);
            } else {
                echo json_encode(["status" => "error", "message" => "No records updated"]);
            }
            mysqli_stmt_close($stmt);
        }
        
      /* UPDATE INSPECTION STATUS */
elseif ($_POST['action'] == "update_inspection") {
    if (!isset($_POST['inspection_id']) || !isset($_POST['status'])) {
        throw new Exception("Inspection ID and status are required");
    }
    
    $inspection_id = $_POST['inspection_id'];
    $status = $_POST['status'];
    
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Update inspection status
        $stmt = mysqli_prepare($conn, "UPDATE tblinspection SET inspection_status=?, inspection_date = NOW() WHERE inspection_id=?");
        mysqli_stmt_bind_param($stmt, "ss", $status, $inspection_id);
        mysqli_stmt_execute($stmt);
        
        // If inspection status is updated to "Passed", update request table status to "Purchased"
        if ($status == "Passed") {
            // Get purchased_id from tblinspection
            $stmt2 = mysqli_prepare($conn, "SELECT purchased_id FROM tblinspection WHERE inspection_id = ?");
            mysqli_stmt_bind_param($stmt2, "s", $inspection_id);
            mysqli_stmt_execute($stmt2);
            $result = mysqli_stmt_get_result($stmt2);
            $inspection = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt2);
            
            if ($inspection) {
                // Get request_id from tblpurchased_items
                $stmt3 = mysqli_prepare($conn, "SELECT request_id FROM tblpurchased_items WHERE purchased_id = ?");
                mysqli_stmt_bind_param($stmt3, "s", $inspection['purchased_id']);
                mysqli_stmt_execute($stmt3);
                $result2 = mysqli_stmt_get_result($stmt3);
                $purchased = mysqli_fetch_assoc($result2);
                mysqli_stmt_close($stmt3);
                
                if ($purchased) {
                    // Update request table status
                    $stmt4 = mysqli_prepare($conn, "UPDATE request SET status = 'Purchased' WHERE request_id = ?");
                    mysqli_stmt_bind_param($stmt4, "s", $purchased['request_id']);
                    mysqli_stmt_execute($stmt4);
                    mysqli_stmt_close($stmt4);
                }
            }
        }
        
        mysqli_commit($conn);
        
        if (mysqli_stmt_affected_rows($stmt) > 0) {
            echo json_encode(["status" => "success", "message" => "Inspection status updated"]);
        } else {
            echo json_encode(["status" => "error", "message" => "No records updated"]);
        }
        mysqli_stmt_close($stmt);
        
    } catch (Exception $e) {
        mysqli_rollback($conn);
        throw $e;
    }
}
        
        elseif ($_POST['action'] == "get_request_items") {
            if (!isset($_POST['request_id'])) {
                throw new Exception("Request ID is required");
            }
            
            $request_id = $_POST['request_id'];
            $stmt = mysqli_prepare($conn, "SELECT ri.item_id, ri.item_quantity, i.item_name 
                                          FROM request_items ri 
                                          JOIN items i ON ri.item_id = i.item_id 
                                          WHERE ri.request_id = ?");
            mysqli_stmt_bind_param($stmt, "s", $request_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            $data = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }
            echo json_encode($data);
            mysqli_stmt_close($stmt);
        }
        
        elseif ($_POST['action'] == "approve_quotation") {
            if (!isset($_POST['request_id']) || !isset($_POST['items']) || !isset($_POST['total_amount'])) {
                throw new Exception("Invalid data: Missing required fields");
            }
            
            $request_id = $_POST['request_id'];
            $items = json_decode($_POST['items'], true);
            $total_amount = $_POST['total_amount'];
            $comment = isset($_POST['comment']) ? $_POST['comment'] : '';
            
            if (!$request_id || !$items) {
                echo json_encode(["status" => "error", "message" => "Invalid data"]);
                exit;
            }
            
            // Start transaction
            mysqli_begin_transaction($conn);
            
            try {
                foreach ($items as $item) {
                    if (!isset($item['item_id']) || !isset($item['total'])) {
                        throw new Exception("Invalid item data");
                    }
                    
                    $item_id = $item['item_id'];
                    $price = $item['total'];
                    
                    $stmt = mysqli_prepare($conn, "UPDATE request_items SET price = ? WHERE item_id = ? AND request_id = ?");
                    mysqli_stmt_bind_param($stmt, "dss", $price, $item_id, $request_id);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);
                }
                
                $stmt = mysqli_prepare($conn, "UPDATE tblprocurement 
                                              SET quotation_status='Approved', 
                                                  quotation_approval_date = NOW(), 
                                                  quotation_comments = ?, 
                                                  total_amount = ? 
                                              WHERE request_id=?");
                mysqli_stmt_bind_param($stmt, "sds", $comment, $total_amount, $request_id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                
                mysqli_commit($conn);
                echo json_encode(["status" => "success", "message" => "Quotation approved successfully"]);
                
            } catch (Exception $e) {
                mysqli_rollback($conn);
                throw $e;
            }
        }
        
        elseif ($_POST['action'] == "get_report_data") {
            if (!isset($_POST['request_id'])) {
                throw new Exception("Request ID is required");
            }
            
            $request_id = $_POST['request_id'];
            $stmt = mysqli_prepare($conn, "SELECT r.request_date, p.total_amount, ri.item_id, 
                                          ri.item_quantity, ri.price, i.item_name 
                                          FROM request r 
                                          JOIN tblprocurement p ON r.request_id = p.request_id 
                                          JOIN request_items ri ON r.request_id = ri.request_id 
                                          JOIN items i ON ri.item_id = i.item_id 
                                          WHERE r.request_id = ?");
            mysqli_stmt_bind_param($stmt, "s", $request_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            $data = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }
            echo json_encode($data);
            mysqli_stmt_close($stmt);
        }
        
        elseif ($_POST['action'] == "get_verify_details") {
            if (!isset($_POST['request_id'])) {
                throw new Exception("Request ID is required");
            }
            
            $request_id = $_POST['request_id'];
            
            /* PROCUREMENT INFO */
            $stmt = mysqli_prepare($conn, "SELECT * FROM tblprocurement WHERE request_id=?");
            mysqli_stmt_bind_param($stmt, "s", $request_id);
            mysqli_stmt_execute($stmt);
            $procResult = mysqli_stmt_get_result($stmt);
            $proc = mysqli_fetch_assoc($procResult);
            mysqli_stmt_close($stmt);
            
            /* ITEMS */
            $items = [];
            $stmt = mysqli_prepare($conn, "SELECT ri.item_id, i.item_name, ri.item_quantity, ri.price 
                                          FROM request_items ri 
                                          JOIN items i ON ri.item_id = i.item_id 
                                          WHERE ri.request_id=?");
            mysqli_stmt_bind_param($stmt, "s", $request_id);
            mysqli_stmt_execute($stmt);
            $itemsResult = mysqli_stmt_get_result($stmt);
            while ($row = mysqli_fetch_assoc($itemsResult)) {
                $items[] = $row;
            }
            mysqli_stmt_close($stmt);
            
            /* SUPPLIERS */
            $suppliers = [];
            $s = mysqli_query($conn, "SELECT s.supplier_id, s.company_name 
                                     FROM tblsupplier s 
                                     JOIN tblcontract c ON s.supplier_id = c.supplier_id 
                                     WHERE c.status = 'Active'");
            while ($row = mysqli_fetch_assoc($s)) {
                $suppliers[] = $row;
            }
            
            echo json_encode([
                "procurement_id" => $proc['procurement_id'] ?? null,
                "quotation_date" => $proc['quotation_approval_date'] ?? null,
                "inventory_date" => $proc['inventory_approval_date'] ?? null,
                "finance_date" => $proc['finance_approval_date'] ?? null,
                "items" => $items,
                "suppliers" => $suppliers
            ]);
        }
        
       elseif ($_POST['action'] == "verify_purchase") {
    if (!isset($_POST['request_id']) || !isset($_POST['procurement_id']) || !isset($_POST['supplier_id'])) {
        throw new Exception("Missing required fields");
    }
    
    $request_id = $_POST['request_id'];
    $procurement_id = $_POST['procurement_id'];
    $supplier_id = $_POST['supplier_id'];
    
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Generate IDs safely
        $res = mysqli_query($conn, "SELECT COUNT(*) as total FROM tblpurchased_items");
        $row = mysqli_fetch_assoc($res);
        $purchased_id = "PRC" . str_pad($row['total'] + 1, 4, "0", STR_PAD_LEFT);
        
        $res = mysqli_query($conn, "SELECT COUNT(*) as total FROM tblinspection");
        $row = mysqli_fetch_assoc($res);
        $inspection_id = "INSP" . str_pad($row['total'] + 1, 4, "0", STR_PAD_LEFT);
        
        $res = mysqli_query($conn, "SELECT COUNT(*) as total FROM tbldelivery");
        $row = mysqli_fetch_assoc($res);
        $delivery_id = "DLY" . str_pad($row['total'] + 1, 4, "0", STR_PAD_LEFT);
        
        /* ================= INSERT PURCHASE ================= */
        $stmt = mysqli_prepare($conn, "INSERT INTO tblpurchased_items 
            (purchased_id, request_id, procurement_id, inspection_id, delivery_id, supplier_id, purchase_status, purchase_date, recieve_status) 
            VALUES (?, ?, ?, ?, ?, ?, 'Purchased', NOW(), 'Pending')");
        
        mysqli_stmt_bind_param($stmt, "ssssss", 
            $purchased_id, 
            $request_id, 
            $procurement_id, 
            $inspection_id, 
            $delivery_id, 
            $supplier_id
        );
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        
        /* ================= INSERT INSPECTION ================= */
        $stmt = mysqli_prepare($conn, "INSERT INTO tblinspection 
            (inspection_id, purchased_id, inspection_status, inspection_comments, inspection_date) 
            VALUES (?, ?, 'Pending', 'None', NULL)");
        
        mysqli_stmt_bind_param($stmt, "ss", $inspection_id, $purchased_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        
        /* ================= INSERT DELIVERY ================= */
        $stmt = mysqli_prepare($conn, "INSERT INTO tbldelivery 
            (delivery_id, purchased_id, delivery_status, delivery_date) 
            VALUES (?, ?, 'Pending', NULL)");
        
        mysqli_stmt_bind_param($stmt, "ss", $delivery_id, $purchased_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        
        /* ================= UPDATE PROCUREMENT (WITH DATE) ================= */
        $stmt = mysqli_prepare($conn, "UPDATE tblprocurement 
            SET verification = 'Verified',
                verification_date = NOW()
            WHERE request_id = ?");
        
        mysqli_stmt_bind_param($stmt, "s", $request_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        
        // Commit all
        mysqli_commit($conn);
        
        echo json_encode([
            "status" => "success",
            "message" => "Purchase verified successfully with date"
        ]);
        
    } catch (Exception $e) {
        mysqli_rollback($conn);
        throw $e;
    }
}
        
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
}

// Close connection at the end
mysqli_close($conn);
?>