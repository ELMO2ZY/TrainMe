<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$page_title = "Admin Dashboard - TrainMe";
$user_name = $_SESSION['user_name'] ?? 'Admin';
$user_role = $_SESSION['user_role'] ?? 'admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="nav-container">
            <div class="logo">
                <div class="logo-icon">T</div>
                TrainMe
            </div>
            <nav>
                <ul class="nav-links">
                    <li><a href="#dashboard">Dashboard</a></li>
                    <li><a href="#campaigns">Campaigns</a></li>
                    <li><a href="#analytics">Analytics</a></li>
                </ul>
            </nav>
            <div class="nav-buttons">
                <span style="margin-right: 1rem; color: #374151;">Welcome, <?php echo htmlspecialchars($user_name); ?></span>
                <button onclick="logout()" class="btn btn-outline">Logout</button>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Dashboard Section -->
        <section id="dashboard" class="dashboard">
            <div class="dashboard-container">
                <h2>Admin Dashboard</h2>
                <div class="dashboard-stats">
                    <div class="stat-card">
                        <h3>Total Campaigns</h3>
                        <div class="stat-number" id="total-campaigns">0</div>
                    </div>
                    <div class="stat-card">
                        <h3>Active Campaigns</h3>
                        <div class="stat-number" id="active-campaigns">0</div>
                    </div>
                    <div class="stat-card">
                        <h3>Total Recipients</h3>
                        <div class="stat-number" id="total-recipients">0</div>
                    </div>
                    <div class="stat-card">
                        <h3>Click Rate</h3>
                        <div class="stat-number" id="click-rate">0%</div>
                    </div>
                    <div class="stat-card">
                        <h3>Report Rate</h3>
                        <div class="stat-number" id="report-rate">0%</div>
                    </div>
                    <div class="stat-card">
                        <h3>Success Rate</h3>
                        <div class="stat-number" id="success-rate">0%</div>
                    </div>
                </div>
                
                <div class="dashboard-content">
                    <div class="dashboard-section">
                        <h3>Department Performance</h3>
                        <div id="department-stats" class="department-stats">
                            <!-- Department stats will be loaded here -->
                        </div>
                    </div>
                    
                    <div class="dashboard-section">
                        <h3>Recent Activity</h3>
                        <div id="recent-activity" class="activity-list">
                            <!-- Recent activity will be loaded here -->
                        </div>
                    </div>
                </div>
                
                <div class="dashboard-actions">
                    <button onclick="createCampaign()" class="btn btn-primary">Create New Campaign</button>
                    <button onclick="viewCampaigns()" class="btn btn-outline">View All Campaigns</button>
                </div>
            </div>
        </section>

        <!-- Campaigns Section -->
        <section id="campaigns" class="campaigns" style="display: none;">
            <div class="campaigns-container">
                <h2>Campaign Management</h2>
                <div id="campaigns-list">
                    <!-- Campaigns will be loaded here -->
                </div>
            </div>
        </section>

        <!-- Analytics Section -->
        <section id="analytics" class="dashboard" style="display: none;">
            <div class="dashboard-container">
                <h2>Analytics & Reports</h2>
                <div class="dashboard-content">
                    <div class="dashboard-section">
                        <h3>Performance Overview</h3>
                        <p>Detailed analytics and reporting features will be displayed here.</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script>
        // Global variables
        let currentUser = {
            name: '<?php echo htmlspecialchars($user_name); ?>',
            role: '<?php echo htmlspecialchars($user_role); ?>'
        };

        // Load dashboard data on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadDashboardData();
            setupEventListeners();
        });

        // Load dashboard data
        async function loadDashboardData() {
            try {
                const statsResponse = await fetch('index.php?api=campaigns&action=stats');
                const statsData = await statsResponse.json();
                
                if (statsData.success) {
                    const stats = statsData.stats;
                    
                    // Update main statistics
                    document.getElementById('total-campaigns').textContent = stats.total_campaigns;
                    document.getElementById('active-campaigns').textContent = stats.active_campaigns;
                    document.getElementById('total-recipients').textContent = stats.total_recipients;
                    document.getElementById('click-rate').textContent = stats.click_rate + '%';
                    document.getElementById('report-rate').textContent = stats.report_rate + '%';
                    document.getElementById('success-rate').textContent = stats.success_rate + '%';
                    
                    // Load department performance
                    loadDepartmentStats(stats.departments);
                    
                    // Load recent activity
                    loadRecentActivity(stats.recent_activity);
                }
            } catch (error) {
                console.error('Error loading dashboard data:', error);
            }
        }

        // Load department statistics
        function loadDepartmentStats(departments) {
            const departmentStats = document.getElementById('department-stats');
            departmentStats.innerHTML = '';
            
            Object.entries(departments).forEach(([dept, count]) => {
                const deptCard = document.createElement('div');
                deptCard.className = 'department-card';
                deptCard.innerHTML = `
                    <div class="department-name">${dept}</div>
                    <div class="department-count">${count} recipients</div>
                `;
                departmentStats.appendChild(deptCard);
            });
        }

        // Load recent activity
        function loadRecentActivity(activities) {
            const activityList = document.getElementById('recent-activity');
            activityList.innerHTML = '';
            
            activities.forEach(activity => {
                const activityItem = document.createElement('div');
                activityItem.className = 'activity-item';
                activityItem.innerHTML = `
                    <div class="activity-content">
                        <div class="activity-action">${activity.action}</div>
                        <div class="activity-details">${activity.campaign}</div>
                        <div class="activity-meta">
                            ${activity.recipients} recipients • ${activity.timestamp}
                        </div>
                    </div>
                `;
                activityList.appendChild(activityItem);
            });
        }

        // Load campaigns data
        async function loadCampaignsData() {
            try {
                const response = await fetch('index.php?api=campaigns&action=list');
                const data = await response.json();
                
                if (data.success) {
                    const campaignsList = document.getElementById('campaigns-list');
                    campaignsList.innerHTML = '';
                    
                    data.campaigns.forEach(campaign => {
                        const campaignCard = document.createElement('div');
                        campaignCard.className = 'campaign-card';
                        campaignCard.innerHTML = `
                            <h3>${campaign.name}</h3>
                            <p><strong>Status:</strong> ${campaign.status}</p>
                            <p><strong>Recipients:</strong> ${campaign.recipients}</p>
                            <p><strong>Clicks:</strong> ${campaign.clicks}</p>
                            <p><strong>Reports:</strong> ${campaign.reports}</p>
                            <div class="campaign-actions">
                                <button onclick="sendCampaign(${campaign.id})" class="btn btn-primary">Send</button>
                                <button onclick="viewCampaignDetails(${campaign.id})" class="btn btn-outline">View Details</button>
                            </div>
                        `;
                        campaignsList.appendChild(campaignCard);
                    });
                }
            } catch (error) {
                console.error('Error loading campaigns data:', error);
            }
        }

        // Campaign functions
        function createCampaign() {
            alert('Create Campaign functionality - This would open a campaign creation form');
        }

        function viewCampaigns() {
            showSection('campaigns');
            loadCampaignsData();
        }

        function sendCampaign(campaignId) {
            alert(`Sending campaign ${campaignId} - This would trigger the email sending process`);
        }

        function viewCampaignDetails(campaignId) {
            alert(`Viewing details for campaign ${campaignId} - This would show detailed analytics`);
        }

        // Show specific section
        function showSection(sectionName) {
            // Hide all sections
            const sections = ['dashboard', 'campaigns', 'analytics'];
            sections.forEach(section => {
                const element = document.getElementById(section);
                if (element) {
                    element.style.display = 'none';
                }
            });
            
            // Show the requested section
            const targetSection = document.getElementById(sectionName);
            if (targetSection) {
                targetSection.style.display = 'block';
                targetSection.scrollIntoView({ behavior: 'smooth' });
            }
        }

        // Logout function
        async function logout() {
            try {
                const response = await fetch('index.php?api=auth&action=logout', {
                    method: 'POST'
                });
                
                const data = await response.json();
                if (data.success) {
                    window.location.href = 'index.php';
                }
            } catch (error) {
                console.error('Logout error:', error);
                window.location.href = 'index.php';
            }
        }

        // Setup event listeners
        function setupEventListeners() {
            // Smooth scrolling for navigation links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        showSection(target.id);
                    }
                });
            });
        }
    </script>
</body>
</html>

