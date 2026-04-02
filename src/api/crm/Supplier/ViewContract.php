<?php
// ViewContract.php - Printable Contract Page

if (!isset($_GET['supplier_id'])) {
    die("Supplier ID not provided.");
}

$supplier_id = $_GET['supplier_id'];

$host = "localhost";
$user = "root";
$password = "";
$config = "mis_coffe";

// Create connection
$conn = new mysqli($host, $user, $password, $config);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch supplier info
$sql = "
    SELECT s.company_name, s.contact_person, s.address, s.email,
           c.created_date, c.expiry_date
    FROM tblsupplier s
    LEFT JOIN tblcontract c ON s.supplier_id = c.supplier_id
    WHERE s.supplier_id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $supplier_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Supplier or contract not found.");
}

$row = $result->fetch_assoc();

// Format dates
$createdDate = date("F j, Y", strtotime($row['created_date']));
$expiryDate = date("F j, Y", strtotime($row['expiry_date']));

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Contract - <?= htmlspecialchars($row['company_name']) ?></title>
<style>
body { font-family: Arial, sans-serif; margin: 40px; color: #333; line-height: 1.6; }
header { text-align: center; margin-bottom:5px; }
header h1 { margin: 0; font-size: 32px; }
header p { margin: 2px 0; font-size: 14px; color: #555; }

.section-title { font-weight: bold; margin-top: 20px; font-size: 18px; text-decoration: underline; }
.rules { font-family: "Times New Roman", serif; font-size: 12px; text-align: justify; margin-top: 10px; }
.rules ol { margin-left: 20px; }
.rules ol li { margin-bottom: 8px; }
.sub-section { font-weight: bold; margin-top: 10px; }

footer { margin-top: 40px; }
.signature { margin-top: 50px; text-align: left; }
.signature p { margin: 0; }

button { margin-top: 20px; padding: 8px 15px; font-size: 14px; cursor: pointer; }

@media print {
    button {
        display: none; /* Hide all buttons when printing */
    }
}
</style>
</head>
<body>

<button onclick="window.print()">Print Contract</button>

<header style="font-family: 'Times New Roman', serif; font-size: 14px; text-align: center; margin-bottom: 20px; line-height: 1.3;">
    <h1 style="font-size: 20px; margin: 0;">CODE LATTE INCORPORATED</h1>
    <p style="font-size: 10px;margin: 2px 0;">Lyceum of Alabang</p>
    <p style="font-size: 10px;margin: 2px 0;">Tunasan, Muntinlupa City</p>
    <p style="font-size: 10px;margin: 2px 0;">codelatte@gmail.com</p>
</header>
<hr>
<br>

<div class="content">
<p style="font-family: 'Times New Roman', serif; font-size: 15px; text-align: justify;">
    CODE LATTE is committed to building strong and professional partnerships. This contract formalizes the relationship with <strong><?= htmlspecialchars($row['company_name']) ?></strong> and sets forth the terms, conditions, rules, and obligations of both parties during the contract period.
</p>
    <div class="section-title">Rules and Regulations / Terms and Privacy Policy</div>
    <div class="rules">
        <ol>
            <li>
                <strong>INTRODUCTION</strong>
                <p>1.1. The terms and conditions of purchase and sale set out herein ("Terms and Conditions") shall apply to all contracts for the procurement and supply of goods ("the Goods") and services ("the Services") by the Supplier to CODE LATTE, where the contract has arisen from a purchase order ("Purchase Order") issued by CODE LATTE and accepted by the Supplier, including any such Purchase Order issued in response to a quotation from the Supplier.</p>
                <p>1.2. This Agreement shall apply between the Supplier and CODE LATTE.</p>
                <p>1.3. CODE LATTE and the Supplier shall collectively be referred to as "the Parties" and "Party" shall refer to any one of them.</p>
            </li>
            <li>
                <strong>WHOLE AGREEMENT</strong>
                <p>2.1. The Agreement between the Parties comprises (a) these Terms and Conditions, (b) the provisions of any Purchase Order, and (c) the vendor application form completed by the Supplier in connection with its supply of Goods and/or Services to CODE LATTE (collectively "the Agreement").</p>
                <p>2.2. The Agreement is the sole record of the contract between the Parties and may only be varied or waived in a written, signed document between the Parties.</p>
                <p>2.3. The Supplier’s standard terms, if any, shall not be binding on CODE LATTE unless expressly accepted in writing.</p>
                <p>2.4. No undertaking, representation, term or condition not incorporated in this Agreement shall be binding on either Party.</p>
                <p>2.5. The Supplier is an independent contracting party and this Agreement does not constitute a contract of agency, representation, employment, or partnership with CODE LATTE.</p>
            </li>
            <li>
                <strong>PURCHASE ORDERS</strong>
                <p>3.1. Purchase Orders will be system-generated and issued electronically or by email, detailing the Goods, Services, and agreed Price.</p>
                <p>3.2. Any discrepancy or ambiguity in description or quantities must be submitted immediately to CODE LATTE before executing the Purchase Order.</p>
                <p>3.3. Amendments to the Purchase Order require prior written approval and acceptance by both Parties.</p>
                <p>3.4. Purchase Orders may be cancelled by CODE LATTE provided that the Supplier is paid for costs reasonably incurred up to the cancellation date, and the Supplier shall mitigate losses where possible.</p>
            </li>
            <li>
                <strong>PRICE AND PAYMENT</strong>
                <p>4.1. The price for Goods and/or Services shall be specified in the Purchase Order and shall be paid accordingly. Prices are exclusive of VAT and include packaging, delivery, and installation unless stated otherwise.</p>
                <p>4.2. Additional charges require prior written approval by CODE LATTE.</p>
                <p>4.3. No invoice shall be binding without a valid Purchase Order.</p>
                <p>4.4. Payment shall be made via EFT to the Supplier's nominated account as per the agreed payment terms.</p>
            </li>
            <li>
                <strong>OWNERSHIP AND RISK</strong>
                <p>5.1. Ownership and risk in Goods pass to CODE LATTE upon physical delivery or installation, and certificates of conformance must be provided.</p>
                <p>5.2. Ownership and risk shall pass if Goods are collected by CODE LATTE.</p>
                <p>5.3. CODE LATTE may inspect Goods and Services at reasonable times; inspection does not relieve the Supplier of obligations under the Agreement.</p>
            </li>
            <li>
                <strong>SUPPLIER WARRANTIES</strong>
                <p>6.1. The Supplier warrants that Services will be rendered efficiently, professionally, and safely, in accordance with industry standards.</p>
                <p>6.2. The Supplier warrants compliance with applicable laws, safety standards, and certifications for Goods and Services.</p>
                <p>6.3. Goods shall be new, merchantable, fit for purpose, free from defects, and durable.</p>
                <p>6.4. Goods are free of liens, mortgages, or other encumbrances.</p>
                <p>6.5. The Supplier warrants disclosure of any facts known or that should have been known that could affect CODE LATTE's decision.</p>
            </li>
            <li>
                <strong>SUPPLIER GENERAL OBLIGATIONS</strong>
                <p>7.1. The Supplier shall provide all relevant certifications, maintain contact with CODE LATTE representatives, and ensure staff behavior is professional at all times.</p>
                <p>7.2. The Supplier shall comply with laws, obtain necessary permits, and deliver Goods and Services without undue delay.</p>
            </li>
            <li>
                <strong>HEALTH AND SAFETY</strong>
                <p>8.1. Supplier shall access premises only according to CODE LATTE’s access control procedures.</p>
                <p>8.2. Work must be conducted safely and comply with site rules and laws.</p>
                <p>8.3. Supplier shall cooperate with audits and safety inspections.</p>
            </li>
            <li>
                <strong>FORCE MAJEURE</strong>
                <p>17.1. Neither Party shall be liable for failure to perform due to events beyond reasonable control.</p>
                <p>17.2. Parties must give prompt notice and cooperate to mitigate effects of Force Majeure.</p>
                <p>17.3. If Force Majeure exceeds fourteen (14) consecutive days, either Party may terminate the Agreement.</p>
            </li>
            <li>
                <strong>GOVERNING LAW</strong>
                <p>20.1. This Agreement shall be governed by the laws of the Republic of the Philippines.</p>
                <p>20.2. Rights not exercised do not constitute waiver.</p>
                <p>20.3. Provisions are severable; invalid provisions do not affect remaining terms.</p>
            </li>
        </ol>
    </div>

    <p><strong>Contract Period:</strong> This contract was formally executed on <strong><?= $createdDate ?></strong> and will remain in effect until <strong><?= $expiryDate ?></strong>.</p>
</div>

<footer>
    <div class="signature">
        <p>Approved by:</p>
        <br>
        <p><strong>Latorre Ahron A.</strong></p>
        <p>Purchasing Management Head</p>
        <br>
        <p><strong><?= htmlspecialchars($row['contact_person']) ?></strong></p>
        <p>Supplier Representative </p>
    </div>

    
</footer>

</body>
</html>