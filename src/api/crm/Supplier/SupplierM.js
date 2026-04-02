document.addEventListener('DOMContentLoaded', function() {
    initializeSupplierModule();
});

function initializeSupplierModule() {
    loadSuppliersList(); // Load suppliers on page load
    updateSummaryCards();
    setupSearchListener();
}


// Display suppliers in the table
function displaySuppliers(suppliers) {
    const tableBody = document.getElementById('suppliersTableBody');
    if (!tableBody) return;
    
    tableBody.innerHTML = ''; // Clear existing rows
    
    if (suppliers.length === 0) {
        // Show empty state
        const emptyRow = document.createElement('tr');
        emptyRow.innerHTML = `
            <td colspan="4" style="text-align: center; padding: 30px;">
                No suppliers found
            </td>
        `;
        tableBody.appendChild(emptyRow);
        return;
    }
    
    suppliers.forEach(supplier => {
        const row = document.createElement('tr');
        
        // Determine status badge class
        let statusClass = '';
        let statusText = supplier.status;
        
        switch(supplier.status.toLowerCase()) {
            case 'active':
                statusClass = 'Active';
                break;
            case 'expired':
                statusClass = 'renew';
                statusText = 'Renew';
                break;
            case 'no contract':
                statusClass = 'inactive';
                break;
            default:
                statusClass = 'inactive';
        }
        
        row.innerHTML = `
            <td>${supplier.supplier_id}</td>
            <td>${supplier.company_name}</td>
            <td><span class="status-badge ${statusClass}">${statusText}</span></td>
            <td>
                <button class="btn-view" onclick="viewContract('${supplier.supplier_id}')">
                    <i class="fa-solid fa-file-contract"></i> View Contract
                </button>
            </td>
        `;
        tableBody.appendChild(row);
    });
}

function showErrorInTable() {
    const tableBody = document.getElementById('suppliersTableBody');
    if (tableBody) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="4" style="text-align: center; padding: 30px; color: #dc3545;">
                    <i class="fa-solid fa-exclamation-triangle"></i> 
                    Error loading suppliers. Please try again.
                </td>
            </tr>
        `;
    }
}


// Search suppliers in table
function setupSearchListener() {
    const searchInput = document.getElementById('searchSupplier');
    if (!searchInput) return;

    searchInput.addEventListener('input', function(e) {
        const term = e.target.value.toLowerCase().trim();
        const rows = document.getElementById('suppliersTableBody').getElementsByTagName('tr');
        
        // Skip if there's only one row and it's the "No suppliers found" or error message
        if (rows.length === 1 && rows[0].cells.length === 1) return;
        
        for (let i = 0; i < rows.length; i++) {
            if (rows[i].cells.length > 1) { // Skip message rows
                const id = rows[i].cells[0].textContent.toLowerCase();
                const name = rows[i].cells[1].textContent.toLowerCase();
                
                if (term === '') {
                    rows[i].style.display = '';
                } else {
                    rows[i].style.display = (id.includes(term) || name.includes(term)) ? '' : 'none';
                }
            }
        }
    });
}

// View contract (you can expand this to show actual contract details)
function viewContract(supplierId) {
    // You can redirect to a contract details page or show a modal
    window.location.href = `../Contracts/ContractView.php?supplier_id=${supplierId}`;
    // For now, just show an alert
    // alert(`Viewing contract for supplier: ${supplierId}`);
}

// Register new supplier
function registerSupplier(event) {
    event.preventDefault();

    if (!document.getElementById("acceptRules").checked ||
        !document.getElementById("acceptTerms").checked) {
        alert("Please accept the Rules and Terms.");
        return false;
    }

    const formData = {
        companyName: document.getElementById('companyName').value,
        address: document.getElementById('address').value,
        contactPerson: document.getElementById('contactPerson').value,
        position: document.getElementById('position').value,
        contactNumber: document.getElementById('contactNumber').value,
        email: document.getElementById('email').value
    };

    // Basic validation
    for (let key in formData) {
        if (!formData[key]) {
            alert('Please fill all required fields');
            return false;
        }
    }
    
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email)) {
        alert('Please enter a valid email address');
        return false;
    }
    
    if (!/^\d{11}$/.test(formData.contactNumber.replace(/\D/g, ''))) {
        alert('Contact number must be 11 digits');
        return false;
    }

    // Here you would typically send this data to your server
    // For now, just show success and reload suppliers
    alert('Supplier registered successfully!');
    document.getElementById('supplierForm').reset();
    
    // Reload the suppliers list
    loadSuppliersList();
    updateSummaryCards();
    
    return true;
}

// Refresh table
function refreshSupplierList() {
    loadSuppliersList();
    updateSummaryCards();
}

// Sidebar toggle
const sidebar = document.getElementById('sidebar');
const toggle = document.getElementById('menuToggle');
if (toggle) {
    toggle.addEventListener('click', () => sidebar.classList.toggle('expanded'));
}

// Global exports
window.viewContract = viewContract;
window.registerSupplier = registerSupplier;
window.refreshSupplierList = refreshSupplierList;