<?php

/**
 * Fired during plugin activation
 *
 * @link       http://www.xavier-guichet.fr
 * @since      1.0.0
 *
 * @package    Pronostique
 * @subpackage Pronostique/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Pronostique
 * @subpackage Pronostique/includes
 * @author     Xavier Guichet <contact@xavier-guichet.fr>
 */
class Pronostique_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		if ( ! wp_next_scheduled( 'cps_cron_hook' ) ) {
				wp_schedule_event(time(), 'twicedaily', 'cps_cron_hook', array());
		}
	}

}
