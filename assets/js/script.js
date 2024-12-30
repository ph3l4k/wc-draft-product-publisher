jQuery(document).ready(function($) {
    function updateCount() {
        $.ajax({
            url: wcdpp_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'wcdpp_get_current_count',
                nonce: wcdpp_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#wcdpp-count').text(response.data.count);
                }
            }
        });
    }

    // Update count every 30 seconds
    setInterval(updateCount, 30000);

    $('#wcdpp-publish-button').on('click', function() {
        var button = $(this);
        button.prop('disabled', true).text('Publishing...');

        $.ajax({
            url: wcdpp_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'wcdpp_publish_products',
                nonce: wcdpp_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#wcdpp-result')
                        .removeClass('error')
                        .addClass('success')
                        .html('Successfully published ' + response.data.count + ' products.')
                        .show();
                    updateCount(); // Update count after publishing
                } else {
                    $('#wcdpp-result')
                        .removeClass('success')
                        .addClass('error')
                        .html('An error occurred while publishing products: ' + response.data)
                        .show();
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#wcdpp-result')
                    .removeClass('success')
                    .addClass('error')
                    .html('An error occurred while publishing products: ' + errorThrown)
                    .show();
            },
            complete: function() {
                button.prop('disabled', false).text('Publish Products');
            }
        });
    });
});

