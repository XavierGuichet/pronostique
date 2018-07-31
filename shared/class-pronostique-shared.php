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
class Pronostique_Shared
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
    }

    public function cron_cache_clear() {
      PronoLib::getInstance()->refreshTaxonomiesFilterData();
      PronoLib::getInstance()->refreshAllData();
    }

    // Force opening comments
    public function prono_comment_open($open, $post_id)
    {
        global $post;
        $comm_status = $post->comment_status;
        $post_type = $post->post_type;
        $post_id = (int) $post->ID;
        $open = ($post_type == 'page') ? $open : $open;
        $open = ($comm_status == 'open') ? true : $open;
        $open = is_front_page() ? false : $open;
        $open = ($post_id == 11557 || $post_id == 19093 || $post_id == 28900) ? false : $open;
        return $open;
    }

    // NOTE : test redirection post 404
    public function handle_rewrite_conflit( $wp_query ) {
        if(isset($wp_query->query_vars['competition']) && !isset($wp_query->query_vars['prono-post'])) {
            $potential_conflit_slug = $wp_query->query_vars['competition'];
            $term = get_term_by('slug', $potential_conflit_slug, 'competition' );
            if(!$term) {
                $wp_query->query_vars['prono-post'] = $potential_conflit_slug;
                $wp_query->query_vars['post_type'] = 'prono-post';
                $wp_query->query_vars['name'] = 'prono-post';
                $wp_query->query_vars['page'] = $potential_conflit_slug;
            }
        }
        return $wp_query;
    }

    // NOTE : New code for permalink
    public function prono_rewrite_rule($rules) {
        // Attachement
            // sans competition
            add_rewrite_rule('pronostic-attachment/[^/]+?/([^/]+?)/$', 'index.php?attachment=$matches[1]', 'bottom');
            // avec competition
            add_rewrite_rule('pronostic-attachment/[^/]+?/[^/]+?/([^/]+?)/$', 'index.php?attachment=$matches[1]', 'bottom');
        // feed
            // sans competition
            add_rewrite_rule('pronostic/[^/]+/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$',	'index.php?attachment=$matches[1]&feed=$matches[2]', 'top');
            add_rewrite_rule('pronostic/[^/]+/([^/]+)/(feed|rdf|rss|rss2|atom)/?$',	'index.php?attachment=$matches[1]&feed=$matches[2]', 'top');
            add_rewrite_rule('pronostic/[^/]+/([^/]+)/comment-page-([0-9]{1,})/?$',	'index.php?attachment=$matches[1]&cpage=$matches[2]', 'top');
            add_rewrite_rule('pronostic/[^/]+/([^/]+)/embed/?$',	'index.php?attachment=$matches[1]&embed=true', 'top');
            // avec competition
            add_rewrite_rule('pronostic/[^/]+/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$',	'index.php?attachment=$matches[1]&feed=$matches[2]', 'top');
            add_rewrite_rule('pronostic/[^/]+/[^/]+/([^/]+)/(feed|rdf|rss|rss2|atom)/?$',	'index.php?attachment=$matches[1]&feed=$matches[2]', 'top');
            add_rewrite_rule('pronostic/[^/]+/[^/]+/([^/]+)/comment-page-([0-9]{1,})/?$',	'index.php?attachment=$matches[1]&cpage=$matches[2]', 'top');
            add_rewrite_rule('pronostic/[^/]+/[^/]+/([^/]+)/embed/?$',	'index.php?attachment=$matches[1]&embed=true', 'top');
        // filter
        add_rewrite_rule('pronostic/([^/]+?)/$', 'index.php?sport=$matches[1]', 'bottom');
        // prono
            // sans competition
            add_rewrite_rule('pronostic/([^/]+?)/([^/]+?)/$', 'index.php?sport=$matches[1]&competition=$matches[2]', 'bottom');
            // avec competition
            add_rewrite_rule('pronostic/([^/]+?)/([^/]+?)/([^/]+?)/$', 'index.php?sport=$matches[1]&competition=$matches[2]&prono-post=$matches[3]', 'bottom');
    }

    public function tips_permalinks( $permalink, $post ) {
        if($post->post_type === 'prono-post') {
            // recupère l'id du field post des pronostique
            // Attention, on ne recupère pas la valeur du field mais bien l'identifiant du field
            $pod_post_field = pods_api( 'pronostique')->load_fields(array('name' => 'post'));
            sort($pod_post_field);
            $pod_post_field_id = $pod_post_field[0]['id'];
            // Limite la recherche par le pronostique en partant de la table des relations pour trouvé le pronostique lié au prono-post
            $infos = pods( 'pronostique', array(
                    'select' => 't.id as prono_id, `prono_post_rel`.*, sport.slug as sport, competition.slug as competition',
                    'join' => 'LEFT JOIN `wp_podsrel` AS `prono_post_rel` ON `prono_post_rel`.`related_field_id` = '.$pod_post_field_id.' AND `prono_post_rel`.`item_id` = '.$post->ID,
                    'where' => 't.id = `prono_post_rel`.`related_item_id`'
                    // 'where' => '`rel_sport`.`item_id` = t.id AND `rel_competition`.`item_id` = t.id AND t.id = `prono_post_rel`.`related_item_id`'
                  )
                    )->data();
            // if($post->ID === 47624) {
            //   var_dump(get_post_status($post->ID));
            // }
            if($infos) {
                if(!isset($infos[0])) {
                    return $permalink;
                }
                $infos = $infos[0];
                $sport = (is_null($infos->sport) ? 'divers' : $infos->sport);
                $competition = (is_null($infos->competition) ? '' : $infos->competition."/");
                $permalink = str_replace( '%sport%', $sport, $permalink );
                $permalink = str_replace( '%competition%/', $competition, $permalink );
                return $permalink;
            }

            $sport = (is_null($sport) ? 'divers' : $sport);
            $competition = (is_null($competition) ? '' : $competition."/");
            $permalink = str_replace( '%sport%', $sport, $permalink );
            $permalink = str_replace( '%competition%/', $competition, $permalink );
            return $permalink;
        }
        return $permalink;
    }

    public function fix_pod_attachement_link( $link, $post_id ){
        $link = str_replace( 'pronostic/', 'pronostic-attachment/', $link );
        return str_replace( 'formulaire-experts', 'pronostic-attachment', $link );
    }

    // NOTE : New code for permalink
    public function prono_term_permalink($permalink, $term ) {
        if($term->taxonomy === 'sport') {
            $permalink = str_replace( '/sport', '/pronostic', $permalink );
            return $permalink;
        }
        if($term->taxonomy === 'competition') {
            $sport = pods( 'competition' )->find(array(
                'select' => ' `t`.term_id, `sport`.slug as sport_slug',
                'where' => '`t`.term_id = '.$term->term_id));
            $replacement = 'pronostic/'.$sport->display('sport_slug');
            $permalink = str_replace( 'competition', $replacement, $permalink );
            return $permalink;
        }
        return $permalink;
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

    public function validate_form($pieces, $is_new_item) {
        //Si ce n'est pas la migration
        if(!isset($_POST['migrate_expert_tips']) && !isset($_POST['migrate_std_tips'])) {
            if (isset($pieces[ 'fields' ][ 'analyse' ][ 'value' ])) {
                $stripped_analyse = strip_tags($pieces[ 'fields' ][ 'analyse' ][ 'value' ]);
                if(strlen($stripped_analyse) < 500) {
                    $message = "Votre analyse doit faire au moins 500 caractères. Elle fait actuellement : ".strlen($stripped_analyse);
                    return pods_error($message);
                }
            }
            if (isset($pieces[ 'fields' ][ 'date' ][ 'value' ]) && !current_user_can('manage_options')) {
              // Force time zone
              date_default_timezone_set('Europe/Paris');
              $match_date = $pieces[ 'fields' ][ 'date' ][ 'value' ];
              $match_time = strtotime($match_date);
              $nowPlus1Hour = time() + 3600;
              if(($nowPlus1Hour - $match_time) > 0) {
                return pods_error("La date du match (ou compétition) doit être dans au moins une heure.");
              }
            }
        }
        return $pieces;
    }

    // pods is localized for number type, So it accept either comma or dot.
    // we've setted pods to use dot, and if user use a comma we transform it in dot
    public function fix_cote_comma_float($pieces, $is_new_item) {
        if (isset($pieces[ 'fields' ][ 'cote' ][ 'value' ])) {
            $cote = $pieces[ 'fields' ][ 'cote' ][ 'value' ];
            $pieces[ 'fields' ][ 'cote' ][ 'value' ] = str_replace(',', '.',$cote);
        }
        return $pieces;
    }

    /*
     *  Create a prono-post if none are associated
     *  Set category of prono-post and sport taxonomy
     *  Associate the prono-post to pronostique and the the inverted relation
     */
    public function sync_post_with_prono($pieces, $is_new_item, $id) {
        $bool = remove_filter('save_post', array($GLOBALS['scoper_admin_filters'], 'custom_taxonomies_helper'), 5);
        $bool = remove_filter('pods_api_post_save_pod_item_prono-post', array($this, 'sync_prono_with_post'));

        $post_id = false;
        $prono = pods('pronostique',$id);

        if (isset($pieces[ 'fields' ][ 'post' ][ 'value' ])) {
            $post_id = (int) $pieces[ 'fields' ][ 'post' ][ 'value' ];
        }
        if ($prono->field('post')) {
            $post_id = $prono->field('post.ID');
        }

        // when pronostique doesn't have a prono-post linked
        // create one and associate it
        if (!$post_id) {
            if(isset($pieces[ 'fields' ][ 'name' ][ 'value' ])) {
                $post_title = $pieces[ 'fields' ][ 'name' ][ 'value' ];
            } else {
                $post_title = $prono->field('name');
            }
            if(isset($pieces[ 'fields' ][ 'analyse' ][ 'value' ])) {
                $post_content = $pieces[ 'fields' ][ 'analyse' ][ 'value' ];
            } else {
                $post_content = $prono->field('analyse');
            }
            if(isset($pieces[ 'fields' ][ 'author' ][ 'value' ])) {
                $post_author = $pieces[ 'fields' ][ 'author' ][ 'value' ];
            } else {
                $post_author = $prono->field('author.ID');
            }

            // $time = strtotime("-1 hour", time());
            // $date = date('Y-m-d H:i:s', $time);
            $new_post = array(
                'post_title' => $post_title,
                'post_content' => $post_content,
                'post_status' => 'publish',
                'post_date' => $date,
                'post_author' => $post_author,
                'post_type' => 'prono-post',
                'meta_input' => array('pronostique' => $id)
            );
            if(strtotime($prono->field('date')) < strtotime( "2017-08-20")) {
              $new_post['post_date'] = $prono->field('date');
            }

            $post_id = wp_insert_post($new_post);
            $prono->save(array('post' => $post_id));
        }

        // synchronyse les analyse, les titres
        $linked_post = get_post($post_id);
        if (strlen($prono->field('analyse')) != strlen($linked_post->post_content)) {
            wp_update_post( array( 'ID' => $post_id, 'post_content' => $prono->field('analyse') ) );
        }
        if ($prono->field('name') != $linked_post->post_title) {
            wp_update_post( array( 'ID' => $post_id, 'post_title' => $prono->field('name') ) );
        }

        // Synchronyse la taxonomy sport
        if (isset($pieces[ 'fields' ][ 'sport' ][ 'value' ])) {
            $bool = wp_set_object_terms($post_id, intval($pieces[ 'fields' ][ 'sport' ][ 'value' ]), 'sport', false);
        } else {
            wp_set_object_terms($post_id, intval($prono->field('sport.id')), 'sport', false);
        }
        // Synchronyse la taxonomy country
        if (isset($pieces[ 'fields' ][ 'country' ][ 'value' ])) {
            $bool = wp_set_object_terms($post_id, intval($pieces[ 'fields' ][ 'country' ][ 'value' ]), 'country', false);
        } else {
            wp_set_object_terms($post_id, intval($prono->field('country.id')), 'country', false);
        }
        // Synchronyse la taxonomy competition
        if (isset($pieces[ 'fields' ][ 'competition' ][ 'value' ])) {
            $bool = wp_set_object_terms($post_id, intval($pieces[ 'fields' ][ 'competition' ][ 'value' ]), 'competition', false);
        } else {
            wp_set_object_terms($post_id, intval($prono->field('competition.id')), 'competition', false);
        }

        wp_update_post( array(  'ID' => $post_id,'meta_input' => array('pronostique' => $id)));

        // Synchronyse les categories Expert et VIP
        //recupère les categories courante
        $current_cat = wp_get_object_terms( $post_id,  'category' );
        $current_cat_ids = array();
        foreach($current_cat as $cat) {
            $current_cat_ids[] = $cat->term_id;
        }
        $added_categories = array();
        $remove_categories = array();
        if(isset($pieces[ 'fields' ][ 'is_vip' ][ 'value' ]) && (int) $pieces[ 'fields' ][ 'is_vip' ][ 'value' ] == 1) {
            $added_categories[] = (int) get_option("prono_vip_default_category", 0);
            $remove_categories[] = (int) get_option("prono_expert_default_category", 0);
            $remove_categories[] = (int) get_option("prono_std_default_category", 0);
        }
        elseif(isset($pieces[ 'fields' ][ 'is_expert' ][ 'value' ]) && (int) $pieces[ 'fields' ][ 'is_expert' ][ 'value' ] == 1) {
            $added_categories[] = (int) get_option("prono_expert_default_category", 0);
            $remove_categories[] = (int) get_option("prono_vip_default_category", 0);
            $remove_categories[] = (int) get_option("prono_std_default_category", 0);
        }
        elseif($is_new_item || (isset($pieces[ 'fields' ][ 'is_vip' ][ 'value' ]) && isset($pieces[ 'fields' ][ 'is_expert' ][ 'value' ]))) {
            $added_categories[] = (int) get_option("prono_std_default_category", 0);
            $remove_categories[] = (int) get_option("prono_expert_default_category", 0);
            $remove_categories[] = (int) get_option("prono_vip_default_category", 0);
        }
        $new_categories = array_merge($current_cat_ids, $added_categories);
        $new_categories = array_diff($new_categories, $remove_categories);
        $new_categories = array_unique($new_categories);
        wp_set_object_terms($post_id, $new_categories, 'category', false);
    }

    public function sync_prono_with_post($pieces, $is_new_item, $post_id) {
        remove_filter('pods_api_post_save_pod_item_pronostique', array($this, 'sync_post_with_prono'));
        $prono_id = false;
        if(isset($pieces[ 'fields' ][ 'pronostique' ][ 'value' ])) {
            $prono_id = $pieces[ 'fields' ][ 'pronostique' ][ 'value' ];
        }
        if($prono_id) {
            $prono = pods('pronostique',$prono_id);
            $linked_post = get_post($post_id);
            if (strlen($prono->field('analyse')) != strlen($linked_post->post_content)) {
                $prono->save( array( 'analyse' => $linked_post->post_content ));
            }
            if ($prono->field('name') != $linked_post->post_title) {
                $prono->save( array( 'name' => $linked_post->post_title ));
            }
        }
    }
}
