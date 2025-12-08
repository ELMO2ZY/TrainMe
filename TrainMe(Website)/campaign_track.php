<?php
/**
 * Campaign Tracking Page
 * Handles click tracking for phishing campaign links
 */

session_start();
require_once __DIR__ . '/index.php'; // For database connection

$token = $_GET['token'] ?? '';
$campaignId = (int)($_GET['campaign'] ?? 0);
$recipientId = (int)($_GET['recipient'] ?? 0);

if (empty($token) || $campaignId <= 0 || $recipientId <= 0) {
    // Invalid tracking link
    header('Location: training_phishing.php?campaign=invalid');
    exit;
}

$pdo = getDBConnection();

if ($pdo !== null) {
    try {
        // Get campaign and recipient info - verify token
        $stmt = $pdo->prepare("
            SELECT c.*, cr.user_id, cr.email, cr.name as recipient_name, cr.token
            FROM campaigns c
            JOIN campaign_recipients cr ON c.id = cr.campaign_id
            WHERE c.id = ? AND cr.id = ? AND cr.token = ?
        ");
        $stmt->execute([$campaignId, $recipientId, $token]);
        $campaign = $stmt->fetch();
        
        if ($campaign) {
            // Check if this click was already recorded (prevent duplicates)
            $checkStmt = $pdo->prepare("
                SELECT id FROM campaign_tracking 
                WHERE campaign_id = ? AND recipient_id = ? AND action_type = 'link_clicked'
                LIMIT 1
            ");
            $checkStmt->execute([$campaignId, $recipientId]);
            $existingClick = $checkStmt->fetch();
            
            if (!$existingClick) {
                // Record click
                $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
                $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
                $referrer = $_SERVER['HTTP_REFERER'] ?? '';
                
                $stmt = $pdo->prepare("
                    INSERT INTO campaign_tracking (campaign_id, recipient_id, user_id, action_type, ip_address, user_agent, referrer, clicked_url, created_at)
                    VALUES (?, ?, ?, 'link_clicked', ?, ?, ?, ?, NOW())
                ");
                $stmt->execute([
                    $campaignId, 
                    $recipientId, 
                    $campaign['user_id'],
                    $ipAddress,
                    $userAgent,
                    $referrer,
                    $_SERVER['REQUEST_URI'] ?? ''
                ]);
                
                // Update recipient status and click count
                $stmt = $pdo->prepare("
                    UPDATE campaign_recipients 
                    SET status = 'clicked', 
                        clicked_at = NOW(),
                        click_count = COALESCE(click_count, 0) + 1
                    WHERE id = ?
                ");
                $stmt->execute([$recipientId]);
                
                // Update campaign click count - use COALESCE to handle NULL
                $stmt = $pdo->prepare("
                    UPDATE campaigns 
                    SET total_clicks = COALESCE(total_clicks, 0) + 1 
                    WHERE id = ?
                ");
                $stmt->execute([$campaignId]);
                
                error_log("Updated total_clicks for campaign $campaignId");
                
                // Store in session for training page
                $_SESSION['phishing_campaign_clicked'] = true;
                $_SESSION['phishing_campaign_id'] = $campaignId;
                $_SESSION['phishing_campaign_name'] = $campaign['name'];
            } else {
                error_log("Click already recorded for recipient $recipientId in campaign $campaignId - skipping duplicate");
            }
        } else {
            error_log("Invalid token, campaign ID, or recipient ID for tracking: token=$token, campaign=$campaignId, recipient=$recipientId");
        }
    } catch (PDOException $e) {
        error_log("Campaign tracking error: " . $e->getMessage());
    }
}

// Redirect to phishing training page with educational message
header('Location: training_phishing.php?campaign=clicked&campaign_id=' . $campaignId);
exit;

