// Admin Panel JavaScript
$(document).ready(function() {
    // User Management
    $('.deactivate-user').click(function() {
        const userId = $(this).data('user-id');
        if (confirm('Are you sure you want to deactivate this user?')) {
            deactivateUser(userId);
        }
    });

    $('.activate-user').click(function() {
        const userId = $(this).data('user-id');
        if (confirm('Are you sure you want to activate this user?')) {
            activateUser(userId);
        }
    });

    $('.delete-user').click(function() {
        const userId = $(this).data('user-id');
        if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
            deleteUser(userId);
        }
    });

    // Tweet Management
    $('.delete-tweet').click(function() {
        const tweetId = $(this).data('tweet-id');
        if (confirm('Are you sure you want to delete this tweet?')) {
            deleteTweet(tweetId);
        }
    });

    // Report Management
    $('.view-report').click(function() {
        const reportId = $(this).data('report-id');
        viewReport(reportId);
    });

    $('.resolve-report').click(function() {
        const reportId = $(this).data('report-id');
        showActionModal(reportId, 'resolve', 'Resolve Report');
    });

    $('.dismiss-report').click(function() {
        const reportId = $(this).data('report-id');
        showActionModal(reportId, 'dismiss', 'Dismiss Report');
    });
});

// User Management Functions
function deactivateUser(userId) {
    $.post('ajax/user_actions.php', {
        action: 'deactivate',
        user_id: userId
    }, function(response) {
        if (response.success) {
            showNotification('User deactivated successfully', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification('Error: ' + response.message, 'error');
        }
    }).fail(function() {
        showNotification('Server error occurred', 'error');
    });
}

function activateUser(userId) {
    $.post('ajax/user_actions.php', {
        action: 'activate',
        user_id: userId
    }, function(response) {
        if (response.success) {
            showNotification('User activated successfully', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification('Error: ' + response.message, 'error');
        }
    }).fail(function() {
        showNotification('Server error occurred', 'error');
    });
}

function deleteUser(userId) {
    $.post('ajax/user_actions.php', {
        action: 'delete',
        user_id: userId
    }, function(response) {
        if (response.success) {
            showNotification('User deleted successfully', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification('Error: ' + response.message, 'error');
        }
    }).fail(function() {
        showNotification('Server error occurred', 'error');
    });
}

// Tweet Management Functions
function deleteTweet(tweetId) {
    $.post('ajax/tweet_actions.php', {
        action: 'delete',
        tweet_id: tweetId
    }, function(response) {
        if (response.success) {
            showNotification('Tweet deleted successfully', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification('Error: ' + response.message, 'error');
        }
    }).fail(function() {
        showNotification('Server error occurred', 'error');
    });
}

// Report Management Functions
function viewReport(reportId) {
    $.get('ajax/get_report.php', { report_id: reportId }, function(response) {
        if (response.success) {
            $('#reportModalBody').html(response.html);
            $('#reportModal').modal('show');
        } else {
            showNotification('Error loading report details', 'error');
        }
    }).fail(function() {
        showNotification('Server error occurred', 'error');
    });
}

function showActionModal(reportId, action, title) {
    $('#actionReportId').val(reportId);
    $('#actionType').val(action);
    $('#actionModalTitle').text(title);
    $('#actionSubmit').text(title);
    $('#actionModal').modal('show');
}

// Notification System
function showNotification(message, type = 'info') {
    // Remove existing notifications
    $('.notification-toast').remove();
    
    const toast = $(`
        <div class="notification-toast alert alert-${type} alert-dismissible fade show" 
             style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            ${message}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    `);
    
    $('body').append(toast);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        toast.alert('close');
    }, 5000);
}