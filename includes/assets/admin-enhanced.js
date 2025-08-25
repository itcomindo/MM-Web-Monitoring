jQuery(document).ready(function ($) {

    // Auto reload page every 30 seconds untuk halaman All Websites
    if (mmwm_admin.current_screen === 'edit-mmwm_website') {
        var autoReloadInterval = setInterval(function () {
            if (document.hidden) return; // Don't reload if tab is not active

            // Show reload indicator
            $('#mmwm-reload-indicator').show();

            // Reload page
            location.reload();
        }, 30000); // 30 seconds

        // Show auto-reload indicator
        if ($('.wrap h1').length > 0) {
            $('.wrap h1').append(' <span id="mmwm-reload-indicator" style="font-size: 12px; color: #666; display: none;">(Auto-reloading...)</span>');
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

    // Enhanced bulk actions untuk check now
    $(document).on('click', '#doaction, #doaction2', function (e) {
        var $form = $(this).closest('form');
        var action = $form.find('select[name="action"]').val() || $form.find('select[name="action2"]').val();

        if (action === 'mmwm_check_now') {
            e.preventDefault();

            var selectedPosts = [];
            $form.find('input[name="post[]"]:checked').each(function () {
                selectedPosts.push($(this).val());
            });

            if (selectedPosts.length === 0) {
                alert('Please select at least one website to check.');
                return;
            }

            if (!confirm('Are you sure you want to check ' + selectedPosts.length + ' selected website(s) now?')) {
                return;
            }

            performBulkCheck(selectedPosts);
        }
    });

    // Function to perform bulk check
    function performBulkCheck(postIds) {
        var $progressDiv = $('<div id="mmwm-bulk-check-progress" style="margin: 20px 0; padding: 15px; background: #f0f0f1; border-left: 4px solid #0073aa;">');
        $progressDiv.html('<h3>Bulk Check Progress</h3><div id="mmwm-bulk-check-log"></div>');
        $('.wrap').prepend($progressDiv);

        var total = postIds.length;
        var completed = 0;

        function checkNext() {
            if (completed >= total) {
                $('#mmwm-bulk-check-log').append('<p style="color: #008a20; font-weight: bold;">All checks completed! Page will reload in 3 seconds...</p>');
                setTimeout(function () {
                    location.reload();
                }, 3000);
                return;
            }

            var postId = postIds[completed];
            var siteName = $('tr#post-' + postId + ' .column-title strong a').text() || 'Site #' + postId;

            $('#mmwm-bulk-check-log').append('<p>Checking: ' + siteName + '...</p>');

            $.ajax({
                url: mmwm_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'mmwm_bulk_action',
                    post_id: postId,
                    bulk_action: 'check-now',
                    _ajax_nonce: mmwm_admin.nonce
                },
                success: function (response) {
                    if (response.success) {
                        $('#mmwm-bulk-check-log').append('<p style="color: #008a20;">✓ ' + siteName + ' checked successfully</p>');
                    } else {
                        $('#mmwm-bulk-check-log').append('<p style="color: #d63384;">✗ ' + siteName + ' check failed: ' + (response.data.message || 'Unknown error') + '</p>');
                    }
                },
                error: function () {
                    $('#mmwm-bulk-check-log').append('<p style="color: #d63384;">✗ ' + siteName + ' check failed: Network error</p>');
                },
                complete: function () {
                    completed++;
                    setTimeout(checkNext, 1000); // Wait 1 second between checks
                }
            });
        }

        checkNext();
    }

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
