<?php
// Renvoie les stats sur ts les tipseurs
function getGlobalStats()
{
    global $wpdb, $table_tips, $table_user, $gain_sql;
    $dateactu = strftime('%Y-%m-');
    $mois = $wpdb->get_var("SELECT $gain_sql FROM $table_tips t WHERE tips_resultat > 0 AND tips_actif = 1 AND tips_date LIKE '$dateactu%'");
    $global = $wpdb->get_var("SELECT $gain_sql FROM $table_tips t WHERE tips_resultat > 0");
    $global = ($global ? $global : 0);
    $mois = ($mois ? $mois : 0);

    return array('global' => $global, 'mois' => $mois);
}
