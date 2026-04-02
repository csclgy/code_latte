
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Supplier Management</title>

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Custom CSS -->
<link rel="stylesheet" href="../../../../assets/css/SupplierM.css">
<style>
    /* Modal background */
.modal {
    display: none; /* 🔥 THIS FIXES YOUR ISSUE */
    position: fixed;
    z-index: 999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);

    /* center content */
    justify-content: center;
    align-items: center;
}

/* Show modal when active */
.modal.show {
    display: flex;
}

/* Modal box */
.modal-content {
    background: #fff;
    padding: 25px;
    border-radius: 10px;
    width: 400px;
    max-width: 90%;
    text-align: left;
    position: relative;
}

/* Close button (X) */
.close-btn {
    position: absolute;
    right: 15px;
    top: 10px;
    font-size: 20px;
    cursor: pointer;
}

/* Back button */
.btn-back {
    margin-top: 15px;
    padding: 10px 15px;
    background: #8B5E3C;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

.btn-renew {
    background-color: orange;
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: 5px;
    cursor: pointer;
    font-weight: bold;
}

.btn-renew:hover {
    background-color: darkorange;
}
</style>


</head>
<body>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="menu-toggle" id="menuToggle"><i class="fa-solid fa-bars"></i></div>
    <nav>
        <a href="../Dashboard/Dashboard.php"><i class="fa-solid fa-box"></i> <span class="link-text">Dashboard</span></a>
        <a href="../Reports/Reports.php"><i class="fa-solid fa-chart-line"></i> <span class="link-text">Report Analytics</span></a>
        <a href="../Supplier/SupplierM.php"><i class="fa-solid fa-users"></i> <span class="link-text">Suppliers</span></a>
        <a href="../Procurement/Procurement.php"><i class="fa fa-folder-open"></i> <span class="link-text">Requisition</span></a>
        <a href="#"><i class="fa fa-sign-out"></i> <span class="link-text">Logout</span></a>
    </nav>
</div>

<!-- Main Content -->
<div class="dashboard-container">


    <!-- PAGE HEADER -->
    <div class="page-header-container">
        <div class="page-header">
            Supplier Management
        </div>
        
    </div>

    <hr class="header-divider">

    <!-- Supplier Management Content -->
    <div class="supplier-management">

        <!-- Summary Cards -->
        <di class="summary-cards">
            <div class="summary-card">
                <div class="card-icon"><i class="fa-solid fa-users"></i></div>
                <div class="card-content">
                    <span class="card-label">Total Active Suppliers</span>
                    <span class="card-value" id="totalSuppliers">0</span>
                </div>
            </div>
            <div class="summary-card renew">
                <div class="card-icon"><i class="fa-solid fa-rotate"></i></div>
                <div class="card-content">
                    <span class="card-label">Need Renewal</span>
                    <span class="card-value" id="needRenewal">0</span>
                </div>
            </div>
            <div class="summary-card warning">
                <div class="card-icon"><i class="fa-solid fa-clock"></i></div>
                <div class="card-content">
                    <span class="card-label">Expiring Soon Contract</span>
                    <span class="card-value" id="almostOver">0</span>
                </div>
            </div>
        </div>

        <!-- Suppliers Table -->
        <div class="suppliers-container">
            <div class="container-header">
                <h3>Supplier List</h3>
                <div class="header-actions">
                    <input type="text" placeholder="Search suppliers..." id="searchSupplier" class="search-input">
                    <button class="btn-refresh" onclick="refreshSupplierList()">
                        <i class="fa-solid fa-rotate-right"></i>
                    </button>
                </div>
            </div>

            <div class="table-container">
                <table class="suppliers-table" id="suppliersTable">
    <thead>
        <tr>
            <th>Supplier ID</th>
            <th>Company/Supplier Name</th>
            <th>Status</th>
            <th>Action</th>
            <th>View More</th>
        </tr>
    </thead>
    <tbody id="suppliersTableBody">
        <!-- Supplier rows will be inserted dynamically -->
    </tbody>
    </table>
            </div>
            
        </div>
        

        <!-- Registration Form -->
        <div class="registration-container">
            <div class="container-header">
                <h3>New Supplier Registration</h3>
            </div>

            <form class="supplier-form" id="supplierForm" onsubmit="return registerSupplier(event)">
                <div class="form-row">
                    <div class="form-group">
                        <label>Company Name *</label>
                        <input type="text" id="companyName" required>
                    </div>
                    <div class="form-group">
                        <label>Address *</label>
                        <input type="text" id="address" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Contact Person *</label>
                        <input type="text" id="contactPerson" required>
                    </div>
                    <div class="form-group">
                        <label>Position *</label>
                        <input type="text" id="position" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Contact Number *</label>
                        <input type="tel" id="contactNumber" required>
                    </div>
                    <div class="form-group">
                        <label>Email *</label>
                        <input type="email" id="email" required>
                    </div>
                </div>

                <div class="form-agreement">
                    <label><input type="checkbox" id="acceptRules" required> Agree to Rules and Regulations</label>
                    <label><input type="checkbox" id="acceptTerms" required> Accept Terms and Privacy Policy</label>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-submit">Register Supplier</button>
                    <button type="reset" class="btn-reset">Clear Form</button>
                </div>
            </form>
        </div>
        

    </div>
    

</div>



<!-- JS -->
<script src="SupplierM.js"></script>

<script>
// Fetch supplier summary counts from SupplierMdb.php
function loadSupplierSummary() {
    fetch('../../../config/SupplierMdb.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('totalSuppliers').textContent = data.totalActive;
                document.getElementById('needRenewal').textContent = data.needRenewal;
                document.getElementById('almostOver').textContent = data.almostOver;
            } else {
                document.getElementById('totalSuppliers').textContent = 0;
                document.getElementById('needRenewal').textContent = 0;
                document.getElementById('almostOver').textContent = 0;
            }
        })
        .catch(error => {
            console.error('Error fetching supplier summary:', error);
            document.getElementById('totalSuppliers').textContent = 0;
            document.getElementById('needRenewal').textContent = 0;
            document.getElementById('almostOver').textContent = 0;
        });
}

// Fetch summary cards + supplier list from SupplierMdb.php
function loadSupplierData() {
    fetch('../../../config/SupplierMdb.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // --- Summary Cards ---
                document.getElementById('totalSuppliers').textContent = data.totalActive;
                document.getElementById('needRenewal').textContent = data.needRenewal;
                document.getElementById('almostOver').textContent = data.almostOver;

                // --- Supplier List Table ---
                const tbody = document.getElementById('suppliersTableBody');
                tbody.innerHTML = '';

                if (data.suppliers.length > 0) {
                    data.suppliers.forEach(supplier => {
                        let statusClass = 'status-badge';
                        if (supplier.status === 'Active') statusClass += ' active';
                        else if (supplier.status === 'Expired') statusClass += ' renew';
                        else statusClass += ' warning';

                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${supplier.supplier_id}</td>
                            <td>${supplier.company_name}</td>
                            <td>
                                ${
                                    supplier.status === 'Expired'
                                    ? `<button class="btn-renew" onclick="confirmRenew('${supplier.supplier_id}', '${supplier.company_name}')">
                                            Renew
                                    </button>`
                                    : `<span class="${statusClass}">${supplier.status}</span>`
                                }
                            </td>

                            <td>
    <button class="btn-view" onclick="window.open('ViewContract.php?supplier_id=${supplier.supplier_id}', '_blank')">
        View Contract
    </button>
</td>
                            <td>
                                <button class="btn-view" onclick='openModal(${JSON.stringify(supplier).replace(/'/g, "&#39;").replace(/"/g, '&quot;')})'>
                                    <i class="fa-solid fa-eye"></i> View More
                                </button>
                            </td>
                        `;
                        tbody.appendChild(row);
                    });
                } else {
                    tbody.innerHTML = `<tr><td colspan="5" style="text-align:center;">No suppliers found</td></tr>`;
                }

            } else {
                console.error('Failed to load supplier data.');
            }
        })
        .catch(error => {
            console.error('Error fetching supplier data:', error);
        });
}





async function registerSupplier(event) {
    event.preventDefault();

    const formData = new FormData();
    formData.append('action', 'registerSupplier'); // Add action
    formData.append('company_name', document.getElementById('companyName').value);
    formData.append('address', document.getElementById('address').value);
    formData.append('contact_person', document.getElementById('contactPerson').value);
    formData.append('position', document.getElementById('position').value);
    formData.append('contact_number', document.getElementById('contactNumber').value);
    formData.append('email', document.getElementById('email').value);

    try {
        const response = await fetch('../../../config/SupplierMdb.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();

        if (result.success) {
            alert(`Supplier Registered!\nSupplier ID: ${result.supplier_id}\nContract ID: ${result.contract_id}`);
            document.getElementById('supplierForm').reset();
            loadSupplierData();    // Refresh table
            loadSupplierSummary(); // Refresh summary cards
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    }

    return false;
}

function openModal(supplier) {
    document.getElementById('modalSupplierName').textContent = supplier.company_name || '-';
    document.getElementById('modalSupplierId').textContent = supplier.supplier_id || '-';
    document.getElementById('modalContractSigned').textContent = supplier.created_date || 'Not specified';
    document.getElementById('modalExpiryDate').textContent = supplier.expiry_date || 'Not specified';
    document.getElementById('modalContactPerson').textContent = supplier.contact_person || '-';
    document.getElementById('modalPosition').textContent = supplier.position || '-';
    document.getElementById('modalContactNumber').textContent = supplier.contact_number || '-';
    document.getElementById('modalEmail').textContent = supplier.email || '-';

    document.getElementById('supplierModal').classList.add('show');
}

function closeModal() {
    document.getElementById('supplierModal').classList.remove('show');
}

window.onclick = function(event) {
    const modal = document.getElementById('supplierModal');
    if (event.target === modal) {
        closeModal();
    }
}

let selectedSupplierId = null;

// Open confirmation modal
function confirmRenew(supplierId, companyName) {
    selectedSupplierId = supplierId;

    document.getElementById('renewText').innerHTML =
        `Are you sure you want to renew the contract for <strong>${companyName}</strong>?<br><br>
         This will extend the contract for another year.`;

    document.getElementById('renewModal').classList.add('show');
}

// Close modal
function closeRenewModal() {
    document.getElementById('renewModal').classList.remove('show');
    selectedSupplierId = null;
}

// Process renewal
async function processRenew() {
    if (!selectedSupplierId) return;

    const newExpiryDate = new Date();
    newExpiryDate.setFullYear(newExpiryDate.getFullYear() + 1);

    const formattedDate = newExpiryDate.toISOString().split('T')[0];

    const formData = new FormData();
    formData.append('action', 'renewContract');
    formData.append('supplier_id', selectedSupplierId);
    formData.append('new_expiry_date', formattedDate);

    try {
        const response = await fetch('../../../config/SupplierMdb.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            alert('✅ Contract successfully renewed!');
            closeRenewModal();
            loadSupplierData();
            loadSupplierSummary();
        } else {
            alert('❌ ' + result.message);
        }

    } catch (error) {
        console.error(error);
        alert('Error processing renewal.');
    }
}

document.getElementById('searchSupplier').addEventListener('keyup', function () {
    const searchValue = this.value.toLowerCase();
    const rows = document.querySelectorAll('#suppliersTableBody tr');

    rows.forEach(row => {
        const supplierId = row.cells[0].textContent.toLowerCase();
        const supplierName = row.cells[1].textContent.toLowerCase();

        if (supplierId.includes(searchValue) || supplierName.includes(searchValue)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});


// Load on page load
window.addEventListener('DOMContentLoaded', loadSupplierSummary);
window.addEventListener('DOMContentLoaded', loadSupplierData);
</script>
<!-- MODAL -->
<div id="supplierModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal()">&times;</span>

        <h2>Supplier Details</h2>

        <p><strong>Name:</strong> <span id="modalSupplierName"></span></p>
        <p><strong>ID:</strong> <span id="modalSupplierId"></span></p>
        <p><strong>Contract Signed:</strong> <span id="modalContractSigned"></span></p>
        <p><strong>Expiry Date:</strong> <span id="modalExpiryDate"></span></p>
        <p><strong>Contact Person:</strong> <span id="modalContactPerson"></span></p>
        <p><strong>Position:</strong> <span id="modalPosition"></span></p>
        <p><strong>Contact Number:</strong> <span id="modalContactNumber"></span></p>
        <p><strong>Email:</strong> <span id="modalEmail"></span></p>

        <button onclick="closeModal()" class="btn-back">Back</button>
    </div>
</div>

<!-- RENEW CONFIRMATION MODAL -->
<div id="renewModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeRenewModal()">&times;</span>

        <h2>Contract Renewal</h2>
        <p id="renewText"></p>

        <div style="margin-top:20px; display:flex; justify-content:space-between;">
            <button onclick="closeRenewModal()" class="btn-back">Cancel</button>
            <button onclick="processRenew()" style="background:green; color:white; padding:10px 15px; border:none; border-radius:5px;">
                Confirm Renewal
            </button>
        </div>
    </div>
</div>
</body>
</html>