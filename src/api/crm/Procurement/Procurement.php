<?php 
// Procurement.php - Complete Version with Delivery & Inspection Buttons
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Procurement Management</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* ================= GLOBAL ================= */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Times New Roman', sans-serif;
        }
        body {
            background-color: #f5f2f0;
            min-height: 100vh;
            display: flex;
        }
        /* ================= SIDEBAR ================= */
        .sidebar {
            width: 70px;
            background: #8b5e3c;
            color: white;
            display: flex;
            flex-direction: column;
            padding: 1rem 0;
            transition: width 0.3s ease;
            overflow: hidden;
        }
        .sidebar.expanded {
            width: 220px;
        }
        .menu-toggle {
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0 1rem;
            margin-bottom: 2rem;
        }
        nav {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        nav a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.5rem 1rem;
            white-space: nowrap;
        }
        nav a:hover {
            background: rgba(255,255,255,0.2);
        }
        nav a i {
            font-size: 1.3rem;
            min-width: 30px;
            text-align: center;
        }
        .link-text {
            opacity: 0;
            transition: 0.3s;
        }
        .sidebar.expanded .link-text {
            opacity: 1;
        }
        /* ================= MAIN ================= */
        .main-content {
            flex: 1;
            padding: 30px;
        }
        .procurement-summary {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
        }
        .summary-card {
            flex: 1;
            padding: 20px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 15px;
            color: white;
            transition: 0.3s ease;
        }
        .summary-card:hover {
            transform: translateY(-6px);
        }
        .summary-card.total {
            background: #c29f85;
        }
        .summary-card.pending {
            background: #caa180;
        }
        .summary-card.fulfilled {
            background: #a77f64;
        }
        .summary-card.failed {
            background: #866453;
        }
        .card-value {
            font-size: 24px;
            font-weight: bold;
        }
        .procurement-container {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(139,94,60,0.15);
        }
        .procurement-table {
            background: #fdfaf6;
            padding: 15px;
            border-radius: 12px;
            margin-top: 20px;
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            border-bottom: 1px solid #fff1d7;
            font-size: 14px;
            text-align: left;
        }
        th {
            background: #f8f3ef;
            color: #8b5e3c;
        }
        .status {
            padding: 5px 8px;
            border-radius: 4px;
            font-size: 12px;
            color: white;
        }
        .status.pending {
            background: #f39c12;
        }
        .status.approved {
            background: #27ae60;
        }
        /* ================= BUTTONS ================= */
        .control-btn, .btn-view, .btn-verify {
            padding: 10px 18px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: 0.3s ease;
        }
        .control-btn {
            background: #8b5e3c;
            color: white;
            margin-right: 10px;
        }
        .control-btn:hover {
            background: #6f4b2f;
            transform: scale(1.05);
        }
        .control-btn.active {
            background: #5c3d2e;
        }
        .btn-view, .btn-verify {
            background: #8b5e3c;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
        }
        .btn-view:hover, .btn-verify:hover {
            background: #6f4b2f;
        }
        .btn-delivery {
            background: #27ae60;
            color: white;
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }
        .btn-delivery:hover {
            background: #1e8449;
        }
        .btn-check {
            background: #8ce2b0;
            color: white;
            padding: 6px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 5px;
        }
        .btn-check:hover {
            background: #27ae60;
        }
        .btn-negative {
            background: #dfc0a5;
            color: white;
            padding: 6px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-negative:hover {
            background: #ca6f1e;
        }
        /* ================= MODAL STYLES ================= */
        .modal {
            display: flex;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            align-items: center;
            justify-content: center;
        }
        .modal.hidden {
            display: none;
        }
        .modal-content {
            background: white;
            padding: 25px;
            border-radius: 12px;
            width: 600px;
            max-width: 90%;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        }
        .close {
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            color: #8b5e3c;
        }
        .close:hover {
            color: #5c3d2e;
        }
        .modal-content h3 {
            margin-bottom: 20px;
            color: #543f30;
        }
        .comment-section {
            margin: 20px 0;
        }
        .comment-section label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #543f30;
        }
        #modalComment, #quotation_comment {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            resize: vertical;
            font-family: inherit;
        }
        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }
        .btn-approve, .btn-back {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: 0.3s ease;
        }
        .btn-approve {
            background: #27ae60;
            color: white;
        }
        .btn-approve:hover {
            background: #1e8449;
        }
        .btn-back {
            background: #8b5e3c;
            color: white;
        }
        .btn-back:hover {
            background: #6f4b2f;
        }
        .form-group {
            margin: 20px 0;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #543f30;
        }
        #supplierSelect {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: inherit;
            background: white;
        }
        .verify-details {
            background: #f9f5f0;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        .verify-details p {
            margin: 8px 0;
            font-size: 14px;
        }
        .verify-details strong {
            color: #8b5e3c;
            display: inline-block;
            width: 130px;
        }
        .page-header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
            flex-wrap: wrap;
        }
        .page-header {
            font-size: 1.7rem;
            font-weight: 700;
            color: #543f30;
        }
        .admin-info {
            font-size: 0.9rem;
            color: #8b6f5a;
        }
        .header-divider {
            border: 0;
            height: 1px;
            background-color: #efded2;
            margin-bottom: 2rem;
        }
        .hidden {
            display: none;
        }
        input[type="number"] {
            width: 100px;
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        select {
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .btn-verify:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        .delivered-status {
            color: #27ae60;
            font-weight: bold;
        }
        .inspection-status {
            font-weight: bold;
        }
        .inspection-passed {
            color: #27ae60;
        }
        .inspection-failed {
            color: #e74c3c;
        }
        .inspection-partial {
            color: #f39c12;
        }
    </style>
</head>
<body>
    <!-- ================= SIDEBAR ================= -->
    <div class="sidebar" id="sidebar">
        <div class="menu-toggle" id="menuToggle"><i class="fa-solid fa-bars"></i></div>
        <nav>
            <a href="../Dashboard/Dashboard.php"><i class="fa-solid fa-box"></i><span class="link-text">Dashboard</span></a>
            <a href="../Reports/Reports.php"><i class="fa-solid fa-chart-line"></i><span class="link-text">Report Analytics</span></a>
            <a href="../Supplier/SupplierM.php"><i class="fa-solid fa-users"></i><span class="link-text">Supplier</span></a>
            <a href="../Procurement/Procurement.php"><i class="fa fa-folder-open"></i><span class="link-text">Requisition</span></a>
            <a href="#"><i class="fa fa-sign-out"></i><span class="link-text">Logout</span></a>
        </nav>
    </div>

    <!-- ================= MAIN CONTENT ================= -->
    <div class="main-content">
        <div class="page-header-container">
            <div class="page-header">Procurement Management</div>
        </div>
        <hr class="header-divider">
        
        <div class="procurement-module">
            <div class="procurement-summary">
                <div class="summary-card total"><i class="fa-solid fa-file-invoice"></i> <div><div>Total Request</div><div class="card-value" id="totalRequest">0</div></div></div>
                <div class="summary-card pending"><i class="fa-solid fa-hourglass-half"></i> <div><div>Pending Request</div><div class="card-value" id="pendingRequest">0</div></div></div>
                <div class="summary-card fulfilled"><i class="fa-solid fa-circle-check"></i> <div><div>Fulfilled Request</div><div class="card-value" id="fulfilledRequest">0</div></div></div>
                <div class="summary-card failed"><i class="fa-solid fa-circle-xmark"></i> <div><div>Rejected Request</div><div class="card-value" id="rejectedRequest">0</div></div></div>
            </div>
            
            <div class="procurement-container">
                <div class="procurement-controls">
                    <button class="control-btn active" onclick="showRequisition()">Purchase Requisition</button>
                    <button class="control-btn" onclick="showTrace()">Purchased Orders</button>

                    <input type="text" id="searchRequest" placeholder="Search Request ID..." 
           style="padding:10px; width:250px; border-radius:6px; border:1px solid #ccc; margin-left:960px;">

            <!-- DATE FILTER -->
    <input type="date" id="filterDate" 
           style="padding:10px; border-radius:6px; border:1px solid #ccc; margin-left:10px;">



    <button onclick="resetFilter()" 
            style="padding:10px 15px; background:green; color:white; border:none; border-radius:6px;">
        Reset
    </button>
                </div>

                
                <div class="procurement-table" id="requisitionTable">
                    <table>
                        <thead>
                            <tr>
                                <th>Request Date</th>
                                <th>Request ID</th>
                                <th>Quotation Status</th>
                                <th>Quotation Action</th>
                                <th>Total</th>
                                <th>Inventory/Date</th>
                                <th>Finance/Date</th>
                                <th>Verification</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                
                <div class="procurement-table hidden" id="traceTable">
                    <table>
                        <thead>
                            <tr>
                                <th>Request ID</th>
                                <th>Supplier</th>
                                <th>Status</th>
                                <th>Purchased Date</th>
                                <th>Delivery</th>
                                <th>Inspection</th>
                                <th>Received</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ================= MODALS ================= -->
    <!-- Quotation Modal -->
    <div id="quotationModal" class="modal hidden">
        <div class="modal-content" style="width:800px;">
            <span class="close" onclick="closeQuotationModal()">&times;</span>
            <h3>Request ID: <span id="q_request_id"></span></h3>
            <p id="q_request_date"></p>
            <p><b>Note:</b> Enter the price for each item</p>
            <table>
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Quantity</th>
                        <th>Price Each Item</th>
                        <th>Total Price</th>
                    </tr>
                </thead>
                <tbody id="quotationBody"></tbody>
            </table>
            <div class="comment-section">
                <label>Quotation Comments</label>
                <textarea id="quotation_comment" rows="3"></textarea>
            </div>
            <div class="modal-actions">
                <button class="btn-approve" onclick="confirmApprove()">Approve</button>
                <button class="btn-back" onclick="closeQuotationModal()">Back</button>
            </div>
        </div>
    </div>

    <!-- Verification Modal -->
    <div id="verifyModal" class="modal hidden">
        <div class="modal-content">
            <span class="close" onclick="closeVerifyModal()">&times;</span>
            <h3>VERIFY AND PURCHASE THE REQUEST</h3>
            <div class="verify-details" id="verifyDetails"></div>
            <div class="form-group">
                <label>Select Supplier:</label>
                <select id="supplierSelect"></select>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Item ID</th>
                        <th>Item Name</th>
                        <th>Quantity</th>
                        <th>Price</th>
                    </tr>
                </thead>
                <tbody id="verifyItems"></tbody>
            </table>
            <div class="modal-actions">
                <button class="btn-approve" onclick="verifyRequisition()">Verify</button>
                <button class="btn-back" onclick="closeVerifyModal()">Back</button>
            </div>
        </div>
    </div>

    <!-- Delivery Modal -->
    <div id="deliveryModal" class="modal hidden">
        <div class="modal-content">
            <span class="close" onclick="closeDeliveryModal()">&times;</span>
            <h3>Confirm Delivery</h3>
            <p>Mark delivery for order <strong id="deliveryRequestId"></strong> as delivered?</p>
            <div class="modal-actions">
                <button class="btn-approve" onclick="confirmDelivery()">Yes</button>
                <button class="btn-back" onclick="closeDeliveryModal()">No</button>
            </div>
        </div>
    </div>

    <!-- Inspection Modal -->
    <div id="inspectionModal" class="modal hidden">
        <div class="modal-content">
            <span class="close" onclick="closeInspectionModal()">&times;</span>
            <h3>Confirm Inspection Validation</h3>
            <p>Are you sure you want to mark this inspection as <strong id="inspectionStatusText"></strong>?</p>
            <div class="modal-actions">
                <button class="btn-approve" onclick="confirmInspection()">Yes</button>
                <button class="btn-back" onclick="closeInspectionModal()">Back</button>
            </div>
        </div>
    </div>

    <script>
        // Global variables
        let currentDeliveryId = null;
        let currentInspectionId = null;
        let selectedInspectionStatus = null;
        let quotationData = [];
        let currentRequestId = null;
        let currentProcurementId = null;
        
        // API Base URL - Update this to your actual path
        const API_URL = "../../../config/Procurementdb.php";
        
        // Helper function for API calls
        async function callAPI(formData) {
            try {
                const response = await fetch(API_URL, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: formData
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                return await response.json();
            } catch (error) {
                console.error("API Error:", error);
                alert("Connection error. Please check your network.");
                throw error;
            }
        }
        
        function loadProcurementSummary() {
            const formData = new URLSearchParams();
            formData.append("action", "get_procurement_summary");
            
            callAPI(formData)
                .then(data => {
                    document.getElementById("totalRequest").innerText = data.total || 0;
                    document.getElementById("pendingRequest").innerText = data.pending || 0;
                    document.getElementById("fulfilledRequest").innerText = data.fulfilled || 0;
                    document.getElementById("rejectedRequest").innerText = data.rejected || 0;
                })
                .catch(error => {
                    console.error("Error loading summary:", error);
                });
        }
        
        function loadRequisitionTable() {
            const formData = new URLSearchParams();
            formData.append("action", "get_requisition_table");
            
            callAPI(formData)
                .then(data => {
                    let table = "";
                    data.forEach(row => {
                        const quotationStatus = row.quotation_status || '-';
                        const totalAmount = row.total_amount ? `₱${parseFloat(row.total_amount).toFixed(2)}` : '-';
                        const inventoryApproval = row.inventory_approval || '-';
                        const financeApproval = row.finance_approval || '-';
                        
                        // Check if verification is possible
                        const canVerify = inventoryApproval !== "Pending" && 
                                         financeApproval !== "Pending" && 
                                         quotationStatus === "Approved";
                        
                        table += `
                            <tr data-request-id="${row.request_id}" data-request-date="${row.request_date}">
                                <td>${row.request_date}</td>
                                <td>${row.request_id}</td>
                                <td>${quotationStatus}</td>
                                <td>
                                    ${quotationStatus === "Approved" 
                                        ? `<button class="btn-view" onclick="printReport('${row.request_id}')">Print</button>`
                                        : `<button class="btn-view" onclick="openQuotation('${row.request_id}', '${row.request_date}')">View</button>`
                                    }
                                </td>
                                <td>${totalAmount}</td>
                                <td>${inventoryApproval}<br>${row.inventory_approval_date || ''}</td>
                                <td>${financeApproval}<br>${row.finance_approval_date || ''}</td>
                               <td>
    ${
        row.verification === "Verified"
        ? `
            <div style="color:green;font-weight:bold;">VERIFIED</div>
            <button class="btn-view" style="background:#27ae60;margin-top:5px;"
                onclick="viewPrintReport('${row.request_id}')">
                View & Print
            </button>
        `
        : !canVerify 
            ? '<button class="btn-verify" disabled style="background:#ccc;">Verify</button>'
            : `<button class="btn-verify" onclick="openVerifyModal(this)">Verify</button>`
    }
</td>
                            </tr>
                        `;
                    });
                    document.querySelector("#requisitionTable tbody").innerHTML = table;
                })
                .catch(error => {
                    console.error("Error loading requisition table:", error);
                });
        }
        
        function loadTraceOrders() {
            const formData = new URLSearchParams();
            formData.append("action", "get_trace_orders");
            
            callAPI(formData)
                .then(data => {
                    let table = "";
                    data.forEach(row => {
                        // Delivery column
                        let deliveryColumn = "";
                        if (row.delivery_status === "Delivered") {
                            deliveryColumn = `<span class="delivered-status">Delivered<br>${row.delivery_date || ''}</span>`;
                        } else {
                            deliveryColumn = `<button class="btn-delivery" onclick="markDelivery('${row.delivery_id}')">Mark Arrived</button>`;
                        }
                        
                        // Inspection column
                        let inspectionColumn = "";
                        if (row.inspection_status === "Pending") {
                            inspectionColumn = `
                                <select onchange="updateInspection('${row.inspection_id}', this.value)">
                                    <option value="">Select Status</option>
                                    <option value="Passed">Passed</option>
                                    <option value="Partially Passed">Partially Passed</option>
                                    <option value="Failed">Failed</option>
                                </select>
                            `;
                        } else {
                            const statusClass = row.inspection_status === "Passed" ? "inspection-passed" : 
                                               (row.inspection_status === "Failed" ? "inspection-failed" : "inspection-partial");
                            inspectionColumn = `<span class="inspection-status ${statusClass}">${row.inspection_status}<br>${row.inspection_date || ''}</span>`;
                        }
                        
                        table += `
                            <tr>
                                <td>${row.request_id}</td>
                                <td>${row.company_name || '-'}</td>
                                <td>${row.purchase_status || '-'}</td>
                                <td>${row.purchase_date || '-'}</td>
                                <td>${deliveryColumn}</td>
                                <td>${inspectionColumn}</td>
                                <td>${row.status || '-'}</td>
                            </tr>
                        `;
                    });
                    document.querySelector("#traceTable tbody").innerHTML = table;
                })
                .catch(error => {
                    console.error("Error loading trace orders:", error);
                });
        }
        
        function showRequisition() {
            document.getElementById("requisitionTable").classList.remove("hidden");
            document.getElementById("traceTable").classList.add("hidden");
            loadRequisitionTable();
        }
        
        function showTrace() {
            document.getElementById("requisitionTable").classList.add("hidden");
            document.getElementById("traceTable").classList.remove("hidden");
            loadTraceOrders();
        }
        
        function markDelivery(deliveryId) {
            if (!deliveryId) return;
            currentDeliveryId = deliveryId;
            document.getElementById("deliveryRequestId").innerText = deliveryId;
            document.getElementById("deliveryModal").classList.remove("hidden");
        }
        
        function closeDeliveryModal() {
            currentDeliveryId = null;
            document.getElementById("deliveryModal").classList.add("hidden");
        }
        
        function confirmDelivery() {
            if (!currentDeliveryId) return;
            
            const formData = new URLSearchParams();
            formData.append("action", "mark_delivery");
            formData.append("delivery_id", currentDeliveryId);
            
            callAPI(formData)
                .then(data => {
                    if (data.status === "success") {
                        alert("Delivery status updated successfully!");
                        closeDeliveryModal();
                        loadTraceOrders();
                    } else {
                        alert("Error updating delivery status: " + (data.message || "Unknown error"));
                    }
                })
                .catch(error => {
                    console.error("Error updating delivery:", error);
                    alert("Failed to update delivery status. Please try again.");
                });
        }
        
        function updateInspection(inspectionId, status) {
            if (!status || status === "") return;
            currentInspectionId = inspectionId;
            selectedInspectionStatus = status;
            document.getElementById("inspectionStatusText").innerText = status;
            document.getElementById("inspectionModal").classList.remove("hidden");
        }
        
        function closeInspectionModal() {
            currentInspectionId = null;
            selectedInspectionStatus = null;
            document.getElementById("inspectionModal").classList.add("hidden");
        }
        
        function confirmInspection() {
            if (!currentInspectionId || !selectedInspectionStatus) return;
            
            const formData = new URLSearchParams();
            formData.append("action", "update_inspection");
            formData.append("inspection_id", currentInspectionId);
            formData.append("status", selectedInspectionStatus);
            
            callAPI(formData)
                .then(data => {
                    if (data.status === "success") {
                        alert("Inspection status updated successfully!");
                        closeInspectionModal();
                        loadTraceOrders();
                    } else {
                        alert("Error updating inspection status: " + (data.message || "Unknown error"));
                    }
                })
                .catch(error => {
                    console.error("Error updating inspection:", error);
                    alert("Failed to update inspection status. Please try again.");
                });
        }
        
        function openQuotation(requestId, requestDate) {
            document.getElementById("q_request_id").innerText = requestId;
            document.getElementById("q_request_date").innerText = "Request Date: " + requestDate;
            
            const formData = new URLSearchParams();
            formData.append("action", "get_request_items");
            formData.append("request_id", requestId);
            
            callAPI(formData)
                .then(data => {
                    quotationData = data;
                    let html = "";
                    data.forEach((row, index) => {
                        html += `
                            <tr>
                                <td>${escapeHtml(row.item_name)}</td>
                                <td>${row.item_quantity}</td>
                                <td>
                                    <input type="number" 
                                           step="0.01" 
                                           onchange="calculateTotal(${index})" 
                                           onkeyup="calculateTotal(${index})"
                                           id="price_${index}" 
                                           class="price-input"
                                           value="0">
                                </td>
                                <td id="total_${index}">0.00</td>
                            </tr>
                        `;
                    });
                    document.getElementById("quotationBody").innerHTML = html;
                    document.getElementById("quotationModal").classList.remove("hidden");
                })
                .catch(error => {
                    console.error("Error loading quotation items:", error);
                    alert("Failed to load items for quotation.");
                });
        }
        
        function calculateTotal(index) {
            const quantity = parseInt(quotationData[index]?.item_quantity) || 0;
            const priceInput = document.getElementById(`price_${index}`);
            const price = parseFloat(priceInput.value) || 0;
            const total = quantity * price;
            document.getElementById(`total_${index}`).innerText = total.toFixed(2);
        }
        
        function confirmApprove() {
            if (confirm("Are you sure you want to approve this quotation?")) {
                approveQuotation();
            }
        }
        
        function approveQuotation() {
            const requestId = document.getElementById("q_request_id").innerText;
            const comment = document.getElementById("quotation_comment").value;
            let items = [];
            let totalAmount = 0;
            
            if (!requestId) {
                alert("Invalid Request ID");
                return;
            }
            
            // Collect item data
            quotationData.forEach((row, index) => {
                const priceInput = document.getElementById(`price_${index}`);
                const priceEach = parseFloat(priceInput.value) || 0;
                const quantity = parseInt(row.item_quantity) || 0;
                const total = priceEach * quantity;
                
                totalAmount += total;
                items.push({
                    item_id: row.item_id,
                    total: total
                });
            });
            
            if (totalAmount === 0) {
                alert("Please enter valid prices for items.");
                return;
            }
            
            const formData = new URLSearchParams();
            formData.append("action", "approve_quotation");
            formData.append("request_id", requestId);
            formData.append("items", JSON.stringify(items));
            formData.append("total_amount", totalAmount);
            formData.append("comment", comment);
            
            callAPI(formData)
                .then(data => {
                    if (data.status === "success") {
                        alert("Quotation Approved Successfully!");
                        closeQuotationModal();
                        loadRequisitionTable();
                        loadProcurementSummary();
                    } else {
                        alert("Error: " + (data.message || "Failed to approve quotation"));
                    }
                })
                .catch(error => {
                    console.error("Error approving quotation:", error);
                    alert("Failed to approve quotation. Please try again.");
                });
        }
        
        function closeQuotationModal() {
            document.getElementById("quotationModal").classList.add("hidden");
            quotationData = [];
        }
        
        function printReport(requestId) {
            const formData = new URLSearchParams();
            formData.append("action", "get_report_data");
            formData.append("request_id", requestId);
            
            callAPI(formData)
                .then(data => {
                    if (!data || data.length === 0) {
                        alert("No data found for this request");
                        return;
                    }
                    
                    const requestDate = data[0].request_date;
                    const totalAmount = data[0].total_amount;
                    let rows = "";
                    
                    data.forEach(item => {
                        rows += `
                            <tr>
                                <td>${item.item_id}</td>
                                <td>${escapeHtml(item.item_name)}</td>
                                <td>${item.item_quantity}</td>
                                <td>₱${parseFloat(item.price).toFixed(2)}</td>
                            </tr>
                        `;
                    });
                    
                    const printWindow = window.open('', '_blank', 'width=900,height=700');
                    printWindow.document.write(`
                        <html>
                            <head>
                                <title>Quotation Report - ${requestId}</title>
                                <style>
                                    body { font-family: Arial, sans-serif; padding: 20px; }
                                    h2, h4 { text-align: center; color: #8b5e3c; }
                                    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                                    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                                    th { background-color: #f8f3ef; }
                                    .total { font-size: 18px; font-weight: bold; margin-top: 20px; text-align: right; }
                                </style>
                            </head>
                            <body>
                                <h2>Code Latte Coffee Shop</h2>
                                <h4>Quotation Report</h4>
                                <p><strong>Request Date:</strong> ${requestDate}</p>
                                <p><strong>Request ID:</strong> ${requestId}</p>
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Item ID</th>
                                            <th>Item Name</th>
                                            <th>Quantity</th>
                                            <th>Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>${rows}</tbody>
                                </table>
                                <div class="total">Total Amount: ₱${parseFloat(totalAmount).toFixed(2)}</div>
                                <script>
                                    window.onload = function() {
                                        window.print();
                                        setTimeout(function() { window.close(); }, 1000);
                                    };
                                <\/script>
                            </body>
                        </html>
                    `);
                    printWindow.document.close();
                })
                .catch(error => {
                    console.error("Error generating report:", error);
                    alert("Failed to generate report");
                });
        }
        
        function openVerifyModal(button) {
            const row = button.closest('tr');
            const requestId = row.cells[1].innerText;
            const requestDate = row.cells[0].innerText;
            const quotationStatus = row.cells[2].innerText;
            const inventoryApproval = row.cells[5].innerText.split('<br>')[0];
            const financeApproval = row.cells[6].innerText.split('<br>')[0];
            
            // Validate approvals
            if (inventoryApproval === "Pending" || financeApproval === "Pending" || quotationStatus !== "Approved") {
                alert("Cannot verify: Missing required approvals.\n\n" +
                      `Quotation Status: ${quotationStatus}\n` +
                      `Inventory Approval: ${inventoryApproval}\n` +
                      `Finance Approval: ${financeApproval}`);
                return;
            }
            
            currentRequestId = requestId;
            
            const formData = new URLSearchParams();
            formData.append("action", "get_verify_details");
            formData.append("request_id", requestId);
            
            callAPI(formData)
                .then(data => {
                    currentProcurementId = data.procurement_id;
                    
                    document.getElementById("verifyDetails").innerHTML = `
                        <p><strong>Request ID:</strong> ${requestId}</p>
                        <p><strong>Request Date:</strong> ${requestDate}</p>
                        <p><strong>Quotation Date:</strong> ${data.quotation_date || 'N/A'}</p>
                        <p><strong>Approved by Inventory:</strong> ${data.inventory_date || 'N/A'}</p>
                        <p><strong>Approved by Finance:</strong> ${data.finance_date || 'N/A'}</p>
                    `;
                    
                    let itemsHTML = "";
                    data.items.forEach(item => {
                        itemsHTML += `
                            <tr>
                                <td>${item.item_id}</td>
                                <td>${escapeHtml(item.item_name)}</td>
                                <td>${item.item_quantity}</td>
                                <td>₱${parseFloat(item.price).toFixed(2)}</td>
                            </tr>
                        `;
                    });
                    document.getElementById("verifyItems").innerHTML = itemsHTML;
                    
                    let supplierHTML = '<option value="">-- Select Supplier --</option>';
                    data.suppliers.forEach(s => {
                        supplierHTML += `<option value="${s.supplier_id}">${escapeHtml(s.company_name)}</option>`;
                    });
                    document.getElementById("supplierSelect").innerHTML = supplierHTML;
                    
                    document.getElementById("verifyModal").classList.remove("hidden");
                })
                .catch(error => {
                    console.error("Error loading verification details:", error);
                    alert("Failed to load verification details");
                });
        }
        
        function closeVerifyModal() {
            document.getElementById("verifyModal").classList.add("hidden");
            currentRequestId = null;
            currentProcurementId = null;
        }
        
        function verifyRequisition() {
            const supplierId = document.getElementById("supplierSelect").value;
            
            if (!supplierId) {
                alert("Please select a supplier");
                return;
            }
            
            if (!currentRequestId || !currentProcurementId) {
                alert("Missing request information. Please try again.");
                return;
            }
            
            const formData = new URLSearchParams();
            formData.append("action", "verify_purchase");
            formData.append("request_id", currentRequestId);
            formData.append("procurement_id", currentProcurementId);
            formData.append("supplier_id", supplierId);
            
            callAPI(formData)
                .then(data => {
                    if (data.status === "success") {
                        alert("Purchase verified successfully!");
                        closeVerifyModal();
                        loadRequisitionTable();
                        loadTraceOrders();
                    } else {
                        alert("Error: " + (data.message || "Failed to verify purchase"));
                    }
                })
                .catch(error => {
                    console.error("Error verifying purchase:", error);
                    alert("Failed to verify purchase. Please try again.");
                });
        }

        function viewPrintReport(requestId) {
    const formData = new URLSearchParams();
    formData.append("action", "get_verified_report");
    formData.append("request_id", requestId);

    callAPI(formData)
    .then(data => {
        if (!data || data.length === 0) {
            alert("No data found");
            return;
        }

        let first = data[0];

        let totalQty = 0;
        let totalAmount = 0;  // ADD THIS LINE - initialize total amount
        let rows = "";

        data.forEach(item => {
            totalQty += parseInt(item.item_quantity);
            totalAmount += parseFloat(item.price) * parseInt(item.item_quantity);  // ADD THIS LINE - calculate total amount
            rows += `
                <tr>
                    <td>${item.item_id}</td>
                    <td>${escapeHtml(item.item_name)}</td>
                    <td>${item.item_quantity}</td>
                    <td>₱${parseFloat(item.price).toFixed(2)}</td>
                </tr>
            `;
        });

        const printWindow = window.open('', '_blank', 'width=900,height=700');

        printWindow.document.write(`
            <html>
            <head>
                <title>Approved Purchased Request Report</title>
                <style>
                    body { font-family: Arial; padding:20px; }
                    h2, h3 { text-align:center; }
                    table { width:100%; border-collapse:collapse; margin-top:20px; }
                    th, td { border:1px solid #000; padding:8px; }
                    th { background:#eee; }
                </style>
            </head>
            <body>

                <h2>Code Latte Coffee Shop</h2>
                <h3>Approved Purchased Request Report</h3>

                <p><b>Request ID:</b> ${first.request_id}</p>
                <p><b>Request Date:</b> ${first.request_date}</p>

                <p><b>Supplier ID:</b> ${first.supplier_id}</p>
                <p><b>Supplier Name:</b> ${first.company_name}</p>

                <p><b>Budget Approval on:</b> ${first.finance_approval_date}</p>
                <p><b>Inventory Approval on:</b> ${first.inventory_approval_date}</p>
                <p><b>Purchased & Verified on:</b> ${first.verification_date}</p>

                <p><b>Total Items (Quantity):</b> ${totalQty}</p>
                <p><b>Total Amount:</b> ₱${totalAmount.toFixed(2)}</p>

                <table>
                    <thead>
                        <tr>
                            <th>Item ID</th>
                            <th>Item Name</th>
                            <th>Quantity</th>
                            <th>Price</th>
                        </tr>
                    </thead>
                    <tbody>${rows}</tbody>
                </table>

                <script>
                    window.onload = function(){
                        window.print();
                        setTimeout(() => window.close(), 1000);
                    }
                <\/script>

            </body>
            </html>
        `);

        printWindow.document.close();
    });
}
        
        // Helper function to escape HTML
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        // Sidebar toggle
        document.getElementById("menuToggle").addEventListener("click", function() {
            document.getElementById("sidebar").classList.toggle("expanded");
        });
        
        // Initialize on page load
        window.onload = function() {
            loadProcurementSummary();
            loadRequisitionTable();
            loadTraceOrders();
        };


        document.getElementById('searchRequest').addEventListener('keyup', function () {
    const value = this.value.toLowerCase();

    // REQUISITION TABLE
    const reqRows = document.querySelectorAll('#requisitionTable tbody tr');
    reqRows.forEach(row => {
        const requestId = row.cells[1].textContent.toLowerCase(); // Request ID column
        row.style.display = requestId.includes(value) ? '' : 'none';
    });

    // TRACE TABLE
    const traceRows = document.querySelectorAll('#traceTable tbody tr');
    traceRows.forEach(row => {
        const requestId = row.cells[0].textContent.toLowerCase(); // Request ID column
        row.style.display = requestId.includes(value) ? '' : 'none';
    });
});

document.getElementById('filterDate').addEventListener('change', function () {
    const selectedDate = this.value;

    const reqRows = document.querySelectorAll('#requisitionTable tbody tr');
    const traceRows = document.querySelectorAll('#traceTable tbody tr');

    // -------- REQUISITION TABLE (Request Date = column 0) --------
    reqRows.forEach(row => {
        const rowDate = row.cells[0].textContent.trim();

        row.style.display =
            selectedDate === "" || rowDate.includes(selectedDate)
            ? ""
            : "none";
    });

    // -------- TRACE TABLE (Purchase Date = column 3) --------
    traceRows.forEach(row => {
        const rowDate = row.cells[3].textContent.trim();

        row.style.display =
            selectedDate === "" || rowDate.includes(selectedDate)
            ? ""
            : "none";
    });
});

function filterByDate() {
    const selectedDate = document.getElementById('filterDate').value;

    if (!selectedDate) {
        alert("Please select a date");
        return;
    }

    // -------- REQUISITION TABLE (column 0 = request date) --------
    const reqRows = document.querySelectorAll('#requisitionTable tbody tr');
    reqRows.forEach(row => {
        const rowDate = row.cells[0].textContent.trim();

        row.style.display = rowDate.includes(selectedDate) ? '' : 'none';
    });

    // -------- TRACE TABLE (column 3 = purchase date) --------
    const traceRows = document.querySelectorAll('#traceTable tbody tr');
    traceRows.forEach(row => {
        const rowDate = row.cells[3].textContent.trim();

        row.style.display = rowDate.includes(selectedDate) ? '' : 'none';
    });
}

function resetFilter() {
    document.getElementById('filterDate').value = '';

    const rows = document.querySelectorAll('#requisitionTable tbody tr, #traceTable tbody tr');
    rows.forEach(row => row.style.display = '');
}
    </script>
</body>
</html>