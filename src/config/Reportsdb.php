<?php

$host = "localhost";
$user = "root";
$password = "";
$config = "mis_coffe";

$conn = mysqli_connect($host,$user,$password,$config);

if(!$conn){
    die("Connection Failed: " . mysqli_connect_error());
}

if(isset($_POST['action'])){

/* TOTAL FULFILLED REQUISITION */
if($_POST['action'] == "get_total_fulfilled"){

$sql = "SELECT COUNT(*) AS total
        FROM request
        WHERE status = 'Received'";

$result = mysqli_query($conn,$sql);

if($result){
$row = mysqli_fetch_assoc($result);
echo $row['total'] ?? 0;
}else{
echo 0;
}

}


/* TOTAL PURCHASED ITEMS */
if($_POST['action'] == "get_total_purchased"){

$sql = "SELECT COUNT(*) AS total
        FROM tblpurchased_items
        WHERE purchase_status = 'Purchased'";

$result = mysqli_query($conn,$sql);

if($result){
$row = mysqli_fetch_assoc($result);
echo $row['total'] ?? 0;
}else{
echo 0;
}

}


/* TOTAL EXPENSES */
if($_POST['action'] == "get_total_expenses"){

$sql = "SELECT IFNULL(SUM(total_amount),0) AS total
        FROM tblprocurement";

$result = mysqli_query($conn,$sql);

if($result){
$row = mysqli_fetch_assoc($result);
echo $row['total'] ?? 0;
}else{
echo 0;
}

}


/* MONTHLY TOTAL PURCHASED */
if($_POST['action'] == "get_monthly_requests"){

$sql = "
SELECT 
MONTH(DATE(request_date)) AS month,
COUNT(*) AS total
FROM request
WHERE YEAR(DATE(request_date)) = YEAR(CURDATE())
GROUP BY MONTH(DATE(request_date))
ORDER BY MONTH(DATE(request_date))
";

$result = mysqli_query($conn,$sql);

$months = array_fill(1,12,0);

if($result){
while($row = mysqli_fetch_assoc($result)){
$months[(int)$row['month']] = (int)$row['total'];
}
}

echo json_encode(array_values($months));

}


/* MONTHLY EXPENSES */
if($_POST['action'] == "get_monthly_expenses"){

$sql = "
SELECT 
MONTH(finance_approval_date) AS month,
IFNULL(SUM(total_amount),0) AS total
FROM tblprocurement
WHERE finance_approval = 'Approved'
AND YEAR(finance_approval_date) = YEAR(CURDATE())
GROUP BY MONTH(finance_approval_date)
ORDER BY MONTH(finance_approval_date)
";

$result = mysqli_query($conn,$sql);

$months = array_fill(1,12,0);

if($result){
while($row = mysqli_fetch_assoc($result)){
$months[(int)$row['month']] = (float)$row['total'];
}
}

echo json_encode(array_values($months));

}


/* TOP 5 MOST REQUESTED PRODUCTS */
if($_POST['action'] == "get_top_products"){

$sql = "
SELECT 
i.item_name,
SUM(r.item_quantity) AS total_quantity
FROM request_items r
INNER JOIN items i 
ON r.item_id = i.item_id
GROUP BY r.item_id, i.item_name
ORDER BY total_quantity DESC
LIMIT 5
";

$result = mysqli_query($conn,$sql);

$products = [];
$quantities = [];

if($result){
while($row = mysqli_fetch_assoc($result)){
$products[] = $row['item_name'];
$quantities[] = (int)$row['total_quantity'];
}
}

echo json_encode([
'labels' => $products,
'data' => $quantities
]);

}


/* PURCHASED ORDER LIST */
if($_POST['action'] == "get_purchased_orders"){

$sql = "
SELECT 
p.purchased_id,
p.purchase_date,
p.recieved_date,
s.company_name
FROM tblpurchased_items p
INNER JOIN tblsupplier s 
ON p.supplier_id = s.supplier_id
ORDER BY p.purchase_date DESC
";

$result = mysqli_query($conn,$sql);

$orders = [];

if($result){
while($row = mysqli_fetch_assoc($result)){
$orders[] = [
'purchased_id' => $row['purchased_id'],
'company_name' => $row['company_name'],
'purchased_date' => date('Y-m-d', strtotime($row['purchase_date']))
];
}
}

echo json_encode($orders);

}


/* PURCHASE REPORT DETAILS */
if($_POST['action'] == "get_purchase_report"){

$purchased_id = $_POST['purchased_id'];

/* MAIN REPORT DETAILS */

$sql = "
SELECT 
p.purchased_id,
p.purchase_date,
p.recieved_date,
p.supplier_id,
s.company_name,

pr.total_amount,
pr.finance_approval_date,
pr.verification_date,

r.request_id,
r.request_date,

d.delivery_date,
i.inspection_status

FROM tblpurchased_items p

INNER JOIN tblsupplier s 
ON p.supplier_id = s.supplier_id

INNER JOIN tblprocurement pr 
ON p.procurement_id = pr.procurement_id

INNER JOIN request r 
ON pr.request_id = r.request_id

LEFT JOIN tbldelivery d 
ON p.purchased_id = d.purchased_id

LEFT JOIN tblinspection i 
ON p.purchased_id = i.purchased_id

WHERE p.purchased_id = '$purchased_id'
LIMIT 1
";

$result = mysqli_query($conn,$sql);
$row = mysqli_fetch_assoc($result);


/* GET ITEMS BASED ON PURCHASED_ID */

$item_sql = "
SELECT 
ri.item_id,
i.item_name,
ri.price
FROM tblpurchased_items p
INNER JOIN tblprocurement pr 
ON p.procurement_id = pr.procurement_id
INNER JOIN request r 
ON pr.request_id = r.request_id
INNER JOIN request_items ri 
ON r.request_id = ri.request_id
INNER JOIN items i 
ON ri.item_id = i.item_id
WHERE p.purchased_id = '$purchased_id'
";

$item_result = mysqli_query($conn,$item_sql);

$items = [];

while($item = mysqli_fetch_assoc($item_result)){
$items[] = $item;
}


/* RETURN JSON DATA */

echo json_encode([

'purchased_id' => $row['purchased_id'],
'purchase_date' => $row['purchase_date'],
'company_name' => $row['company_name'],
'supplier_id' => $row['supplier_id'],

'request_id' => $row['request_id'],
'request_date' => $row['request_date'],

'finance_date' => $row['finance_approval_date'],
'verified_date' => $row['verification_date'], // ✅ FIXED HERE

'delivery_date' => $row['delivery_date'],
'inspection_status' => $row['inspection_status'],

'total_amount' => $row['total_amount'],
'items' => $items

]);

}

}

?>