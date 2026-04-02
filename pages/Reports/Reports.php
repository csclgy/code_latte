<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reports - CRM</title>

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>    /* Sidebar base */
.sidebar {
    width: 70px; /* collapsed width */
    background: #8b5e3c;
    color: white;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    padding: 1rem 0;
    transition: width 0.3s ease;
    position: fixed; /* keep sidebar fixed */
    top: 0;
    left: 0;
    bottom: 0;
    z-index: 1000;
    overflow: hidden;

    font-family: 'Times New Roman', Times, serif; /* <-- ADD THIS */
}
.sidebar.expanded { width: 220px; }

/* Sidebar nav text */
nav a .link-text {
    font-weight: 200;
    font-size: 1.1rem;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.3s;
    font-family: 'Times New Roman', Times, serif; /* <-- ADD THIS */

}
.sidebar.expanded .link-text { opacity: 1; pointer-events: auto; }

/* Sidebar icons */
nav a i {
    font-size: 1.0rem;
    min-width: 30px;
    text-align: center;
}

/* Main Content */
.dashboard-container { flex: 1;  margin-left: 70px; display: flex; flex-direction: column; width: 100%; max-width: 1800px; padding: 1rem; }
.sidebar.expanded ~ .dashboard-container {
    margin-left: 220px; /* shift content right when expanded */
}

.menu-toggle { font-size: 1.5rem; cursor: pointer; padding: 0 1rem; margin-bottom: 2rem; }

nav { display: flex; flex-direction: column; gap: 1rem; width: 100%; }
nav a { color: white; text-decoration: none; display: flex; align-items: center; gap: 1rem; width: 100%; padding: 0.5rem 1rem; border-radius: 12px; transition: background 0.2s; white-space: nowrap; }
nav a:hover { background: rgba(255,255,255,0.2); }
nav a .link-text { font-weight: 600; font-size: 1.1rem; opacity: 0; pointer-events: none; transition: opacity 0.3s; }
.sidebar.expanded .link-text { opacity: 1; pointer-events: auto; }
nav a i { font-size: 1.3rem; min-width: 30px; text-align: center; }



/* Page Header */
.page-header-container { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; }
.page-header { font-size: 2.0rem; font-weight: 700; color: #543f30; }
.header-divider { border: 0; height: 1px; background-color: #464545; }

/* Analytics Module */
.analytics-summary { display: flex; gap: 20px; margin-bottom: 30px; }
.analytics-card { flex: 1; background: #fff; padding: 20px; border-radius: 15px; display: flex; align-items: center; gap: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); transition: 0.3s ease; }
.analytics-card:hover { transform: translateY(-5px); }
.card-icon { width: 75px; height: 75px; background: #8b5e3c; border-radius: 12px; display: flex; align-items: center; justify-content: center; }
.card-icon i { color: white; font-size: 22px; }
.card-title { font-size: 14px; color: #000; }
.card-value { font-size: 22px; font-weight: bold; color: #000; }

/* Grid Cards */
.analytics-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
.grid-card { background: #f0f0f0; padding: 20px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); min-height: 320px; }
.grid-card h3 { margin-top: 0; color: #000; }

/* Table */
.table-search { margin-bottom: 10px; }
.table-search input { width: 30%; padding: 8px 12px; border-radius: 8px; border: 1px solid #ccc; font-size: 13px; }
.requisition-table { width: 100%; border-collapse: collapse; margin-top: 5px; table-layout: fixed; font-size: 13px; }
.requisition-table th { background: #8b5e3c; color: white; padding: 10px; font-size: 14px; text-align: left; border-bottom: 2px solid #c8b29e; }
.requisition-table td { padding: 10px; border-bottom: 1px solid #e0d7ce; }
.requisition-table tbody tr:nth-child(even) { background: #f9f7f5; }
.view-btn { background: #8b5e3c; color: white; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 12px; }
.view-btn:hover { background: #6f4b2f; }

@media print {
    button {
        display: none; /* Hide all buttons when printing */
    }
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
        <a href="../Supplier/SupplierM.php"><i class="fa-solid fa-users"></i> <span class="link-text">Supplier</span></a>
        <a href="../Procurement/Procurement.php"><i class="fa fa-folder-open"></i> <span class="link-text">Requisition</span></a>
        <a href="#"><i class="fa fa-sign-out"></i> <span class="link-text">Logout</span></a>
    </nav>
</div>

<!-- Main Content -->
<div class="dashboard-container">

    <!-- Big Page Header -->
    <div class="page-header-container">
        <div class="page-header">
            Report Analytics
        </div>
       
        
    </div>
    <hr class="header-divider">

    <!-- Reports Analytics Module -->
    <div class="analytics-module">

        <!-- Summary Cards -->
        <div class="analytics-summary">
            <div class="analytics-card">
    <div class="card-icon"><i class="fa-solid fa-clipboard-check"></i></div>
    <div class="card-info">
        <div class="card-title">Total Fulfilled Requisition</div>
        <div class="card-value" id="totalFulfilled">0</div>
    </div>
</div>

<div class="analytics-card">
    <div class="card-icon"><i class="fa-solid fa-star"></i></div>
    <div class="card-info">
        <div class="card-title">Total Purchased</div>
        <div class="card-value" id="totalPurchased">0</div>
    </div>
</div>

<div class="analytics-card">
    <div class="card-icon"><i class="fa-solid fa-users"></i></div>
    <div class="card-info">
        <div class="card-title">Total Expenses</div>
        <div class="card-value">₱<span id="totalExpenses">0</span></div>
    </div>
</div>
            <div class="analytics-card">
                <div class="card-icon"><i class="fa-solid fa-chart-line"></i></div>
                <div class="card-info">
                    <div class="card-title">Inspection rate</div>
                    <div class="card-value">80%</div>
                </div>
            </div>
        </div>

        <!-- 2x2 Grid -->
        <div class="analytics-grid">

        
            <!-- Bar Chart -->
            <div class="grid-card">
                <h3>Monthly Total Purchased</h3>
                <canvas id="customerChart"></canvas>
            </div>

             <!-- Line Chart -->
            <div class="grid-card">
                <h3>Monthly Expenses</h3>
                <canvas id="incomeChart"></canvas>
            </div>


            <!-- Purchased Order Table - Update the table headers -->
<div class="grid-card">
    <h3>Purchased Order List</h3>
    <div class="table-search">
        <input type="text" id="requisitionSearch" placeholder="Search Purchased Order...">
    </div>
    <table class="requisition-table" id="requisitionTable">
        <thead>
            <tr>
                <th>Purchased ID</th>
                <th>Supplier Name</th>
                <th>Purchased Date</th>
                  <th>View</th>
            </tr>
        </thead>
        <tbody id="purchasedOrdersTableBody">
            <!-- Data will be loaded here dynamically -->
        </tbody>
    </table>
</div>

            <!-- Pie Chart -->
            <div class="grid-card">
                <h3>Most Requested Product</h3>
                <canvas id="ratingChart"></canvas>
            </div>
        


           

        </div>
    </div>

</div>

<!-- JAVASCRIPT MERGED HERE -->
<script>

/* SIDEBAR */
const sidebar = document.getElementById('sidebar');
const toggle = document.getElementById('menuToggle');

toggle.addEventListener('click', () => {
sidebar.classList.toggle('expanded');
});


/* LOAD DATA WHEN PAGE LOADS */
document.addEventListener("DOMContentLoaded", function(){

loadTotalFulfilled();
loadTotalPurchased();
loadTotalExpenses();
loadMonthlyRequests();
loadMonthlyExpenses();
loadTopProducts();
loadPurchasedOrders(); // Add this new function call

});


/* TOTAL FULFILLED */
function loadTotalFulfilled(){

fetch('../../../config/Reportsdb.php',{
method:'POST',
headers:{'Content-Type':'application/x-www-form-urlencoded'},
body:'action=get_total_fulfilled'
})
.then(res=>res.text())
.then(data=>{
document.getElementById("totalFulfilled").innerHTML=data;
});

}


/* TOTAL PURCHASED */
function loadTotalPurchased(){

fetch('../../../config/Reportsdb.php',{
method:'POST',
headers:{'Content-Type':'application/x-www-form-urlencoded'},
body:'action=get_total_purchased'
})
.then(res=>res.text())
.then(data=>{
document.getElementById("totalPurchased").innerHTML=data;
});

}


/* TOTAL EXPENSES */
function loadTotalExpenses(){

fetch('../../../config/Reportsdb.php',{
method:'POST',
headers:{'Content-Type':'application/x-www-form-urlencoded'},
body:'action=get_total_expenses'
})
.then(res=>res.text())
.then(data=>{
document.getElementById("totalExpenses").innerHTML=data;
});

}


/* MONTHLY REQUESTS FUNCTION */
function loadMonthlyRequests(){

fetch('../../../config/Reportsdb.php',{
method:'POST',
headers:{'Content-Type':'application/x-www-form-urlencoded'},
body:'action=get_monthly_requests'
})
.then(res=>res.json())
.then(data=>{
console.log('Monthly requests data:', data);
new Chart(document.getElementById('customerChart'),{
type:'bar',
data:{
labels:['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
datasets:[{
label: 'Monthly Purchased',
data:data,
backgroundColor:'#8b5e3c'
}]
},
options:{
plugins:{legend:{display:false}},
scales: {
y: {
beginAtZero: true,
ticks: {
stepSize: 1
}
}
}
}
});
})
.catch(error => {
console.error('Error loading monthly requests:', error);
});

}

/* MONTHLY EXPENSES FUNCTION */
function loadMonthlyExpenses(){

fetch('../../../config/Reportsdb.php',{
method:'POST',
headers:{'Content-Type':'application/x-www-form-urlencoded'},
body:'action=get_monthly_expenses'
})
.then(res=>res.json())
.then(data=>{
console.log('Monthly expenses data:', data);
new Chart(document.getElementById('incomeChart'),{
type:'line',
data:{
labels:['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
datasets:[{
label: 'Monthly Expenses',
data:data,
borderColor:'#8b5e3c',
backgroundColor:'rgba(139,94,60,0.2)',
tension:0.4,
fill:true
}]
},
options:{
plugins:{legend:{display:false}},
scales: {
y: {
beginAtZero: true,
ticks: {
callback: function(value) {
return '₱' + value.toLocaleString();
}
}
}
}
}
});
})
.catch(error => {
console.error('Error loading monthly expenses:', error);
});

}

/* TOP 5 MOST REQUESTED PRODUCTS */
function loadTopProducts(){

fetch('../../../config/Reportsdb.php',{
method:'POST',
headers:{'Content-Type':'application/x-www-form-urlencoded'},
body:'action=get_top_products'
})
.then(res=>res.json())
.then(data=>{
console.log('Top products data:', data);

// Default colors for the pie chart
const colors = ['#8b5e3c', '#a47148', '#c19a6b', '#d8b384', '#f5deb3'];

new Chart(document.getElementById('ratingChart'),{
type:'pie',
data:{
labels: data.labels,
datasets:[{
data: data.data,
backgroundColor: colors.slice(0, data.data.length)
}]
},
options:{
plugins:{
legend:{
position: 'top',
labels: {
boxWidth: 20,
padding: 20,
font: {
size: 15,
weight: 'bold'
}
}
},
tooltip: {
callbacks: {
label: function(context) {
let label = context.label || '';
let value = context.raw || 0;
let total = context.dataset.data.reduce((a, b) => a + b, 0);
let percentage = ((value / total) * 100).toFixed(1);
return label + ': ' + value + ' (' + percentage + '%)';
}
}
}
},
layout: {
padding: {
bottom: 20
}
}
}
});
})
.catch(error => {
console.error('Error loading top products:', error);
// Fallback to sample data if error occurs
new Chart(document.getElementById('ratingChart'),{
type:'pie',
data:{
labels:['Sugar','Pearl','Cup','Straw','Crushed Ice'],
datasets:[{
data:[60,25,8,4,3],
backgroundColor:['#8b5e3c','#a47148','#c19a6b','#d8b384','#f5deb3']
}]
},
options:{
plugins:{
legend:{
position: 'bottom',
labels: {
boxWidth: 15,
padding: 20,
font: {
size: 14,
weight: 'bold'
}
}
}
},
layout: {
padding: {
bottom: 20
}
}
}
});
});

}

/* NEW FUNCTION: LOAD PURCHASED ORDERS */
function loadPurchasedOrders(){

fetch('../../../config/Reportsdb.php',{
method:'POST',
headers:{'Content-Type':'application/x-www-form-urlencoded'},
body:'action=get_purchased_orders'
})
.then(res=>res.json())
.then(data=>{
console.log('Purchased orders data:', data);
displayPurchasedOrders(data);
})
.catch(error => {
console.error('Error loading purchased orders:', error);
});

}

/* DISPLAY PURCHASED ORDERS IN TABLE */
function displayPurchasedOrders(orders){

const tableBody = document.getElementById('purchasedOrdersTableBody');
tableBody.innerHTML = ''; // Clear existing rows

if(orders.length === 0){
    // Show no data message
    const row = document.createElement('tr');
    row.innerHTML = '<td colspan="4" style="text-align: center; padding: 20px;">No purchased orders found</td>';
    tableBody.appendChild(row);
    return;
}

// Loop through orders and create table rows
orders.forEach(order => {
    const row = document.createElement('tr');
    
    // Format date nicely
    const date = new Date(order.purchased_date);
    const formattedDate = date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit'
    });
    
    row.innerHTML = `
        <td>${order.purchased_id}</td>
        <td>${order.company_name}</td>
        <td>${formattedDate}</td>
        <td><button class="view-btn" data-id="${order.purchased_id}">View</button></td>
    `;
    tableBody.appendChild(row);
});

// Re-attach search functionality to work with new data
attachSearchFunctionality();

}

/* SEARCH TABLE FUNCTIONALITY */
function attachSearchFunctionality() {
const searchInput = document.getElementById('requisitionSearch');
const table = document.getElementById('requisitionTable');

searchInput.addEventListener('keyup', function(){
    const filter = this.value.toUpperCase();
    const tr = table.getElementsByTagName('tr');

    for(let i = 1; i < tr.length; i++){ // Start from 1 to skip header
        const tdArr = tr[i].getElementsByTagName('td');
        let rowText = '';
        
        for(let j = 0; j < tdArr.length - 1; j++){ // Exclude the View button column
            if(tdArr[j]) {
                rowText += tdArr[j].textContent.toUpperCase() + " ";
            }
        }
        
        tr[i].style.display = rowText.indexOf(filter) > -1 ? '' : 'none';
    }
});
}

// Initialize search functionality
document.addEventListener("DOMContentLoaded", function() {
attachSearchFunctionality();
});

/* VIEW BUTTON CLICK */
document.addEventListener('click', function(e){

if(e.target.classList.contains('view-btn')){

let id = e.target.getAttribute('data-id');

if(!id) return;

fetch('../../../config/Reportsdb.php',{
method:'POST',
headers:{'Content-Type':'application/x-www-form-urlencoded'},
body:'action=get_purchase_report&purchased_id='+id
})
.then(res=>res.json())
.then(data=>{

/* SET HEADER DATA */

document.getElementById("r_purchase_date").innerText = data.purchase_date;
document.getElementById("r_purchased_id").innerText = data.purchased_id;
document.getElementById("r_supplier").innerText = data.company_name;
document.getElementById("r_supplier_id").innerText = data.supplier_id;
document.getElementById("r_request_id").innerText = data.request_id;
document.getElementById("r_request_date").innerText = data.request_date;
document.getElementById("r_finance_date").innerText = data.finance_date;
document.getElementById("r_verified_date").innerText = data.verified_date;
document.getElementById("r_total").innerText = data.total_amount;
document.getElementById("r_delivery_date").innerText = data.delivery_date;
document.getElementById("r_inspection_status").innerText = data.inspection_status;

/* ITEMS TABLE */

let tbody = document.getElementById("reportItems");
tbody.innerHTML = "";

data.items.forEach(item => {

tbody.innerHTML += `
<tr>
<td style="border:1px solid #ddd;padding:8px;">${item.item_id}</td>
<td style="border:1px solid #ddd;padding:8px;">${item.item_name}</td>
<td style="border:1px solid #ddd;padding:8px;">₱${item.price}</td>
</tr>
`;

});

document.getElementById("purchaseReportModal").style.display="flex";

});

}

});


/* CLOSE REPORT */
function closeReport(){
document.getElementById("purchaseReportModal").style.display="none";
}


/* PRINT FUNCTION */
function printReport(){

let printContents = document.getElementById("printableArea").innerHTML;
let originalContents = document.body.innerHTML;

document.body.innerHTML = printContents;

window.print();

document.body.innerHTML = originalContents;

location.reload();

}

</script>

<!-- PURCHASE REPORT MODAL -->
<div id="purchaseReportModal" style="
display:none;
position:fixed;
top:0;
left:0;
width:100%;
height:100%;
background:rgba(0,0,0,0.6);
justify-content:center;
align-items:center;
z-index:2000;
">

<div id="purchaseReportContent" style="
background:white;
width:800px;
max-height:90%;
overflow:auto;
padding:30px;
border-radius:10px;
">

<div id="printableArea">
    <button onclick="printReport()" class="view-btn">
<i class="fa fa-print"></i> Print
</button>

<button onclick="closeReport()" class="view-btn" style="background:#999;">
Close
</button>

<h2 style="text-align:center;">CODE LATTE</h2>
<h3 style="text-align:center;">Purchased Order Report</h3>
<hr>

<div style="display:flex; justify-content:space-between; gap:40px;">

<!-- LEFT SIDE -->
<div style="flex:1;">

<p><b>Purchased On:</b> <span id="r_purchase_date"></span></p>
<p><b>Purchased ID:</b> <span id="r_purchased_id"></span></p>
<p><b>Supplier:</b> <span id="r_supplier"></span></p>
<p><b>Supplier ID:</b> <span id="r_supplier_id"></span></p>
<p><b>Request ID:</b> <span id="r_request_id"></span></p>
<p><b>Request Date:</b> <span id="r_request_date"></span></p>

</div>


<!-- RIGHT SIDE -->
<div style="flex:1; text-align:left;">

<p><b>Finance Approved in a day of:</b> <span id="r_finance_date"></span></p>
<p><b>Order Verified in the day of:</b> <span id="r_verified_date"></span></p>
<p><b>Delivery Date:</b> <span id="r_delivery_date"></span></p>
<p><b>Inspection Status:</b> <span id="r_inspection_status"></span></p>
<p><b>Total Amount:</b> ₱<span id="r_total"></span></p>

</div>

</div>

<br>

<table style="width:100%;border-collapse:collapse;">
<thead>
<tr style="background:#8b5e3c;color:white;">
<th style="padding:8px;border:1px solid #ddd;">Item ID</th>
<th style="padding:8px;border:1px solid #ddd;">Item Name</th>
<th style="padding:8px;border:1px solid #ddd;">Price</th>
</tr>
</thead>

<tbody id="reportItems"></tbody>

</table>

</div>

<br>



</div>
</div>

</body>
</html>