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
$last_login = $_SESSION['last_login'] ?? null;
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
                    <li><a href="#dashboard" data-section="dashboard">Dashboard</a></li>
                    <li><a href="#campaigns" data-section="campaigns">Campaigns</a></li>
                    <li><a href="#analytics" data-section="analytics">Analytics</a></li>
                    <li><a href="#users" data-section="users">Users</a></li>
                </ul>
            </nav>
            <div class="nav-buttons">
                <span style="margin-right: 1rem; color: #374151;"><?php echo htmlspecialchars($user_name); ?></span>
                <button onclick="logout()" class="btn btn-outline">Logout</button>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Admin Top Stats Bar -->
        <section class="admin-top-bar">
            <div class="admin-top-container">
                <div class="admin-top-stat">
                    <div class="admin-stat-value" id="top-total-campaigns">—</div>
                    <div class="admin-stat-label">Total Campaigns</div>
                </div>
                <div class="admin-top-stat">
                    <div class="admin-stat-value" id="top-active-campaigns">—</div>
                    <div class="admin-stat-label">Active Now</div>
                </div>
                <div class="admin-top-stat">
                    <div class="admin-stat-value" id="top-total-recipients">—</div>
                    <div class="admin-stat-label">Total Recipients</div>
                </div>
                <div class="admin-top-stat">
                    <div class="admin-stat-value success" id="top-success-rate">—</div>
                    <div class="admin-stat-label">Success Rate</div>
                </div>
            </div>
        </section>

        <!-- Dashboard Section -->
        <section id="dashboard" class="admin-dashboard">
            <div class="admin-container">
                <div class="admin-header">
                    <div>
                        <h1>Dashboard Overview</h1>
                        <p class="admin-subtitle">Real-time security awareness metrics and campaign performance</p>
                    </div>
                    <button onclick="createCampaign()" class="btn btn-primary">+ New Campaign</button>
                </div>

                <!-- Key Metrics Grid -->
                <div class="admin-metrics-grid">
                    <div class="admin-metric-card">
                        <div class="admin-metric-header">
                            <span class="admin-metric-icon">📊</span>
                            <span class="admin-metric-label">Click Rate</span>
                        </div>
                        <div class="admin-metric-value" id="metric-click-rate">0%</div>
                        <div class="admin-metric-change">Phishing susceptibility indicator</div>
                    </div>
                    <div class="admin-metric-card">
                        <div class="admin-metric-header">
                            <span class="admin-metric-icon">📧</span>
                            <span class="admin-metric-label">Report Rate</span>
                        </div>
                        <div class="admin-metric-value" id="metric-report-rate">0%</div>
                        <div class="admin-metric-change">Security awareness indicator</div>
                    </div>
                    <div class="admin-metric-card">
                        <div class="admin-metric-header">
                            <span class="admin-metric-icon">✅</span>
                            <span class="admin-metric-label">Success Rate</span>
                        </div>
                        <div class="admin-metric-value success" id="metric-success-rate">0%</div>
                        <div class="admin-metric-change">Overall performance score</div>
                    </div>
                    <div class="admin-metric-card">
                        <div class="admin-metric-header">
                            <span class="admin-metric-icon">👥</span>
                            <span class="admin-metric-label">Total Users</span>
                        </div>
                        <div class="admin-metric-value" id="metric-total-users">—</div>
                        <div class="admin-metric-change">Active employees in system</div>
                    </div>
                </div>

                <!-- Two Column Layout -->
                <div class="admin-content-grid">
                    <div class="admin-content-card">
                        <div class="admin-card-header">
                            <h3>Department Performance</h3>
                            <button class="admin-link-btn" onclick="showAdminSection('analytics')">View All →</button>
                        </div>
                        <div id="department-stats" class="admin-department-list">
                            <!-- Department stats will be loaded here -->
                        </div>
                    </div>

                    <div class="admin-content-card">
                        <div class="admin-card-header">
                            <h3>Recent Activity</h3>
                            <button class="admin-link-btn" onclick="showAdminSection('campaigns')">View All →</button>
                        </div>
                        <div id="recent-activity" class="admin-activity-list">
                            <!-- Recent activity will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Campaigns Section -->
        <section id="campaigns" class="admin-dashboard" style="display: none;">
            <div class="admin-container">
                <div class="admin-header">
                    <div>
                        <h1>Campaign Management</h1>
                        <p class="admin-subtitle">Create, deploy, and monitor phishing simulation campaigns</p>
                    </div>
                    <button onclick="createCampaign()" class="btn btn-primary">+ Create Campaign</button>
                </div>

                <div id="campaigns-list" class="admin-campaigns-table">
                    <!-- Campaigns will be loaded here -->
                </div>
            </div>
        </section>

        <!-- Analytics Section -->
        <section id="analytics" class="admin-dashboard" style="display: none;">
            <div class="admin-container">
                <div class="admin-header">
                    <div>
                        <h1>Analytics & Reports</h1>
                        <p class="admin-subtitle">Detailed insights and performance trends</p>
                    </div>
                    <div>
                        <button class="btn btn-outline" onclick="exportReport('pdf')" style="margin-right: 0.5rem;">Export PDF</button>
                        <button class="btn btn-outline" onclick="exportReport('csv')">Export CSV</button>
                    </div>
                </div>

                <div class="admin-content-grid">
                    <div class="admin-content-card">
                        <h3>Performance Trends</h3>
                        <div class="admin-chart-placeholder">
                            <div class="admin-chart-icon">📈</div>
                            <p>Performance trends visualization</p>
                            <p class="admin-chart-note">Chart showing click rates, report rates, and success rates over time</p>
                        </div>
                    </div>
                    <div class="admin-content-card">
                        <h3>Department Comparison</h3>
                        <div class="admin-chart-placeholder">
                            <div class="admin-chart-icon">📊</div>
                            <p>Department performance comparison</p>
                            <p class="admin-chart-note">Compare security awareness across different departments</p>
                        </div>
                    </div>
                </div>

                <div class="admin-content-card" style="margin-top: 2rem;">
                    <h3>Training Module Statistics</h3>
                    <div id="detailed-analytics" class="admin-analytics-details">
                        <!-- Detailed analytics will be loaded here -->
                    </div>
                </div>
                
                <div class="admin-content-card" style="margin-top: 2rem;">
                    <h3>User Performance Overview</h3>
                    <div id="user-performance" class="admin-analytics-details">
                        <!-- User performance will be loaded here -->
                    </div>
                </div>
            </div>
        </section>

        <!-- Users Section -->
        <section id="users" class="admin-dashboard" style="display: none;">
            <div class="admin-container">
                <div class="admin-header">
                    <div>
                        <h1>User Management</h1>
                        <p class="admin-subtitle">Manage employees and administrators</p>
                    </div>
                    <button onclick="addUser()" class="btn btn-primary">+ Add User</button>
                </div>

                <div id="users-list" class="admin-users-table">
                    <!-- Users will be loaded here -->
                </div>
            </div>
        </section>
    </main>

    <!-- Custom Modal for Confirmations -->
    <div id="admin-modal" class="admin-modal" style="display: none;">
        <div class="admin-modal-overlay" onclick="closeAdminModal()"></div>
        <div class="admin-modal-content">
            <div class="admin-modal-header">
                <h3 id="modal-title">Confirm Action</h3>
                <button class="admin-modal-close" onclick="closeAdminModal()">&times;</button>
            </div>
            <div class="admin-modal-body">
                <p id="modal-message">Are you sure you want to proceed?</p>
            </div>
            <div class="admin-modal-footer">
                <button id="modal-cancel-btn" class="btn btn-outline" onclick="closeAdminModal()">Cancel</button>
                <button id="modal-confirm-btn" class="btn btn-primary">Confirm</button>
            </div>
        </div>
    </div>

    <!-- Add User Modal -->
    <div id="add-user-modal" class="admin-modal" style="display: none;">
        <div class="admin-modal-overlay" onclick="closeAddUserModal()"></div>
        <div class="admin-modal-content" style="max-width: 500px; position: relative; z-index: 10001; pointer-events: auto;">
            <div class="admin-modal-header">
                <h3>Add New User</h3>
                <button class="admin-modal-close" onclick="closeAddUserModal()">&times;</button>
            </div>
            <div class="admin-modal-body">
                <form id="add-user-form" style="display: flex; flex-direction: column; gap: 1.25rem;" onsubmit="event.preventDefault(); submitAddUser();">
                    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                        <label for="add-user-name" style="font-weight: 600; color: #374151; font-size: 0.875rem;">Full Name <span style="color: #dc2626;">*</span></label>
                        <input type="text" id="add-user-name" name="name" required 
                               style="padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.875rem; 
                                      transition: all 0.2s; outline: none; background: white; width: 100%; box-sizing: border-box;
                                      pointer-events: auto; position: relative; z-index: 1;"
                               onfocus="this.style.borderColor='#2563eb'; this.style.boxShadow='0 0 0 3px rgba(37, 99, 235, 0.1)'"
                               onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='none'"
                               placeholder="Enter full name"
                               autocomplete="name">
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                        <label for="add-user-email" style="font-weight: 600; color: #374151; font-size: 0.875rem;">Email Address <span style="color: #dc2626;">*</span></label>
                        <input type="email" id="add-user-email" name="email" required 
                               style="padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.875rem; 
                                      transition: all 0.2s; outline: none; background: white; width: 100%; box-sizing: border-box;
                                      pointer-events: auto; position: relative; z-index: 1;"
                               onfocus="this.style.borderColor='#2563eb'; this.style.boxShadow='0 0 0 3px rgba(37, 99, 235, 0.1)'"
                               onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='none'"
                               placeholder="user@example.com"
                               autocomplete="email">
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                        <label for="add-user-role" style="font-weight: 600; color: #374151; font-size: 0.875rem;">Role <span style="color: #dc2626;">*</span></label>
                        <select id="add-user-role" name="role" required 
                                style="padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.875rem; 
                                       transition: all 0.2s; outline: none; background: white; width: 100%; box-sizing: border-box;
                                       pointer-events: auto; position: relative; z-index: 1; cursor: pointer;"
                                onfocus="this.style.borderColor='#2563eb'; this.style.boxShadow='0 0 0 3px rgba(37, 99, 235, 0.1)'"
                                onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='none'">
                            <option value="employee">Employee</option>
                            <option value="admin">Administrator</option>
                        </select>
                    </div>
                    <div style="background: #f3f4f6; padding: 1rem; border-radius: 6px; border-left: 3px solid #2563eb;">
                        <p style="margin: 0; font-size: 0.875rem; color: #6b7280; line-height: 1.5;">
                            <strong style="color: #374151;">Note:</strong> A temporary password will be automatically generated. The user will receive a welcome email with login instructions.
                        </p>
                    </div>
                </form>
            </div>
            <div class="admin-modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeAddUserModal()">Cancel</button>
                <button type="button" id="add-user-submit-btn" class="btn btn-primary" onclick="submitAddUser()">Create User</button>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="edit-user-modal" class="admin-modal" style="display: none;">
        <div class="admin-modal-overlay" onclick="closeEditUserModal()"></div>
        <div class="admin-modal-content" style="max-width: 500px;">
            <div class="admin-modal-header">
                <h3>Edit User</h3>
                <button class="admin-modal-close" onclick="closeEditUserModal()">&times;</button>
            </div>
            <div class="admin-modal-body">
                <form id="edit-user-form" style="display: flex; flex-direction: column; gap: 1.25rem;" onsubmit="event.preventDefault(); document.getElementById('edit-user-save-btn').click();">
                    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                        <label for="edit-user-name" style="font-weight: 600; color: #374151; font-size: 0.875rem;">Name</label>
                        <input type="text" id="edit-user-name" required 
                               style="padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.875rem; 
                                      transition: all 0.2s; outline: none;"
                               onfocus="this.style.borderColor='#2563eb'; this.style.boxShadow='0 0 0 3px rgba(37, 99, 235, 0.1)'"
                               onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='none'"
                               placeholder="Enter user name">
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                        <label for="edit-user-email" style="font-weight: 600; color: #374151; font-size: 0.875rem;">Email</label>
                        <input type="email" id="edit-user-email" readonly disabled
                               style="padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.875rem; 
                                      background: #f3f4f6; color: #6b7280; cursor: not-allowed; outline: none;"
                               placeholder="Enter email address">
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                        <label for="edit-user-role" style="font-weight: 600; color: #374151; font-size: 0.875rem;">Role</label>
                        <select id="edit-user-role" disabled
                                style="padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.875rem; 
                                       background: #f3f4f6; color: #6b7280; cursor: not-allowed; outline: none;">
                            <option value="employee">Employee</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="admin-modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeEditUserModal()">Cancel</button>
                <button type="button" id="edit-user-save-btn" class="btn btn-primary">Save Changes</button>
            </div>
        </div>
    </div>

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
            showAdminSection('dashboard');
        });

        // Load dashboard data
        async function loadDashboardData() {
            try {
                const statsResponse = await fetch('index.php?api=campaigns&action=stats');
                const statsData = await statsResponse.json();
                
                if (statsData.success) {
                    const stats = statsData.stats;
                    
                    // Update top bar stats
                    document.getElementById('top-total-campaigns').textContent = stats.total_campaigns;
                    document.getElementById('top-active-campaigns').textContent = stats.active_campaigns;
                    document.getElementById('top-total-recipients').textContent = stats.total_recipients;
                    document.getElementById('top-success-rate').textContent = stats.success_rate + '%';
                    
                    // Update metric cards
                    document.getElementById('metric-click-rate').textContent = stats.click_rate + '%';
                    document.getElementById('metric-report-rate').textContent = stats.report_rate + '%';
                    document.getElementById('metric-success-rate').textContent = stats.success_rate + '%';
                    document.getElementById('metric-total-users').textContent = stats.total_users || stats.total_employees || 0;
                    
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
                const deptItem = document.createElement('div');
                deptItem.className = 'admin-department-item';
                deptItem.innerHTML = `
                    <div class="admin-department-info">
                        <span class="admin-department-name">${dept}</span>
                        <span class="admin-department-count">${count} recipients</span>
                    </div>
                `;
                departmentStats.appendChild(deptItem);
            });
        }

        // Load recent activity
        function loadRecentActivity(activities) {
            const activityList = document.getElementById('recent-activity');
            activityList.innerHTML = '';
            
            activities.forEach(activity => {
                const activityItem = document.createElement('div');
                activityItem.className = 'admin-activity-item';
                activityItem.innerHTML = `
                    <div class="admin-activity-content">
                        <div class="admin-activity-action">${activity.action}</div>
                        <div class="admin-activity-details">${activity.campaign}</div>
                        <div class="admin-activity-meta">${activity.recipients} recipients • ${activity.timestamp}</div>
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
                    
                    if (data.campaigns.length === 0) {
                        campaignsList.innerHTML = `
                            <div style="text-align: center; padding: 4rem; background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                                <div style="font-size: 3rem; margin-bottom: 1rem;">📧</div>
                                <h3 style="color: #374151; margin-bottom: 0.5rem;">No campaigns yet</h3>
                                <p style="color: #6b7280; margin-bottom: 1.5rem;">Create your first phishing simulation campaign to get started</p>
                                <button onclick="createCampaign()" class="btn btn-primary">+ Create Campaign</button>
                            </div>
                        `;
                        return;
                    }
                    
                    let tableHTML = `
                        <div style="background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;">
                            <table class="admin-table" style="width: 100%; border-collapse: collapse;">
                                <thead>
                                    <tr style="background: #f9fafb; border-bottom: 2px solid #e5e7eb;">
                                        <th style="padding: 1rem; text-align: left; font-weight: 600; color: #374151; font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.05em;">Campaign Name</th>
                                        <th style="padding: 1rem; text-align: left; font-weight: 600; color: #374151; font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.05em;">Template</th>
                                        <th style="padding: 1rem; text-align: center; font-weight: 600; color: #374151; font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.05em;">Status</th>
                                        <th style="padding: 1rem; text-align: center; font-weight: 600; color: #374151; font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.05em;">Recipients</th>
                                        <th style="padding: 1rem; text-align: center; font-weight: 600; color: #374151; font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.05em;">Clicks</th>
                                        <th style="padding: 1rem; text-align: center; font-weight: 600; color: #374151; font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.05em;">Created</th>
                                        <th style="padding: 1rem; text-align: center; font-weight: 600; color: #374151; font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.05em;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;
                    
                    data.campaigns.forEach(campaign => {
                        const statusClass = campaign.status === 'active' ? 'admin-status-active' : 
                                          campaign.status === 'completed' ? 'admin-status-completed' : 'admin-status-draft';
                        const statusLabel = campaign.status.charAt(0).toUpperCase() + campaign.status.slice(1);
                        const templateLabel = (campaign.template || 'custom').charAt(0).toUpperCase() + (campaign.template || 'custom').slice(1);
                        const createdDate = campaign.created_at ? new Date(campaign.created_at).toLocaleDateString('en-US', { 
                            year: 'numeric', 
                            month: 'short', 
                            day: 'numeric' 
                        }) : '—';
                        const campaignNameEscaped = (campaign.name || 'Unnamed Campaign').replace(/'/g, "\\'");
                        
                        tableHTML += `
                            <tr style="border-bottom: 1px solid #e5e7eb; transition: background 0.2s;" 
                                onmouseover="this.style.background='#f9fafb'" 
                                onmouseout="this.style.background='white'">
                                <td style="padding: 1rem;">
                                    <div style="font-weight: 600; color: #111827; font-size: 0.95rem;">${campaign.name || 'Unnamed Campaign'}</div>
                                </td>
                                <td style="padding: 1rem;">
                                    <span style="display: inline-block; padding: 0.25rem 0.75rem; background: #f3f4f6; border-radius: 6px; font-size: 0.875rem; color: #6b7280; font-weight: 500;">${templateLabel}</span>
                                </td>
                                <td style="padding: 1rem; text-align: center;">
                                    <span class="admin-status-badge ${statusClass}" style="display: inline-block; padding: 0.375rem 0.75rem; border-radius: 6px; font-size: 0.875rem; font-weight: 500; text-transform: capitalize;">${statusLabel}</span>
                                </td>
                                <td style="padding: 1rem; text-align: center; color: #374151; font-weight: 500;">${campaign.recipients || 0}</td>
                                <td style="padding: 1rem; text-align: center; color: #374151; font-weight: 500;">${campaign.clicks || campaign.total_clicks || 0}</td>
                                <td style="padding: 1rem; text-align: center; color: #6b7280; font-size: 0.875rem;">${createdDate}</td>
                                <td style="padding: 1rem; text-align: center;">
                                    <div style="display: flex; gap: 0.5rem; justify-content: center; align-items: center;">
                                        <button onclick="viewCampaignDetails(${campaign.id})" 
                                                style="padding: 0.5rem 1rem; background: #2563eb; color: white; border: none; border-radius: 6px; font-size: 0.875rem; font-weight: 500; cursor: pointer; transition: all 0.2s;"
                                                onmouseover="this.style.background='#1d4ed8'; this.style.transform='translateY(-1px)'"
                                                onmouseout="this.style.background='#2563eb'; this.style.transform='translateY(0)'">
                                            Details
                                        </button>
                                        ${campaign.status === 'draft' ? `
                                            <button onclick="sendCampaign(${campaign.id})" 
                                                    style="padding: 0.5rem 1rem; background: #059669; color: white; border: none; border-radius: 6px; font-size: 0.875rem; font-weight: 500; cursor: pointer; transition: all 0.2s;"
                                                    onmouseover="this.style.background='#047857'; this.style.transform='translateY(-1px)'"
                                                    onmouseout="this.style.background='#059669'; this.style.transform='translateY(0)'">
                                                Send
                                            </button>
                                        ` : ''}
                                        <button onclick="deleteCampaign(${campaign.id}, '${campaignNameEscaped}')" 
                                                style="padding: 0.5rem 1rem; background: #dc2626; color: white; border: none; border-radius: 6px; font-size: 0.875rem; font-weight: 500; cursor: pointer; transition: all 0.2s;"
                                                onmouseover="this.style.background='#b91c1c'; this.style.transform='translateY(-1px)'"
                                                onmouseout="this.style.background='#dc2626'; this.style.transform='translateY(0)'">
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        `;
                    });
                    
                    tableHTML += `
                                </tbody>
                            </table>
                        </div>
                    `;
                    
                    campaignsList.innerHTML = tableHTML;
                } else {
                    campaignsList.innerHTML = `
                        <div style="text-align: center; padding: 4rem; background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                            <div style="color: #dc2626; margin-bottom: 1rem;">⚠️</div>
                            <h3 style="color: #374151; margin-bottom: 0.5rem;">Error loading campaigns</h3>
                            <p style="color: #6b7280;">${data.error || 'Unknown error'}</p>
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Error loading campaigns data:', error);
                document.getElementById('campaigns-list').innerHTML = `
                    <div style="text-align: center; padding: 4rem; background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                        <div style="color: #dc2626; margin-bottom: 1rem;">⚠️</div>
                        <h3 style="color: #374151; margin-bottom: 0.5rem;">Failed to load campaigns</h3>
                        <p style="color: #6b7280;">Please try again later</p>
                    </div>
                `;
            }
        }

        // Campaign functions
        function createCampaign() {
            window.location.href = 'create_campaign.php';
        }

        function viewCampaigns() {
            showAdminSection('campaigns');
            loadCampaignsData();
        }

        function sendCampaign(campaignId) {
            showAdminModal(
                'Send Campaign',
                'Are you sure you want to send this campaign to all recipients?',
                async () => {
                    try {
                        const formData = new FormData();
                        formData.append('action', 'send');
                        formData.append('campaign_id', campaignId);
                        
                        const response = await fetch('index.php?api=campaigns', {
                            method: 'POST',
                            body: formData
                        });
                        
                        const data = await response.json();
                        closeAdminModal();
                        
                        if (data.success) {
                            showAdminModal('Success', `Campaign sent successfully to ${data.sent || 0} recipients!`, null, 'success');
                            setTimeout(() => {
                                closeAdminModal();
                                loadCampaignsData();
                            }, 1500);
                        } else {
                            showAdminModal('Error', 'Failed to send campaign: ' + (data.error || 'Unknown error'), null, 'error');
                        }
                    } catch (error) {
                        console.error('Error sending campaign:', error);
                        closeAdminModal();
                        showAdminModal('Error', 'Failed to send campaign. Please try again.', null, 'error');
                    }
                }
            );
        }

        async function viewCampaignDetails(campaignId) {
            try {
                const response = await fetch(`index.php?api=campaigns&action=details&id=${campaignId}`);
                const data = await response.json();
                
                if (!data.success) {
                    showAdminModal('Error', data.error || 'Failed to load campaign details', null, 'error');
                    return;
                }
                
                const campaign = data.campaign;
                const recipients = data.recipients || [];
                const clicks = data.clicks || [];
                const statusCounts = data.status_counts || {};
                
                // Debug: Log the data we received
                console.log('=== CAMPAIGN DETAILS DEBUG ===');
                console.log('Clicks received:', clicks);
                console.log('Clicks length:', clicks.length);
                console.log('First click:', clicks[0]);
                
                // Format created date
                const createdDate = new Date(campaign.created_at).toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
                
                // Build HTML content
                let html = `
                    <div style="max-width: 1000px; margin: 0 auto;">
                        <div style="background: #f9fafb; padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem;">
                            <h3 style="margin: 0 0 1rem 0; color: #111827; font-size: 1.25rem;">Campaign Information</h3>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                <div>
                                    <p style="margin: 0.5rem 0; color: #6b7280; font-size: 0.875rem;"><strong>Name:</strong> ${campaign.name || 'N/A'}</p>
                                    <p style="margin: 0.5rem 0; color: #6b7280; font-size: 0.875rem;"><strong>Template:</strong> ${campaign.template || 'Custom'}</p>
                                    <p style="margin: 0.5rem 0; color: #6b7280; font-size: 0.875rem;"><strong>Status:</strong> 
                                        <span style="padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem; font-weight: 600; 
                                            background: ${campaign.status === 'active' ? '#d1fae5' : campaign.status === 'completed' ? '#dbeafe' : '#f3f4f6'}; 
                                            color: ${campaign.status === 'active' ? '#065f46' : campaign.status === 'completed' ? '#1e40af' : '#374151'};">
                                            ${campaign.status || 'draft'}
                                        </span>
                                    </p>
                                </div>
                                <div>
                                    <p style="margin: 0.5rem 0; color: #6b7280; font-size: 0.875rem;"><strong>Subject:</strong> ${campaign.subject || 'N/A'}</p>
                                    <p style="margin: 0.5rem 0; color: #6b7280; font-size: 0.875rem;"><strong>Created:</strong> ${createdDate}</p>
                                    <p style="margin: 0.5rem 0; color: #6b7280; font-size: 0.875rem;"><strong>Created by:</strong> ${campaign.created_by_name || 'Unknown'}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div style="background: #f9fafb; padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem;">
                            <h3 style="margin: 0 0 1rem 0; color: #111827; font-size: 1.25rem;">Statistics</h3>
                            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
                                <div style="text-align: center;">
                                    <p style="margin: 0; font-size: 1.5rem; font-weight: 700; color: #2563eb;">${data.recipient_count || 0}</p>
                                    <p style="margin: 0.25rem 0 0 0; font-size: 0.875rem; color: #6b7280;">Total Recipients</p>
                                </div>
                                <div style="text-align: center;">
                                    <p style="margin: 0; font-size: 1.5rem; font-weight: 700; color: #059669;">${statusCounts.sent || 0}</p>
                                    <p style="margin: 0.25rem 0 0 0; font-size: 0.875rem; color: #6b7280;">Sent</p>
                                </div>
                                <div style="text-align: center;">
                                    <p style="margin: 0; font-size: 1.5rem; font-weight: 700; color: #d97706;">${data.click_count || clicks.length || 0}</p>
                                    <p style="margin: 0.25rem 0 0 0; font-size: 0.875rem; color: #6b7280;">Clicks</p>
                                </div>
                            </div>
                        </div>
                        
                        <div style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem;">
                            <h3 style="margin: 0 0 1.5rem 0; color: #111827; font-size: 1.25rem; font-weight: 600;">Who Clicked (${clicks.length})</h3>
                `;
                
                if (!clicks || clicks.length === 0) {
                    html += `<div style="text-align: center; color: #6b7280; padding: 3rem;">No clicks recorded yet</div>`;
                } else {
                    console.log('Building table for', clicks.length, 'clicks');
                    
                    // Build table rows array first
                    const tableRowsArray = [];
                    
                    clicks.forEach((click, index) => {
                        console.log(`Processing click ${index}:`, click);
                        const name = (click.user_name || click.recipient_name || click.name || 'Unknown').trim();
                        const email = (click.email || click.user_email || 'N/A').trim();
                        const ipAddress = (click.ip_address || click.ip || 'N/A').trim();
                        
                        console.log(`Click ${index} - Name: ${name}, Email: ${email}, IP: ${ipAddress}`);
                        
                        // Format date
                        let clickedDate = 'N/A';
                        const dateField = click.clicked_at || click.created_at || click.action_timestamp;
                        if (dateField) {
                            try {
                                const dateObj = new Date(dateField);
                                if (!isNaN(dateObj.getTime())) {
                                    clickedDate = dateObj.toLocaleString('en-US', {
                                        month: 'short',
                                        day: 'numeric',
                                        year: 'numeric',
                                        hour: '2-digit',
                                        minute: '2-digit'
                                    });
                                }
                            } catch (e) {
                                clickedDate = String(dateField);
                            }
                        }
                        
                        // Get device info
                        let deviceInfo = 'Unknown';
                        if (click.user_agent) {
                            const ua = String(click.user_agent);
                            if (ua.includes('Chrome') && !ua.includes('Edg')) deviceInfo = 'Chrome';
                            else if (ua.includes('Firefox')) deviceInfo = 'Firefox';
                            else if (ua.includes('Safari') && !ua.includes('Chrome')) deviceInfo = 'Safari';
                            else if (ua.includes('Edg')) deviceInfo = 'Edge';
                            else if (ua.includes('Mobile')) deviceInfo = 'Mobile';
                        }
                        
                        tableRowsArray.push({
                            name: name,
                            email: email,
                            ipAddress: ipAddress,
                            clickedDate: clickedDate,
                            deviceInfo: deviceInfo
                        });
                    });
                    
                    // Build table HTML from array
                    let tableRowsHTML = '';
                    tableRowsArray.forEach(row => {
                        tableRowsHTML += `
                            <tr>
                                <td style="padding: 1rem; border-bottom: 1px solid #e5e7eb; font-weight: 500; color: #111827;">${row.name}</td>
                                <td style="padding: 1rem; border-bottom: 1px solid #e5e7eb; color: #6b7280;">${row.email}</td>
                                <td style="padding: 1rem; border-bottom: 1px solid #e5e7eb; text-align: center; font-family: monospace; color: #6b7280;">${row.ipAddress}</td>
                                <td style="padding: 1rem; border-bottom: 1px solid #e5e7eb; text-align: center; color: #6b7280;">${row.clickedDate}</td>
                                <td style="padding: 1rem; border-bottom: 1px solid #e5e7eb; color: #6b7280;">${row.deviceInfo}</td>
                            </tr>
                        `;
                    });
                    
                    console.log('Table rows HTML length:', tableRowsHTML.length);
                    console.log('Table rows preview:', tableRowsHTML.substring(0, 300));
                    
                    html += `
                            <div style="overflow-x: auto; border: 1px solid #e5e7eb; border-radius: 4px; background: white;">
                                <table style="width: 100%; border-collapse: collapse; background: white; min-width: 800px;">
                                    <thead>
                                        <tr style="background: #f9fafb; border-bottom: 2px solid #e5e7eb;">
                                            <th style="padding: 1rem; text-align: left; font-weight: 600; color: #374151; font-size: 0.875rem; white-space: nowrap;">Name</th>
                                            <th style="padding: 1rem; text-align: left; font-weight: 600; color: #374151; font-size: 0.875rem; white-space: nowrap;">Email</th>
                                            <th style="padding: 1rem; text-align: center; font-weight: 600; color: #374151; font-size: 0.875rem; white-space: nowrap;">IP Address</th>
                                            <th style="padding: 1rem; text-align: center; font-weight: 600; color: #374151; font-size: 0.875rem; white-space: nowrap;">Clicked At</th>
                                            <th style="padding: 1rem; text-align: left; font-weight: 600; color: #374151; font-size: 0.875rem; white-space: nowrap;">Device</th>
                                        </tr>
                                    </thead>
                                    <tbody style="background: white;">
                                        ${tableRowsHTML}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    `;
                    
                    console.log('Final HTML contains table:', html.includes('<table'));
                    console.log('Final HTML contains tbody:', html.includes('<tbody>'));
                    console.log('Final HTML contains first name:', html.includes(tableRowsArray[0]?.name || ''));
                }
                
                html += `
                        <div style="background: #f9fafb; padding: 1.5rem; border-radius: 8px;">
                            <h3 style="margin: 0 0 1rem 0; color: #111827; font-size: 1.25rem;">All Recipients (${recipients.length})</h3>
                            <div style="max-height: 300px; overflow-y: auto;">
                                <table style="width: 100%; border-collapse: collapse;">
                                    <thead>
                                        <tr style="background: #f3f4f6; border-bottom: 2px solid #e5e7eb;">
                                            <th style="padding: 0.75rem; text-align: left; font-size: 0.875rem; font-weight: 600; color: #374151;">Email</th>
                                            <th style="padding: 0.75rem; text-align: left; font-size: 0.875rem; font-weight: 600; color: #374151;">Name</th>
                                            <th style="padding: 0.75rem; text-align: center; font-size: 0.875rem; font-weight: 600; color: #374151;">Status</th>
                                            <th style="padding: 0.75rem; text-align: center; font-size: 0.875rem; font-weight: 600; color: #374151;">Sent At</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                `;
                
                if (recipients.length === 0) {
                    html += `<tr><td colspan="4" style="padding: 2rem; text-align: center; color: #6b7280;">No recipients found</td></tr>`;
                } else {
                    recipients.forEach(recipient => {
                        const status = recipient.status || 'pending';
                        const statusColors = {
                            'sent': { bg: '#d1fae5', text: '#065f46' },
                            'clicked': { bg: '#dbeafe', text: '#1e40af' },
                            'reported': { bg: '#fee2e2', text: '#991b1b' },
                            'failed': { bg: '#f3f4f6', text: '#6b7280' },
                            'pending': { bg: '#fef3c7', text: '#92400e' }
                        };
                        const statusStyle = statusColors[status] || statusColors['pending'];
                        const sentDate = recipient.sent_at ? new Date(recipient.sent_at).toLocaleDateString('en-US', {
                            month: 'short',
                            day: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        }) : 'N/A';
                        
                        html += `
                            <tr style="border-bottom: 1px solid #e5e7eb;">
                                <td style="padding: 0.75rem; font-size: 0.875rem; color: #111827;">${recipient.email || 'N/A'}</td>
                                <td style="padding: 0.75rem; font-size: 0.875rem; color: #6b7280;">${recipient.recipient_name || recipient.user_name || 'N/A'}</td>
                                <td style="padding: 0.75rem; text-align: center;">
                                    <span style="padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem; font-weight: 600; 
                                        background: ${statusStyle.bg}; color: ${statusStyle.text};">
                                        ${status}
                                    </span>
                                </td>
                                <td style="padding: 0.75rem; text-align: center; font-size: 0.875rem; color: #6b7280;">${sentDate}</td>
                            </tr>
                        `;
                    });
                }
                
                html += `
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                `;
                
                // Debug: Log final HTML
                console.log('Final HTML length:', html.length);
                console.log('HTML contains table:', html.includes('<table'));
                console.log('HTML contains tbody:', html.includes('<tbody>'));
                
                // Show modal with wider width for details view
                const modal = document.getElementById('admin-modal');
                const modalTitle = document.getElementById('modal-title');
                const modalMessage = document.getElementById('modal-message');
                const modalContent = modal.querySelector('.admin-modal-content');
                const modalConfirmBtn = document.getElementById('modal-confirm-btn');
                const modalCancelBtn = document.getElementById('modal-cancel-btn');
                
                modalTitle.textContent = 'Campaign Details';
                modalMessage.innerHTML = html;
                
                // Make modal wider for details view
                if (modalContent) {
                    modalContent.style.maxWidth = '1000px';
                    modalContent.style.width = '95%';
                }
                
                // Make modal body scrollable
                const modalBody = modal.querySelector('.admin-modal-body');
                if (modalBody) {
                    modalBody.style.maxHeight = '85vh';
                    modalBody.style.overflowY = 'auto';
                    modalBody.style.padding = '1.5rem';
                }
                
                // Set button styles
                modalConfirmBtn.className = 'btn btn-primary';
                modalConfirmBtn.textContent = 'Close';
                modalConfirmBtn.onclick = () => {
                    closeAdminModal();
                };
                modalCancelBtn.style.display = 'none';
                
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            } catch (error) {
                console.error('Error loading campaign details:', error);
                showAdminModal('Error', 'Failed to load campaign details. Please try again.', null, 'error');
            }
        }

        function deleteCampaign(campaignId, campaignName) {
            showAdminModal(
                'Delete Campaign',
                `Are you sure you want to delete <strong>"${campaignName}"</strong>? This action cannot be undone and will delete all associated data.`,
                async () => {
                    try {
                        const formData = new FormData();
                        formData.append('action', 'delete');
                        formData.append('campaign_id', campaignId);
                        
                        const response = await fetch('index.php?api=campaigns', {
                            method: 'POST',
                            body: formData
                        });
                        
                        const data = await response.json();
                        closeAdminModal();
                        
                        if (data.success) {
                            showAdminModal('Success', 'Campaign deleted successfully!', null, 'success');
                            setTimeout(() => {
                                closeAdminModal();
                                loadCampaignsData();
                            }, 1500);
                        } else {
                            showAdminModal('Error', 'Failed to delete campaign: ' + (data.error || 'Unknown error'), null, 'error');
                        }
                    } catch (error) {
                        console.error('Error deleting campaign:', error);
                        closeAdminModal();
                        showAdminModal('Error', 'Failed to delete campaign. Please try again.', null, 'error');
                    }
                },
                'danger'
            );
        }

        async function exportReport(format) {
            try {
                // Fetch the latest analytics data
                const statsResponse = await fetch('index.php?api=campaigns&action=stats');
                const statsData = await statsResponse.json();
                
                if (!statsData.success) {
                    alert('Failed to fetch data for export. Please try again.');
                    return;
                }
                
                const stats = statsData.stats;
                const trainingStats = stats.training_stats || {};
                const moduleCompletions = trainingStats.module_completions || {};
                const moduleNames = {
                    'phishing': 'Phishing Awareness',
                    'password': 'Password Security',
                    'data': 'Data Protection',
                    'browsing': 'Safe Browsing'
                };
                
                if (format === 'csv') {
                    // Generate CSV content
                    let csvContent = 'TrainMe Security Training Report\n';
                    csvContent += `Generated: ${new Date().toLocaleString()}\n\n`;
                    
                    // Overall Statistics
                    csvContent += 'Overall Statistics\n';
                    csvContent += `Total Users,${stats.total_users}\n`;
                    csvContent += `Total Campaigns,${stats.total_campaigns}\n`;
                    csvContent += `Active Campaigns,${stats.active_campaigns}\n`;
                    csvContent += `Total Recipients,${stats.total_recipients}\n`;
                    csvContent += `Click Rate,${stats.click_rate}%\n`;
                    csvContent += `Report Rate,${stats.report_rate}%\n`;
                    csvContent += `Success Rate,${stats.success_rate}%\n`;
                    csvContent += `Total Completions,${trainingStats.total_completions || 0}\n`;
                    csvContent += `Average Score,${trainingStats.average_score || 0}%\n`;
                    csvContent += `Users Completed All Modules,${trainingStats.users_with_all_modules || 0}\n\n`;
                    
                    // Module Statistics
                    csvContent += 'Module Statistics\n';
                    csvContent += 'Module,Completions,Completion Rate\n';
                    Object.entries(moduleCompletions).forEach(([key, count]) => {
                        const moduleName = moduleNames[key] || key;
                        const completionRate = stats.total_users > 0 ? Math.round((count / stats.total_users) * 100) : 0;
                        csvContent += `${moduleName},${count},${completionRate}%\n`;
                    });
                    
                    // Add all modules even if they have 0 completions
                    ['phishing', 'password', 'data', 'browsing'].forEach(key => {
                        if (!moduleCompletions.hasOwnProperty(key)) {
                            const moduleName = moduleNames[key] || key;
                            csvContent += `${moduleName},0,0%\n`;
                        }
                    });
                    
                    // Download CSV
                    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                    const link = document.createElement('a');
                    const url = URL.createObjectURL(blob);
                    link.setAttribute('href', url);
                    link.setAttribute('download', `trainme-report-${new Date().toISOString().split('T')[0]}.csv`);
                    link.style.visibility = 'hidden';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    
                } else if (format === 'pdf') {
                    // For PDF, we'll create a simple HTML-based PDF using window.print()
                    // or we can use a library. For now, let's create a printable report
                    const printWindow = window.open('', '_blank');
                    const reportDate = new Date().toLocaleString();
                    
                    printWindow.document.write(`
                        <!DOCTYPE html>
                        <html>
                        <head>
                            <title>TrainMe Security Training Report</title>
                            <style>
                                body { font-family: Arial, sans-serif; padding: 20px; }
                                h1 { color: #2563eb; border-bottom: 2px solid #2563eb; padding-bottom: 10px; }
                                h2 { color: #374151; margin-top: 30px; }
                                table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                                th, td { border: 1px solid #e5e7eb; padding: 12px; text-align: left; }
                                th { background-color: #f3f4f6; font-weight: 600; }
                                .stat-box { display: inline-block; margin: 10px; padding: 15px; background: #f3f4f6; border-radius: 8px; min-width: 150px; }
                                .stat-value { font-size: 24px; font-weight: 700; color: #2563eb; }
                                .stat-label { color: #6b7280; font-size: 14px; margin-top: 5px; }
                                @media print {
                                    body { padding: 0; }
                                    button { display: none; }
                                }
                            </style>
                        </head>
                        <body>
                            <h1>TrainMe Security Training Report</h1>
                            <p><strong>Generated:</strong> ${reportDate}</p>
                            
                            <h2>Overall Statistics</h2>
                            <div>
                                <div class="stat-box">
                                    <div class="stat-value">${stats.total_users}</div>
                                    <div class="stat-label">Total Users</div>
                                </div>
                                <div class="stat-box">
                                    <div class="stat-value">${stats.total_campaigns}</div>
                                    <div class="stat-label">Total Campaigns</div>
                                </div>
                                <div class="stat-box">
                                    <div class="stat-value">${stats.success_rate}%</div>
                                    <div class="stat-label">Success Rate</div>
                                </div>
                                <div class="stat-box">
                                    <div class="stat-value">${trainingStats.total_completions || 0}</div>
                                    <div class="stat-label">Total Completions</div>
                                </div>
                                <div class="stat-box">
                                    <div class="stat-value">${trainingStats.average_score || 0}%</div>
                                    <div class="stat-label">Average Score</div>
                                </div>
                            </div>
                            
                            <h2>Training Module Statistics</h2>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Module</th>
                                        <th>Completions</th>
                                        <th>Completion Rate</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${Object.entries(moduleCompletions).map(([key, count]) => {
                                        const moduleName = moduleNames[key] || key;
                                        const completionRate = stats.total_users > 0 ? Math.round((count / stats.total_users) * 100) : 0;
                                        return `<tr><td>${moduleName}</td><td>${count}</td><td>${completionRate}%</td></tr>`;
                                    }).join('')}
                                    ${['phishing', 'password', 'data', 'browsing'].filter(key => !moduleCompletions.hasOwnProperty(key)).map(key => {
                                        const moduleName = moduleNames[key] || key;
                                        return `<tr><td>${moduleName}</td><td>0</td><td>0%</td></tr>`;
                                    }).join('')}
                                </tbody>
                            </table>
                            
                            <h2>Performance Metrics</h2>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Metric</th>
                                        <th>Value</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td>Click Rate</td><td>${stats.click_rate}%</td><td>Users needing training</td></tr>
                                    <tr><td>Report Rate</td><td>${stats.report_rate}%</td><td>Users engaged</td></tr>
                                    <tr><td>Success Rate</td><td>${stats.success_rate}%</td><td>Completion progress</td></tr>
                                </tbody>
                            </table>
                            
                            <button onclick="window.print()" style="margin-top: 20px; padding: 10px 20px; background: #2563eb; color: white; border: none; border-radius: 5px; cursor: pointer;">Print / Save as PDF</button>
                        </body>
                        </html>
                    `);
                    printWindow.document.close();
                    
                    // Wait for content to load, then trigger print
                    setTimeout(() => {
                        printWindow.print();
                    }, 250);
                }
            } catch (error) {
                console.error('Error exporting report:', error);
                alert('Failed to export report. Please try again.');
            }
        }

        function addUser() {
            // Reset form
            document.getElementById('add-user-form').reset();
            // Show modal
            document.getElementById('add-user-modal').style.display = 'flex';
        }

        function closeAddUserModal() {
            document.getElementById('add-user-modal').style.display = 'none';
            document.getElementById('add-user-form').reset();
        }

        function submitAddUser() {
            const name = document.getElementById('add-user-name').value.trim();
            const email = document.getElementById('add-user-email').value.trim();
            const role = document.getElementById('add-user-role').value;

            // Validation
            if (!name || name.length < 2) {
                showAdminModal('Error', 'Please enter a valid name (at least 2 characters).', null, 'error');
                return;
            }

            if (!email || !email.includes('@')) {
                showAdminModal('Error', 'Please enter a valid email address.', null, 'error');
                return;
            }

            if (role !== 'employee' && role !== 'admin') {
                showAdminModal('Error', 'Please select a valid role.', null, 'error');
                return;
            }

            // Disable submit button
            const submitBtn = document.getElementById('add-user-submit-btn');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Creating...';

            const formData = new FormData();
            formData.append('action', 'register');
            formData.append('name', name);
            formData.append('email', email);
            formData.append('password', 'Temp1234'); // Temporary password; user will receive email
            formData.append('role', role);

            fetch('index.php?api=auth', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeAddUserModal();
                    const emailStatus = data.email_sent ? 
                        '<p style="margin: 0.75rem 0 0 0; font-size: 0.875rem; color: #059669;">✓ Welcome email sent successfully with login credentials.</p>' :
                        '<p style="margin: 0.75rem 0 0 0; font-size: 0.875rem; color: #dc2626;">⚠ Welcome email could not be sent. Please share credentials manually.</p>';
                    
                    showAdminModal('Success', 
                        `User created successfully!<br><br>
                        <div style="background: #f3f4f6; padding: 1rem; border-radius: 6px; margin-top: 0.5rem;">
                            <p style="margin: 0; font-size: 0.875rem; color: #374151;"><strong>Email:</strong> ${email}</p>
                            <p style="margin: 0.5rem 0 0 0; font-size: 0.875rem; color: #374151;"><strong>Temporary Password:</strong> <code style="background: white; padding: 0.25rem 0.5rem; border-radius: 3px; font-family: monospace;">Temp1234</code></p>
                            ${emailStatus}
                        </div>`, 
                        null, 'success');
                    setTimeout(() => {
                        closeAdminModal();
                        loadUsersData();
                    }, 4000);
                } else {
                    showAdminModal('Error', 'Failed to create user: ' + (data.error || 'Unknown error'), null, 'error');
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                }
            })
            .catch(error => {
                console.error('Error creating user:', error);
                showAdminModal('Error', 'Failed to create user. Please try again.', null, 'error');
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            });
        }

        // Show specific section
        function showAdminSection(sectionName) {
            const sections = ['dashboard', 'campaigns', 'analytics', 'users'];
            sections.forEach(section => {
                const element = document.getElementById(section);
                if (element) {
                    element.style.display = section === sectionName ? 'block' : 'none';
                }
            });

            // Update active link styling
            document.querySelectorAll('.nav-links a[data-section]').forEach(link => {
                const isActive = link.getAttribute('data-section') === sectionName;
                link.classList.toggle('active', isActive);
            });

            // Load section-specific data
            if (sectionName === 'campaigns') {
                loadCampaignsData();
            } else if (sectionName === 'users') {
                loadUsersData();
            } else if (sectionName === 'analytics') {
                loadAnalyticsData();
            }

            // Scroll to top
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        async function loadUsersData() {
            try {
                const response = await fetch('index.php?api=users&action=list');
                const data = await response.json();
                
                if (data.success) {
                    const usersList = document.getElementById('users-list');
                    let tableHTML = '<table class="admin-table"><thead><tr><th class="col-user">User</th><th class="col-role">Role</th><th class="col-status">Status</th><th class="col-created">Created</th><th class="col-actions">Actions</th></tr></thead><tbody>';
                    
                    if (data.users && data.users.length > 0) {
                        data.users.forEach(user => {
                            const roleClass = user.role === 'admin' ? 'admin-status-active' : 'admin-status-completed';
                            const roleLabel = user.role === 'admin' ? 'Admin' : 'Employee';
                            const createdDate = new Date(user.created_at).toLocaleDateString('en-US', { 
                                year: 'numeric', 
                                month: 'short', 
                                day: 'numeric' 
                            });
                            const userNameEscaped = user.name.replace(/'/g, "\\'");
                            
                            tableHTML += `
                                <tr data-user-id="${user.id}" data-user-name="${user.name}" data-user-email="${user.email}" data-user-role="${user.role}">
                                    <td class="col-user">
                                        <div class="admin-user-name">
                                            <div class="admin-user-avatar">${user.name.charAt(0).toUpperCase()}</div>
                                            <div class="admin-user-info">
                                                <div class="admin-user-primary">${user.name}</div>
                                                <div class="admin-user-secondary">${user.email}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="col-role"><span class="admin-status-badge ${roleClass}">${roleLabel}</span></td>
                                    <td class="col-status"><span class="admin-status-badge admin-status-completed">${user.status}</span></td>
                                    <td class="col-created">${createdDate}</td>
                                    <td class="col-actions">
                                        <div class="admin-users-actions">
                                            <button onclick="viewUserTraining(${user.id}, '${userNameEscaped}')" class="admin-action-btn" style="background: #2563eb; color: white;">View Results</button>
                                            <button onclick="editUser(${user.id})" class="admin-action-btn">Edit Name</button>
                                            ${user.role !== 'admin' ? `<button onclick="deleteUser(${user.id}, '${userNameEscaped}')" class="admin-action-btn admin-action-danger">Delete</button>` : ''}
                                        </div>
                                    </td>
                                </tr>
                            `;
                        });
                    } else {
                        tableHTML += '<tr><td colspan="5" style="text-align: center; color: #6b7280; padding: 2rem;">No users found</td></tr>';
                    }
                    
                    tableHTML += '</tbody></table>';
                    usersList.innerHTML = tableHTML;
                } else {
                    document.getElementById('users-list').innerHTML = '<table class="admin-table"><thead><tr><th>User</th><th>Role</th><th>Status</th><th>Created</th><th>Actions</th></tr></thead><tbody><tr><td colspan="5" style="text-align: center; color: #dc2626; padding: 2rem;">Error loading users: ' + (data.error || 'Unknown error') + '</td></tr></tbody></table>';
                }
            } catch (error) {
                console.error('Error loading users data:', error);
                document.getElementById('users-list').innerHTML = '<table class="admin-table"><thead><tr><th>User</th><th>Role</th><th>Status</th><th>Created</th><th>Actions</th></tr></thead><tbody><tr><td colspan="5" style="text-align: center; color: #dc2626; padding: 2rem;">Failed to load users. Please try again.</td></tr></tbody></table>';
            }
        }

        function editUser(userId) {
            const row = document.querySelector(`tr[data-user-id="${userId}"]`);
            if (!row) {
                showAdminModal('Error', 'Could not find user row to edit.', null, 'error');
                return;
            }

            const currentName = row.getAttribute('data-user-name') || '';
            const currentEmail = row.getAttribute('data-user-email') || '';
            const currentRole = row.getAttribute('data-user-role') || 'employee';

            // Populate form fields
            document.getElementById('edit-user-name').value = currentName;
            document.getElementById('edit-user-email').value = currentEmail;
            document.getElementById('edit-user-role').value = currentRole;

            // Show modal
            const modal = document.getElementById('edit-user-modal');
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';

            // Set up save button handler
            const saveBtn = document.getElementById('edit-user-save-btn');
            saveBtn.onclick = () => {
                const newName = document.getElementById('edit-user-name').value.trim();
                const currentEmail = document.getElementById('edit-user-email').value.trim();
                const currentRole = document.getElementById('edit-user-role').value;

                // Validation
                if (!newName) {
                    showAdminModal('Error', 'Name is required.', null, 'error');
                    return;
                }

                // Close edit modal
                closeEditUserModal();

                // Update user (email and role are read-only, so we send the current values)
                const formData = new FormData();
                formData.append('action', 'update');
                formData.append('id', userId);
                formData.append('name', newName);
                formData.append('email', currentEmail);
                formData.append('role', currentRole);

                fetch('index.php?api=users', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAdminModal('Success', 'User updated successfully.', null, 'success');
                        setTimeout(() => {
                            closeAdminModal();
                            loadUsersData();
                        }, 1500);
                    } else {
                        showAdminModal('Error', 'Failed to update user: ' + (data.error || 'Unknown error'), null, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error updating user:', error);
                    showAdminModal('Error', 'Failed to update user. Please try again.', null, 'error');
                });
            };
        }

        function closeEditUserModal() {
            const modal = document.getElementById('edit-user-modal');
            modal.style.display = 'none';
            document.body.style.overflow = '';
            // Reset form
            document.getElementById('edit-user-form').reset();
        }

        function deleteUser(userId, userName) {
            const row = document.querySelector(`.admin-users-table tr[data-user-id="${userId}"]`);
            if (!row) {
                showAdminModal('Error', 'Could not find user to delete.', null);
                return;
            }

            const name = userName || row.getAttribute('data-user-name') || 'this user';
            
            showAdminModal(
                'Delete User',
                `Are you sure you want to delete <strong>${name}</strong>? This action cannot be undone.`,
                () => {
                    const formData = new FormData();
                    formData.append('action', 'delete');
                    formData.append('id', userId);

                    fetch('index.php?api=users', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        closeAdminModal();
                        if (data.success) {
                            showAdminModal('Success', 'User deleted successfully.', null, 'success');
                            setTimeout(() => {
                                closeAdminModal();
                                loadUsersData();
                            }, 1500);
                        } else {
                            showAdminModal('Error', 'Failed to delete user: ' + (data.error || 'Unknown error'), null, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error deleting user:', error);
                        closeAdminModal();
                        showAdminModal('Error', 'Failed to delete user. Please try again.', null, 'error');
                    });
                },
                'danger'
            );
        }

        // Modal functions
        function showAdminModal(title, message, onConfirm, type = 'default') {
            const modal = document.getElementById('admin-modal');
            const modalTitle = document.getElementById('modal-title');
            const modalMessage = document.getElementById('modal-message');
            const modalConfirmBtn = document.getElementById('modal-confirm-btn');
            const modalCancelBtn = document.getElementById('modal-cancel-btn');

            modalTitle.textContent = title;
            modalMessage.innerHTML = message;
            
            // Set button styles based on type
            modalConfirmBtn.className = 'btn';
            if (type === 'danger') {
                modalConfirmBtn.className += ' btn-danger';
                modalConfirmBtn.textContent = 'Delete';
            } else if (type === 'success') {
                modalConfirmBtn.className += ' btn-success';
                modalConfirmBtn.textContent = 'OK';
            } else if (type === 'error') {
                modalConfirmBtn.className += ' btn-danger';
                modalConfirmBtn.textContent = 'OK';
            } else {
                modalConfirmBtn.className += ' btn-primary';
                modalConfirmBtn.textContent = 'Confirm';
            }

            // Set up confirm handler
            modalConfirmBtn.onclick = () => {
                if (onConfirm) {
                    onConfirm();
                } else {
                    closeAdminModal();
                }
            };

            // Show/hide cancel button
            if (type === 'success' || type === 'error') {
                modalCancelBtn.style.display = 'none';
            } else {
                modalCancelBtn.style.display = 'inline-block';
            }

            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeAdminModal() {
            const modal = document.getElementById('admin-modal');
            modal.style.display = 'none';
            document.body.style.overflow = '';
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

        // Load analytics data
        async function loadAnalyticsData() {
            try {
                const statsResponse = await fetch('index.php?api=campaigns&action=stats');
                const statsData = await statsResponse.json();
                
                if (statsData.success) {
                    const stats = statsData.stats;
                    const trainingStats = stats.training_stats || {};
                    
                    // Debug: log the data to see what we're getting
                    console.log('Analytics Data:', {
                        stats: stats,
                        trainingStats: trainingStats,
                        moduleCompletions: trainingStats.module_completions
                    });
                    
                    // Update Performance Trends card
                    const performanceCard = document.querySelector('#analytics .admin-content-grid .admin-content-card:first-child .admin-chart-placeholder');
                    if (performanceCard) {
                        performanceCard.innerHTML = `
                            <div style="padding: 1rem;">
                                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-bottom: 1rem;">
                                    <div style="text-align: center; padding: 1rem; background: #f3f4f6; border-radius: 8px;">
                                        <div style="font-size: 2rem; font-weight: 700; color: #dc2626;">${stats.click_rate}%</div>
                                        <div style="color: #6b7280; font-size: 0.875rem;">Click Rate</div>
                                        <div style="color: #9ca3af; font-size: 0.75rem; margin-top: 0.25rem;">Users needing training</div>
                                    </div>
                                    <div style="text-align: center; padding: 1rem; background: #f3f4f6; border-radius: 8px;">
                                        <div style="font-size: 2rem; font-weight: 700; color: #2563eb;">${stats.report_rate}%</div>
                                        <div style="color: #6b7280; font-size: 0.875rem;">Report Rate</div>
                                        <div style="color: #9ca3af; font-size: 0.75rem; margin-top: 0.25rem;">Users engaged</div>
                                    </div>
                                    <div style="text-align: center; padding: 1rem; background: #f3f4f6; border-radius: 8px;">
                                        <div style="font-size: 2rem; font-weight: 700; color: #059669;">${stats.success_rate}%</div>
                                        <div style="color: #6b7280; font-size: 0.875rem;">Success Rate</div>
                                        <div style="color: #9ca3af; font-size: 0.75rem; margin-top: 0.25rem;">Completion progress</div>
                                    </div>
                                </div>
                                <div style="margin-top: 1rem; padding: 1rem; background: #f9fafb; border-radius: 8px;">
                                    <div style="font-weight: 600; margin-bottom: 0.5rem;">Summary</div>
                                    <div style="color: #6b7280; font-size: 0.875rem;">
                                        <div>Total Users: <strong>${stats.total_users}</strong></div>
                                        <div>Total Completions: <strong>${trainingStats.total_completions || 0}</strong></div>
                                        <div>Average Score: <strong>${trainingStats.average_score || 0}%</strong></div>
                                    </div>
                                </div>
                            </div>
                        `;
                    }
                    
                    // Update Department Comparison card
                    const deptCard = document.querySelector('#analytics .admin-content-grid .admin-content-card:last-child .admin-chart-placeholder');
                    if (deptCard) {
                        const moduleNames = {
                            'phishing': 'Phishing Awareness',
                            'password': 'Password Security',
                            'data': 'Data Protection',
                            'browsing': 'Safe Browsing'
                        };
                        
                        const moduleCompletions = trainingStats.module_completions || {};
                        const allModules = ['phishing', 'password', 'data', 'browsing'];
                        let moduleHTML = '<div style="padding: 1rem;">';
                        
                        // Show all modules, even if they have 0 completions
                        allModules.forEach(key => {
                            const moduleName = moduleNames[key] || key;
                            // Get count - handle both number and string, and ensure it's a number
                            let count = 0;
                            if (moduleCompletions.hasOwnProperty(key)) {
                                count = parseInt(moduleCompletions[key]) || 0;
                            }
                            const percentage = stats.total_users > 0 ? Math.round((count / stats.total_users) * 100) : 0;
                            const hasCompletions = count > 0;
                            
                            moduleHTML += `
                                <div style="margin-bottom: 1rem; padding: 1rem; background: #f3f4f6; border-radius: 8px;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                        <div style="font-weight: 600;">${moduleName}</div>
                                        <div style="font-weight: 700; color: ${hasCompletions ? '#2563eb' : '#9ca3af'};">
                                            ${count} ${count === 1 ? 'completion' : 'completions'}
                                        </div>
                                    </div>
                                    <div style="background: #e5e7eb; border-radius: 4px; height: 8px; overflow: hidden;">
                                        <div style="background: ${hasCompletions ? '#2563eb' : '#d1d5db'}; height: 100%; width: ${Math.max(percentage, 1)}%; transition: width 0.3s; min-width: ${hasCompletions ? '2px' : '0'};"></div>
                                    </div>
                                    <div style="margin-top: 0.5rem; color: #6b7280; font-size: 0.875rem;">
                                        ${percentage}% of users completed
                                        ${hasCompletions ? `(${count} out of ${stats.total_users} ${stats.total_users === 1 ? 'user' : 'users'})` : `(0 out of ${stats.total_users} ${stats.total_users === 1 ? 'user' : 'users'})`}
                                    </div>
                                </div>
                            `;
                        });
                        
                        moduleHTML += '</div>';
                        deptCard.innerHTML = moduleHTML;
                    }
                    
                    // Update Detailed Metrics
                    const detailedAnalytics = document.getElementById('detailed-analytics');
                    if (detailedAnalytics) {
                        const moduleCompletions = trainingStats.module_completions || {};
                        const moduleNames = {
                            'phishing': 'Phishing Awareness',
                            'password': 'Password Security',
                            'data': 'Data Protection',
                            'browsing': 'Safe Browsing'
                        };
                        
                        let tableHTML = '<table class="admin-table" style="width: 100%;"><thead><tr><th>Module</th><th>Completions</th><th>Completion Rate</th><th>Average Score</th></tr></thead><tbody>';
                        
                        Object.entries(moduleCompletions).forEach(([key, count]) => {
                            const moduleName = moduleNames[key] || key;
                            const completionRate = stats.total_users > 0 ? Math.round((count / stats.total_users) * 100) : 0;
                            // For now, we'll show completion count. Average score per module would need additional query
                            tableHTML += `
                                <tr>
                                    <td><strong>${moduleName}</strong></td>
                                    <td>${count}</td>
                                    <td>${completionRate}%</td>
                                    <td>—</td>
                                </tr>
                            `;
                        });
                        
                        if (Object.keys(moduleCompletions).length === 0) {
                            tableHTML += '<tr><td colspan="4" style="text-align: center; color: #9ca3af; padding: 2rem;">No training data available yet</td></tr>';
                        }
                        
                        tableHTML += '</tbody></table>';
                        detailedAnalytics.innerHTML = tableHTML;
                    }
                    
                    // Update User Performance
                    const userPerformance = document.getElementById('user-performance');
                    if (userPerformance) {
                        userPerformance.innerHTML = `
                            <div style="padding: 1rem;">
                                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                                    <div style="padding: 1rem; background: #f3f4f6; border-radius: 8px; text-align: center;">
                                        <div style="font-size: 1.5rem; font-weight: 700; color: #2563eb;">${stats.total_users}</div>
                                        <div style="color: #6b7280; font-size: 0.875rem; margin-top: 0.25rem;">Total Employees</div>
                                    </div>
                                    <div style="padding: 1rem; background: #f3f4f6; border-radius: 8px; text-align: center;">
                                        <div style="font-size: 1.5rem; font-weight: 700; color: #059669;">${trainingStats.users_with_all_modules || 0}</div>
                                        <div style="color: #6b7280; font-size: 0.875rem; margin-top: 0.25rem;">Completed All Modules</div>
                                    </div>
                                    <div style="padding: 1rem; background: #f3f4f6; border-radius: 8px; text-align: center;">
                                        <div style="font-size: 1.5rem; font-weight: 700; color: #dc2626;">${stats.total_users - (trainingStats.users_with_all_modules || 0)}</div>
                                        <div style="color: #6b7280; font-size: 0.875rem; margin-top: 0.25rem;">Incomplete Training</div>
                                    </div>
                                    <div style="padding: 1rem; background: #f3f4f6; border-radius: 8px; text-align: center;">
                                        <div style="font-size: 1.5rem; font-weight: 700; color: #7c3aed;">${trainingStats.average_score || 0}%</div>
                                        <div style="color: #6b7280; font-size: 0.875rem; margin-top: 0.25rem;">Average Score</div>
                                    </div>
                                </div>
                            </div>
                        `;
                    }
                }
            } catch (error) {
                console.error('Error loading analytics data:', error);
            }
        }

        // View individual user training results
        async function viewUserTraining(userId, userName) {
            try {
                const response = await fetch(`index.php?api=users&action=training&user_id=${userId}`);
                const data = await response.json();
                
                if (data.success) {
                    const user = data.user;
                    const training = data.training;
                    const progress = training.progress;
                    const stats = training.statistics;
                    
                    // Create modal content with training results
                    const moduleNames = {
                        'phishing': 'Phishing Awareness',
                        'password': 'Password Security',
                        'data': 'Data Protection',
                        'browsing': 'Safe Browsing'
                    };
                    
                    let progressHTML = '';
                    if (progress.length > 0) {
                        progress.forEach(module => {
                            const scoreColor = module.score >= 80 ? '#059669' : module.score >= 60 ? '#f59e0b' : '#dc2626';
                            progressHTML += `
                                <div style="margin-bottom: 1rem; padding: 1rem; background: #f3f4f6; border-radius: 8px; border-left: 4px solid ${scoreColor};">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                        <div style="font-weight: 600; font-size: 1.1rem;">${module.module_name}</div>
                                        <div style="font-weight: 700; font-size: 1.2rem; color: ${scoreColor};">${module.score}%</div>
                                    </div>
                                    <div style="color: #6b7280; font-size: 0.875rem;">
                                        Completed: ${new Date(module.completed_at).toLocaleDateString('en-US', { 
                                            year: 'numeric', 
                                            month: 'short', 
                                            day: 'numeric',
                                            hour: '2-digit',
                                            minute: '2-digit'
                                        })}
                                    </div>
                                </div>
                            `;
                        });
                    } else {
                        progressHTML = '<div style="text-align: center; color: #9ca3af; padding: 2rem;">No training completed yet</div>';
                    }
                    
                    // Show all modules, marking incomplete ones
                    const allModules = ['phishing', 'password', 'data', 'browsing'];
                    const completedModules = progress.map(p => p.module_key);
                    const incompleteModules = allModules.filter(m => !completedModules.includes(m));
                    
                    incompleteModules.forEach(moduleKey => {
                        progressHTML += `
                            <div style="margin-bottom: 1rem; padding: 1rem; background: #f9fafb; border-radius: 8px; border-left: 4px solid #d1d5db;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <div style="font-weight: 600; color: #9ca3af;">${moduleNames[moduleKey]}</div>
                                    <div style="color: #9ca3af;">Not Started</div>
                                </div>
                            </div>
                        `;
                    });
                    
                    const modalContent = `
                        <div style="max-height: 60vh; overflow-y: auto;">
                            <div style="margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 2px solid #e5e7eb;">
                                <h3 style="margin: 0 0 0.5rem 0; color: #2563eb;">${user.name}</h3>
                                <div style="color: #6b7280; font-size: 0.875rem;">
                                    <div>Email: ${user.email}</div>
                                    <div>Role: ${user.role.charAt(0).toUpperCase() + user.role.slice(1)}</div>
                                </div>
                            </div>
                            
                            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 1.5rem;">
                                <div style="text-align: center; padding: 1rem; background: #f3f4f6; border-radius: 8px;">
                                    <div style="font-size: 1.5rem; font-weight: 700; color: #2563eb;">${stats.completed_modules}</div>
                                    <div style="color: #6b7280; font-size: 0.875rem; margin-top: 0.25rem;">Completed</div>
                                </div>
                                <div style="text-align: center; padding: 1rem; background: #f3f4f6; border-radius: 8px;">
                                    <div style="font-size: 1.5rem; font-weight: 700; color: #059669;">${stats.completion_rate}%</div>
                                    <div style="color: #6b7280; font-size: 0.875rem; margin-top: 0.25rem;">Completion Rate</div>
                                </div>
                                <div style="text-align: center; padding: 1rem; background: #f3f4f6; border-radius: 8px;">
                                    <div style="font-size: 1.5rem; font-weight: 700; color: #7c3aed;">${stats.average_score}%</div>
                                    <div style="color: #6b7280; font-size: 0.875rem; margin-top: 0.25rem;">Average Score</div>
                                </div>
                                <div style="text-align: center; padding: 1rem; background: #f3f4f6; border-radius: 8px;">
                                    <div style="font-size: 1.5rem; font-weight: 700; color: #374151;">${stats.total_modules}</div>
                                    <div style="color: #6b7280; font-size: 0.875rem; margin-top: 0.25rem;">Total Modules</div>
                                </div>
                            </div>
                            
                            <h4 style="margin-bottom: 1rem; color: #374151;">Training Progress</h4>
                            ${progressHTML}
                        </div>
                        <div style="margin-top: 1.5rem; padding-top: 1rem; border-top: 2px solid #e5e7eb; display: flex; gap: 0.5rem; justify-content: flex-end;">
                            <button onclick="printUserReport(${userId}, '${userName.replace(/'/g, "\\'")}')" class="btn btn-primary" style="background: #2563eb;">Print Report</button>
                        </div>
                    `;
                    
                    showAdminModal(
                        `Training Results - ${userName}`,
                        modalContent,
                        null,
                        'default'
                    );
                } else {
                    showAdminModal('Error', 'Failed to load training results: ' + (data.error || 'Unknown error'), null, 'error');
                }
            } catch (error) {
                console.error('Error loading user training:', error);
                showAdminModal('Error', 'Failed to load training results. Please try again.', null, 'error');
            }
        }

        // Print individual user report
        function printUserReport(userId, userName) {
            // Fetch the data again to ensure we have the latest
            fetch(`index.php?api=users&action=training&user_id=${userId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const user = data.user;
                        const training = data.training;
                        const progress = training.progress;
                        const stats = training.statistics;
                        
                        const moduleNames = {
                            'phishing': 'Phishing Awareness',
                            'password': 'Password Security',
                            'data': 'Data Protection',
                            'browsing': 'Safe Browsing'
                        };
                        
                        const printWindow = window.open('', '_blank');
                        const reportDate = new Date().toLocaleString();
                        
                        let progressTable = '';
                        if (progress.length > 0) {
                            progressTable = '<table style="width: 100%; border-collapse: collapse; margin: 20px 0;"><thead><tr><th style="border: 1px solid #e5e7eb; padding: 12px; background: #f3f4f6;">Module</th><th style="border: 1px solid #e5e7eb; padding: 12px; background: #f3f4f6;">Score</th><th style="border: 1px solid #e5e7eb; padding: 12px; background: #f3f4f6;">Completed Date</th></tr></thead><tbody>';
                            
                            progress.forEach(module => {
                                progressTable += `
                                    <tr>
                                        <td style="border: 1px solid #e5e7eb; padding: 12px;">${module.module_name}</td>
                                        <td style="border: 1px solid #e5e7eb; padding: 12px; font-weight: 600; color: ${module.score >= 80 ? '#059669' : module.score >= 60 ? '#f59e0b' : '#dc2626'};">${module.score}%</td>
                                        <td style="border: 1px solid #e5e7eb; padding: 12px;">${new Date(module.completed_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })}</td>
                                    </tr>
                                `;
                            });
                            
                            // Add incomplete modules
                            const allModules = ['phishing', 'password', 'data', 'browsing'];
                            const completedModules = progress.map(p => p.module_key);
                            allModules.filter(m => !completedModules.includes(m)).forEach(moduleKey => {
                                progressTable += `
                                    <tr style="color: #9ca3af;">
                                        <td style="border: 1px solid #e5e7eb; padding: 12px;">${moduleNames[moduleKey]}</td>
                                        <td style="border: 1px solid #e5e7eb; padding: 12px;">Not Started</td>
                                        <td style="border: 1px solid #e5e7eb; padding: 12px;">—</td>
                                    </tr>
                                `;
                            });
                            
                            progressTable += '</tbody></table>';
                        } else {
                            progressTable = '<p style="color: #9ca3af; text-align: center; padding: 2rem;">No training completed yet</p>';
                        }
                        
                        printWindow.document.write(`
                            <!DOCTYPE html>
                            <html>
                            <head>
                                <title>Training Report - ${user.name}</title>
                                <style>
                                    body { font-family: Arial, sans-serif; padding: 20px; }
                                    h1 { color: #2563eb; border-bottom: 2px solid #2563eb; padding-bottom: 10px; }
                                    h2 { color: #374151; margin-top: 30px; }
                                    table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                                    th, td { border: 1px solid #e5e7eb; padding: 12px; text-align: left; }
                                    th { background-color: #f3f4f6; font-weight: 600; }
                                    .stat-box { display: inline-block; margin: 10px; padding: 15px; background: #f3f4f6; border-radius: 8px; min-width: 150px; }
                                    .stat-value { font-size: 24px; font-weight: 700; color: #2563eb; }
                                    .stat-label { color: #6b7280; font-size: 14px; margin-top: 5px; }
                                    @media print {
                                        body { padding: 0; }
                                        button { display: none; }
                                    }
                                </style>
                            </head>
                            <body>
                                <h1>Training Report - ${user.name}</h1>
                                <p><strong>Generated:</strong> ${reportDate}</p>
                                
                                <h2>User Information</h2>
                                <table>
                                    <tr><th style="width: 150px;">Name</th><td>${user.name}</td></tr>
                                    <tr><th>Email</th><td>${user.email}</td></tr>
                                    <tr><th>Role</th><td>${user.role.charAt(0).toUpperCase() + user.role.slice(1)}</td></tr>
                                    <tr><th>Account Created</th><td>${new Date(user.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</td></tr>
                                </table>
                                
                                <h2>Training Statistics</h2>
                                <div>
                                    <div class="stat-box">
                                        <div class="stat-value">${stats.completed_modules}</div>
                                        <div class="stat-label">Completed Modules</div>
                                    </div>
                                    <div class="stat-box">
                                        <div class="stat-value">${stats.completion_rate}%</div>
                                        <div class="stat-label">Completion Rate</div>
                                    </div>
                                    <div class="stat-box">
                                        <div class="stat-value">${stats.average_score}%</div>
                                        <div class="stat-label">Average Score</div>
                                    </div>
                                    <div class="stat-box">
                                        <div class="stat-value">${stats.total_modules}</div>
                                        <div class="stat-label">Total Modules</div>
                                    </div>
                                </div>
                                
                                <h2>Training Progress</h2>
                                ${progressTable}
                                
                                <button onclick="window.print()" style="margin-top: 20px; padding: 10px 20px; background: #2563eb; color: white; border: none; border-radius: 5px; cursor: pointer;">Print / Save as PDF</button>
                            </body>
                            </html>
                        `);
                        printWindow.document.close();
                        
                        setTimeout(() => {
                            printWindow.print();
                        }, 250);
                    } else {
                        alert('Failed to load training data for printing');
                    }
                })
                .catch(error => {
                    console.error('Error loading training data:', error);
                    alert('Failed to load training data for printing');
                });
        }

        // Setup event listeners
        function setupEventListeners() {
            document.querySelectorAll('.nav-links a[data-section]').forEach(link => {
                link.addEventListener('click', function (e) {
                    e.preventDefault();
                    const sectionId = this.getAttribute('data-section');
                    showAdminSection(sectionId);
                });
            });
        }
    </script>
</body>
</html>
