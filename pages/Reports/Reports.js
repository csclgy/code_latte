// Sidebar toggle
const sidebar = document.getElementById('sidebar');
const toggle = document.getElementById('menuToggle');
toggle.addEventListener('click', () => sidebar.classList.toggle('expanded'));
document.addEventListener("DOMContentLoaded", function(){

    loadTotalFulfilled();
    loadTotalPurchased();
    loadTotalExpenses();

});

/* TOTAL FULFILLED REQUEST */
function loadTotalFulfilled(){

    fetch('../../../config/Reportsdb.php',{
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:'action=get_total_fulfilled'
    })
    .then(res => res.text())
    .then(data=>{
        document.getElementById("totalFulfilled").innerHTML = data;
    });

}

/* TOTAL PURCHASED */
function loadTotalPurchased(){

    fetch('../../../config/Reportsdb.php',{
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:'action=get_total_purchased'
    })
    .then(res => res.text())
    .then(data=>{
        document.getElementById("totalPurchased").innerHTML = data;
    });

}

/* TOTAL EXPENSES */
function loadTotalExpenses(){

    fetch('../../../config/Reportsdb.php',{
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:'action=get_total_expenses'
    })
    .then(res => res.text())
    .then(data=>{
        document.getElementById("totalExpenses").innerHTML = data;
    });

}
// Charts
window.onload = function() {
    // PIE CHART - Customer Ratings
    new Chart(document.getElementById('ratingChart'), {
        type: 'pie',
        data: {
            labels: ['Sugar', 'Pearl', 'Cup', 'Straw', 'Crashed Ice'],
            datasets: [{
                data: [60,25,8,4,3],
                backgroundColor: ['#8b5e3c','#a47148','#c19a6b','#d8b384','#f5deb3']
            }]
        },
        options: { responsive: true }
    });

    // BAR CHART - Monthly Customers
    new Chart(document.getElementById('customerChart'), {
        type: 'bar',
        data: {
            labels: ['Jan','Feb','Mar','Apr','May','Jun'],
            datasets: [{ label: 'Customers', data: [250,300,280,350,320,400], backgroundColor: '#8b5e3c' }]
        },
        options: { responsive: true, plugins: { legend: { display: false } } }
    });

    // LINE CHART - Income Rate
    new Chart(document.getElementById('incomeChart'), {
        type: 'line',
        data: {
            labels: ['Jan','Feb','Mar','Apr','May','Jun'],
            datasets: [{
                label: 'Income ($)',
                data: [20000,22000,21000,25000,24000,27000],
                borderColor: '#8b5e3c',
                backgroundColor: 'rgba(139,94,60,0.2)',
                tension: 0.4, fill: true
            }]
        },
        options: { responsive: true, plugins: { legend: { display: false } } }
    });
};

// Filter requisition table
document.getElementById('requisitionSearch').addEventListener('keyup', function() {
    const filter = this.value.toUpperCase();
    const table = document.getElementById('requisitionTable');
    const tr = table.getElementsByTagName('tr');

    for (let i=1; i<tr.length; i++) {
        const tdArr = tr[i].getElementsByTagName('td');
        let rowText = '';
        for (let j=0; j<tdArr.length-1; j++) rowText += tdArr[j].textContent.toUpperCase() + ' ';
        tr[i].style.display = rowText.indexOf(filter) > -1 ? '' : 'none';
    }
});