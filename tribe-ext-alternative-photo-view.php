<?php
/**
 * Plugin Name:       The Events Calendar Pro Extension: Alternative Photo View
 * Plugin URI:        https://theeventscalendar.com/extensions/tribe-ext-alternative-photo-view/
 * GitHub Plugin URI: https://github.com/mt-support/tribe-ext-alternative-photo-view/
 * Description:       The extension will replace the existing photo view of Events Calendar Pro with an alternative one.
 * Version:           1.0.0
 * Extension Class:   Tribe\Extensions\AlternativePhotoView\Main
 * Author:            Modern Tribe, Inc.
 * Author URI:        http://m.tri.be/1971
 * License:           GPL version 3 or any later version
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       tribe-ext-alternative-photo-view
 *
 *     This plugin is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU General Public License as published by
 *     the Free Software Foundation, either version 3 of the License, or
 *     any later version.
 *
 *     This plugin is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *     GNU General Public License for more details.
 */
namespace Tribe\Extensions\AlternativePhotoView;

use Tribe__Dependency;
use Tribe__Extension;

/**
 * Define Constants
 */

if ( ! defined( __NAMESPACE__ . '\NS' ) ) {
	define( __NAMESPACE__ . '\NS', __NAMESPACE__ . '\\' );
}

if ( ! defined( NS . 'PLUGIN_TEXT_DOMAIN' ) ) {
	// `Tribe\Extensions\AlternativePhotoView\PLUGIN_TEXT_DOMAIN` is defined
	define( NS . 'PLUGIN_TEXT_DOMAIN', 'tribe-ext-alternative-photo-view' );
}

// Do not load unless Tribe Common is fully loaded and our class does not yet exist.
if (
	class_exists( 'Tribe__Extension' )
	&& ! class_exists( NS . 'Main' )
) {
	/**
	 * Extension main class, class begins loading on init() function.
	 */
	class Main extends Tribe__Extension {

		/**
		 * Is Events Calendar PRO active. If yes, we will add some extra functionality.
		 *
		 * @return bool
		 */
		public $ecp_active = false;

		/**
		 * Is Event Tickets active. If yes, we will add some extra functionality.
		 *
		 * @return bool
		 */
		public $et_active = false;

		/**
		 * Setup the Extension's properties.
		 *
		 * This always executes even if the required plugins are not present.
		 */
		public function construct() {
			//$this->add_required_plugin( 'Tribe__Events__Main' );
			$this->add_required_plugin( 'Tribe__Events__Pro__Main', '5.0.1' );

			// Conditionally-require Events Calendar PRO or Event Tickets. If it is active, run an extra bit of code.
			//add_action( 'tribe_plugins_loaded', [ $this, 'detect_tribe_plugins' ], 0 );
		}

		/**
		 * Check required plugins after all Tribe plugins have loaded.
		 *
		 * Useful for conditionally-requiring a Tribe plugin, whether to add extra functionality
		 * or require a certain version but only if it is active.
		 */
		public function detect_tribe_plugins() {
			/** @var Tribe__Dependency $dep */
			$dep = tribe( Tribe__Dependency::class );

			if ( $dep->is_plugin_active( 'Tribe__Events__Pro__Main' ) ) {
				$this->add_required_plugin( 'Tribe__Events__Pro__Main' );
				$this->ecp_active = true;
			}
			if ( $dep->is_plugin_active( 'Tribe__Tickets__Main' ) ) {
				$this->add_required_plugin( 'Tribe__Tickets__Main' );
				$this->et_active = true;
			}
			if ( $dep->is_plugin_active( 'Tribe__Events__Filterbar__View' ) ) {
				$this->add_required_plugin( 'Tribe__Events__Filterbar__View' );
				$this->fb_active = true;
			}
			if ( $dep->is_plugin_active( 'Tribe__Events__Community__Main' ) ) {
				$this->add_required_plugin( 'Tribe__Events__Community__Main' );
				$this->ce_active = true;
			}
		}

		/**
		 * Extension initialization and hooks.
		 */
		public function init() {
			// Load plugin textdomain
			// Don't forget to generate the 'languages/tribe-ext-admin-bar-plus.pot' file
			load_plugin_textdomain( PLUGIN_TEXT_DOMAIN, false, basename( dirname( __FILE__ ) ) . '/languages/' );

			if ( ! $this->php_version_check() ) {
				return;
			}

			add_action( 'wp_enqueue_scripts', [ $this, 'safely_add_stylesheet' ] );
			add_filter( 'tribe_template_path_list', [ $this, 'alternative_photo_view_1_template_locations' ], 10, 2 );
		}

		/**
		 * Check if we have a sufficient version of PHP. Admin notice if we don't and user should see it.
		 *
		 * @return bool
		 */
		private function php_version_check() {
			$php_required_version = '7.0';

			if ( version_compare( PHP_VERSION, $php_required_version, '<' ) ) {
				if ( is_admin() && current_user_can( 'activate_plugins' ) ) {
					$message = '<p>';
					$message .= sprintf( __( '%s requires PHP version %s or newer to work. Please contact your website host and inquire about updating PHP.',
					                         PLUGIN_TEXT_DOMAIN ),
					                     $this->get_name(),
					                     $php_required_version );
					$message .= sprintf( ' <a href="%1$s">%1$s</a>', 'https://wordpress.org/about/requirements/' );
					$message .= '</p>';
					tribe_notice( PLUGIN_TEXT_DOMAIN . '-php-version', $message, [ 'type' => 'error' ] );
				}

				return false;
			}

			return true;
		}

		function alternative_photo_view_1_template_locations( $folders, \Tribe__Template $template ) {
			// Which file namespace your plugin will use.
			$plugin_name = 'my-plugin';

			// Which order we should load your plugin files at.
			$priority = 5;
			// Plugin in which the file was loaded from = 20
			// Events Pro = 25
			// Tickets = 17

			// Which folder in your plugin the customizations will be loaded from.
			$custom_folder[] = 'tribe-customizations';

			// Builds the correct file path to look for.
			$plugin_path = array_merge( (array) trailingslashit( plugin_dir_path( __FILE__ ) ),
			                            (array) $custom_folder,
			                            array_diff( $template->get_template_folder(), [ 'src', 'views' ] ) );

			/*
			 * Custom loading location for overwriting file loading.
			 */
			$folders[ $plugin_name ] = [
				'id'        => $plugin_name,
				'namespace' => $plugin_name, // Only set this if you want to overwrite theme namespacing
				'priority'  => $priority,
				'path'      => $plugin_path,
			];

			return $folders;
		}

		/**
		 * Add stylesheet to the page
		 */
		function safely_add_stylesheet() {
			wp_enqueue_style( 'prefix-style', plugins_url('style.css', __FILE__) );
		}
	}
}


