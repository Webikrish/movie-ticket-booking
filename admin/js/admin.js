// Admin Panel JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialize popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
    
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        var alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
        alerts.forEach(function(alert) {
            var bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
    
    // Confirm before delete
    document.querySelectorAll('form[onsubmit]').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            if (this.getAttribute('onsubmit') && !confirm('Are you sure?')) {
                e.preventDefault();
            }
        });
    });
    
    // DataTables initialization (if DataTables is loaded)
    if (typeof $.fn.DataTable !== 'undefined') {
        $('#usersTable, #moviesTable, #bookingsTable, #theatresTable, #showsTable').DataTable({
            "pageLength": 25,
            "order": [[0, 'desc']],
            "language": {
                "search": "Search:",
                "lengthMenu": "Show _MENU_ entries",
                "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                "paginate": {
                    "first": "First",
                    "last": "Last",
                    "next": "Next",
                    "previous": "Previous"
                }
            }
        });
    }
    
    // Form validation
    document.querySelectorAll('form[data-validate]').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            var isValid = true;
            var requiredFields = form.querySelectorAll('[required]');
            
            requiredFields.forEach(function(field) {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                    
                    // Create error message if not exists
                    if (!field.nextElementSibling || !field.nextElementSibling.classList.contains('invalid-feedback')) {
                        var errorDiv = document.createElement('div');
                        errorDiv.className = 'invalid-feedback';
                        errorDiv.textContent = 'This field is required';
                        field.parentNode.appendChild(errorDiv);
                    }
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    });
    
    // Remove invalid class on input
    document.querySelectorAll('input, select, textarea').forEach(function(field) {
        field.addEventListener('input', function() {
            this.classList.remove('is-invalid');
        });
    });
    
    // Password strength meter
    var passwordInput = document.getElementById('password');
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            var strength = checkPasswordStrength(this.value);
            var meter = document.getElementById('password-strength-meter');
            var text = document.getElementById('password-strength-text');
            
            if (meter && text) {
                meter.value = strength.score;
                meter.className = 'form-range password-strength-' + strength.score;
                
                var messages = [
                    'Very weak',
                    'Weak',
                    'Fair',
                    'Good',
                    'Strong'
                ];
                text.textContent = messages[strength.score - 1] || '';
                text.className = 'text-' + strength.color;
            }
        });
    }
    
    // Image preview
    document.querySelectorAll('input[type="url"][data-preview]').forEach(function(input) {
        input.addEventListener('input', function() {
            var previewId = this.getAttribute('data-preview');
            var preview = document.getElementById(previewId);
            
            if (preview && this.value) {
                preview.src = this.value;
                preview.style.display = 'block';
            }
        });
    });
    
    // Auto-generate slug
    document.querySelectorAll('input[name="title"][data-slug-target]').forEach(function(input) {
        input.addEventListener('input', function() {
            var target = document.getElementById(this.getAttribute('data-slug-target'));
            if (target) {
                target.value = generateSlug(this.value);
            }
        });
    });
    
    // Character counter for textareas
    document.querySelectorAll('textarea[data-max-length]').forEach(function(textarea) {
        var maxLength = textarea.getAttribute('data-max-length');
        var counter = document.createElement('small');
        counter.className = 'form-text text-muted float-end';
        counter.textContent = '0/' + maxLength;
        textarea.parentNode.appendChild(counter);
        
        textarea.addEventListener('input', function() {
            var remaining = maxLength - this.value.length;
            counter.textContent = this.value.length + '/' + maxLength;
            counter.className = 'form-text float-end ' + (remaining < 0 ? 'text-danger' : 'text-muted');
        });
    });
    
    // Bulk actions
    document.getElementById('bulk-action-select')?.addEventListener('change', function() {
        var action = this.value;
        var selected = document.querySelectorAll('input[name="selected[]"]:checked');
        
        if (selected.length === 0 && action !== '') {
            alert('Please select items first');
            this.value = '';
            return;
        }
        
        if (action && confirm('Apply "' + action + '" to ' + selected.length + ' item(s)?')) {
            document.getElementById('bulk-action-form').submit();
        }
    });
});

// Utility Functions
function generateSlug(text) {
    return text.toString().toLowerCase()
        .replace(/\s+/g, '-')           // Replace spaces with -
        .replace(/[^\w\-]+/g, '')       // Remove all non-word chars
        .replace(/\-\-+/g, '-')         // Replace multiple - with single -
        .replace(/^-+/, '')             // Trim - from start of text
        .replace(/-+$/, '');            // Trim - from end of text
}

function checkPasswordStrength(password) {
    var score = 0;
    var color = 'danger';
    
    if (!password) {
        return { score: 0, color: 'danger' };
    }
    
    // Length check
    if (password.length >= 8) score++;
    if (password.length >= 12) score++;
    
    // Complexity checks
    if (/[a-z]/.test(password)) score++; // Lowercase
    if (/[A-Z]/.test(password)) score++; // Uppercase
    if (/[0-9]/.test(password)) score++; // Numbers
    if (/[^a-zA-Z0-9]/.test(password)) score++; // Special chars
    
    // Cap score at 5
    score = Math.min(score, 5);
    
    // Set color based on score
    if (score >= 4) color = 'success';
    else if (score >= 3) color = 'primary';
    else if (score >= 2) color = 'warning';
    else color = 'danger';
    
    return { score: score, color: color };
}

// AJAX Helper Functions
function ajaxRequest(url, data, callback) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', url, true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                try {
                    var response = JSON.parse(xhr.responseText);
                    callback(response);
                } catch (e) {
                    callback({ success: false, message: 'Invalid response' });
                }
            } else {
                callback({ success: false, message: 'Request failed' });
            }
        }
    };
    
    var params = [];
    for (var key in data) {
        params.push(key + '=' + encodeURIComponent(data[key]));
    }
    xhr.send(params.join('&'));
}

function showToast(message, type = 'success') {
    var toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.className = 'position-fixed bottom-0 end-0 p-3';
        toastContainer.style.zIndex = '11';
        document.body.appendChild(toastContainer);
    }
    
    var toastId = 'toast-' + Date.now();
    var toastHtml = `
        <div id="${toastId}" class="toast align-items-center text-bg-${type} border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    
    toastContainer.innerHTML += toastHtml;
    var toastElement = document.getElementById(toastId);
    var toast = new bootstrap.Toast(toastElement, { delay: 3000 });
    toast.show();
    
    toastElement.addEventListener('hidden.bs.toast', function() {
        this.remove();
    });
}

// Export/Import functions
function exportTableToCSV(tableId, filename) {
    var table = document.getElementById(tableId);
    var rows = table.querySelectorAll('tr');
    var csv = [];
    
    for (var i = 0; i < rows.length; i++) {
        var row = [], cols = rows[i].querySelectorAll('td, th');
        
        for (var j = 0; j < cols.length; j++) {
            row.push('"' + cols[j].innerText.replace(/"/g, '""') + '"');
        }
        
        csv.push(row.join(','));
    }
    
    var csvContent = csv.join('\n');
    var blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    var link = document.createElement('a');
    
    if (navigator.msSaveBlob) {
        navigator.msSaveBlob(blob, filename);
    } else {
        link.href = URL.createObjectURL(blob);
        link.setAttribute('download', filename);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
}

// Search functionality
function filterTable(tableId, searchTerm) {
    var table = document.getElementById(tableId);
    var rows = table.querySelectorAll('tbody tr');
    var searchTermLower = searchTerm.toLowerCase();
    
    rows.forEach(function(row) {
        var text = row.innerText.toLowerCase();
        row.style.display = text.includes(searchTermLower) ? '' : 'none';
    });
}