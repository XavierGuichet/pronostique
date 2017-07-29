<?php

function getStatsExperts()
{
    $statsglob = StatsDAO::getGlobalStats();

    $profit_expert = Formatter::formatCurrency($statsglob->profit, 'unités', true);

    $paris_gagnes = $statsglob->V;

    $paris_perdus = $statsglob->P;

    return '<span class="editorsprofit">
            Profit de nos experts: <span>'.$profit_expert.'</span><br>
            Paris gagnés: <span class="editorsgreen">'.$paris_gagnes.'</span> | Paris perdus: <span class="editorsred">'.$paris_perdus.'</span><br>
        </span>';
}
