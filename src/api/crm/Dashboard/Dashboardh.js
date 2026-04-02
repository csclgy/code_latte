// Dashboard.js

// Sidebar toggle
const sidebar = document.getElementById('sidebar');
const toggle = document.getElementById('menuToggle');

if (toggle) {
    toggle.addEventListener('click', () => {
        sidebar.classList.toggle('expanded');
    });
}

// ================================
// LOAD DASHBOARD DATA FROM config
// ================================

// Load Total Suppliers
function loadTotalSuppliers() {
    console.log('Loading suppliers...');
    fetch('../config/input.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=get_total_suppliers'
    })
    .then(response => {
        console.log('Suppliers response status:', response.status);
        return response.text();
    })
    .then(data => {
        console.log('Suppliers data:', data);
        const supplierElement = document.getElementById("totalSuppliers");
        if (supplierElement) {
            supplierElement.innerHTML = data;
        } else {
            console.error('Supplier element not found');
        }
    })
    .catch(error => console.error('Error loading suppliers:', error));
}



// Load Total Requests Today
function loadTotalRequestsToday() {
    console.log('Loading requests...');
    fetch('../config/input.php', {
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
    fetch('../config/input.php', {
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
    fetch('../config/input.php', {
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
    fetch('../config/input.php',{
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

// ==================================
// PAGE LOAD
// ==================================
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing dashboard...');
    
    // Call all functions
    loadTotalSuppliers();
    loadTotalItems();
    loadTotalRequestsToday();
    loadTotalIncome();
    loadNotifications();
    loadWeeklyChart();
});