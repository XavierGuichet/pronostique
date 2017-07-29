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

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Pronostique_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Pronostique_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/pronostique-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Pronostique_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Pronostique_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/pronostique-admin.js', array( 'jquery' ), $this->version, false );

	}

    public function add_option_page() {
        if (function_exists('add_options_page')) {
            add_options_page('Configuration du plugin pronostique', 'Pronostique', 'manage_options', 'pronostique_settings', array($this,'printPronosticsAdminPage'));
        }
    }

    public function printPronosticsAdminPage()
    {
        global $wpdb;
        $table_tips = $wpdb->prefix.'bmk_tips';
        $table_tips_experts = $wpdb->prefix.'bmk_tips_experts';
        $results = null;

        // TODO : keep in case of reset during migration
        // update_option( 'pronostique_migrate_last_id', '0' );
        // update_option( 'pronostique_migrate_expert_last_id', '0' );

        $std_tips_last_imported_id = get_option( 'pronostique_migrate_last_id', 0);
        $expert_tips_last_imported_id = get_option( 'pronostique_migrate_expert_last_id', 0);

        if (isset($_POST['RAZ_tipseurs'])) {
            //wp_nonce check
            check_admin_referer('pronostics-raz-tipseurs');
            $date_actu = strftime('%Y-%m-');
            // $results = $wpdb->query("UPDATE $table_name SET tips_actif = 0 WHERE tips_date LIKE '$date_actu%'");
        }
        if (isset($_POST['migrate_std_tips'])) {
            check_admin_referer('pronostics-migrate-tips');
            $all_tips = $wpdb->get_results("SELECT * FROM ".$table_tips." t WHERE tips_ID > ".$std_tips_last_imported_id." ORDER BY tips_ID ASC LIMIT 0,250");
            $std_tips_last_imported_id = $this->migrate_tips($all_tips, 0);
            update_option( 'pronostique_migrate_last_id', $std_tips_last_imported_id );
        }
        if (isset($_POST['migrate_expert_tips'])) {
            check_admin_referer('pronostics-migrate-tips');
            $all_tips = $wpdb->get_results("SELECT * FROM ".$table_tips_experts." t WHERE tips_ID > ".$expert_tips_last_imported_id." ORDER BY tips_ID ASC LIMIT 0,250");
            $expert_tips_last_imported_id = $this->migrate_tips($all_tips, 1);
            update_option( 'pronostique_migrate_expert_last_id', $expert_tips_last_imported_id );
        }

        $count_std_tips_to_migrate = $wpdb->get_var("SELECT COUNT(*) FROM ".$table_tips." t WHERE tips_ID > ".$std_tips_last_imported_id);
        $count_expert_tips_to_migrate = $wpdb->get_var("SELECT COUNT(*) FROM ".$table_tips_experts." t WHERE tips_ID > ".$expert_tips_last_imported_id);

        $formaction = esc_attr($_SERVER['REQUEST_URI']);
        $formnonce_raz = function_exists('wp_nonce_field') ? wp_nonce_field('pronostics-raz-tipseurs') : '';
        $formsubmit_raz = __('Remise à zéro des stats tipseurs', 'pronostics');

        $formnonce_migrate = function_exists('wp_nonce_field') ? wp_nonce_field('pronostics-migrate-tips') : '';
        $formsubmit_migrate = __('Migrer les données', 'pronostics');

        ob_start();
        include_once 'partials/options-page.php';
        $result = ob_get_contents();
        ob_end_clean();

        echo $result;
    }

    private function migrate_tips($all_tips, $is_expert) {
        $pods_bookmaker = pods('bookmaker')->find();
        $bookmaker_ids = array();
        while ( $pods_bookmaker->fetch() ) {
            $bookmaker_ids[$pods_bookmaker->field('name')] = $pods_bookmaker->field('id');
        }

        $pods_sport = pods('sport')->find(null, 100);
        $sport_ids = array();
        while ( $pods_sport->fetch() ) {
            $sport_ids[$pods_sport->field('name')] = $pods_sport->field('id');
        }

        $last_id = 0;
        $pods_data = array();
        foreach ($all_tips as $key => $tips) {
            //Transforme les strings bookmaker en relation
            $bookmaker_infos = explode(',',$tips->tips_bookmaker);
            if (isset($bookmaker_ids[$bookmaker_infos[0]])) {
                $bookmaker_id = $bookmaker_ids[$bookmaker_infos[0]];
            } else {
                $bookmaker_id = null;
            }

            //Transforme les strings sport en relation
            //Changement de titre du sport foot américain
            if( $tips->tips_sport == "Foot Am") { $tips->tips_sport = "Foot Américain";}
            $sport_id = $sport_ids[$tips->tips_sport];
            if( !is_numeric($bookmaker_id)) {
                $sport_id = null;
            }

            //transforme les champs date et heure (du match/evenement) en un unique champ
            $date_match = $tips->tips_date." ".preg_replace('`h`',':',$tips->tips_heure).':00';

            //prepare le champs code_poolbox
            $code_poolbox = null;
            if (isset($tips->tips_code_poolbox)) {
                $code_poolbox = $tips->tips_code_poolbox;
            }

            $pods_data[] = array(
                'name' => $tips->tips_match,
                'pari' => $tips->tips_pari,
                'cote' => $tips->tips_cote,
                'mise' => $tips->tips_mise,
                'analyse' => $tips->tips_analyse,
                'created' => $tips->tips_created_at,
                'resultat' => $tips->tips_resultat,
                'actif' => $tips->tips_actif,
                'sport' => $sport_id,
                'bookmaker' => $bookmaker_id,
                'date' => $date_match,
                'code_poolbox' => $code_poolbox,
                'author' => $tips->user_id,
                'post' => $tips->tips_post_id,
                'is_expert' => $is_expert,
                'is_vip' => 0,
                // 'miniature' => $tips->,
                // 'modified' => $tips->,
                // 'permalink' => , //Is auto set
            );
            $last_id = $tips->tips_ID;
        }
        $api = pods_api( 'prono' );
        $ids = $api->import( $pods_data, true );
        return $last_id;
    }

}
