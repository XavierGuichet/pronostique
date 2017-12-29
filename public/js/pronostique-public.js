(function($) {
    'use strict';

    $(function() {
        var competition_relation = php_vars;
        var sport_select, country_select, competition_select, sport_id, country_id, competition_id;
        sport_select = $('select[name=pods_field_sport]');
        country_select = $('select[name=pods_field_country]');
        competition_select = $('select[name=pods_field_competition]');

        if (!country_select.val()) {
            country_select.prop('disabled', true);
        }
        if (!competition_select.val()) {
            competition_select.prop('disabled', true);
        }

        sport_select.change(limit_country);
        country_select.change(limit_competition);

        function limit_country() {
            sport_id = sport_select.val();
            country_select.prop('disabled', false);
            country_select.children('option').hide().prop('disabled', true);
            country_select.children('option:first-child').prop('selected', true);

            competition_select.prop('disabled', true);
            competition_select.children('option').hide().prop('disabled', true);
            competition_select.children('option:first-child').prop('selected', true);


            for (var country in competition_relation[sport_id]) {
                country_select.children('option[value=' + country + ']').show().prop('disabled', false);
            }
            if (competition_relation[sport_id].length == 1) {
                for (var country in competition_relation[sport_id]) {
                    country_id = country;
                    country_select.val(country_id);
                }
            }
        }

        function limit_competition() {
            country_id = country_select.val();
            competition_select.prop('disabled', false);
            competition_select.children('option').hide().prop('disabled', true);
            competition_select.children('option:first-child').prop('selected', true);
            for (var competition in competition_relation[sport_id][country_id]) {
                competition_id = competition_relation[sport_id][country_id][competition];
                competition_select.children('option[value=' + competition_id + ']').show().prop('disabled', false);
            }
            if (competition_relation[sport_id][country_id].length == 1) {
                for (var competition in competition_relation[sport_id][country_id]) {
                    competition_select.val(competition_id);
                }
            }
        }
    });



    $(document).ready(function() {
        $('.prono-tax-nav_sport-list_header').click(function(e) {
            if (window.matchMedia("(max-width: 767px)").matches) {
                if ($(this).siblings('ul.prono-tax-nav_sport-list_list').length == 1) {
                    e.preventDefault();
                    $(this).siblings('ul.prono-tax-nav_sport-list_list').first().slideToggle('1000');
                }
            }
        });
    });



    /**
     * All of the code for your public-facing JavaScript source
     * should reside in this file.
     *
     * Note: It has been assumed you will write jQuery code here, so the
     * $ function reference has been prepared for usage within the scope
     * of this function.
     *
     * This enables you to define handlers, for when the DOM is ready:
     *
     * $(function() {
     *
     * });
     *
     * When the window is loaded:
     *
     * $( window ).load(function() {
     *
     * });
     *
     * ...and/or other possibilities.
     *
     * Ideally, it is not considered best practise to attach more than a
     * single DOM-ready or window-load handler for a particular page.
     * Although scripts in the WordPress core, Plugins and Themes may be
     * practising this, we should strive to set a better example in our own work.
     */

})(jQuery);
