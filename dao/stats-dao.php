<?php

class StatsDAO
{
    // Get all experts pronostics
    public static function getAllExpertsStats($cond_str = '')
    {
        trigger_error('getAllPronosticsExpertsWithUser deprecated',E_USER_NOTICE);
        global $wpdb;

        $table_tips = $wpdb->prefix.'bmk_tips';
        $table_user = $wpdb->prefix.'users';
        $table_tipseur = $wpdb->prefix.'bmk_tipsers';
        $table_tips_experts = $wpdb->prefix.'bmk_tips_experts';
        $mises_sql = 'ROUND(SUM( IF(t.tips_resultat IN (1,2,3), t.tips_mise, 0) ), 2)'; // E_E : prise en compte pari annules
        $gain_sql = ' ROUND(SUM( IF(t.tips_resultat = 1, (t.tips_cote-1)*t.tips_mise, IF(t.tips_resultat = 2, - t.tips_mise, IF(t.tips_resultat = 3, 0, 0))) ), 2) ';
        $VPNA_sql = "SUM(IF(t.tips_resultat = 1,1,0)) AS 'V', SUM(IF(t.tips_resultat = 3,1,0)) AS 'N', SUM(IF(t.tips_resultat = 2,1,0)) AS 'P', SUM(IF(t.tips_resultat = 0,1,0)) AS 'A'";

        if (isset($cond_str) && !empty($cond_str)) {
            $cond_str = "WHERE $cond_str";
        }

        $sort_order = 'DESC';
        $sql = "SELECT t.*, u.display_name AS 'nom_tipser', $gain_sql as 'profit', $mises_sql as 'mises' FROM $table_tips_experts t ";
        $sql .= "JOIN $table_user u ON u.ID = t.user_id $cond_str GROUP BY u.ID ORDER BY t.tips_date $sort_order, t.tips_heure $sort_order";

        return $wpdb->get_results($sql);
    }
}
