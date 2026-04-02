<?php
// config/input.php

$host = "localhost";
$user = "root";
$password = "";
$dbname = "mis_coffe";

$conn = mysqli_connect($host, $user, $password, $dbname);

if (!$conn) {
    die("Connection Failed: " . mysqli_connect_error());
}

if(isset($_POST['action'])){

    date_default_timezone_set("Asia/Manila");
    $today = date("Y-m-d");
    $next7days = date("Y-m-d", strtotime("+7 days"));

    /* TOTAL ACTIVE SUPPLIERS */
    if($_POST['action'] == "get_total_suppliers"){

        $sql = "SELECT COUNT(DISTINCT supplier_id) AS total_suppliers
                FROM tblcontract
                WHERE status = 'Active'";

        $result = mysqli_query($conn,$sql);
        $row = mysqli_fetch_assoc($result);

        echo $row['total_suppliers'] ?? 0;
    }

     /* TOTAL ACTIVE SUPPLIERS */
    if($_POST['action'] == "get_total_suppliers1"){

        $sql = "SELECT COUNT(DISTINCT supplier_id) AS total_suppliers
                FROM tblcontract
                WHERE status = 'Active'";

        $result = mysqli_query($conn,$sql);
        $row = mysqli_fetch_assoc($result);

        echo $row['total_suppliers'] ?? 0;
    }

    /* TOTAL NEED RENEWAL (Expired Contracts) */
    if($_POST['action'] == "get_need_renewal"){

        $sql = "SELECT COUNT(DISTINCT supplier_id) AS need_renewal
                FROM tblcontract
                WHERE status = 'Expired'";

        $result = mysqli_query($conn,$sql);
        $row = mysqli_fetch_assoc($result);

        echo $row['need_renewal'] ?? 0;
    }

    /* TOTAL EXPIRING SOON CONTRACTS (Within 7 Days) */
    if($_POST['action'] == "get_expiring_soon"){

        $sql = "SELECT COUNT(DISTINCT supplier_id) AS expiring_soon
                FROM tblcontract
                WHERE status = 'Active'
                AND expiryDate BETWEEN '$today' AND '$next7days'";

        $result = mysqli_query($conn,$sql);
        $row = mysqli_fetch_assoc($result);

        echo $row['expiring_soon'] ?? 0;
    }

    /* TOTAL ITEMS IN STOCK */
    if($_POST['action'] == "get_total_items"){

        $sql = "SELECT SUM(stock_quantity) AS total_stock FROM items";

        $result = mysqli_query($conn,$sql);
        $row = mysqli_fetch_assoc($result);

        echo $row['total_stock'] ?? 0;
    }

    /* TOTAL REQUEST TODAY (PH TIME) */
    if($_POST['action'] == "get_total_requests_today"){

        $sql = "SELECT COUNT(*) AS total_requests 
                FROM request
                WHERE DATE(request_date) = '$today'";

        $result = mysqli_query($conn,$sql);
        $row = mysqli_fetch_assoc($result);

        echo $row['total_requests'] ?? 0;
    }

   /* TOTAL INCOME FOR CURRENT WEEK (MONDAY TO SUNDAY) BASED ON FINANCE APPROVAL DATE */
if($_POST['action'] == "get_total_income_today"){

    // Get current week's Monday and Sunday
    $monday = date('Y-m-d', strtotime('monday this week'));
    $sunday = date('Y-m-d', strtotime('sunday this week'));

    $sql = "SELECT SUM(tblprocurement.total_amount) AS total_income
            FROM tblprocurement
            INNER JOIN request 
            ON tblprocurement.request_id = request.request_id
            WHERE tblprocurement.inventory_approval = 'Approved'
            AND DATE(tblprocurement.finance_approval_date) BETWEEN '$monday' AND '$sunday'";

    $result = mysqli_query($conn,$sql);
    $row = mysqli_fetch_assoc($result);

    echo $row['total_income'] ?? 0;
}

   /* GET LATEST REQUEST NOTIFICATIONS */
if($_POST['action'] == "get_notifications"){

    $sql = "SELECT request_id, request_date
            FROM request
            WHERE status = 'Pending'
            ORDER BY request_date DESC
            LIMIT 6";

    $result = mysqli_query($conn,$sql);

    $notifications = [];

    while($row = mysqli_fetch_assoc($result)){
        $notifications[] = $row;
    }

    echo json_encode($notifications);
}

    /* WEEKLY REQUEST CHART - ALWAYS 7 DAYS */
    if($_POST['action'] == "get_weekly_requests"){

        $labels = [];
        $values = [];

        // Generate last 7 days including today
        for($i = 6; $i >= 0; $i--) {

            $date = date("Y-m-d", strtotime("-$i days"));
            $label = date("F d", strtotime($date));

            $sql = "SELECT COUNT(*) AS total 
                    FROM request 
                    WHERE DATE(request_date) = '$date'";

            $result = mysqli_query($conn,$sql);
            $row = mysqli_fetch_assoc($result);

            $labels[] = $label;
            $values[] = (int)$row['total']; // if no record → 0
        }

        echo json_encode([
            "labels"=>$labels,
            "values"=>$values
        ]);
    }
/* GET SUPPLIERS LIST WITH CONTRACT STATUS */
if($_POST['action'] == "get_suppliers_list"){

    $sql = "SELECT 
                s.supplier_id,
                s.company_name,
                COALESCE(c.status, 'No Contract') AS status
            FROM tblsupplier s
            LEFT JOIN (
                SELECT supplier_id, status
                FROM tblcontract
                WHERE (supplier_id, contract_id) IN (
                    SELECT supplier_id, MAX(contract_id)
                    FROM tblcontract
                    GROUP BY supplier_id
                )
            ) c ON s.supplier_id = c.supplier_id
            ORDER BY s.supplier_id DESC";

    $result = mysqli_query($conn, $sql);
    
    $suppliers = [];
    while($row = mysqli_fetch_assoc($result)){
        $suppliers[] = $row;
    }
    
    echo json_encode($suppliers);
}
    
}
?>