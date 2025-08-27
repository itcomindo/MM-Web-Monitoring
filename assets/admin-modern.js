/**
 * MM Web Monitoring - Modern Admin JavaScript v1.0.9
 */

jQuery(document).ready(function ($) {

    // Auto-save functionality for certain fields
    let saveTimeout;

    // Auto-save for fields that don't need Save button
    $('.mmwm-auto-save').on('change', function () {
        const field = $(this);
        const fieldName = field.attr('name');
        const fieldValue = field.val();

        clearTimeout(saveTimeout);
        saveTimeout = setTimeout(function () {
            autoSaveField(fieldName, fieldValue);
        }, 1000);
    });

    function autoSaveField(fieldName, fieldValue) {
        $.ajax({
            url: mmwm_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'mmwm_auto_save_field',
                field_name: fieldName,
                field_value: fieldValue,
                nonce: mmwm_ajax.nonce
            },
            success: function (response) {
                if (response.success) {
                    showNotice('Settings saved automatically', 'success');
                }
            }
        });
    }

    // Enhanced form submission with loading state
    $('#mmwm-global-form').on('submit', function (e) {
        const submitBtn = $(this).find('input[type="submit"]');
        const originalText = submitBtn.val();

        submitBtn.val('Saving...').prop('disabled', true);

        // Re-enable after form submission
        setTimeout(function () {
            submitBtn.val(originalText).prop('disabled', false);
            showNotice('Settings saved successfully!', 'success');
        }, 1000);
    });

    // Smooth scroll to notices
    function showNotice(message, type) {
        const notice = $('#' + type + '-notice');
        notice.text(message).fadeIn();

        $('html, body').animate({
            scrollTop: notice.offset().top - 100
        }, 300);

        setTimeout(function () {
            notice.fadeOut();
        }, 3000);
    }

    // Copy to clipboard with better UX
    window.copyToClipboard = function (text) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(function () {
                showNotice('Copied to clipboard!', 'success');
            }).catch(function () {
                fallbackCopy(text);
            });
        } else {
            fallbackCopy(text);
        }
    };

    function fallbackCopy(text) {
        const textArea = document.createElement('textarea');
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();

        try {
            document.execCommand('copy');
            showNotice('Copied to clipboard!', 'success');
        } catch (err) {
            showNotice('Copy failed. Please copy manually.', 'error');
        }

        document.body.removeChild(textArea);
    }

    // Toggle animations
    $('.mmwm-toggle-switch input').on('change', function () {
        const toggle = $(this);
        const slider = toggle.siblings('.mmwm-slider');

        // Add visual feedback
        slider.addClass('mmwm-transition');
        setTimeout(function () {
            slider.removeClass('mmwm-transition');
        }, 400);
    });

    // Global check frequency handler
    $('#mmwm_global_check_frequency').on('change', function () {
        const dailyOption = $('#daily-time-option');
        if ($(this).val() === 'daily') {
            dailyOption.slideDown(300);
        } else {
            dailyOption.slideUp(300);
        }
    });

    // Enhanced user agent toggle with better error handling
    window.handleUserAgentToggle = function (enabled) {
        const statusDiv = $('#user-agent-status');
        const toggle = $('#mmwm_custom_user_agent_enabled');

        statusDiv.html('<span style="color: #666;"><i class="dashicons dashicons-update spin"></i> Processing...</span>');

        $.ajax({
            url: mmwm_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'mmwm_toggle_user_agent',
                enabled: enabled ? 1 : 0,
                nonce: mmwm_ajax.nonce
            },
            success: function (response) {
                if (response.success) {
                    statusDiv.html('<span style="color: #28a745;"><i class="dashicons dashicons-yes-alt"></i> ' + response.data + '</span>');
                    showNotice(response.data, 'success');
                } else {
                    statusDiv.html('<span style="color: #dc3545;"><i class="dashicons dashicons-dismiss"></i> ' + response.data + '</span>');
                    showNotice(response.data, 'error');
                    toggle.prop('checked', !enabled);
                }
            },
            error: function (xhr, status, error) {
                const errorMsg = 'Ajax request failed: ' + error;
                statusDiv.html('<span style="color: #dc3545;"><i class="dashicons dashicons-dismiss"></i> ' + errorMsg + '</span>');
                showNotice(errorMsg, 'error');
                toggle.prop('checked', !enabled);
            }
        });
    };

    // Card animations on load
    $('.mmwm-card').each(function (index) {
        $(this).delay(index * 100).animate({
            opacity: 1,
            transform: 'translateY(0)'
        }, 400);
    });

    // Add CSS for card animations
    $('<style>')
        .prop('type', 'text/css')
        .html(`
            .mmwm-card {
                opacity: 0;
                transform: translateY(20px);
                transition: all 0.4s ease;
            }
            .mmwm-transition {
                transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
            }
            .dashicons.spin {
                animation: spin 1s linear infinite;
            }
            @keyframes spin {
                from { transform: rotate(0deg); }
                to { transform: rotate(360deg); }
            }
            .mmwm-slider:hover {
                box-shadow: 0 0 8px rgba(33, 150, 243, 0.3);
            }
            .mmwm-form-row:hover {
                background: #f0f8ff;
                transition: background 0.3s ease;
            }
        `)
        .appendTo('head');
});
