<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://www.xavier-guichet.fr
 * @since      1.0.0
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @author     Xavier Guichet <contact@xavier-guichet.fr>
 */
class Pronostique_Public
{
    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     *
     * @var string The ID of this plugin
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     *
     * @var string The current version of this plugin
     */
    private $version;

    private  $templater;

    private $table_user;
    private $table_tips;
    private $gain_sql;

/**
 * Initialize the class and set its properties.
 *
 * @since    1.0.0
 *
 * @param string $plugin_name The name of the plugin
 * @param string $version     The version of this plugin
 */
    // public function __construct( $plugin_name, $version ) {
    public function __construct()
    {
        $this->plugin_name = '';
        $this->version = '';

        global $wpdb;
        $this->table_user = $wpdb->prefix.'users';

        $this->table_tips = $wpdb->prefix.'bmk_tips';
        $this->templater = new TemplateEngine(__DIR__);
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__).'css/pronostique-public.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__).'js/pronostique-public.js', array('jquery'), $this->version, false);
    }

    // Force opening comments
    public function prono_comment_open($open, $post_id)
    {
        global $post;
        $comm_status = $post->comment_status;
        $post_type = $post->post_type;
        $post_id = (int) $post->ID;
        $open = ($post_type == 'page') ? true : $open;
        $open = ($comm_status == 'open') ? true : $open;
        $open = is_front_page() ? false : $open;
        $open = ($post_id == 11557 || $post_id == 19093) ? false : $open;

        return $open;
    }

    // Comment Meta
    public function update_comments_meta($post_id)
    {
        $args = array(
            'post_id' => $post_id,
        );
        $comments = get_comments($args);
        if (!empty($comments)) {
            foreach ($comments as $comment) {
                update_comment_meta($comment->comment_ID, 'pronostic_tips_id', $_GET['id']);
            }
        }
    }

    public function register_widgets() {
        register_widget( 'TopTipster_Widget' );
    }

    public function register_shortcodes()
    {
        add_shortcode('menu-pronostics', array($this, 'sc_displayMenuPronostic'));

        add_shortcode('liste-experts',   array($this, 'sc_displayListExperts'));

        add_shortcode('liste-paris', array($this, 'sc_displayListParis'));

        add_shortcode('liste-top-tipsers', array($this, 'sc_getListTop'));
        add_shortcode('classement-hotstreak', array($this, 'sc_getHotStreakRanking'));

        add_shortcode('poolbox', array($this, 'sc_displayPoolBox'));
        add_shortcode('stats-experts', array($this, 'sc_displayStatsExperts'));

        add_shortcode('user-stats-side', array($this, 'sc_displayUserStatsSide'));


        add_shortcode('user-perf-summary', array($this, 'sc_displayUserPerfSummary'));
        add_shortcode('history-graph', array($this, 'sc_displayHistoryGraph'));
        add_shortcode('user-history-pagination', array($this, 'sc_displayUserHistoryPagination'));

        // add_shortcode('stats-experts-details', 'getStatsExpertsDetailsHtml');
        // add_shortcode('liste-paris-expert-par-mois', 'getListParisExpertParMois');
        // add_shortcode('stats-tipsters-side', 'getTipsterStatsSide');

        add_shortcode('listParisTipsterParMois', array($this, 'deprecated'));
        add_shortcode('liste-top-tipsers-mois', array($this, 'deprecated'));
        add_shortcode('liste-prono-experts', array($this, 'deprecated')); //getPronosExperts
        add_shortcode('liste-paris-experts',  array($this, 'deprecated'));
        add_shortcode('liste-experts-actifs',   array($this, 'deprecated'));
        add_shortcode('liste-experts-inactifs', array($this, 'deprecated'));
    }

    public static function deprecated($atts = [], $content = null, $tag = '')
    {
        trigger_error('Deprecated shortcode used : '.$tag, E_USER_NOTICE);
    }

    //######################
    //     SHORTCODE
    //######################
    public static function sc_displayHistoryGraph($atts = [], $content = null, $tag = '') {
        // TODO: WArning, le graph est 'inversé'
        $atts = array_change_key_case((array) $atts, CASE_LOWER);
        $params = shortcode_atts([
                     'user_id' => '',
                 ], $atts, $tag);

        $tips = $this->getPronostics($params['user_id'], 'all', '','', '',  false, '', 0, 50);

        $graph_data = array();
        $previous_profit = 0;
        // TODO : this is false, sum are made in bad order
        while($tips->fetch()) {
            $profit = 0;
            if ($tips->field('resultat') == 1) {
                $profit = ($tips->field('mise') * ($tips->field('cote') - 1));
            } elseif ($tips->field('resultat') == 2) {
                $profit = -$tips->field('mise');
            }
            $graph_data[] = $previous_profit + $profit;
            $previous_profit += $profit;
            // $graph_tips_array[] = $graph_tips;
        }
        $graph_data = array_reverse($graph_data);

        $emptylabels = implode(',',array_fill(0,count($graph_data),"''"));
        $graphdata = implode(',', $graph_data);

        return $this->templater->display('history-graph',
                        array('labels' => $emptylabels,
                              'graphdata' => $graphdata));
    }

    public function sc_displayUserPerfSummary($atts = [], $content = null, $tag = '') {
        $atts = array_change_key_case((array) $atts, CASE_LOWER);
        $params = shortcode_atts([
                     'user_id' => '',
                 ], $atts, $tag);

        $gain_sql = " ROUND(SUM( IF(resultat = 1, (cote-1)*mise, IF(resultat = 2, - mise, IF(resultat = 3, 0, 0))) ), 2) as gain";
        $mises_sql = " ROUND(SUM( IF(resultat IN (1,2,3), mise, 0) ), 2) as mises";
        $VPNA_sql = " SUM(IF(resultat = 1,1,0)) AS V, SUM(IF(resultat = 3,1,0)) AS N, SUM(IF(resultat = 2,1,0)) AS P, SUM(IF(resultat = 0,1,0)) AS A";

        $stats = pods('pronostique')->find(array(
                            'select' => "count(*) as 'nb_total_tips',".$gain_sql.",".$mises_sql.",".$VPNA_sql,
                            'where' => "author.id = ".$params['user_id']
        ));

        $user_month_profit = pods('pronostique')->find(array(
                            'select' => $gain_sql,
                            'where' => "author.id = ".$params['user_id']." AND MONTH(date) like MONTH(NOW())"
        ));

        if($user_month_profit->total() == 0 || $user_month_profit->field('gain') === null) {
            $month_profit = 0;
        } else {
            $month_profit = $user_month_profit->field('gain');
        }

        $yield = Calculator::Yield( $stats->field('mises'), $stats->field('gain'));

        return $this->templater->display('user-perf-summary',
                            array('stats' => $stats,
                                  'month_profit' => $month_profit,
                                  'yield' => $yield ));
    }

    public function sc_displayUserHistoryPagination($atts = [], $content = null, $tag = '') {
        $atts = array_change_key_case((array) $atts, CASE_LOWER);
        $params = shortcode_atts([
                     'user_id' => '',
                     'currentmonth' => date('M-y'),
                 ], $atts, $tag);

        $months_with_nb_pari = pods('pronostique')->find(array(
                            'select' => "DATE_FORMAT(date,'%Y-%m') as month, COUNT(*) as nb_tips",
                            'groupby' => 'month',
                            'where' => "author.id =".$params['user_id'],
                            'orderby' => 'month DESC',
                            'limit' => 20
                        )
        );

        return $this->templater->display('user-history-pagination',
                            array( 'months_with_nb_pari' => $months_with_nb_pari,
                                   'user_id' => $params['user_id'],
                                   'currentmonth' => $currentmonth ));
    }

    public function sc_displayMenuPronostic($atts = [], $content = null, $tag = '')
    {
        global $wpdb;
        $links = array();
        if (is_user_logged_in()) {
            // Verifie si l'utilisateur appartient au groupe Tipseur et/ou Expert
            $user_id = get_current_user_id();
            $est_expert = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->user2group_rs JOIN $wpdb->groups_rs ON $wpdb->groups_id_col = $wpdb->user2group_gid_col WHERE $wpdb->groups_name_col LIKE 'Experts' AND $wpdb->user2group_uid_col = '$user_id'");
            $est_tipster = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->user2group_rs JOIN $wpdb->groups_rs ON $wpdb->groups_id_col = $wpdb->user2group_gid_col WHERE $wpdb->groups_name_col LIKE 'Tipseurs' AND $wpdb->user2group_uid_col = '$user_id'");

            if ($est_tipster) {
                $links[] = array('title' => 'Mes pronostics',
                                 'href' => '/tipser-stats/?id='.$user_id, );
                // $links[] = array('title' => 'Ajouter un pronostic',
                //                  'href' => '/formulaire-pronostics', );
                $links[] = array('title' => 'Ajouter un pronostic (pod)',
                                 'href' => '/formulaire-pronostic-new', );
            } else {
                $links[] = array('title' => 'S\'enregistrer comme tipster',
                                 'href' => '/formulaire-tipseur', );
            }
            if ($est_expert) {
                // $links[] = array('title' => 'Ajouter un prono. expert',
                //                  'href' => '/formulaire-experts', );
            }
        }



        return $this->templater->display('menu-pronostique',
                        array('links' => $links));
    }

    public function sc_displayListExperts($atts = [], $content = null, $tag = '')
    {
        trigger_error('Deprecated '.$tag.', should be a widget.', E_USER_NOTICE);
        $atts = array_change_key_case((array) $atts, CASE_LOWER);
        $params = shortcode_atts([
                                         'limit' => 'all',
                                     ], $atts, $tag);

        // TODO this should request directly only actif / all or inactif experts
        $experts = StatsDAO::getAllExpertsStats('');

        $out = '';

        foreach ($experts as $e) {
            $is_expert = UsersDAO::isUserInGroup($e->user_id, UsersDAO::GROUP_EXPERTS);
            if (!$is_expert && $params['limit'] == 'inactif') {
                continue;
            }

            $is_retired = UsersDAO::isUserInGroup($e->user_id, UsersDAO::GROUP_RETIRED_EXPERTS);
            if (!$is_retired && $params['limit'] == 'actif') {
                continue;
            }

            $yield = Formatter::prefixSign(StatsDAO::calculateYield($e));

            $href_expert = '/bilan-expert/?id='.$e->user_id;

            $profit = Formatter::prefixSign($e->profit ? $e->profit : 0);

            $profit_class = Formatter::valeur2CSS($e->profit);

            $out .= '<p class="bloc_expert">
                    <a href="'.$href_expert.'">'.$e->nom_tipser.'</a>
                    <span class="'.$profit_class.'">'.$profit.' Unités</span> <br />
                    <span class="subtitle">Yield : '.$yield.'</span>
                    <hr/>
                </p>';

                // FIXME: Ancien contenu pour l'affichage des experts inactifs
                // $out .= '<p>
                //             '.$e->nom_tipser.'
                //             <span class="'.$profit_class.'">'.$profit.' Unités</span>
                //         </p>';
        }

        if (!count($experts)) {
            $out .= '<em>Aucuns experts...</em>';
        }

        return $out;
    }

    public function sc_getListTop($atts = [], $content = null, $tag = '')
    {
        $atts = array_change_key_case((array) $atts, CASE_LOWER);
        $params = shortcode_atts([
                                         'titre' => '',
                                         'of_the_month' => true,
                                         'limit' => 10,
                                     ], $atts, $tag);

        return $this->getListTop($params['titre'], $params['of_the_month'], $params['limit']);
    }

    public function sc_getHotStreakRanking($atts = [], $content = null, $tag = '')
    {
        trigger_error('WIP : '.$tag, E_USER_NOTICE);
        // TODO: This could be done with a subquery system to have the 20 limit
        $users_hotstreak = pods('pronostique')->find(
                                array(
                                    'select' => "0 AS V, 0 AS P, 0 AS N, resultat, author.ID, author.user_nicename",
                                    'limit' => 0,
                                    'where' => 'resultat > 0',
                                    'orderby' => 'author.ID ASC'
                                )
        );

        return 'HotStreak Classement WIP';
    }

    public function sc_displayPoolBox($atts = [], $content = null, $tag = '')
    {
        $pronos = pods('pronostique')->find(
                    array(
                        'where' => 'is_expert = 1',
                        'limit' => '10',
                        'orderby' => 'date Desc',
                    ));

        return $this->templater->display('poolbox', array('pronos' => $pronos));
    }

    public function sc_displayStatsExperts($atts = [], $content = null, $tag = '')
    {
        //$statsglob = StatsDAO::getGlobalStats();
        $mises_sql = ' ROUND(SUM( IF(resultat IN (1,2,3), mise, 0) ), 2) as mises';
        $gain_sql = ' ROUND(SUM( IF(resultat = 1, (cote-1)*mise, IF(resultat = 2, - mise, IF(resultat = 3, 0, 0))) ), 2) as gain';
        $VPNA_sql = "SUM(IF(resultat = 1,1,0)) AS 'V', SUM(IF(resultat = 3,1,0)) AS 'N', SUM(IF(resultat = 2,1,0)) AS 'P', SUM(IF(resultat = 0,1,0)) AS 'A'";
        $stats = pods('pronostique')->find(
                        array(
                            'select' => $mises_sql.','.$gain_sql.','.$VPNA_sql,
                            'where' => 'is_expert = 1',
                        ));

        return $this->templater->display('global-stats', array('stats' => $stats));
    }

    public function sc_displayListParis($atts = [], $content = null, $tag = '')
    {
        $atts = array_change_key_case((array) $atts, CASE_LOWER);
        $params = shortcode_atts([
                                    'user_id' => 0,
                                    'expertonly' => false,
                                    'sport' => 'all',
                                    'excludesport' => null,
                                    'sportname' => '',
                                    'offset' => 0,
                                    'limit' => 20,
                                    'display' => 'list',
                                    'direction' => 'column',
                                    'month' => '',
                                    'viponly' => 0,
                                    'showvip' => 1
                                     ], $atts, $tag);

        $tips = $this->getPronostics($params['user_id'], $params['sport'], $params['excludesport'], $params['month'], $params['viponly'], $params['showvip'] , '', $params['offset'], $params['limit'], 'DESC');

        $show_sport = true;
        if ($params['sport'] != '' && $params['sport'] != 'all') {
            $show_sport = false;
        }

        $show_user = ($params['user_id'] == -1);

        $isUserAdherent = UsersDAO::isUserInGroup(get_current_user_id(), UsersDAO::GROUP_ADHERENTS);

        $template = $params['display'].'-pronostics';


        return $this->templater->display($template,array('all_tips' => $tips,
              'show_sport' => $show_sport,
              'show_user' => $show_user,
              'isUserAdherent' => $isUserAdherent,
              'direction' => $params['direction']
          ));
    }

    public function sc_displayUserStatsSide($atts = [], $content = null, $tag = '') {
        return 'sc_displayUserStatsSide';
    }


    //######################
    //   RLY USED
    //######################
    // TODO : Le shortcode 'list tipster ...' à un nom pourri.
    // Celui ci retourne des classements. Il devrait donc s'appeller classement.
    // Comme les tips std et expert sont maintenant regroupé. Il faudra ajouter un paramètre
    // Les params d'un classement sont :
    //   + Pari expert ou pari standard (potentiellement pari VIP)
    //   + Limit de resultat
    //   + Limite de mois (possibilité d'archive ?)
    //   + Titre 'bien que je trouve pas ca top'
    public function getListTop($titre = 'Top tipsters du mois', $of_the_month = true, $max = 10)
    {
        // TODO : fix to real month / year when data set will be ready
        $cond_month = $of_the_month == 'true' ? ' AND actif = 1 AND MONTH(date) = 3 AND YEAR(date) = 2014' : '';

        $pronos = pods('pronostique')->find(
            array(
                'select' => 'ROUND(SUM( IF(resultat = 1, (cote-1)*mise, IF(resultat = 2, - mise, IF(resultat = 3, 0, 0))) ), 2) AS Gain, t.*',
                'where' => 'resultat > 0'.$cond_month,
                'limit' => $max,
                'orderby' => 'Gain Desc',
                'groupby' => 'author.id',
            )
            );

        $entete = '<tr><th>&nbsp;</th> <th>Pseudo</th> <th>Profit</th></tr>';

        $tpl_params = array('titre' => $titre,
                            'entetes' => $entetes,
                                'row' => $pronos,
                            );

        return $this->templater->display('classements', $tpl_params);
    }

    public function getPronostics($user_id = 0, $sport = '', $exclude_sport = '', $month = '', $viponly = 0, $showvip = 1, $cond_param = 'resultat = 0', $offset = 0, $limit = 20, $sort_order = 'ASC')
    {
        global $wpdb;

        $params = array(
            'limit' => $limit,
            'offset' => $offset,
            'orderby' => 'date '.$sort_order,
            'where' => '1 ',
        );

        // TODO : may not be usefull & should be sanatize
        if (!empty($cond_param)) {
            $params['where'] .= ' AND '.$cond_param;
        }

        if ($user_id == 0) {
            $params['where'] .= ' AND author.id = '.get_current_user_id();
        } elseif (is_numeric($user_id) && $user_id > 0) {
            $params['where'] .= ' AND author.id = '.$user_id;
        }

        if ($sport != '' && $sport != 'all') {
            $params['where'] .= " AND sport.name = '".$sport."'";
        }
        if ($exclude_sport != '') {
            $params['where'] .= " AND sport.name != '".$exclude_sport."'";
        }
        if ($month != '') {
            $params['where'] .= " AND date LIKE '%".$month."%'";
        }

        if ((int) $viponly) {
            $params['where'] .= " AND is_vip = 1";
            $showvip = 1;
        }

        if (! (int) $showvip) {
            $params['where'] .= " AND is_vip = 0";
        }
        $all_tips = pods('pronostique')->find($params);

        return $all_tips;
    }
}
