<?php


// retourne le code HTML/JS permettant le filtrage des pronos d'un expert par mois
// FIXME : doit etre combiné avec la version getListParisTipsterParMois
function getListParisExpertParMois($uid, $date = '')
{
    global $wpdb, $table_tips_experts;
    if (empty($date) || preg_match('/^[0-9]*-[0-9]*$/', $date) != 1) {
        $date = strftime('%Y-%m');
    }
    $listeParis = getPronosticsExperts($uid, "t.tips_date LIKE '$date%'");

    // liens date
    $liens_date_tab = array();
    $date_fin = $wpdb->get_var("SELECT MAX(DATE_FORMAT(t.tips_date,'%Y-%m-%d')) FROM $table_tips_experts t WHERE t.user_id = ".(int) $uid);
    $today = date_create($date_fin);

    $date_debut = $wpdb->get_var("SELECT MIN(DATE_FORMAT(t.tips_date,'%Y-%m')) FROM $table_tips_experts t WHERE t.user_id = ".(int) $uid);
    for ($i = 1; $i < 10; ++$i) {
        if (date_format($today, 'Y-m') < $date_debut) {
            break;
        }
        $style = date_format($today, 'Y-m') == $date ? 'text-decoration:none;color:black;font-weight:bold;' : '';
        $liens_date_tab[] = sprintf('<a style="%s" href="/bilan-expert/?id=%d&mois=%s">%s</a>', $style, $uid, urlencode(date_format($today, 'Y-m')), $i);
        date_sub($today, DateInterval::createFromDateString('1 month'));
    }
    $liens_date = implode(' | ', $liens_date_tab);
    $dateToday = strftime('%Y-%m');
    $sExp = StatsDAO::getStatsExperts($uid, $dateToday);
    $sExpM = StatsDAO::getMonthSummary($uid, $date);
    $graphdata = StatsDAO::getNLastProfits($uid);
    $statsExperts = getStatsExpertsDetails($sExp, $graphdata);
    setlocale(LC_TIME, 'fr_FR');
    $timestamp = strtotime($date);
    $stat_mois_top = sprintf('%s - Gagné / Nul / Perdu = %s-%s-%s', ucfirst(htmlentities(strftime('%B %Y', $timestamp))), $sExpM->V, $sExpM->N, $sExpM->P);

    $out = "<h2>Expert '$sExp->nom_tipser'</h2>$statsExperts<h3>Performances mensuelles</h3>";
    $out .= "<div style=\"clear:both;text-align:center;font-size:20px;margin-bottom: 10px;\">$liens_date<br/><br/>$stat_mois_top</div>\n$listeParis\n";
  // Month summary
  $patt = '<div class="stat_expert" style="%s"><span class="stat_title">%s</span><br/><span class="stat_value">%s</span></div>';
    $out .= '<br/><h3>Résumé du mois</h3>';
    $out .= '<div>';
    $out .= sprintf($patt, 'width:200px;', 'Mises totales', $sExpM->mises_all, '%d');
    $out .= sprintf($patt, 'width:200px;', 'Profit', Formatter::formatCurrency($sExpM->profit), '%s');
    $out .= sprintf($patt, 'width:195px;border-width:1px;', 'Yield', StatsDAO::calculateYield2($sExpM->mises, $sExpM->profit), '%s');
    $out .= '</div>';

    return $out;
}


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

// retourne les stats de l'user
function getUserStats($user_id)
{
    global $wpdb, $table_tips, $table_user, $table_tipseur, $gain_sql, $VPNA_sql, $mises_sql;
  // chargement user
  $from_sql = " FROM $table_tips t JOIN $table_user u ON t.user_id = u.ID ";

  // Stats globales (tips_actif qcq)
  $user1_sql = "SELECT u.ID, u.display_name, $gain_sql AS 'profit', SUM(IF(t.tips_resultat=0,1,0)) AS 'NbEC', $mises_sql as 'mises', $VPNA_sql, COUNT(*) AS 'Nb' $from_sql WHERE u.ID = $user_id GROUP BY u.ID";
    $users1 = $wpdb->get_results($user1_sql);
    $u1 = $users1[0];
    $yield = $u1->mises != 0 ? sprintf('%.2f', ($u1->profit / $u1->mises) * 100) : '0.00';
  // Stats du mois (tips_actif = 1)
  $dateactu = strftime('%Y-%m-');
    $user2_sql = "SELECT $gain_sql AS 'profit' $from_sql WHERE u.ID=$user_id AND t.tips_actif=1 AND t.tips_date LIKE '$dateactu%' GROUP BY u.ID";
    $users2 = $wpdb->get_results($user2_sql);
    $u2 = $users2[0];

  // Pays
  $pays = $wpdb->get_var("SELECT tipser_pays FROM $table_tipseur WHERE user_id = $user_id");
    $yield = prefixSign($yield);
    $profit = prefixSign($u1->profit ? $u1->profit : 0);
    $profitM = prefixSign($u2->profit ? $u2->profit : 0);
    $yield_class = valeur2CSS($yield);
    $profit_class = valeur2CSS($u1->profit);
    $profitmois_class = valeur2CSS($u2->profit);

  // affichage user
  $out = '<h2>Statistiques de '.$u1->display_name.'</h2>
            <table class="stats_tips">
                <tr>
                    <td>Pseudonyme : <strong>'.$u1->display_name.'<strong></td>
                    <td>Tips publiés : <strong>'.$u1->Nb.'</strong></td>
                </tr>
                <tr>
                    <td>Pays : '.$pays.'</td>
                    <td>Tips gagnants : <strong class="gagne">'.$u1->V.'</strong></td>
                </tr>
                <tr>
                    <td>Yield : <strong class="'.$yield_class.'">'.$yield.' %</strong></td>
                    <td>Tips remboursés : <strong class="annule">'.$u1->N.'</strong></td>
                </tr>
                <tr>
                    <td>Profit : <strong class="'.$profit_class.'">'.$profit.' unités</strong></td>
                    <td>Tips perdus : <strong class="perdu">$u1->P</strong></td>
                </tr>
                <tr>
                    <td>Profit du mois : <strong class="'.$profitmois_class.'">'.$profitM.' unités</strong></td>
                    <td>Tips en cours : <strong>'.$u1->NbEC.'</strong></td>
                </tr>
            </table>';

    return $out;
}
