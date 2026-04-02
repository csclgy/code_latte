<?php
// DashboardContent.php
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CRM Dashboard</title>

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<!-- Dashboard CSS -->
<link rel="stylesheet" href="../../../../assets/css/Dashboard.css">
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

    <!-- Page Header -->
    <div class="page-header-container">
        <div class="page-header">
            Purchasing Management
        </div>
    </div>

    <hr class="header-divider">

    <!-- Metrics -->
    <div class="metric-row">

        <div class="metric-card">
            <div class="label">📦 Total Suppliers</div>
            <div class="value" id="totalSuppliers">10</div>
            <div class="metric-footer"><span class="trend-up">▲ Active</span> suppliers</div>
        </div>

        <div class="metric-card">
            <div class="label">🧮 Items in Stock</div>
            <div class="value" id="totalItems">0</div>
            <div class="metric-footer"><span class="stock-warning">▼ 12%</span> running out</div>
        </div>

        <div class="metric-card">
            <div class="label">💰 Weekly Total Expenses</div>
            <div class="value">₱<span id="totalIncome">0</span></div>
        </div>

        <div class="metric-card">
            <div class="label">📊 Total Request - Daily</div>
            <div class="value" id="totalRequests">0</div>
        </div>

    </div>

    <!-- Notifications & Chart -->
    <div class="panel-content">

        <!-- Notifications -->
        <div class="notifications-container">

            <div class="notifications-header">
                <h3>Recent Notifications & Messages</h3>
                <span class="notification-badge">5 new</span>
            </div>

            <div class="notifications-list" id="notificationList">
                </div>

        </div>

        <!-- Chart -->
        <div class="ratings-container">
    <div class="ratings-header">
        <h3>Weekly Requisition Overview</h3>
    </div>

    <div class="ratings-content">
        <canvas id="ratingsChart"></canvas>
    </div>
</div>

    </div>

</div>

<!-- Dashboard JS -->
<script src="Dashboardh.js"></script>

<!-- Load Supplier Count -->
<script>

function loadTotalSuppliers(){

    fetch('../../../config/input.php', {
        method: 'POST',
        headers:{
            'Content-Type':'application/x-www-form-urlencoded'
        },
        body:'action=get_total_suppliers'
    })  
    .then(response => response.text())
    .then(data => {
        document.getElementById("totalSuppliers").innerHTML = data;
    });

}
// Load Total Items in Stock
function loadTotalItems() {
    console.log('Loading items...');
    fetch('../../../config/input.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=get_total_items'
    })
    .then(response => response.text())
    .then(data => {
        console.log('Items data:', data);
        const itemElement = document.getElementById("totalItems");
        if (itemElement) {
            itemElement.innerHTML = data;
        } else {
            console.error('Items element not found');
        }
    })
    .catch(error => console.error('Error loading items:', error));
}

// Load Total Requests Today
function loadTotalRequestsToday() {
    console.log('Loading requests...');
    fetch('../../../config/input.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=get_total_requests_today'
    })
    .then(response => response.text())
    .then(data => {
        console.log('Requests data:', data);
        const requestElement = document.getElementById("totalRequests");
        if (requestElement) {
            requestElement.innerHTML = data;
        } else {
            console.error('Requests element not found');
        }
    })
    .catch(error => console.error('Error loading requests:', error));
}

// Load Weekly Income
function loadTotalIncome() {
    console.log('Loading income...');
    fetch('../../../config/input.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=get_total_income_today'
    })
    .then(response => response.text())
    .then(data => {
        console.log('Income data:', data);
        const incomeElement = document.getElementById("totalIncome");
        if (incomeElement) {
            incomeElement.innerHTML = data;
        } else {
            console.error('Income element not found');
        }
    })
    .catch(error => console.error('Error loading income:', error));
}

// Load Notifications
function loadNotifications() {
    console.log('Loading notifications...');
    fetch('../../../config/input.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=get_notifications'
    })
    .then(response => response.json())
    .then(data => {
        console.log('Notifications data:', data);
        const container = document.getElementById("notificationList");
        if (container) {
            container.innerHTML = "";

            if (data.length === 0) {
                container.innerHTML = '<div class="notification-item">No notifications</div>';
                return;
            }

            data.forEach(item => {
                const notification = `
                <div class="notification-item">
                    <div class="notification-icon">📦</div>
                    <div class="notification-content">
                        <span class="notification-text">
                            Inventory Dept has new request [ID: ${item.request_id}]
                        </span>
                        <span class="notification-date">${item.request_date}</span>
                    </div>
                </div>
                `;
                container.innerHTML += notification;
            });
        } else {
            console.error('Notification container not found');
        }
    })
    .catch(error => console.error('Error loading notifications:', error));
}

function loadWeeklyChart(){
    console.log('Loading chart...');
    fetch('../../../config/input.php',{
        method:'POST',
        headers:{
            'Content-Type':'application/x-www-form-urlencoded'
        },
        body:'action=get_weekly_requests'
    })
    .then(res => {
        console.log('Chart response status:', res.status);
        return res.json();
    })
    .then(data => {
        console.log('Chart data:', data);
        const canvas = document.getElementById("ratingsChart");
        if (!canvas) {
            console.error('Chart canvas not found');
            return;
        }
        
        const ctx = canvas.getContext("2d");

        if(window.weeklyChart){
            window.weeklyChart.destroy();
        }

        window.weeklyChart = new Chart(ctx,{
            type:'bar',
            data:{
                labels:data.labels,
                datasets:[{
                    label:"Requests",
                    data:data.values,
                    backgroundColor:"#8b5e3c",
                    borderRadius:6
                }]
            },
            options:{
                responsive:true,
                maintainAspectRatio:false,
                scales:{
                    y:{
                        beginAtZero:true,
                        ticks:{stepSize:1}
                    },
                    x:{
                        grid:{display:false}
                    }
                },
                plugins:{
                    legend:{display:false}
                }
            }
        });
        console.log('Chart created successfully');
    })
    .catch(err => console.error("Chart error:",err));
}



</script>

</body>
</html>