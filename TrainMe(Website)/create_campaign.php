<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SESSION['user_role'] !== 'admin') {
    header('Location: admin.php');
    exit;
}

$page_title = "Create Campaign - TrainMe";
$user_name = $_SESSION['user_name'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .campaign-form-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 2rem;
        }
        .campaign-form-section {
            background: white;
            border-radius: 12px;
            padding: 2.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border: 1px solid #f3f4f6;
        }
        .campaign-form-section h2 {
            margin-top: 0;
            color: #111827;
            font-size: 1.375rem;
            font-weight: 700;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 0.75rem;
            margin-bottom: 2rem;
            letter-spacing: -0.01em;
        }
        .form-group {
            margin-bottom: 1.75rem;
        }
        .form-group:last-child {
            margin-bottom: 0;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.625rem;
            font-weight: 600;
            color: #374151;
            font-size: 0.9375rem;
        }
        .form-group label::after {
            content: '';
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 1.5px solid #d1d5db;
            border-radius: 8px;
            font-size: 0.9375rem;
            transition: all 0.2s;
            background: #ffffff;
            color: #111827;
            font-family: inherit;
        }
        .form-group input::placeholder,
        .form-group textarea::placeholder {
            color: #9ca3af;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
            background: #ffffff;
        }
        .form-group input:hover,
        .form-group select:hover,
        .form-group textarea:hover {
            border-color: #9ca3af;
        }
        .form-group textarea {
            min-height: 120px;
            resize: vertical;
            line-height: 1.5;
        }
        .template-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }
        .template-card {
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 1.5rem;
            cursor: pointer;
            transition: all 0.2s;
            text-align: center;
        }
        .template-card:hover {
            border-color: #2563eb;
            background: #f0f9ff;
        }
        .template-card.selected {
            border-color: #2563eb;
            background: #eff6ff;
        }
        .template-card .template-icon {
            font-size: 3rem;
            margin-bottom: 0.5rem;
        }
        .template-card .template-name {
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.25rem;
        }
        .template-card .template-desc {
            font-size: 0.875rem;
            color: #6b7280;
        }
        .recipient-options {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }
        .recipient-option {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem 1.25rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            background: white;
            cursor: pointer;
            transition: all 0.2s;
        }
        .recipient-option:hover {
            border-color: #2563eb;
            background: #f0f9ff;
        }
        .recipient-option input[type="radio"] {
            width: 18px;
            height: 18px;
            margin: 0;
            cursor: pointer;
            accent-color: #2563eb;
        }
        .recipient-option input[type="radio"]:checked {
            accent-color: #2563eb;
        }
        .recipient-option label {
            flex: 1;
            margin: 0;
            cursor: pointer;
            font-weight: 500;
            color: #374151;
            font-size: 0.9375rem;
        }
        .recipient-option:has(input:checked) {
            border-color: #2563eb;
            background: #eff6ff;
        }
        .recipient-option:has(input:checked) label {
            color: #2563eb;
            font-weight: 600;
        }
        .recipients-list {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 1rem;
            background: #f9fafb;
            margin-top: 1rem;
        }
        .recipients-list::-webkit-scrollbar {
            width: 6px;
        }
        .recipients-list::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 3px;
        }
        .recipients-list::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }
        .recipients-list::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        .recipient-item {
            display: flex;
            align-items: center;
            padding: 0.75rem;
            margin-bottom: 0.5rem;
            background: white;
            border-radius: 6px;
            border: 1px solid #e5e7eb;
            transition: all 0.15s;
        }
        .recipient-item:hover {
            border-color: #d1d5db;
            background: #f9fafb;
        }
        .recipient-item input[type="checkbox"] {
            width: 18px;
            height: 18px;
            margin-right: 0.75rem;
            cursor: pointer;
            accent-color: #2563eb;
        }
        .recipient-item label {
            flex: 1;
            margin: 0;
            cursor: pointer;
            font-weight: 400;
            color: #374151;
            font-size: 0.875rem;
        }
        .preview-section {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 1.5rem;
            margin-top: 1rem;
        }
        .preview-email {
            background: white;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 1.5rem;
            max-width: 600px;
            margin: 0 auto;
        }
        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 2rem;
        }
        .btn-secondary {
            background: #6b7280;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
        }
        .btn-secondary:hover {
            background: #4b5563;
        }
        .loading {
            display: none;
            text-align: center;
            padding: 1rem;
            color: #2563eb;
        }
        .error-message {
            background: #fee2e2;
            color: #dc2626;
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1rem;
            display: none;
        }
        .success-message {
            background: #d1fae5;
            color: #059669;
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1rem;
            display: none;
        }
    </style>
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
                    <li><a href="admin.php">Admin Dashboard</a></li>
                </ul>
            </nav>
            <div class="nav-buttons">
                <span style="margin-right: 1rem; color: #374151;"><?php echo htmlspecialchars($user_name); ?></span>
                <button onclick="window.location.href='admin.php'" class="btn btn-outline">Back to Dashboard</button>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="campaign-form-container">
            <div class="error-message" id="error-message"></div>
            <div class="success-message" id="success-message"></div>

            <form id="campaign-form">
                <!-- Campaign Basic Info -->
                <div class="campaign-form-section">
                    <h2>1. Campaign Information</h2>
                    <div class="form-group">
                        <label for="campaign-name">Campaign Name *</label>
                        <input type="text" id="campaign-name" name="name" required placeholder="e.g., Q4 Security Awareness Test">
                    </div>
                    <div class="form-group">
                        <label for="campaign-description">Description (Optional)</label>
                        <textarea id="campaign-description" name="description" placeholder="Brief description of this campaign"></textarea>
                    </div>
                </div>

                <!-- Email Template Selection -->
                <div class="campaign-form-section">
                    <h2>2. Select Email Template</h2>
                    <div class="template-grid" id="template-grid">
                        <div class="template-card" data-template="microsoft">
                            <div class="template-icon">🔷</div>
                            <div class="template-name">Microsoft</div>
                            <div class="template-desc">Office 365 / Account Security</div>
                        </div>
                        <div class="template-card" data-template="paypal">
                            <div class="template-icon">💳</div>
                            <div class="template-name">PayPal</div>
                            <div class="template-desc">Payment Security Alert</div>
                        </div>
                        <div class="template-card" data-template="amazon">
                            <div class="template-icon">📦</div>
                            <div class="template-name">Amazon</div>
                            <div class="template-desc">Account Verification</div>
                        </div>
                        <div class="template-card" data-template="google">
                            <div class="template-icon">🔍</div>
                            <div class="template-name">Google</div>
                            <div class="template-desc">Account Security Alert</div>
                        </div>
                        <div class="template-card" data-template="apple">
                            <div class="template-icon">🍎</div>
                            <div class="template-name">Apple</div>
                            <div class="template-desc">iCloud Security Notice</div>
                        </div>
                        <div class="template-card" data-template="bank">
                            <div class="template-icon">🏦</div>
                            <div class="template-name">Bank</div>
                            <div class="template-desc">Account Security Alert</div>
                        </div>
                    </div>
                    <input type="hidden" id="selected-template" name="template" required>
                </div>

                <!-- Email Content -->
                <div class="campaign-form-section" id="email-content-section" style="display: none;">
                    <h2>3. Email Content</h2>
                    <div class="form-group">
                        <label for="email-subject">Email Subject *</label>
                        <input type="text" id="email-subject" name="subject" required placeholder="e.g., Action Required: Verify Your Account">
                    </div>
                    <div class="form-group">
                        <label for="email-content">Email Body (HTML) *</label>
                        <textarea id="email-content" name="email_content" required placeholder="Email content will be auto-filled based on template"></textarea>
                        <small style="color: #6b7280; margin-top: 0.5rem; display: block;">You can customize the email content. Use {name} for recipient name, {link} for tracking link.</small>
                    </div>
                    <div class="form-group">
                        <label for="sender-name">Sender Name</label>
                        <input type="text" id="sender-name" name="sender_name" value="Security Team" placeholder="e.g., Microsoft Security">
                    </div>
                    <div class="form-group">
                        <label for="sender-email">Sender Email</label>
                        <input type="email" id="sender-email" name="sender_email" value="noreply@trainme.com" placeholder="e.g., security@microsoft.com">
                    </div>
                </div>

                <!-- Recipients Selection -->
                <div class="campaign-form-section">
                    <h2>4. Select Recipients</h2>
                    <div class="recipient-options">
                        <div class="recipient-option">
                            <input type="radio" name="recipient-type" value="all" id="recipient-all" checked>
                            <label for="recipient-all">Send to All Employees</label>
                        </div>
                        <div class="recipient-option">
                            <input type="radio" name="recipient-type" value="selected" id="recipient-selected">
                            <label for="recipient-selected">Select Specific Users</label>
                        </div>
                    </div>
                    <div class="recipients-list" id="recipients-list" style="display: none;">
                        <!-- Recipients will be loaded here -->
                    </div>
                </div>

                <!-- Preview Section -->
                <div class="campaign-form-section" id="preview-section" style="display: none;">
                    <h2>5. Preview</h2>
                    <div class="preview-section">
                        <div class="preview-email" id="email-preview">
                            <!-- Email preview will be shown here -->
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <a href="admin.php" class="btn-secondary">Cancel</a>
                    <button type="button" onclick="saveDraft()" class="btn btn-outline">Save as Draft</button>
                    <button type="submit" class="btn btn-primary">Create & Send Campaign</button>
                </div>
                <div class="loading" id="loading">Creating campaign...</div>
            </form>
        </div>
    </main>

    <script>
        let selectedTemplate = '';
        let emailTemplates = {};
        let allUsers = [];

        // Load email templates and users on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadEmailTemplates();
            loadUsers();
            setupEventListeners();
        });

        // Setup event listeners
        function setupEventListeners() {
            // Template selection
            document.querySelectorAll('.template-card').forEach(card => {
                card.addEventListener('click', function() {
                    document.querySelectorAll('.template-card').forEach(c => c.classList.remove('selected'));
                    this.classList.add('selected');
                    selectedTemplate = this.getAttribute('data-template');
                    document.getElementById('selected-template').value = selectedTemplate;
                    loadTemplateContent(selectedTemplate);
                });
            });

            // Recipient type selection
            document.querySelectorAll('input[name="recipient-type"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    const recipientsList = document.getElementById('recipients-list');
                    if (this.value === 'selected') {
                        recipientsList.style.display = 'block';
                    } else {
                        recipientsList.style.display = 'none';
                    }
                });
            });

            // Form submission
            document.getElementById('campaign-form').addEventListener('submit', function(e) {
                e.preventDefault();
                createCampaign(false);
            });
        }

        // Load email templates
        async function loadEmailTemplates() {
            try {
                const response = await fetch('campaign_templates.php?action=get_templates');
                const data = await response.json();
                if (data.success) {
                    emailTemplates = data.templates;
                }
            } catch (error) {
                console.error('Error loading templates:', error);
            }
        }

        // Load users for recipient selection
        async function loadUsers() {
            try {
                const response = await fetch('index.php?api=users&action=list');
                const data = await response.json();
                if (data.success && data.users) {
                    allUsers = data.users.filter(user => user.role === 'employee');
                    renderRecipientsList();
                }
            } catch (error) {
                console.error('Error loading users:', error);
            }
        }

        // Render recipients list
        function renderRecipientsList() {
            const recipientsList = document.getElementById('recipients-list');
            recipientsList.innerHTML = '';
            
            allUsers.forEach(user => {
                const item = document.createElement('div');
                item.className = 'recipient-item';
                item.innerHTML = `
                    <input type="checkbox" name="recipients[]" value="${user.id}" id="user-${user.id}">
                    <label for="user-${user.id}" style="flex: 1; cursor: pointer;">
                        <strong>${user.name}</strong> - ${user.email}
                    </label>
                `;
                recipientsList.appendChild(item);
            });
        }

        // Load template content
        function loadTemplateContent(template) {
            if (!emailTemplates[template]) {
                showError('Template not found. Please try again.');
                return;
            }

            const templateData = emailTemplates[template];
            document.getElementById('email-subject').value = templateData.subject || '';
            document.getElementById('email-content').value = templateData.content || '';
            document.getElementById('sender-name').value = templateData.sender_name || 'Security Team';
            document.getElementById('sender-email').value = templateData.sender_email || 'noreply@trainme.com';

            // Show email content section
            document.getElementById('email-content-section').style.display = 'block';
            
            // Update preview
            updatePreview();
        }

        // Update email preview
        function updatePreview() {
            const subject = document.getElementById('email-subject').value;
            const content = document.getElementById('email-content').value;
            const senderName = document.getElementById('sender-name').value;

            if (!content) {
                document.getElementById('preview-section').style.display = 'none';
                return;
            }

            // Replace placeholders
            let previewContent = content
                .replace(/{name}/g, 'John Doe')
                .replace(/{link}/g, '#');

            document.getElementById('email-preview').innerHTML = `
                <div style="margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid #e5e7eb;">
                    <div><strong>From:</strong> ${senderName}</div>
                    <div><strong>Subject:</strong> ${subject}</div>
                </div>
                <div>${previewContent}</div>
            `;
            document.getElementById('preview-section').style.display = 'block';
        }

        // Add event listeners for preview updates
        document.getElementById('email-subject').addEventListener('input', updatePreview);
        document.getElementById('email-content').addEventListener('input', updatePreview);
        document.getElementById('sender-name').addEventListener('input', updatePreview);

        // Save as draft
        function saveDraft() {
            createCampaign(true);
        }

        // Create campaign
        async function createCampaign(isDraft) {
            const form = document.getElementById('campaign-form');
            const formData = new FormData(form);
            formData.append('action', 'create');
            formData.append('is_draft', isDraft ? '1' : '0');

            // Get selected recipients
            const recipientType = document.querySelector('input[name="recipient-type"]:checked').value;
            formData.append('recipient_type', recipientType);

            if (recipientType === 'selected') {
                const selectedRecipients = Array.from(document.querySelectorAll('input[name="recipients[]"]:checked'))
                    .map(cb => cb.value);
                if (selectedRecipients.length === 0) {
                    showError('Please select at least one recipient.');
                    return;
                }
                formData.append('recipients', JSON.stringify(selectedRecipients));
            }

            // Validate required fields
            if (!formData.get('name') || !formData.get('template') || !formData.get('subject') || !formData.get('email_content')) {
                showError('Please fill in all required fields.');
                return;
            }

            document.getElementById('loading').style.display = 'block';
            hideMessages();

            try {
                const response = await fetch('index.php?api=campaigns', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    let message = isDraft ? 'Campaign saved as draft successfully!' : 'Campaign created and sent successfully!';
                    
                    // Show warning if recipients weren't added
                    if (data.warning) {
                        message += '\n\n⚠️ Warning: ' + data.warning;
                        if (data.recipients === 0) {
                            message += '\n\nNo emails were sent because no recipients were added.';
                        }
                    } else if (data.recipients > 0 && data.sent === 0 && !isDraft) {
                        message += '\n\n⚠️ Warning: Campaign created but no emails were sent. Check error logs.';
                    }
                    
                    showSuccess(message);
                    setTimeout(() => {
                        window.location.href = 'admin.php';
                    }, 3000);
                } else {
                    showError(data.error || 'Failed to create campaign. Please try again.');
                }
            } catch (error) {
                console.error('Error creating campaign:', error);
                showError('An error occurred. Please try again.');
            } finally {
                document.getElementById('loading').style.display = 'none';
            }
        }

        // Show error message
        function showError(message) {
            const errorDiv = document.getElementById('error-message');
            errorDiv.textContent = message;
            errorDiv.style.display = 'block';
            document.getElementById('success-message').style.display = 'none';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // Show success message
        function showSuccess(message) {
            const successDiv = document.getElementById('success-message');
            successDiv.textContent = message;
            successDiv.style.display = 'block';
            document.getElementById('error-message').style.display = 'none';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // Hide messages
        function hideMessages() {
            document.getElementById('error-message').style.display = 'none';
            document.getElementById('success-message').style.display = 'none';
        }
    </script>
</body>
</html>

