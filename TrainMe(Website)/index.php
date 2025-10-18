<?php
// TrainMe Backend - Eyad's Backend Logic
session_start();

// Backend configuration
$backend_config = [
    'database' => [
        'host' => 'localhost',
        'dbname' => 'trainme_db',
        'username' => 'root',
        'password' => ''
    ],
    'email' => [
        'sendgrid_api_key' => 'your_sendgrid_key_here',
        'smtp_host' => 'smtp.gmail.com',
        'smtp_port' => 587,
        'smtp_username' => 'your_email@gmail.com',
        'smtp_password' => 'your_app_password'
    ],
    'tracking' => [
        'base_url' => (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']
    ]
];

// API Endpoints for Frontend (Amr)
if (isset($_GET['api'])) {
    header('Content-Type: application/json');
    
    switch($_GET['api']) {
        case 'auth':
            handleAuthAPI();
            break;
        case 'campaigns':
            handleCampaignsAPI();
            break;
        case 'tracking':
            handleTrackingAPI();
            break;
        case 'email':
            handleEmailAPI();
            break;
        default:
            http_response_code(404);
            echo json_encode(['error' => 'API endpoint not found']);
    }
    exit;
}

// Authentication API
function handleAuthAPI() {
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    
    switch($action) {
        case 'login':
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            
            // Simulate authentication (replace with real DB check)
            if ($email === 'admin@trainme.com' && $password === 'admin123') {
                $_SESSION['user_id'] = 1;
                $_SESSION['user_role'] = 'admin';
                $_SESSION['user_name'] = 'Admin User';
                echo json_encode(['success' => true, 'role' => 'admin']);
            } elseif ($email === 'employee@trainme.com' && $password === 'emp123') {
                $_SESSION['user_id'] = 2;
                $_SESSION['user_role'] = 'employee';
                $_SESSION['user_name'] = 'Employee User';
                echo json_encode(['success' => true, 'role' => 'employee']);
            } else {
                http_response_code(401);
                echo json_encode(['error' => 'Invalid credentials']);
            }
            break;
            
        case 'logout':
            session_destroy();
            echo json_encode(['success' => true]);
            break;
            
        case 'check':
            if (isset($_SESSION['user_id'])) {
                echo json_encode([
                    'authenticated' => true,
                    'user_id' => $_SESSION['user_id'],
                    'role' => $_SESSION['user_role'],
                    'name' => $_SESSION['user_name']
                ]);
            } else {
                echo json_encode(['authenticated' => false]);
            }
            break;
    }
}

// Campaigns API
function handleCampaignsAPI() {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Not authenticated']);
        return;
    }
    
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    
    switch($action) {
        case 'list':
            // Return realistic campaigns data
            $campaigns = [
                [
                    'id' => 1,
                    'name' => 'Microsoft Security Alert - Q1 2024',
                    'status' => 'active',
                    'recipients' => 150,
                    'clicks' => 45,
                    'reports' => 12,
                    'created_at' => '2024-01-15',
                    'template_type' => 'microsoft',
                    'department' => 'All Departments'
                ],
                [
                    'id' => 2,
                    'name' => 'PayPal Payment Verification',
                    'status' => 'completed',
                    'recipients' => 80,
                    'clicks' => 22,
                    'reports' => 8,
                    'created_at' => '2024-01-10',
                    'template_type' => 'paypal',
                    'department' => 'Finance'
                ],
                [
                    'id' => 3,
                    'name' => 'HR Policy Update Notice',
                    'status' => 'active',
                    'recipients' => 200,
                    'clicks' => 35,
                    'reports' => 15,
                    'created_at' => '2024-01-20',
                    'template_type' => 'hr_notice',
                    'department' => 'Human Resources'
                ],
                [
                    'id' => 4,
                    'name' => 'IT Security Training Reminder',
                    'status' => 'draft',
                    'recipients' => 0,
                    'clicks' => 0,
                    'reports' => 0,
                    'created_at' => '2024-01-25',
                    'template_type' => 'attachment',
                    'department' => 'IT'
                ],
                [
                    'id' => 5,
                    'name' => 'Bank Account Verification',
                    'status' => 'completed',
                    'recipients' => 120,
                    'clicks' => 18,
                    'reports' => 25,
                    'created_at' => '2024-01-05',
                    'template_type' => 'fake_download',
                    'department' => 'Accounting'
                ]
            ];
            echo json_encode(['success' => true, 'campaigns' => $campaigns]);
            break;
            
        case 'create':
            if ($_SESSION['user_role'] !== 'admin') {
                http_response_code(403);
                echo json_encode(['error' => 'Admin access required']);
                return;
            }
            
            $name = $_POST['name'] ?? '';
            $template = $_POST['template'] ?? '';
            $targets = $_POST['targets'] ?? [];
            
            // Simulate campaign creation
            $campaign_id = rand(100, 999);
            echo json_encode([
                'success' => true, 
                'campaign_id' => $campaign_id,
                'message' => 'Campaign created successfully'
            ]);
            break;
            
        case 'send':
            if ($_SESSION['user_role'] !== 'admin') {
                http_response_code(403);
                echo json_encode(['error' => 'Admin access required']);
                return;
            }
            
            $campaign_id = $_POST['campaign_id'] ?? '';
            
            // Simulate email sending
            echo json_encode([
                'success' => true,
                'message' => 'Campaign sent to 50 recipients',
                'sent_count' => 50
            ]);
            break;
            
        case 'stats':
            // Return dashboard statistics
            $stats = [
                'total_campaigns' => 5,
                'active_campaigns' => 2,
                'total_recipients' => 550,
                'total_clicks' => 120,
                'total_reports' => 60,
                'click_rate' => 21.8,
                'report_rate' => 10.9,
                'success_rate' => 78.2,
                'departments' => [
                    'All Departments' => 150,
                    'Finance' => 80,
                    'Human Resources' => 200,
                    'IT' => 0,
                    'Accounting' => 120
                ],
                'recent_activity' => [
                    [
                        'action' => 'Campaign Sent',
                        'campaign' => 'Microsoft Security Alert - Q1 2024',
                        'timestamp' => '2024-01-25 14:30:00',
                        'recipients' => 150
                    ],
                    [
                        'action' => 'Campaign Completed',
                        'campaign' => 'PayPal Payment Verification',
                        'timestamp' => '2024-01-24 16:45:00',
                        'recipients' => 80
                    ],
                    [
                        'action' => 'High Click Rate',
                        'campaign' => 'HR Policy Update Notice',
                        'timestamp' => '2024-01-23 09:15:00',
                        'recipients' => 200
                    ]
                ]
            ];
            echo json_encode(['success' => true, 'stats' => $stats]);
            break;
    }
}

// Tracking API
function handleTrackingAPI() {
    $campaign_id = $_GET['campaign_id'] ?? '';
    $user_id = $_GET['user_id'] ?? '';
    $action = $_GET['action'] ?? 'click';
    
    // Log tracking data (in real implementation, save to database)
    $tracking_data = [
        'campaign_id' => $campaign_id,
        'user_id' => $user_id,
        'action' => $action,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    // In real implementation, save to database
    // For now, just return success
    echo json_encode(['success' => true, 'tracked' => $tracking_data]);
}

// Email API
function handleEmailAPI() {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Not authenticated']);
        return;
    }
    
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    
    switch($action) {
        case 'send':
            $to = $_POST['to'] ?? '';
            $subject = $_POST['subject'] ?? '';
            $content = $_POST['content'] ?? '';
            
            // Simulate email sending
            echo json_encode([
                'success' => true,
                'message' => 'Email sent successfully',
                'to' => $to
            ]);
            break;
            
        case 'templates':
            $templates = [
                [
                    'id' => 1,
                    'name' => 'Microsoft Security Alert',
                    'subject' => 'Urgent: Unusual sign-in activity detected',
                    'type' => 'microsoft'
                ],
                [
                    'id' => 2,
                    'name' => 'PayPal Payment Issue',
                    'subject' => 'Action Required: Payment verification needed',
                    'type' => 'paypal'
                ],
                [
                    'id' => 3,
                    'name' => 'HR Policy Update',
                    'subject' => 'New company policy - immediate action required',
                    'type' => 'hr_notice'
                ]
            ];
            echo json_encode(['success' => true, 'templates' => $templates]);
            break;
    }
}

// Main page logic
$page_title = "TrainMe - Security Awareness Training Platform";
$current_time = date('Y-m-d H:i:s');

// Check authentication
$is_logged_in = isset($_SESSION['user_id']);
$user_role = $_SESSION['user_role'] ?? 'guest';
$user_name = $_SESSION['user_name'] ?? 'Guest';

// Include the HTML template
include 'template.html';
?>
