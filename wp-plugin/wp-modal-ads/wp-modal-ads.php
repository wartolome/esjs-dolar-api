<?php
/**
 * Plugin Name:       WP Modal Ads
 * Plugin URI:        https://github.com/wartolome/esjs-dolar-api/tree/main/wp-plugin/wp-modal-ads
 * Description:       Muestra anuncios en ventanas modales configurables. Permite configurar la URL del anuncio, tamaño, tiempo antes del botón "Saltar", disparadores y máximo de anuncios por visitante por hora.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            wartolome
 * License:           MIT
 * Text Domain:       wp-modal-ads
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WP_MODAL_ADS_VERSION', '1.0.0' );
define( 'WP_MODAL_ADS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WP_MODAL_ADS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WP_MODAL_ADS_OPTION_KEY', 'wp_modal_ads_settings' );

require_once WP_MODAL_ADS_PLUGIN_DIR . 'includes/class-admin.php';
require_once WP_MODAL_ADS_PLUGIN_DIR . 'includes/class-modal-ads.php';

/**
 * Initialize the plugin.
 */
function wp_modal_ads_init() {
	$admin = new WP_Modal_Ads_Admin();
	$admin->init();

	$modal = new WP_Modal_Ads_Frontend();
	$modal->init();
}

add_action( 'plugins_loaded', 'wp_modal_ads_init' );

/**
 * Register default settings on plugin activation.
 */
function wp_modal_ads_activate() {
	if ( false === get_option( WP_MODAL_ADS_OPTION_KEY ) ) {
		$defaults = array(
			'enabled'         => '1',
			'ad_url'          => '',
			'modal_size'      => '100',
			'skip_delay'      => '5',
			'trigger'         => 'click',
			'scroll_percent'  => '30',
			'time_delay'      => '3',
			'max_per_hour'    => '3',
		);
		update_option( WP_MODAL_ADS_OPTION_KEY, $defaults );
	}
}
register_activation_hook( __FILE__, 'wp_modal_ads_activate' );
