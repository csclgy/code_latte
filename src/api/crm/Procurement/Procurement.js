// ================= SIDEBAR TOGGLE =================
const sidebar = document.getElementById('sidebar');
const toggle = document.getElementById('menuToggle');
toggle.addEventListener('click', () => { sidebar.classList.toggle('expanded'); });

// ================= TABLE SWITCHING =================
function showRequisition() {
    document.getElementById("requisitionTable").classList.remove("hidden");
    document.getElementById("traceTable").classList.add("hidden");
    document.querySelectorAll(".control-btn")[0].classList.add("active");
    document.querySelectorAll(".control-btn")[1].classList.remove("active");
}

function showTrace() {
    document.getElementById("traceTable").classList.remove("hidden");
    document.getElementById("requisitionTable").classList.add("hidden");
    document.querySelectorAll(".control-btn")[1].classList.add("active");
    document.querySelectorAll(".control-btn")[0].classList.remove("active");
}

// ================= DELIVERY FUNCTION =================
function markDelivered(button) {
    const cell = button.parentElement;
    cell.innerHTML = "Delivered";
}

// ================= INSPECTION FUNCTION =================
function markInspection(button, type) {
    const cell = button.parentElement;
    if(type === 'check') cell.innerHTML = "Passed";
    else if(type === 'negative') cell.innerHTML = "Partially Passed";
}

// ================= VIEW ITEM MODAL =================
const modal = document.getElementById('itemModal');
const modalBody = document.getElementById('modalItemsBody');
const modalComment = document.getElementById('modalComment');
const modalRequestIdSpan = document.getElementById('modalRequestId');

// Example data for demonstration
const itemsData = {
    "REQ-001": [
        { name: "Laptop", productId: "LP-1001", quantity: 2 },
        { name: "Mouse", productId: "MS-2001", quantity: 5 }
    ],
    "REQ-002": [
        { name: "Printer", productId: "PR-3001", quantity: 1 }
    ]
};

// Attach event to all view buttons
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btn-view').forEach(btn => {
        btn.addEventListener('click', () => {
            const row = btn.closest('tr');
            const requestId = row.cells[1].innerText; // second cell = Request ID
            openModal(requestId);
        });
    });

    // Attach event to all verify buttons
    document.querySelectorAll('.btn-verify').forEach(btn => {
        btn.addEventListener('click', () => {
            const row = btn.closest('tr');
            openVerifyModal(row);
        });
    });
});

function openModal(requestId) {
    modalBody.innerHTML = ""; // clear previous
    modalComment.value = ""; // clear comment
    modalRequestIdSpan.innerText = requestId;
    const items = itemsData[requestId] || [];
    items.forEach(item => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><input type="checkbox"></td>
            <td>${item.name}</td>
            <td>${item.productId}</td>
            <td>${item.quantity}</td>
        `;
        modalBody.appendChild(tr);
    });
    modal.classList.remove('hidden');
}

function closeModal() {
    modal.classList.add('hidden');
}

function approveItems() {
    const requestId = modalRequestIdSpan.innerText;
    if (!requestId) return;

    // Find the row in requisition table that matches the request ID
    const table = document.querySelector('#requisitionTable tbody');
    if (!table) return;

    const rows = table.querySelectorAll('tr');
    let targetRow = null;
    for (let row of rows) {
        const cell = row.cells[1];
        if (cell && cell.innerText.trim() === requestId) {
            targetRow = row;
            break;
        }
    }

    if (targetRow) {
        // Quotation Status is the fourth cell (index 3)
        const statusCell = targetRow.cells[3];
        if (statusCell) {
            statusCell.innerHTML = '<span class="status approved">Approved</span>';
        }
    }

    const checkedItems = Array.from(modalBody.querySelectorAll('input[type="checkbox"]:checked'));
    const comment = modalComment.value;
    console.log("Approved Items:", checkedItems.length, "Comment:", comment);
    
    closeModal();
}

// ================= VERIFICATION MODAL (NEW) =================
const verifyModal = document.getElementById('verifyModal');
const verifyDetails = document.getElementById('verifyDetails');
const supplierSelect = document.getElementById('supplierSelect');
let currentVerifyRow = null; // store the row being verified

function openVerifyModal(row) {
    currentVerifyRow = row; // save the row

    // Extract cell values (0: Request Date, 1: Request ID, 2: Items button, 3: Quotation Status, 4: Total, 5: Inventory/Date, 6: Finance/Date)
    const requestDate = row.cells[0].innerText;
    const requestId = row.cells[1].innerText;
    const itemsCell = row.cells[2].innerHTML; // includes the button
    const quotationStatus = row.cells[3].innerText;
    const total = row.cells[4].innerText;
    const inventoryDate = row.cells[5].innerText;
    const financeDate = row.cells[6].innerText;

    // Populate details
    verifyDetails.innerHTML = `
        <p><strong>Request Date:</strong> ${requestDate}</p>
        <p><strong>Request ID:</strong> ${requestId}</p>
        <p><strong>Items:</strong> ${itemsCell}</p>
        <p><strong>Quotation Status:</strong> ${quotationStatus}</p>
        <p><strong>Total:</strong> ${total}</p>
        <p><strong>Inventory/Date:</strong> ${inventoryDate}</p>
        <p><strong>Finance/Date:</strong> ${financeDate}</p>
    `;

    // Reset supplier dropdown
    supplierSelect.value = "";

    // Show modal
    verifyModal.classList.remove('hidden');
}

function closeVerifyModal() {
    verifyModal.classList.add('hidden');
    currentVerifyRow = null;
}

function verifyRequisition() {
    if (!currentVerifyRow) {
        alert("No requisition selected.");
        return;
    }

    const supplier = supplierSelect.value;
    if (!supplier) {
        alert("Please select a supplier.");
        return;
    }

    // Update the Verification/Date cell (index 7)
    const verificationCell = currentVerifyRow.cells[7];
    const currentDate = new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
    verificationCell.innerHTML = `Verified (${currentDate})`;

    // Optional: you can also log the supplier for further processing
    console.log(`Requisition ${currentVerifyRow.cells[1].innerText} verified with supplier: ${supplier}`);

    // Close modal
    closeVerifyModal();
}


document.addEventListener("DOMContentLoaded", loadProcurementSummary);