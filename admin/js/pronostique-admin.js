(function($) {
    'use strict';

    $(document).ready(function() {
        $('.js-pqe-toggle-content').click(function() {
            $(this).siblings('.prono-quick-edit-content').toggleClass('expanded');
        });

        var options = {
            url: prono_ajax_object.ajax_url,
            type: 'post',
            dataType: 'json',
            success: function(response, statusText, xhr, $form) {
                if (response.success == 1) {
                    $form.parents('.prono-quick-edit-col').fadeOut(1000);
                } else {
                    $form.siblings('.form-errors').empty();
                    for (var i = 0; i < response.errors.length; i++) {
                        $form.siblings('.form-errors').append('<p>' + response.errors[i] + '</p>');
                    }
                    $form.siblings('.form-errors').show();
                }
            },
            beforeSubmit: function(arr, $form, options) {
                arr.push({
                    "name": "security",
                    "value": prono_ajax_object.nonce
                });
                arr.push({
                    "name": "action",
                    "value": prono_ajax_object.action
                });
            },
        };
        $('.prono-quick-edit-ajaxform').ajaxForm(options);


    });
})(jQuery);
