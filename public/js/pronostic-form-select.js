(function($) {
    'use strict';

    $( window ).load(function() {
        var competition_relation = php_vars;
        var sport_select, country_select, competition_select, sport_id, country_id, competition_id;
        sport_select = $('select[name=pods_field_sport]');
        country_select = $('select[name=pods_field_country]');
        competition_select = $('select[name=pods_field_competition]');

        if (!country_select.val() || country_select.val() == 0) {
            country_select.prop('disabled', true);
        }

        if (!competition_select.val() || competition_select.val() == 0) {
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
})(jQuery);
