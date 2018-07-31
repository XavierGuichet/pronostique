<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://www.xavier-guichet.fr
 * @since      1.0.0
 *
 * @package    Pronostique
 * @subpackage Pronostique/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Pronostique
 * @subpackage Pronostique/admin
 * @author     Xavier Guichet <contact@xavier-guichet.fr>
 */
class Pronostique_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

    private $templater;
    private $xdkdcache;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
    $this->templater = new TemplateEngine(__DIR__);
    $this->xdkdcache = new XdkdCache();
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/pronostique-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/pronostique-admin.js', array( 'jquery','jquery-form' ), $this->version, false );
        wp_localize_script( $this->plugin_name, 'prono_ajax_object',
            array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'nonce' => wp_create_nonce( 'pronostique-quick-edit' ), 'action' => 'quick_edit_pronostique' ) );

	}

    public function add_admin_menu() {
        if (function_exists('add_options_page')) {
            add_options_page('Configuration du plugin pronostique', 'Pronostique', 'manage_options', 'pronostique_settings', array($this,'printPronosticsAdminPage'));
        }

        if (function_exists('add_menu_page')) {
            add_menu_page(
                __( 'Pronostique sans resultat', 'textdomain' ),
                __( 'Prono w/o result', 'textdomain' ),
                'manage_options',
                'pronostiques_quick_edit',
                array($this,'printPronosticsQuickEditAdminPage'),
                '',
                4
            );
            add_menu_page(
                __( 'Nouveaux Tipsters', 'textdomain' ),
                __( 'New Tipster', 'textdomain' ),
                'manage_options',
                'add_tipster_confirmation',
                array($this,'printTipsterConfirmationAdminPage'),
                '',
                5
            );
            add_menu_page(
                __( 'Resultat concours', 'textdomain' ),
                __( 'Concours', 'textdomain' ),
                'manage_options',
                'show_month_contest_results',
                array($this,'printMonthContestResult'),
                '',
                5
            );
        }
    }

    public function display_pronostique_ui() {
        $object = pods( 'pronostique' );
        $ui = array(
            'pod' => $object,
            'orderby' => 'ID desc',
            'search_across_picks' => true,
            'filters' => array('bookmaker','tips_result','is_expert','is_vip'),
            'fields' => array('manage' => array('name','pari','bookmaker','date','tips_result','match_result','is_expert','is_vip','author','id'))
        );

        pods_ui( $ui );
    }

    public function printMonthContestResult() {
        $month = date('n');
        $year = date('Y');

        if (isset($_POST['change_month'])) {
            check_admin_referer('change-month');
            $month = (int) $_POST['month'];
            $year = (int) $_POST['year'];
        }
        $cond_month = ' AND MONTH(date) = '.$month.' AND YEAR(date) = '.$year.' ';


        $VPNA_sql = " SUM(IF(tips_result = 1,1,0)) AS 'V', SUM(IF(tips_result = 3,1,0)) AS 'N', SUM(IF(tips_result = 2,1,0)) AS 'P', SUM(IF(tips_result = 0,1,0)) AS 'A'";
        $pronos = pods('pronostique')->find(
            array(
                'select' => $VPNA_sql.', ROUND(SUM( IF(tips_result = 1, (cote-1)*mise, IF(tips_result = 2, - mise, IF(tips_result = 3, 0, 0))) ), 2) AS Gain, COUNT(t.id) as nb_tips, t.*',
                'where' => 'tips_result > 0 AND is_expert = 0 AND is_vip = 0 '.$cond_month,
                'limit' => -1,
                'orderby' => 'Gain Desc',
                'groupby' => 'author.id',
            )
            );

        $selected_month = date('F Y',strtotime($year."-".$month."-01"));
        $formnonce = function_exists('wp_nonce_field') ? wp_nonce_field('change-month') : '';
        $formaction = esc_attr($_SERVER['REQUEST_URI']);
        $entetes = '<tr><th>&nbsp;</th> <th>Pseudo</th> <th>V - P -N</th> <th>Nb Tips</th><th>Profit</th></tr>';
        $tpl_params = array('titre' => 'resultat du concours',
                            'entetes' => $entetes,
                            'formnonce' => $formnonce,
                            'formaction' => $formaction,
                                'row' => $pronos,
                                'month' => $selected_month,
                            );

        echo $this->templater->display('month-contest-result', $tpl_params);
    }

    public function printPronosticsQuickEditAdminPage() {
        $tipsWithoutResult = pods('pronostique')->find(
                            array('limit' => 0,
                                'where' => '(tips_result = 0 OR tips_result IS NULL) AND date < NOW()',
                                'orderby' => 'date DESC'
                            ));

        echo $this->templater->display('pronostique-quick-edit', array('tips' => $tipsWithoutResult));
    }

    public function ajax_quick_edit_pronostique() {
        check_ajax_referer( 'pronostique-quick-edit', 'security' );
        $id = intval( $_POST['ID'] );
        $tips_result = intval( $_POST['tips_result'] );
        $match_result = sanitize_text_field( $_POST['match_result'] );
        $errors = array();
        if(!$id) {
            $errors[] = "Erreur Id, contactez Xavier";
        }
        if(!$tips_result) {
            $errors[] = "Résultat du pari non choisi";
        }
        if(count($errors) == 0) {
            $prono = pods('pronostique',$id);
            $bool = remove_filter('save_post', array($GLOBALS['scoper_admin_filters'], 'custom_taxonomies_helper'), 5);
            if(!$prono->save(array('tips_result' => $tips_result,'match_result' => $match_result))) {
                $errors[] = "Pod erreur: impossible de sauvegarder";
            }
            if ($prono->field('post')) {
                $post_id = $prono->field('post.ID');
                wp_set_object_terms($post_id, intval($prono->field('sport.term_id')), 'sport', false);
                wp_set_object_terms($post_id, intval($prono->field('competition.term_id')), 'competition', false);
            }
        }
        $success = (count($errors) == 0 ? 1 : 0);
        echo json_encode( array("success" => $success,
                                "errors" => $errors));

    	wp_die();
    }

    public function printTipsterConfirmationAdminPage() {
        // Pods has a bug that doesn't permit to search for meta_value analyse && pays
        // https://github.com/pods-framework/pods/issues/3196
        // so we filter user in the hard way
        $search_params = array(
            'select' => 't.*, analyse.meta_value as analyse, pays.meta_value as pays',
            'limit' => "-1",
            'where' => "analyse.meta_value IS NOT NULL");
        $users = pods( 'user' )->find($search_params);
        $user_to_confirm = array();
        while( $users->fetch() ) {
            if ($users->field('analyse') != "") {
                if (!UsersGroup::isUserConfirmed($users->field('id'))) {
                    $user_to_confirm[] = array(
                        'id' => $users->field('id'),
                        'name' => $users->field('name'),
                        'analyse' => $users->field('analyse'),
                        'pays' => $users->field('pays')
                    );
                }
            }
        }

        echo $this->templater->display('tipsters-confirmation', array('users' => $user_to_confirm));
    }

    public function printPronosticsAdminPage()
    {
        $formresult = null;
        $formaction = esc_attr($_SERVER['REQUEST_URI']);

        if (isset($_POST['set_default_cat'])) {
            check_admin_referer('pronostics-set-default-cat');
            $expert_cat = (int) $_POST['prono_expert_default_cat'];
            update_option( "prono_expert_default_category", $expert_cat );
            $vip_cat = (int) $_POST['prono_vip_default_cat'];
            update_option( "prono_vip_default_category", $vip_cat );
            $std_cat = (int) $_POST['prono_std_default_cat'];
            update_option( "prono_std_default_category", $std_cat );
						$formresult = true;
        }
				if (isset($_POST['clear_cache'])) {
            check_admin_referer('pronostics-cache-handling');
						$formresult = $this->xdkdcache->clearCache();
        }
				if (isset($_POST['refresh_filter_cache'])) {
            check_admin_referer('pronostics-cache-handling');
						$formresult = PronoLib::getInstance()->refreshTaxonomiesFilterData();
        }
				if (isset($_POST['refresh_tops_cache'])) {
            check_admin_referer('pronostics-cache-handling');
						$formresult = PronoLib::getInstance()->refreshAllData();
        }

        $formnonce_default_cat = function_exists('wp_nonce_field') ? wp_nonce_field('pronostics-set-default-cat') : '';
        $formnonce_cache_handling = function_exists('wp_nonce_field') ? wp_nonce_field('pronostics-cache-handling') : '';

        $categories = get_categories(array('hide_empty' => 0));
        $prono_expert_cat = get_option("prono_expert_default_category", 0);
        $prono_vip_cat = get_option("prono_vip_default_category", 0);
        $prono_std_cat = get_option("prono_std_default_category", 0);

        ob_start();
        include_once 'partials/options-page.php';
        $result = ob_get_contents();
        ob_end_clean();

        echo $result;
    }
}
