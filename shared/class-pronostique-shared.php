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

    // Force opening comments
    public function prono_comment_open($open, $post_id)
    {
        global $post;
        $comm_status = $post->comment_status;
        $post_type = $post->post_type;
        $post_id = (int) $post->ID;
        $open = ($comm_status == 'open') ? true : $open;
        $open = is_front_page() ? false : $open;
        $open = ($post_type == 'page') ? false : $open;
        $open = ($post_id == 11557 || $post_id == 19093 || 28900) ? false : $open;
        $open = ($post_type == 'prono-post') ? true : $open;

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
        remove_filter('save_post', array($GLOBALS['scoper_admin_filters'], 'custom_taxonomies_helper'), 5, 2);
        remove_filter('pods_api_post_save_pod_item_pronostique', array($this, 'sync_prono_with_post'), 5, 2);
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
            $date = date('Y-m-d H:i:s');
            if(strtotime($prono->field('date')) < strtotime( "2017-08-20")) {
                $date = $prono->field('date');
            }
            $new_post = array(
                'post_title' => $pieces[ 'fields' ][ 'name' ][ 'value' ],
                'post_content' => $pieces[ 'fields' ][ 'analyse' ][ 'value' ],
                'post_status' => 'publish',
                'post_date' => $date,
                'post_author' => $pieces[ 'fields' ][ 'author' ][ 'value' ],
                'post_type' => 'prono-post',
                'meta_input' => array('pronostique' => $id)
            );
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
            wp_set_object_terms($post_id, intval($pieces[ 'fields' ][ 'sport' ][ 'value' ]), 'sport', false);
        }

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
        else {
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
        remove_filter('pods_api_post_save_pod_item_prono-post', array($this, 'sync_post_with_prono'), 5, 2);
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
