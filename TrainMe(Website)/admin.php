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
                    campaignsList.innerHTML = '<table class="admin-table"><thead><tr><th>Campaign Name</th><th>Status</th><th>Recipients</th><th>Clicks</th><th>Reports</th><th>Actions</th></tr></thead><tbody>';
                    
                    data.campaigns.forEach(campaign => {
                        const statusClass = campaign.status === 'active' ? 'admin-status-active' : 
                                          campaign.status === 'completed' ? 'admin-status-completed' : 'admin-status-draft';
                        campaignsList.innerHTML += `
                            <tr>
                                <td><strong>${campaign.name}</strong></td>
                                <td><span class="admin-status-badge ${statusClass}">${campaign.status}</span></td>
                                <td>${campaign.recipients}</td>
                                <td>${campaign.clicks}</td>
                                <td>${campaign.reports}</td>
                                <td>
                                    <button onclick="viewCampaignDetails(${campaign.id})" class="admin-action-btn">View</button>
                                    <button onclick="sendCampaign(${campaign.id})" class="admin-action-btn primary">Send</button>
                                </td>
                            </tr>
                        `;
                    });
                    
                    campaignsList.innerHTML += '</tbody></table>';
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
            showAdminSection('campaigns');
            loadCampaignsData();
        }

        function sendCampaign(campaignId) {
            if (confirm(`Send campaign ${campaignId} to all recipients?`)) {
                alert(`Campaign ${campaignId} sent successfully!`);
                loadCampaignsData();
            }
        }

        function viewCampaignDetails(campaignId) {
            alert(`Viewing detailed analytics for campaign ${campaignId}`);
        }

        function exportReport(format) {
            alert(`Exporting report as ${format.toUpperCase()}...`);
        }

        function addUser() {
            const name = prompt('Enter name for new user:');
            if (!name) return;
            const email = prompt('Enter email for new user:');
            if (!email) return;
            const role = prompt("Enter role for new user ('employee' or 'admin'):", 'employee');
            if (!role || (role !== 'employee' && role !== 'admin')) {
                showAdminModal('Error', 'Invalid role. Please use "employee" or "admin".', null, 'error');
                return;
            }

            const formData = new FormData();
            formData.append('action', 'register');
            formData.append('name', name);
            formData.append('email', email);
            formData.append('password', 'Temp1234'); // Temporary password; in real app you would send a proper invite
            formData.append('role', role);

            fetch('index.php?api=auth', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAdminModal('Success', `User created successfully!<br><small style="color: #6b7280;">Temporary password: Temp1234</small>`, null, 'success');
                    setTimeout(() => {
                        closeAdminModal();
                        loadUsersData();
                    }, 2000);
                } else {
                    showAdminModal('Error', 'Failed to create user: ' + (data.error || 'Unknown error'), null, 'error');
                }
            })
            .catch(error => {
                console.error('Error creating user:', error);
                showAdminModal('Error', 'Failed to create user. Please try again.', null, 'error');
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
                                            <button onclick="editUser(${user.id})" class="admin-action-btn">Edit</button>
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

            const newName = prompt('Edit name:', currentName);
            if (newName === null) return;

            const newEmail = prompt('Edit email:', currentEmail);
            if (newEmail === null) return;

            const newRole = prompt("Edit role ('employee' or 'admin'):", currentRole);
            if (newRole === null) return;

            if (newRole !== 'employee' && newRole !== 'admin') {
                alert('Invalid role. Please use \"employee\" or \"admin\".');
                return;
            }

            const formData = new FormData();
            formData.append('action', 'update');
            formData.append('id', userId);
            formData.append('name', newName.trim());
            formData.append('email', newEmail.trim());
            formData.append('role', newRole);

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
