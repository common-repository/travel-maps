<?php
/*
Plugin Name: Travel Map
Description: A Travel Map Wordpress Plugin based on Baidu maps
Version: 1.0
Author: Liu Yang, Samuel Jesse, uditvirwani
Author URI: http://blog.grainbuds.net
License:

  Copyright 2015 Samuel Jesse (samueljesse@digitalcreative.asia)
  Copyright 2017 Yang Liu (yaang.liu@gmail.com)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


require_once 'include/travel-maps-admin.php';
require_once 'include/travel-maps-api.php';
require_once 'include/travel-maps-settings.php';


class Baidu_Travel_Maps {

	protected $plugin_path;
	protected $plugin_url;

	/*--------------------------------------------*
	 * Constructor
	 *--------------------------------------------*/

	/**
	 * Initializes the plugin by setting localization, filters, and administration functions.
	 */
	public function __construct() {

		// Set the plugin path
		$this->plugin_path = dirname( __FILE__ );

		// Load all the settings for Baidu Maps
		$this->settings = get_option( 'travel_maps_settings' );

		// Set the plugin url
		$this->plugin_url = WP_PLUGIN_URL . '/' . plugin_basename( __DIR__ ) . '/';

		load_plugin_textdomain( 'travel-maps', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );


		add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ) );


		// Register the shortcode
		add_shortcode( 'btmap', array( $this, 'shortcode' ) );


		// Initialize the admin interface
		$admin = new Baidu_Travel_Maps_Admin($this->plugin_url, $this);

		add_action( 'admin_notices', array( $this, 'display_admin_notices' ) );

	}


	/*--------------------------------------------*
	 * Core Functions
	 *---------------------------------------------*/

	/**
	 * Upon activation, create and show the options_page with default options.
	 */
	public function activate() {

	}

	/**
	 * Upon deactivation, removes the options page.
	 */
	public function deactivate() {

	}

	/**
	 * Registers and enqueues admin-specific minified JavaScript.
	 */
	public function register_scripts() {
		// Enqueue Baidu Maps Script
		wp_register_script( 'travel-maps-script-api', 'http://api.map.baidu.com/api?v=2.0&ak=' . $this->settings['api_key'], false, true );
		wp_enqueue_script( 'travel-maps-script-api' );


		// Enqueue Plugin's Frontend Styles
		wp_register_style( 'travel-maps-style-frontend', $this->plugin_url . 'assets/css/frontend.css' );
		wp_enqueue_style( 'travel-maps-style-frontend' );

		// Enqueue Plugin's Frontend Script
		wp_register_script( 'travel-maps-script-map', $this->plugin_url . 'assets/js/map.js', array( 'jquery' ), false, true );
		wp_enqueue_script( 'travel-maps-script-map' );
	}

	/**
	 * Registers and enqueues admin-specific JavaScript.
	 */
	public function register_admin_scripts() {

		// Enqueue Baidu Maps Script
		if ( get_post_type( ) == 'btmap' ) {
			wp_register_script( 'travel-maps-script-api', 'http://api.map.baidu.com/api?v=2.0&ak=' . $this->settings['api_key'], false, true );
			wp_enqueue_script( 'travel-maps-script-api' );

			wp_register_style( 'travel-maps-style-admin', $this->plugin_url . 'assets/css/admin.css' );
			wp_enqueue_style( 'travel-maps-style-admin' );

			wp_register_script( 'travel-maps-script-admin', $this->plugin_url . 'assets/js/admin.js', array( 'jquery', 'wp-color-picker' ), false, true );
			wp_enqueue_script( 'travel-maps-script-admin' );

			wp_localize_script( 'travel-maps-script-admin', 'pluginUrl', $this->plugin_url );
			wp_localize_script( 'travel-maps-script-admin', 'ajaxurl', admin_url( 'admin-ajax.php' ) );

			wp_enqueue_script( 'thickbox' );
			wp_enqueue_style( 'thickbox' );

			wp_enqueue_style( 'wp-color-picker' );
		}
	}

	/**
	 * Creates the shortcode to use with themes
	 */
	public function shortcode( $atts ) {

		extract( shortcode_atts( array(
			'id'     => 0,
			'zoom'   => 13,
			'lat'    => '39.915',
			'lng'    => '116.404',
			'width'  => 500,
			'height' => 300,
		), $atts ) );

		return $id > 0 ? $this->makeMapWithID( $id ) : $this->makeMap( $zoom, $lat, $lng, $width, $height );
	}

	/**
	 * Returns a Map Element (div) created with the parameters provided below.
	 *
	 * @param $zoom
	 * @param $lat
	 * @param $lng
	 * @param $width
	 * @param $height
	 *
	 * @return $map_element
	 */
	public function makeMap( $zoom, $lat, $lng, $width, $height ) {
		$id             = uniqid();
		$travel_maps_api = new Baidu_Travel_Maps_API();
		$map_element    = $travel_maps_api->createMapElement( $id, $width, $height);
		$map            = $travel_maps_api->createMap( $id, $zoom, $lat, $lng );

		return $map_element;
	}

	/**
	 * Returns a Map Element (div) created with the id of the map, created with the baidu maps admin.
	 *
	 * @param $id
	 *
	 * @return $map_element
	 */
	public function makeMapWithID( $id ) {
		$travel_maps_api = new Baidu_Travel_Maps_API();
		$map_element    = $travel_maps_api->createMapWithID( $id, $this->settings['showtime'] );

		return $map_element;
	}

	public function display_admin_notices() {
		// Check if API Key is entered
		if ( $this->settings['api_key'] == '' && current_user_can( 'manage_options' ) ) {
			global $post_type;
			if ( $post_type == 'btmap' ) {
				$notice[] = "<div class='error'>";
				$notice[] = "<p>" . __( "You have not entered your Baidu Developer API Key. Click ", 'travel-maps' ) . " " . "<a href='" . admin_url( "edit.php?post_type=btmap&page=travel-maps-admin" ) . "'>" . __( "here", 'travel-maps' ) . "</a> to enter</p> </div>";

				echo implode( "\n", $notice );
			}
		}


		if ( isset( $_GET['settings-updated'] ) && $_GET['page'] === 'travel-maps-admin' ) {
			$notice[] = "<div class='updated notice notice-success'>";
			$notice[] = "<p>" . __( "Settings have been updated", 'travel-maps' ) . "</p> </div>";

			echo implode( "\n", $notice );
		}
	}

}

new Baidu_Travel_Maps;
