async function loadAnalytics() {
    console.log("Loading analytics...");
    try {
        const response = await fetch('/service-pro/actions/admin/get_analytics.php');
        const data = await response.json();
        console.log(data);
        if (data.success) {
            // Update Status Counts
            document.getElementById('total-bookings').textContent = data.stats.total || 0;
            document.getElementById('completed-bookings').textContent = data.stats.completed || 0;
            document.getElementById('cancelled-bookings').textContent = data.stats.cancelled || 0;


            const formatter = new Intl.NumberFormat('en-PH', {
                style: 'currency',
                currency: 'PHP',
            });
            console.log(data);

            document.getElementById('daily-earn').textContent = formatter.format(data.earnings.daily || 0);
            document.getElementById('weekly-earn').textContent = formatter.format(data.earnings.weekly || 0);
            document.getElementById('monthly-earn').textContent = formatter.format(data.earnings.monthly || 0);

            // Create Earnings Chart
            const earningsCtx = document.getElementById('earningsChart').getContext('2d');
            new Chart(earningsCtx, {
                type: 'bar',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    datasets: [{
                        label: 'Earnings (₱)',
                        data: data.monthlyEarnings,
                        backgroundColor: 'rgba(54, 162, 235, 0.6)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Create Bookings Chart
            const bookingsCtx = document.getElementById('bookingsChart').getContext('2d');
            new Chart(bookingsCtx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    datasets: [{
                        label: 'Bookings',
                        data: data.monthlyBookings,
                        backgroundColor: 'rgba(75, 192, 192, 0.6)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 2,
                        fill: false
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
    } catch (error) {
        console.error("Analytics Load Failed:", error);
    }
}

// Call it when the page loads
document.addEventListener('DOMContentLoaded', loadAnalytics);