<?php

function getStatsExpertsDetailsHtml()
{
    $dateToday = strftime('%Y-%m');
    $se = StatsDAO::getStatsExperts(0, $dateToday);
    $graphdata = StatsDAO::getNLastProfits();
    $html = getStatsExpertsDetails($se, $graphdata);

    return sprintf('<div class="bilanexpert2">%s</div>', $html);
}
