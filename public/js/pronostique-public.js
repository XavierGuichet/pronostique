(function($) {
    'use strict';

    $( window ).load(function() {
        $('.prono-tax-nav_sport-list_header').click(function(e) {
            if (window.matchMedia("(max-width: 767px)").matches) {
                if ($(this).siblings('ul.prono-tax-nav_sport-list_list').length == 1) {
                    e.preventDefault();
                    $(this).siblings('ul.prono-tax-nav_sport-list_list').first().slideToggle('1000');
                }
            }
        });
    });
})(jQuery);
