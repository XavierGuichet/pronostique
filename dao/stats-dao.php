<?php

class StatsDAO
{
    // Get all experts pronostics
    public static function getAllExpertsStats($cond_str = '')
    {
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

    public static function getStatsExperts($uid, $date = '')
    {
        global $table_tips_experts;

        return self::getStatsUser($uid, $date, $table_tips_experts);
    }

    public static function getStatsUser($uid, $date, $table)
    {
        global $wpdb, $table_user, $gain_sql, $mises_sql, $VPNA_sql;
        $uid_where = (int) $uid ? "WHERE u.ID=$uid" : '';
        $uid_group = (int) $uid ? 'GROUP BY u.ID' : '';

        $sql = "SELECT t.*, u.display_name AS 'nom_tipser', count(*) as 'total', $gain_sql as 'profit', $mises_sql as 'mises', $VPNA_sql ";
        $sql .= "FROM $table t JOIN $table_user u ON u.ID = t.user_id $uid_where $uid_group";
        $res = $wpdb->get_row($sql);

        if (!empty($date) && preg_match('/^[0-9]*-[0-9]*-?$/', $date) == 1) {
            $datewhere = ($uid_where ? "$uid_where AND" : 'WHERE')." t.tips_date LIKE '$date%'";
            $sql = "SELECT $gain_sql as 'profit' FROM $table t JOIN $table_user u ON u.ID = t.user_id ";
            $sql .= "$datewhere";
            $res_mois = $wpdb->get_row($sql);
            $res->profit_mois = $res_mois->profit;
        }

        $res->profit_mois = !isset($res->profit_mois) || !$res->profit_mois ? 0 : $res->profit_mois;
        $res->yield = self::calculateYield($res);

        return $res;
    }

    public static function getMonthSummary($uid, $date = '')
    {
        global $table_tips_experts;

        return self::getMonthSummaryInternal($uid, $date, $table_tips_experts);
    }
    public static function getMonthSummaryInternal($uid, $date, $table)
    {
        global $wpdb, $table_user, $gain_sql, $mises_sql, $VPNA_sql;

        $mises_all_sql = 'SUM( t.tips_mise )';
        $sql = "SELECT t.*, $gain_sql as 'profit', $mises_all_sql AS 'mises_all', $mises_sql as 'mises', $VPNA_sql ";
        $sql .= "FROM $table t JOIN $table_user u ON u.ID = t.user_id WHERE t.tips_date LIKE '$date%' AND u.ID = ".(int) $uid.' GROUP BY u.ID';
        $res = $wpdb->get_row($sql);

        if (!$res->profit) {
            $res->profit = 0;
        }
        if (!$res->mises) {
            $res->mises = 0;
        }
        if (!$res->mises_all) {
            $res->mises_all = 0;
        }

        if (!$res->V) {
            $res->V = 0;
        }
        if (!$res->P) {
            $res->P = 0;
        }
        if (!$res->N) {
            $res->N = 0;
        }

        return $res;
    }

    public static function getGlobalStats()
    {
        global $wpdb;
        $table_tips = $wpdb->prefix.'bmk_tips';
        $table_user = $wpdb->prefix.'users';
        $table_tipseur = $wpdb->prefix.'bmk_tipsers';
        $table_tips_experts = $wpdb->prefix.'bmk_tips_experts';
        $mises_sql = 'ROUND(SUM( IF(t.tips_resultat IN (1,2,3), t.tips_mise, 0) ), 2)'; // E_E : prise en compte pari annules
    $gain_sql = ' ROUND(SUM( IF(t.tips_resultat = 1, (t.tips_cote-1)*t.tips_mise, IF(t.tips_resultat = 2, - t.tips_mise, IF(t.tips_resultat = 3, 0, 0))) ), 2) ';
        $VPNA_sql = "SUM(IF(t.tips_resultat = 1,1,0)) AS 'V', SUM(IF(t.tips_resultat = 3,1,0)) AS 'N', SUM(IF(t.tips_resultat = 2,1,0)) AS 'P', SUM(IF(t.tips_resultat = 0,1,0)) AS 'A'";
        $sql = "SELECT $gain_sql as 'profit', $VPNA_sql FROM $table_tips_experts t ";

        return $wpdb->get_row($sql);
    }

    public static function getNLastProfits($uid = '', $max = 45)
    {
        global $table_tips_experts;

        return self::getNLastProfitsInternal($uid, $max, $table_tips_experts);
    }
    public static function getNLastProfitsInternal($uid, $max, $table)
    {
        global $wpdb, $table_user;
        $where = 'WHERE t.tips_resultat > 0'.($uid ? ' AND t.user_id = '.(int) $uid : '');

        $sql = "SELECT t.tips_match,t.tips_mise, t.tips_cote, t.tips_resultat FROM $table t $where ";
        $sql .= "ORDER BY t.tips_date DESC, t.tips_heure DESC, t.tips_ID DESC LIMIT $max";

        $res = $wpdb->get_results($sql);
        $res = array_reverse($res);
        $datas = array();

        foreach ($res as $tip) {
            $profit = 0;
            if ($tip->tips_resultat == 1) {
                $profit = ($tip->tips_mise * ($tip->tips_cote - 1));
            } elseif ($tip->tips_resultat == 2) {
                $profit = -$tip->tips_mise;
            }

            $previous = end(array_values($datas)) ? end(array_values($datas)) : 0;
            $datas[] = $previous + $profit;
        }

        return $datas;
    }

    public static function calculateYield($u)
    {
        if (!isset($u->mises) || !isset($u->profit)) {
            return;
        }
        $out = $u->mises != 0 ? sprintf('%.2f', ($u->profit / $u->mises) * 100) : '0.00';

        return "$out%";
    }

    public static function calculateYield2($mises, $profit)
    {
        $obj = new StdClass();
        $obj->mises = $mises;
        $obj->profit = $profit;

        return self::calculateYield($obj);
    }
}
