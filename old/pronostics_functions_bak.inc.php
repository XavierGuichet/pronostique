<?php
// retourne le code HTML/JS CONTENANT LES STATS D'UN TIPSTER POUR LA SIDEBAR
function getStatsTipster($uid)
{
    global $wpdb, $table_tips;
    $out = '';

    // Stats avec graphe (PRofit total, Ce mois, Publiés, gagnés, perdus, ...)
    $currentMonth = strftime('%Y-%m');
    $sExp = StatsDAO::getStatsUser($uid, $currentMonth, $table_tips);
    //$out .= "<h3>Stats de $sExp->nom_tipser</h3>";
    $out .= '<div class="stats_tipster_side">';

    // stats tispter
    $htmlOne = '<div class="stat_tipster_side"><span>%s</span> <span class="stat_value %s">%s</span></div>';
    $out .= sprintf($htmlOne, 'Tips publiés', '', $sExp->total);
    $out .= sprintf($htmlOne, 'Tips gagnés', valeur2CSS(1), $sExp->V);
    $out .= sprintf($htmlOne, 'Tips perdus', valeur2CSS(-1), $sExp->P);
    $out .= sprintf($htmlOne, 'Tips remb.', 'nul', $sExp->N);
    $out .= sprintf($htmlOne, 'Profit total', valeur2CSS($sExp->profit), Formatter::formatCurrency($sExp->profit));
    $out .= sprintf($htmlOne, 'Ce mois', valeur2CSS($sExp->profit_mois), Formatter::formatCurrency($sExp->profit_mois));

    $yield = prefixSign(StatsDAO::calculateYield($sExp));
    $out .= sprintf($htmlOne, 'Yield total', valeur2CSS($yield), $yield);

    $out .= '<div class="clearfix"><br/></div>';
    $out .= '</div>';

    // Liste des derniers paris
    $out .= "<h3>Derniers paris de $sExp->nom_tipser</h3>";
    $out .= getPronosticsUser($uid, '1=1', 10, $table_tips, true); // dernier params : true si sidebar

    return $out;
}
