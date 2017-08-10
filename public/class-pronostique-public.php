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

    private $templater;

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

    function add_custom_types( $query ) {
    if( (is_category() || is_tag()) && $query->is_archive() && empty( $query->query_vars['suppress_filters'] ) ) {
        $query->set( 'post_type', array(
         'post', 'prono-post'
            ));
        }
    return $query;
    }

    public function register_widgets()
    {
        register_widget('TipsterStats_Widget');
        register_widget('TopTipster_Widget');
        register_widget('TopVip_Widget');
        register_widget('TipsterLastTips_Widget');
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

        add_shortcode('global-perf', array($this, 'sc_displayGlobalPerf'));

        add_shortcode('listParisTipsterParMois', array($this, 'deprecated'));
        add_shortcode('liste-top-tipsers-mois', array($this, 'deprecated'));
        add_shortcode('liste-prono-experts', array($this, 'deprecated'));
        add_shortcode('liste-paris-experts',  array($this, 'deprecated'));
        add_shortcode('liste-experts-actifs',   array($this, 'deprecated'));
        add_shortcode('liste-experts-inactifs', array($this, 'deprecated'));
    }

    public static function deprecated($atts = [], $content = null, $tag = '')
    {
        trigger_error('Deprecated shortcode used : '.$tag, E_USER_NOTICE);
    }

    /*
     *  Create a prono-post if none are associated
     *  Set category of prono-post and sport taxonomy
     *  Associate the prono-post to pronostique and the the inverted relation
     */
    public function create_linked_prono_post($pieces, $is_new_item, $id) {
        $post_id = false;
        if (isset($pieces[ 'fields' ][ 'post' ][ 'value' ])) {
            $post_id = (int) $pieces[ 'fields' ][ 'post' ][ 'value' ];
        }
        $category = array();
        if (!$post_id) {
            if((int) $pieces[ 'fields' ][ 'is_vip' ][ 'value' ] == 1) {
                // TODO: get this by an admin param
                // $category[] = $vip_category->term_id;
                // $vip_category = get_term_by('slug', 'vip', 'category');
                // if ($vip_category->term_id) {
                // }
            }
            elseif((int) $pieces[ 'fields' ][ 'is_expert' ][ 'value' ] == 1) {
                $category[] = 6; // TODO: get this by an admin param
                // $expert_category = get_term_by('slug', 'les-paris-de-nos-experts', 'category');
                // if ($expert_category->term_id) {
                //     $category[] = $expert_category->term_id;
                // }
            }
            $new_post = array(
                'post_title' => $pieces[ 'fields' ][ 'name' ][ 'value' ],
                'post_content' => $pieces[ 'fields' ][ 'analyse' ][ 'value' ],
                'post_status' => 'publish',
                'post_date' => date('Y-m-d H:i:s'),
                'post_author' => $pieces[ 'fields' ][ 'author' ][ 'value' ],
                'post_type' => 'prono-post',
                'meta_input' => array('pronostique' => $id)
            );
            if (count($category) > 0) {
                $new_post['post_category'] = $category;
            }
            $post_id = wp_insert_post($new_post);
            wp_set_object_terms($post_id, intval($pieces[ 'fields' ][ 'sport' ][ 'value' ]), 'sport', false);

            $prono = pods('pronostique',$id);
            $prono->save(array('post' => $post_id));

        }
    }

    //######################
    //     SHORTCODE
    //######################
    public static function sc_displayHistoryGraph($atts = [], $content = null, $tag = '')
    {
        $params = $this->prepareParams($atts,$tag);

        $tips = $this->getPronostics($params['user_id'],
                                     null, //sport
                                     null, //exclude_sport
                                     null, //month
                                     $params['hidetips'], //hidetips
                                     $params['hideexpert'], //hideexpert
                                     null, //hidevip
                                     null, //viponly
                                     1, //avec resultat
                                     0, //offset
                                     50, //limit
                                     'DESC');

        $graph_data = array();
        $cumulated_profit = 0;
        $inv_data = array();
        while ($tips->fetch()) {
            $inv_data[] = $tips->row();
        }
        $data = array_reverse($inv_data);
        for ($i = 0; $i < count($data); ++$i) {
            $tip = $data[$i];
            $profit = 0;
            if (intval($tip['tips_result']) == 1) {
                $profit = $tip['mise'] * ($tip['cote'] - 1);
            } elseif (intval($tip['tips_result']) == 2) {
                $profit = -$tip['mise'];
            }
            $cumulated_profit = floatval($cumulated_profit) + floatval($profit);
            $graph_data[] = $cumulated_profit;
        }

        $emptylabels = implode(',', array_fill(0, count($graph_data), "''"));
        $graphdata = implode(',', $graph_data);

        return $this->templater->display('history-graph',
                        array('labels' => $emptylabels,
                              'graphdata' => $graphdata, ));
    }

    public function sc_displayUserPerfSummary($atts = [], $content = null, $tag = '')
    {
        $params = $this->prepareParams($atts, $tag);

        $gain_sql = ' ROUND(SUM( IF(tips_result = 1, (cote-1)*mise, IF(tips_result = 2, - mise, IF(tips_result = 3, 0, 0))) ), 2) as gain';
        $mises_sql = ' ROUND(SUM( IF(tips_result IN (1,2,3), mise, 0) ), 2) as mises';
        $VPNA_sql = ' SUM(IF(tips_result = 1,1,0)) AS V, SUM(IF(tips_result = 3,1,0)) AS N, SUM(IF(tips_result = 2,1,0)) AS P, SUM(IF(tips_result = 0,1,0)) AS A';

        $where = '1';
        if ($params['user_id'] !== null and is_numeric($params['user_id'])) {
            $where .= ' AND author.id = '.intval($params['user_id']);
        }
        if ($params['hidetips'] !== null) {
            $where .= ' AND is_expert = 1';
        }
        $hide_month_profit = false;
        if ($params['month'] !== null) {
            if (preg_match('/^[0-9]{4}\-[0-9]{2}$/',$params['month'])) {
                $hide_month_profit = true;
                $where .= " AND date LIKE '%".$params['month']."%'";
            }
        }


        $stats = pods('pronostique')->find(array(
                            'select' => "count(*) as 'nb_total_tips',".$gain_sql.','.$mises_sql.','.$VPNA_sql,
                            'where' => $where,
        ));

        $user_month_profit = pods('pronostique')->find(array(
                            'select' => $gain_sql,
                            'where' => $where.' AND MONTH(date) like MONTH(NOW()) AND YEAR(date) like YEAR(NOW())',
        ));

        if ($user_month_profit->total() == 0 || $user_month_profit->field('gain') === null) {
            $month_profit = 0;
        } else {
            $month_profit = $user_month_profit->field('gain');
        }

        $yield = Calculator::Yield($stats->field('mises'), $stats->field('gain'));

        return $this->templater->display('user-perf-summary',
                            array('stats' => $stats,
                                  'month_profit' => $month_profit,
                                  'hide_month_profit' => $hide_month_profit,
                                  'yield' => $yield ));
    }

    public function sc_displayUserHistoryPagination($atts = [], $content = null, $tag = '')
    {
        $atts = array_change_key_case((array) $atts, CASE_LOWER);
        $params = shortcode_atts([
                     'user_id' => '',
                     'currentmonth' => date('M-y'),
                 ], $atts, $tag);

        $months_with_nb_pari = pods('pronostique')->find(array(
                            'select' => "DATE_FORMAT(date,'%Y-%m') as month, COUNT(*) as nb_tips",
                            'groupby' => 'month',
                            'where' => 'author.id ='.$params['user_id'],
                            'orderby' => 'month DESC',
                            'limit' => 20,
                        )
        );

        $month_list = array();
        $index_current_month = -1;
        $i = 0;
        while ($months_with_nb_pari->fetch()) {
            $month_list[] = $months_with_nb_pari->row();
            if ($params['currentmonth'] == $months_with_nb_pari->field('month')) {
                $index_current_month = $i;
            }
            ++$i;
        }

        return $this->templater->display('user-history-pagination',
                            array('months_list' => $month_list,
                                   'user_id' => $params['user_id'],
                                   'currentmonth' => $params['currentmonth'],
                                   'index_current_month' => $index_current_month, ));
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
                $links[] = array('title' => 'Ajouter un pronostic',
                                 'href' => '/formulaire-pronostics', );
            } else {
                $links[] = array('title' => 'S\'enregistrer comme tipster',
                                 'href' => '/formulaire-tipseur', );
            }
            if ($est_expert) {
                $links[] = array('title' => 'Ajouter un prono. expert',
                                 'href' => '/formulaire-experts', );
            }
        }

        return $this->templater->display('menu-pronostique',
                        array('links' => $links));
    }

    public function sc_displayListExperts($atts = [], $content = null, $tag = '')
    {
        $atts = array_change_key_case((array) $atts, CASE_LOWER);
        $params = shortcode_atts([
                                         'limit' => 'all',
                                     ], $atts, $tag);

        // TODO this should request directly only actif / all or inactif experts
        $mises_sql = ' ROUND(SUM( IF(tips_result IN (1,2,3), mise, 0) ), 2) as mises';
        $gain_sql = ' ROUND(SUM( IF(tips_result = 1, (cote-1)*mise, IF(tips_result = 2, - mise, IF(tips_result = 3, 0, 0))) ), 2) as gain';
        $experts = pods('pronostique')->find(
                        array(
                            'select' => $mises_sql.','.$gain_sql.', author.ID as user_id, author.user_nicename as username',
                            'where' => 'is_expert = 1',
                            'groupby' => 'author.ID',
                            'orderby' => 'date DESC'
                        ));


        $tpl_params = array('experts' => $experts, 'limit' => $params['limit']);

        return $this->templater->display('list-experts', $tpl_params);
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
        $tips = pods('pronostique')->find(
                                array(
                                    'select' => 't.ID, tips_result, author.ID as user_id, author.user_nicename as username',
                                    'limit' => 0,
                                    'where' => 'tips_result > 0 AND is_expert != 1 AND date BETWEEN (CURDATE() - INTERVAL 365 DAY) AND CURDATE()',
                                    'orderby' => 'user_id DESC, date DESC',
                                )
        );

        $res2letter = array(1 => 'V', 2 => 'P', 3 => 'N');
        $hotStreaks_by_uid = array();

        // create array with hotstreak of all tipster
        while ($tips->fetch()) {
            $user_id = $tips->field('user_id');
            if (!isset($hotStreaks_by_uid[$user_id])) {
                $hotStreaks_by_uid[$user_id] = array(
                                    'V' => 0,
                                    'P' => 0,
                                    'N' => 0,
                                    'tips_count' => 0,
                                    'display_name' => $tips->field('username'),
                                    'user_id' => $user_id,
                                    );
            }
            if ($hotStreaks_by_uid[$user_id]['tips_count'] >= 20) {
                continue;
            }
            $hotStreaks_by_uid[$user_id]['tips_count'] += 1;
            $letter = $res2letter[$tips->field('tips_result')];
            $hotStreaks_by_uid[$user_id][$letter] += 1;
        }

        // create an array in which value are hot-streak string
        $best_id = array();
        foreach ($hotStreaks_by_uid as $uid => $user_hot_streak) {
            $best_id[$uid] = sprintf('%02d-%02d-%02d', $user_hot_streak['V'], $user_hot_streak['N'], $user_hot_streak['P']);
        }
        // order that array and keep first 25
        arsort($best_id);
        $best_id = array_slice($best_id, 0, 25, true);

        // get hotstreak complete information and add them to the outputed array;
        $best_hotstreak = array();
        foreach ($best_id as $uid => $v2) {
            $best_hotstreak[] = $hotStreaks_by_uid[$uid];
        }

        $entetes = '<tr><th>&nbsp;</th> <th>Pseudo</th> <th>Série</th></tr>';

        $tpl_params = array('titre' => 'Série en cours',
                            'entetes' => $entetes,
                                'row' => $best_hotstreak,
                            );

        return $this->templater->display('classements-hotstreak', $tpl_params);
    }

    public function sc_displayPoolBox($atts = [], $content = null, $tag = '')
    {
        $pronos = pods('pronostique')->find(
                    array(
                        'where' => 'is_expert = 1 AND is_vip = 0',
                        'limit' => '10',
                        'orderby' => 'date Desc',
                    ));

        return $this->templater->display('poolbox', array('pronos' => $pronos));
    }

    public function sc_displayStatsExperts($atts = [], $content = null, $tag = '')
    {
        $mises_sql = ' ROUND(SUM( IF(tips_result IN (1,2,3), mise, 0) ), 2) as mises';
        $gain_sql = ' ROUND(SUM( IF(tips_result = 1, (cote-1)*mise, IF(tips_result = 2, - mise, IF(tips_result = 3, 0, 0))) ), 2) as gain';
        $VPNA_sql = " SUM(IF(tips_result = 1,1,0)) AS 'V', SUM(IF(tips_result = 3,1,0)) AS 'N', SUM(IF(tips_result = 2,1,0)) AS 'P', SUM(IF(tips_result = 0,1,0)) AS 'A'";
        $stats = pods('pronostique')->find(
                        array(
                            'select' => $mises_sql.','.$gain_sql.','.$VPNA_sql,
                            'where' => 'is_expert = 1',
                        ));

        return $this->templater->display('global-stats', array('stats' => $stats));
    }

    public function sc_displayListParis($atts = [], $content = null, $tag = '')
    {
        $params = $this->prepareParams($atts, $tag);

        $tips = $this->getPronostics($params['user_id'],
                                     $params['sport'],
                                     $params['excludesport'],
                                     $params['month'],
                                     $params['hidetips'],
                                     $params['hideexpert'],
                                     $params['hidevip'],
                                     $params['viponly'],
                                     $params['with_result'],
                                     $params['offset'],
                                     $params['limit'],
                                     'DESC');

        $show_sport = ($params['sport'] === null && is_front_page());
        $show_user = ($params['user_id'] === null);
        $show_pari = !(is_front_page());
        $show_match_result = !(is_front_page());
        $show_profit = !(is_front_page());
        $use_poolbox = ($params['use_poolbox'] !== null);

        $isUserAdherent = UsersDAO::isUserInGroup(get_current_user_id(), UsersDAO::GROUP_ADHERENTS);

        $template = $params['display'].'-pronostics';

        return $this->templater->display($template, array('all_tips' => $tips,
              'show_sport' => $show_sport,
              'show_user' => $show_user,
              'show_pari' => $show_pari,
              'show_match_result' => $show_match_result,
              'show_profit' => $show_profit,
              'use_poolbox' => $use_poolbox,
              'isUserAdherent' => $isUserAdherent,
              'direction' => $params['direction'],
          ));
    }

    public function sc_displayGlobalPerf($atts = [], $content = null, $tag = '')
    {
        $params = $this->prepareParams($atts, $tag);

        $dateactu = strftime('%Y-%m-');
        $gain_sql = ' ROUND(SUM( IF(tips_result = 1, (cote-1)*mise, IF(tips_result = 2, - mise, IF(tips_result = 3, 0, 0))) ), 2) as gain';

        $gain_month_res = pods('pronostique')->find(array(
                            'select' => $gain_sql,
                            'where' => 'tips_result > 0 AND is_expert = 0 AND MONTH(date) like MONTH(NOW()) AND YEAR(date) like YEAR(NOW())',
        ));
        $gain_global_res = pods('pronostique')->find(array(
                            'select' => $gain_sql,
                            'where' => 'tips_result > 0 AND is_expert = 0', ));

        if ($gain_month_res->total() == 0 || $gain_month_res->field('gain') === null) {
            $gain_month = 0;
        } else {
            $gain_month = $gain_month_res->field('gain');
        }
        if ($gain_global_res->total() == 0 || $gain_global_res->field('gain') === null) {
            $gain_global = 0;
        } else {
            $gain_global = $gain_global_res->field('gain');
        }

        return $this->templater->display('global-stats-tipster', array(
                            'gain_month' => $gain_month,
                            'gain_global' => $gain_global, ));
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
        $cond_month = $of_the_month == 'true' ? ' AND MONTH(date) = 3 AND YEAR(date) = 2014' : '';

        $pronos = pods('pronostique')->find(
            array(
                'select' => 'ROUND(SUM( IF(tips_result = 1, (cote-1)*mise, IF(tips_result = 2, - mise, IF(tips_result = 3, 0, 0))) ), 2) AS Gain, t.*',
                'where' => 'tips_result > 0'.$cond_month,
                'limit' => $max,
                'orderby' => 'Gain Desc',
                'groupby' => 'author.id',
            )
            );

        $entetes = '<tr><th>&nbsp;</th> <th>Pseudo</th> <th>Profit</th></tr>';

        $tpl_params = array('titre' => $titre,
                            'entetes' => $entetes,
                                'row' => $pronos,
                            );

        return $this->templater->display('classements', $tpl_params);
    }

    public function getPronostics($user_id = null, $sport = null, $exclude_sport = null, $month = null, $hidetips = null, $hideexpert = null, $hidevip = null, $viponly = null, $with_result = null, $offset = 0, $limit = null, $sort_order = 'ASC')
    {
        $params = array(
            'offset' => $offset,
            'orderby' => 'date '.$sort_order,
            'where' => '1 ',
        );

        if ($user_id === '0') {
            $params['where'] .= ' AND author.id = '.get_current_user_id();
        } elseif (is_numeric($user_id)) {
            $params['where'] .= ' AND author.id = '.$user_id;
        }

        if ($sport != null) {
            $params['where'] .= " AND sport.name = '".$sport."'";
        }
        if ($exclude_sport != null) {
            $params['where'] .= " AND sport.name != '".$exclude_sport."'";
        }
        if ($month != null) {
            $params['where'] .= " AND date LIKE '%".$month."%'";
        }

        if ($hidetips !== null) {
            $params['where'] .= ' AND is_expert = 1';
        }

        if ($hideexpert !== null) {
            $params['where'] .= ' AND is_expert = 0';
        }

        if ($hidevip !== null && $hidevip != '0') {
            $params['where'] .= ' AND is_vip = 0';
        }

        if ($viponly !== null && $viponly != '0') {
            $params['where'] .= ' AND is_vip = 1';
        }

        if ($with_result !== null) {
            if (intval($with_result) === 1) {
                $params['where'] .= ' AND tips_result IS NOT NULL AND tips_result != 0';
            } else {
                $params['where'] .= " AND (tips_result IS NULL OR tips_result = '' OR tips_result = 0)";
            }
        }

        if ($limit !== null) {
            $params['limit'] = $limit;
        }

        $all_tips = pods('pronostique')->find($params);

        return $all_tips;
    }

    private function prepareParams($atts, $tag) {
        $atts = array_change_key_case((array) $atts, CASE_LOWER);
        $params = shortcode_atts([
                            'user_id' => null,
                            'sport' => null,
                            'excludesport' => null,
                            'month' => null,
                            'viponly' => 0,
                            'hidetips' => null,
                            'hideexpert' => null,
                            'hidevip' => null,
                            'with_result' => null,
                            'offset' => 0,
                            'limit' => 20,
                            'display' => 'list',
                            'direction' => 'column',
                            'use_poolbox' => null,
                             ], $atts, $tag);

        return $params;
    }
}
