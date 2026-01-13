<?php
$pageTitle = 'System Settings - Admin Panel';
require_once '../header.php';
require_once '../../../db_connection.php';

// Get all settings from database (we'll need to create a settings table)
// For now, let's create a sample settings system

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_general':
                // Update general settings logic here
                echo '<script>alert("General settings updated successfully!");</script>';
                break;
                
            case 'update_email':
                // Update email settings logic here
                echo '<script>alert("Email settings updated successfully!");</script>';
                break;
                
            case 'update_payment':
                // Update payment settings logic here
                echo '<script>alert("Payment settings updated successfully!");</script>';
                break;
        }
    }
}

// Default settings
$settings = [
    'general' => [
        'site_name' => 'CinemaKrish',
        'site_title' => 'CinemaKrish - Your Movie Experience',
        'site_description' => 'Premium movie theater with state-of-the-art facilities',
        'site_keywords' => 'movies, cinema, tickets, booking, entertainment',
        'admin_email' => 'admin@cinemakrish.com',
        'timezone' => 'America/New_York',
        'date_format' => 'Y-m-d',
        'time_format' => 'h:i A'
    ],
    'email' => [
        'smtp_host' => 'smtp.gmail.com',
        'smtp_port' => '587',
        'smtp_username' => 'your-email@gmail.com',
        'smtp_password' => 'your-password',
        'smtp_encryption' => 'tls',
        'from_email' => 'noreply@cinemakrish.com',
        'from_name' => 'CinemaKrish'
    ],
    'payment' => [
        'currency' => 'USD',
        'tax_rate' => '10',
        'booking_fee' => '2.50',
        'payment_methods' => 'credit_card,debit_card,paypal,cash',
        'test_mode' => '1'
    ]
];
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5>System Settings</h5>
            </div>
            <div class="card-body">
                <ul class="nav nav-tabs" id="settingsTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="general-tab" data-bs-toggle="tab" 
                                data-bs-target="#general" type="button" role="tab">
                            <i class="fas fa-cog me-2"></i>General
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="email-tab" data-bs-toggle="tab" 
                                data-bs-target="#email" type="button" role="tab">
                            <i class="fas fa-envelope me-2"></i>Email
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="payment-tab" data-bs-toggle="tab" 
                                data-bs-target="#payment" type="button" role="tab">
                            <i class="fas fa-credit-card me-2"></i>Payment
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="maintenance-tab" data-bs-toggle="tab" 
                                data-bs-target="#maintenance" type="button" role="tab">
                            <i class="fas fa-tools me-2"></i>Maintenance
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content mt-4" id="settingsTabsContent">
                    <!-- General Settings Tab -->
                    <div class="tab-pane fade show active" id="general" role="tabpanel">
                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Site Name *</label>
                                        <input type="text" class="form-control" name="site_name" 
                                               value="<?php echo $settings['general']['site_name']; ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Site Title *</label>
                                        <input type="text" class="form-control" name="site_title" 
                                               value="<?php echo $settings['general']['site_title']; ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Site Description</label>
                                        <textarea class="form-control" name="site_description" rows="3"><?php echo $settings['general']['site_description']; ?></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Site Keywords</label>
                                        <input type="text" class="form-control" name="site_keywords" 
                                               value="<?php echo $settings['general']['site_keywords']; ?>">
                                        <small class="text-muted">Separate with commas</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Admin Email *</label>
                                        <input type="email" class="form-control" name="admin_email" 
                                               value="<?php echo $settings['general']['admin_email']; ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Timezone</label>
                                        <select class="form-select" name="timezone">
                                            <option value="America/New_York" <?php echo $settings['general']['timezone'] == 'America/New_York' ? 'selected' : ''; ?>>Eastern Time (ET)</option>
                                            <option value="America/Chicago" <?php echo $settings['general']['timezone'] == 'America/Chicago' ? 'selected' : ''; ?>>Central Time (CT)</option>
                                            <option value="America/Denver" <?php echo $settings['general']['timezone'] == 'America/Denver' ? 'selected' : ''; ?>>Mountain Time (MT)</option>
                                            <option value="America/Los_Angeles" <?php echo $settings['general']['timezone'] == 'America/Los_Angeles' ? 'selected' : ''; ?>>Pacific Time (PT)</option>
                                            <option value="UTC" <?php echo $settings['general']['timezone'] == 'UTC' ? 'selected' : ''; ?>>UTC</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Date Format</label>
                                        <select class="form-select" name="date_format">
                                            <option value="Y-m-d" <?php echo $settings['general']['date_format'] == 'Y-m-d' ? 'selected' : ''; ?>>YYYY-MM-DD</option>
                                            <option value="d/m/Y" <?php echo $settings['general']['date_format'] == 'd/m/Y' ? 'selected' : ''; ?>>DD/MM/YYYY</option>
                                            <option value="m/d/Y" <?php echo $settings['general']['date_format'] == 'm/d/Y' ? 'selected' : ''; ?>>MM/DD/YYYY</option>
                                            <option value="d-M-Y" <?php echo $settings['general']['date_format'] == 'd-M-Y' ? 'selected' : ''; ?>>DD-Mon-YYYY</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Time Format</label>
                                        <select class="form-select" name="time_format">
                                            <option value="h:i A" <?php echo $settings['general']['time_format'] == 'h:i A' ? 'selected' : ''; ?>>12-hour (1:30 PM)</option>
                                            <option value="H:i" <?php echo $settings['general']['time_format'] == 'H:i' ? 'selected' : ''; ?>>24-hour (13:30)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary" name="action" value="update_general">
                                <i class="fas fa-save"></i> Save General Settings
                            </button>
                        </form>
                    </div>
                    
                    <!-- Email Settings Tab -->
                    <div class="tab-pane fade" id="email" role="tabpanel">
                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">SMTP Host *</label>
                                        <input type="text" class="form-control" name="smtp_host" 
                                               value="<?php echo $settings['email']['smtp_host']; ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">SMTP Port *</label>
                                        <input type="number" class="form-control" name="smtp_port" 
                                               value="<?php echo $settings['email']['smtp_port']; ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">SMTP Username *</label>
                                        <input type="text" class="form-control" name="smtp_username" 
                                               value="<?php echo $settings['email']['smtp_username']; ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">SMTP Password *</label>
                                        <input type="password" class="form-control" name="smtp_password" 
                                               value="<?php echo $settings['email']['smtp_password']; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">SMTP Encryption</label>
                                        <select class="form-select" name="smtp_encryption">
                                            <option value="tls" <?php echo $settings['email']['smtp_encryption'] == 'tls' ? 'selected' : ''; ?>>TLS</option>
                                            <option value="ssl" <?php echo $settings['email']['smtp_encryption'] == 'ssl' ? 'selected' : ''; ?>>SSL</option>
                                            <option value="" <?php echo empty($settings['email']['smtp_encryption']) ? 'selected' : ''; ?>>None</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">From Email *</label>
                                        <input type="email" class="form-control" name="from_email" 
                                               value="<?php echo $settings['email']['from_email']; ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">From Name *</label>
                                        <input type="text" class="form-control" name="from_name" 
                                               value="<?php echo $settings['email']['from_name']; ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <button type="button" class="btn btn-outline-primary" onclick="testEmailSettings()">
                                            <i class="fas fa-paper-plane"></i> Test Email Settings
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary" name="action" value="update_email">
                                <i class="fas fa-save"></i> Save Email Settings
                            </button>
                        </form>
                    </div>
                    
                    <!-- Payment Settings Tab -->
                    <div class="tab-pane fade" id="payment" role="tabpanel">
                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Currency *</label>
                                        <select class="form-select" name="currency" required>
                                            <option value="USD" <?php echo $settings['payment']['currency'] == 'USD' ? 'selected' : ''; ?>>US Dollar (USD)</option>
                                            <option value="EUR" <?php echo $settings['payment']['currency'] == 'EUR' ? 'selected' : ''; ?>>Euro (EUR)</option>
                                            <option value="GBP" <?php echo $settings['payment']['currency'] == 'GBP' ? 'selected' : ''; ?>>British Pound (GBP)</option>
                                            <option value="INR" <?php echo $settings['payment']['currency'] == 'INR' ? 'selected' : ''; ?>>Indian Rupee (INR)</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Tax Rate (%)</label>
                                        <input type="number" class="form-control" name="tax_rate" step="0.01" 
                                               value="<?php echo $settings['payment']['tax_rate']; ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Booking Fee</label>
                                        <input type="number" class="form-control" name="booking_fee" step="0.01" 
                                               value="<?php echo $settings['payment']['booking_fee']; ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Payment Methods</label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="payment_methods[]" value="credit_card" 
                                                   <?php echo strpos($settings['payment']['payment_methods'], 'credit_card') !== false ? 'checked' : ''; ?>>
                                            <label class="form-check-label">Credit Card</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="payment_methods[]" value="debit_card" 
                                                   <?php echo strpos($settings['payment']['payment_methods'], 'debit_card') !== false ? 'checked' : ''; ?>>
                                            <label class="form-check-label">Debit Card</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="payment_methods[]" value="paypal" 
                                                   <?php echo strpos($settings['payment']['payment_methods'], 'paypal') !== false ? 'checked' : ''; ?>>
                                            <label class="form-check-label">PayPal</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="payment_methods[]" value="cash" 
                                                   <?php echo strpos($settings['payment']['payment_methods'], 'cash') !== false ? 'checked' : ''; ?>>
                                            <label class="form-check-label">Cash</label>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="test_mode" value="1" 
                                                   <?php echo $settings['payment']['test_mode'] == '1' ? 'checked' : ''; ?>>
                                            <label class="form-check-label">Test Mode</label>
                                        </div>
                                        <small class="text-muted">Enable test mode for payment gateways</small>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary" name="action" value="update_payment">
                                <i class="fas fa-save"></i> Save Payment Settings
                            </button>
                        </form>
                    </div>
                    
                    <!-- Maintenance Tab -->
                    <div class="tab-pane fade" id="maintenance" role="tabpanel">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Warning:</strong> These actions may affect the system. Proceed with caution.
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-header bg-danger text-white">
                                        <h6 class="mb-0"><i class="fas fa-database me-2"></i>Database Backup</h6>
                                    </div>
                                    <div class="card-body">
                                        <p>Create a backup of your database.</p>
                                        <button class="btn btn-outline-primary" onclick="createBackup()">
                                            <i class="fas fa-download"></i> Create Backup
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-header bg-warning text-dark">
                                        <h6 class="mb-0"><i class="fas fa-broom me-2"></i>Cache Clear</h6>
                                    </div>
                                    <div class="card-body">
                                        <p>Clear all system cache files.</p>
                                        <button class="btn btn-outline-warning" onclick="clearCache()">
                                            <i class="fas fa-trash"></i> Clear Cache
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header bg-info text-white">
                                        <h6 class="mb-0"><i class="fas fa-chart-line me-2"></i>System Information</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <table class="table table-bordered">
                                                    <tr>
                                                        <th>PHP Version</th>
                                                        <td><?php echo phpversion(); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Database</th>
                                                        <td>MySQL/MariaDB</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Server Software</th>
                                                        <td><?php echo $_SERVER['SERVER_SOFTWARE']; ?></td>
                                                    </tr>
                                                </table>
                                            </div>
                                            <div class="col-md-6">
                                                <table class="table table-bordered">
                                                    <tr>
                                                        <th>System Load</th>
                                                        <td><?php echo sys_getloadavg()[0]; ?> (1 min)</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Memory Usage</th>
                                                        <td><?php echo round(memory_get_usage() / 1024 / 1024, 2); ?> MB</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Uptime</th>
                                                        <td><?php echo round(time() - strtotime(date('Y-m-d 00:00:00')), 2); ?> seconds</td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function testEmailSettings() {
    alert('This feature would test email settings. Implementation required.');
}

function createBackup() {
    if (confirm('Create database backup? This may take a moment.')) {
        window.location.href = 'ajax/create_backup.php';
    }
}

function clearCache() {
    if (confirm('Clear all cache files?')) {
        window.location.href = 'ajax/clear_cache.php';
    }
}

// Initialize tabs
document.addEventListener('DOMContentLoaded', function() {
    var triggerTabList = [].slice.call(document.querySelectorAll('#settingsTabs button'));
    triggerTabList.forEach(function (triggerEl) {
        var tabTrigger = new bootstrap.Tab(triggerEl);
        triggerEl.addEventListener('click', function (event) {
            event.preventDefault();
            tabTrigger.show();
        });
    });
});
</script>

<?php require_once '../footer.php'; ?>