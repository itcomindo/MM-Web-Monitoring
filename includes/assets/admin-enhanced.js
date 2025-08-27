jQuery(document).ready(function ($) {

    // Auto reload page dengan interval yang bisa dikonfigurasi untuk halaman All Websites
    if (mmwm_admin.current_screen === 'edit-mmwm_website') {
        var reloadInterval = (parseInt(mmwm_admin.auto_reload_interval) || 30) * 1000; // Convert to milliseconds

        var autoReloadInterval = setInterval(function () {
            if (document.hidden) return; // Don't reload if tab is not active

            // Show reload indicator
            $('#mmwm-reload-indicator').show();

            // Reload page
            location.reload();
        }, reloadInterval);

        // Show auto-reload indicator with current interval
        if ($('.wrap h1').length > 0) {
            var intervalSeconds = Math.floor(reloadInterval / 1000);
            $('.wrap h1').append(' <span id="mmwm-reload-indicator" style="font-size: 12px; color: #666; display: none;">(Auto-reloading every ' + intervalSeconds + 's...)</span>');
        }
    }

    // Countdown timer untuk next check
    function updateCountdowns() {
        $('.mmwm-next-check-countdown').each(function () {
            var $element = $(this);
            var timestamp = parseInt($element.data('timestamp'));
            var currentTime = Math.floor(Date.now() / 1000);
            var difference = timestamp - currentTime;

            if (difference <= 0) {
                $element.html('<span style="color: #d63384; font-weight: bold;">Due now</span>');
                return;
            }

            var hours = Math.floor(difference / 3600);
            var minutes = Math.floor((difference % 3600) / 60);
            var seconds = difference % 60;

            var display = '';
            if (hours > 0) {
                display = hours + 'h ' + minutes + 'm ' + seconds + 's';
            } else if (minutes > 0) {
                display = minutes + 'm ' + seconds + 's';
            } else {
                display = seconds + 's';
            }

            $element.html('<span style="color: #0073aa;">' + display + '</span>');
        });
    }

    // Update countdown every second
    if ($('.mmwm-next-check-countdown').length > 0) {
        updateCountdowns();
        setInterval(updateCountdowns, 1000);
    }

    // Enhanced bulk actions have been removed in v1.1.1 as they were not working properly

    // Bulk check functionality has been removed in v1.1.1 as it was not working properly

    // Existing functionality for individual check buttons
    $(document).on('click', '.mmwm-action-btn', function () {
        var $btn = $(this);
        var action = $btn.data('action');
        var postId = $btn.data('postid');
        var $spinner = $btn.siblings('.spinner');

        $btn.prop('disabled', true);
        $spinner.addClass('is-active');

        if (action === 'run_check_now') {
            $.ajax({
                url: mmwm_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'mmwm_run_check_now',
                    post_id: postId,
                    _ajax_nonce: mmwm_admin.nonce
                },
                success: function (response) {
                    if (response.success) {
                        // Update the check result column
                        var status = response.data.status;
                        var statusColor = '#777';
                        if (status === 'UP') statusColor = '#28a745';
                        if (status === 'DOWN') statusColor = '#dc3545';
                        if (status === 'CONTENT_ERROR') statusColor = '#ffc107';

                        var $statusColumn = $btn.closest('tr').find('.column-check_result');
                        $statusColumn.html('<span style="font-weight: bold; color: ' + statusColor + ';">' + status + '</span>');

                        // Update last check column
                        var $lastCheckColumn = $btn.closest('tr').find('.column-last_check');
                        $lastCheckColumn.html('<span title="Just now">0 seconds ago</span>');

                        alert('Check completed successfully! Status: ' + status);
                    } else {
                        alert('Check failed: ' + (response.data.message || 'Unknown error'));
                    }
                },
                error: function () {
                    alert('Network error occurred.');
                },
                complete: function () {
                    $btn.prop('disabled', false);
                    $spinner.removeClass('is-active');
                }
            });
        } else {
            // Handle status change (start/pause/stop)
            $.ajax({
                url: mmwm_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'mmwm_update_monitoring_status',
                    post_id: postId,
                    new_status: action,
                    _ajax_nonce: mmwm_admin.nonce
                },
                success: function (response) {
                    if (response.success) {
                        location.reload(); // Reload to update buttons and status
                    } else {
                        alert('Failed to update status: ' + (response.data.message || 'Unknown error'));
                    }
                },
                error: function () {
                    alert('Network error occurred.');
                },
                complete: function () {
                    $btn.prop('disabled', false);
                    $spinner.removeClass('is-active');
                }
            });
        }
    });

    // Visual feedback for page reload
    $(window).on('beforeunload', function () {
        $('#mmwm-reload-indicator').text('(Reloading...)').show();
    });
});
