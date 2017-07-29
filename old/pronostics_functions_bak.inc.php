<?php

date_default_timezone_set('Europe/Paris');




//------------------------------------------------------------------

// retourne les stats de l'user sur le sport donné
function getUserStatsSport($user_id, $sport)
{
    global $wpdb, $table_tips, $table_user, $gain_sql, $VPNA_sql, $mises_sql;
  // chargement user
  $from_sql = " FROM $table_tips t JOIN $table_user u ON t.user_id = u.ID ";

    // Stats globales (tips_actif qcq)
    $user1_sql = "SELECT u.ID, u.display_name, $gain_sql AS 'profit', $mises_sql as 'mises', $VPNA_sql $from_sql WHERE u.ID = $user_id AND t.tips_sport LIKE '$sport' GROUP BY u.ID";
    $users1 = $wpdb->get_results($user1_sql);
    $u1 = $users1[0];
    $profit = prefixSign($u1->profit ? $u1->profit : 0);
    $yield = $u1->mises != 0 ? prefixSign(sprintf('%.2f', ($u1->profit / $u1->mises) * 100)) : '+0.00';
    //$yield  = prefixSign(sprintf('%.2f', ($u1->profit / $u1->mises) * 100 ));
    $yield_class = valeur2CSS($yield);
    $profit_class = valeur2CSS($u1->profit);

    // affichage user
    $out = '<h2>Statistiques de '.$u1->display_name.' pour le sport courant ('.$sport.')</h2>
        <table class="stats_tips">
            <tr>
                <td colspan="3">Profit : <strong class="'.$profit_class.'">'.$profit.' unités</strong></td>
                <td colspan="3">Yield : <strong class="'.$yield_class.'">'.$yield.' %</strong></td>
            </tr>
            <tr>
                <td colspan="2">Tips gagnants : <strong class="gagne">'.$u1->V.'</strong></td>
                <td colspan="2">Tips remboursés : <strong class="annule">'.$u1->N.'</strong></td>
                <td colspan="2">Tips perdus : <strong class="perdu">'.$u1->P.'</strong></td>
            </tr>
        </table>';

    return $out;
}



//-----------------------------------------------------------------

// Renvoie les infos tipseurs a partir d'un tips
function getTipseurData($tips_id)
{
    global $wpdb, $table_tips, $table_tips, $table_user;
    $tips_id = (int) $tips_id;
    $all_tips = $wpdb->get_results("SELECT t.*, u.display_name AS 'nom_tipseur' FROM $table_tips t JOIN $table_user u ON u.ID = t.user_id WHERE t.id = $tips_id");

    return $all_tips[0];
}

// --------------------------------------------------------



// ----------------------------------------------------------

// retourne le tableau contenant les pronostics experts formates
// IN: id_user	-1 pour tous, 0 pour l'user courant, <ID> pr un user donné
function getPronosticsExperts($user_id = -1, $cond_param = '', $limit = 0)
{
    global $table_tips_experts;

    return getPronosticsUser($user_id, $cond_param, $limit, $table_tips_experts);
}

function getPronosticsUser($user_id = -1, $cond_param = '', $limit = 0, $table = '', $is_sidebar = false)
{
    $limit = $user_id < 0 ? 25 : $limit;
    $cond = array($cond_param);
    if ($user_id == 0) {
        $cond[] = 'user_id = '.get_current_user_id();
    } elseif (is_numeric($user_id) && $user_id > 0) {
        $cond[] = "user_id = $user_id";
    }

    $all_tips = PronosticsDAO::getAllPronosticsExpertsWithUser(implode(' AND ', $cond), $limit, $table);

    $colonne_user = $user_id == -1 ? '<th>Tipster</th>' : '';

    $out = '';
    if ($is_sidebar) {
        $out = '<table class="tableau_pronostics">
      <tr style="background-color:#EAEAEA;">
      <th style="width:8%">Date</th>
      <th style="width:5%"><!--Win/Lose--></th>
      <th>Match</th>
      <th>Cote</th>
      </tr>';
    } else {
        $out = '<table class="tableau_pronostics">
      <tr style="background-color:#EAEAEA;">
      <th style="width:8%">Date</th>
      <th style="width:8%">Heure</th>
      <th style="width:5%"><!--Win/Lose--></th>
      <th style="width:35%">Match</th>
      <th style="width:15%">Pari</th>
      <th style="width:10%">Résultat</th>
      <th>Cote</th>
      <th>Mise</th>
      '.$colonne_user.'
      <th>Profit</th>
      </tr>';
    }
    $cpt = 1;
    foreach ($all_tips as $tips) {
        if ($cpt > $limit && $limit != 0) {
            break;
        }
        ++$cpt;

        $resultat_str = resultat2str($tips->tips_resultat);
        $mise_str = mise2str($tips->tips_mise);
        $dateFR = substr(dateUS2FR($tips->tips_date), 0, 5);
        $tipser_str = $user_id == -1 ? '<td>'.$tips->nom_tipser.'</td>' : '';
        $match_href = sprintf('<a class="simplelink" href="%s">%s</a>', get_permalink($tips->tips_post_id), stripslashes($tips->tips_match));
        $tips_profit = $tips->tips_resultat > 0 ? sprintf('%.2f', calculateGain($tips->tips_resultat, $tips->tips_mise, $tips->tips_cote)) : '';

        $out_tab = array();
        $out_tab[] = $dateFR;
        if (!$is_sidebar) {
            $out_tab[] = $tips->tips_heure;
        }
        $out_tab[] = $resultat_str;
        $out_tab[] = "<strong>$match_href</strong>";
        if (!$is_sidebar) {
            $out_tab[] = $tips->tips_code_poolbox;
            $out_tab[] = $tips->tips_resultat_str;
        }
        $out_tab[] = $tips->tips_cote;
        if (!$is_sidebar) {
            $out_tab[] = $mise_str;
            if ($user_id == -1) {
                $out_tab[] = $tips->nom_tipser;
            }
            $out_tab[] = $tips_profit;
        }
        $out_tab = array_map(function ($x) {
            return "<td>$x</td>";
        }, $out_tab);
        $out .= '<tr>'.implode("\n", $out_tab).'</tr>';
    }
    $out .= '</table>';

    return $out;
}

// retourne le code HTML/JS CONTENANT LES STATS D'UN TIPSTER POUR LA SIDEBAR
function getStatsTipster($uid)
{
    global $wpdb, $table_tips;
    $out = '';

    // Stats avec graphe (PRofit total, Ce mois, Publiés, gagnés, perdus, ...)
    $currentMonth = strftime('%Y-%m');
    $sExp = StatsDAO::getStatsUser($uid, $currentMonth, $table_tips);
    //$out .= "<h3>Stats de $sExp->nom_tipser</h3>";
    $out .= '<div class="stats_tipster_side">'.getStatsTipsterSide($sExp).'</div>';

    // Liste des derniers paris
    $out .= "<h3>Derniers paris de $sExp->nom_tipser</h3>";
    $out .= getPronosticsUser($uid, '1=1', 10, $table_tips, true); // dernier params : true si sidebar

    return $out;
}

// Stats tispter pour side bar
function getStatsTipsterSide($sExp)
{
    // stats tispter
    $htmlOne = '<div class="stat_tipster_side"><span>%s</span> <span class="stat_value %s">%s</span></div>';
    $out = '';
    $out .= sprintf($htmlOne, 'Tips publiés', '', $sExp->total);
    $out .= sprintf($htmlOne, 'Tips gagnés', valeur2CSS(1), $sExp->V);
    $out .= sprintf($htmlOne, 'Tips perdus', valeur2CSS(-1), $sExp->P);
    $out .= sprintf($htmlOne, 'Tips remb.', 'nul', $sExp->N);
    $out .= sprintf($htmlOne, 'Profit total', valeur2CSS($sExp->profit), Formatter::formatCurrency($sExp->profit));
    $out .= sprintf($htmlOne, 'Ce mois', valeur2CSS($sExp->profit_mois), Formatter::formatCurrency($sExp->profit_mois));

    $yield = prefixSign(StatsDAO::calculateYield($sExp));
    $out .= sprintf($htmlOne, 'Yield total', valeur2CSS($yield), $yield);

    $out .= '<div class="clearfix"><br/></div>';

    return $out;
}

function getStatsExpertsDetails($sExp, $graphdata)
{
    // stats expert
    $htmlOne = '<div class="stat_expert" style="%s"><span class="stat_title">%s</span><br/><span class="stat_value %s">%s</span></div>';
    $statsHtml = sprintf($htmlOne, '', 'Profit total', valeur2CSS($sExp->profit), $sExp->profit);
    $statsHtml .= sprintf($htmlOne, '', 'Ce mois', valeur2CSS($sExp->profit_mois), $sExp->profit_mois);
    $statsHtml .= sprintf($htmlOne, '', 'Tips publiés', '', $sExp->total);
    $statsHtml .= sprintf($htmlOne, '', 'Tips gagnés', valeur2CSS(1), $sExp->V);
    $statsHtml .= sprintf($htmlOne, '', 'Tips perdus', valeur2CSS(-1), $sExp->P);
    $statsHtml .= sprintf($htmlOne, '', 'Tips remb.', 'nul', $sExp->N);

    $yield = prefixSign(StatsDAO::calculateYield($sExp));

    $statsHtml .= sprintf($htmlOne, 'border-width:1px;width:96px;padding-left:0;padding-right:0;', 'Yield total', valeur2CSS($yield), $yield);


    // FIXME : this must be useless as it's only set empty label for the graph
    $deuxcents = '';
    for ($i = 1; $i <= count($graphdata); ++$i) {
        $deuxcents .= "'',";
    }

    $graphdata = implode(',', $graphdata);

    $graph = '<script src="/wp-content/plugins/pronostics/Chart.min.js"></script>
    <h5 class="graphtitle">Evolution du profit sur les 50 derniers paris</h5>
    <canvas id="buyers" width="720" height="180"></canvas>

    <script>
    var options = {
    scaleOverlay : false,
    scaleOverride : false,
    scaleLineColor : "rgba(0,0,0,.1)",
    scaleLineWidth : 1,
    scaleShowLabels : true,
    scaleLabel : "<%=value%>",
    scaleFontFamily : "\'Arial\'",
    scaleFontSize : 12,
    scaleFontStyle : "normal",
    scaleFontColor : "#666",
    scaleShowGridLines : true,
    scaleGridLineColor : "rgba(0,0,0,.08)",
    scaleGridLineWidth : 1,
    bezierCurve : true,
    pointDot : true,
    pointDotRadius : 3,
    pointDotStrokeWidth : 0,
    datasetStroke : false,
    datasetStrokeWidth : 1,
    datasetFill : true,
    animation : false
    };

    var data = {
    labels : ['.$deuxcents.'\'\'],
    datasets : [
    {
    strokeColor : "rgba(95, 140, 163, 1)",
    pointColor : "rgba(95, 140, 163, 1)",
    fillColor: "rgba(95, 140, 163, 1)",
    pointHighlightFill: "#fff",
    pointHighlightStroke: "rgba(95, 140, 163, 1)",
    pointStrokeColor : "#fff",
    data : ['.$graphdata.']
    }
    ]

    };

    var ctx = document.getElementById("buyers").getContext("2d");
    new Chart(ctx).Line(data,options);;
    </script>';

    return "<div>$statsHtml</div><div>$graph</div><div style=\"clear:both;text-align:center;font-size:20px;\">$liens_date</div>\n$listeParis";
}
