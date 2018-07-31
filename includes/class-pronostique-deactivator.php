<?php

/**
 * Fired during plugin deactivation
 *
 * @link       http://www.xavier-guichet.fr
 * @since      1.0.0
 *
 * @package    Pronostique
 * @subpackage Pronostique/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Pronostique
 * @subpackage Pronostique/includes
 * @author     Xavier Guichet <contact@xavier-guichet.fr>
 */
class Pronostique_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		$timestamp = wp_next_scheduled( 'cps_cron_hook' );
 		wp_unschedule_event( $timestamp, 'cps_cron_hook' );
	}

}
