<?php
/**
 * Plugin Name:       The Events Calendar Pro Extension: Alternative Photo View
 * Plugin URI:        https://theeventscalendar.com/extensions/alternative-photo-view/
 * GitHub Plugin URI: https://github.com/mt-support/tribe-ext-alternative-photo-view/
 * Description:       The extension will override the existing photo view of Events Calendar Pro with an alternative one.
 * Version:           1.1.2
 * Extension Class:   Tribe\Extensions\AlternativePhotoView\Main
 * Author:            The Events Calendar
 * Author URI:        https://evnt.is/1971
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

use Tribe__Autoloader;
use Tribe__Extension;

/**
 * Define Constants
 */

if ( ! defined( __NAMESPACE__ . '\NS' ) ) {
	define( __NAMESPACE__ . '\NS', __NAMESPACE__ . '\\' );
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
		 * @var Tribe__Autoloader
		 */
		private $class_loader;

		/**
		 * @var Settings
		 */
		private $settings;

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
		 * Set up the Extension's properties.
		 *
		 * This always executes even if the required plugins are not present.
		 */
		public function construct() {
			$this->add_required_plugin( 'Tribe__Events__Pro__Main', '5.0.1' );
		}

		/**
		 * Get this plugin's options prefix.
		 *
		 * Settings_Helper will append a trailing underscore before each option.
		 *
		 * @see \Tribe\Extensions\AlternativePhotoView\Settings::set_options_prefix()
		 *
		 * @return string
		 */
		private function get_options_prefix() {
			return (string) str_replace( '-', '_', 'tribe-ext-alternative-photo-view' );
		}

		/**
		 * Get Settings instance.
		 *
		 * @return Settings
		 */
		private function get_settings() {
			if ( empty( $this->settings ) ) {
				$this->settings = new Settings( $this->get_options_prefix() );
			}

			return $this->settings;
		}

		/**
		 * Extension initialization and hooks.
		 */
		public function init() {
			// Load plugin textdomain
			load_plugin_textdomain( 'tribe-ext-alternative-photo-view', false, basename( dirname( __FILE__ ) ) . '/languages/' );

			if ( ! $this->php_version_check() ) {
				return;
			}

			$this->class_loader();

			$this->get_settings();

			add_action( 'wp_enqueue_scripts', [ $this, 'safely_add_stylesheet' ] );
			add_filter( 'tribe_template_path_list', [ $this, 'alternative_photo_view_template_locations' ], 10, 2 );

			add_action( 'wp_footer', [ $this, 'footer_styles' ] );
		}

		/**
		 * Add dynamically calculated styles to the footer.
		 */
		public function footer_styles() {

			$container_height = $this->get_option( 'container_height', '400px' );

			$column_width_tablet  = round( 100 / (int) $this->get_option( 'number_of_columns_tablet', 3 ), 1 );
			$column_width_desktop = round( 100 / (int) $this->get_option( 'number_of_columns_desktop', 3 ), 1 );

			$event_title_font_size   = $this->get_option( 'event_title_font_size', '24px' );
			$event_title_alignment   = $this->get_option( 'event_title_alignment', 'left' );
			$container_border_radius = $this->get_option( 'container_border_radius', '16px' );

			?>
			<style id="tribe-ext-alternative-photo-view-styles">
				.tribe-events-pro-photo__event {
					height: <?php echo $container_height ?>;
				}

				.tribe-common--breakpoint-medium.tribe-events-pro .tribe-events-pro-photo__event {
					width: <?php echo $column_width_tablet ?>%;
				}

				.tribe-common--breakpoint-full.tribe-events-pro .tribe-events-pro-photo__event {
					width: <?php echo $column_width_desktop ?>%;
				}

				.tribe-events-pro-photo__event-title a {
					font-size: <?php echo $event_title_font_size ?>;
				}

				.tribe-events-pro-photo__event .tribe-events-pro-photo__event-title {
					text-align: <?php echo $event_title_alignment ?>;
				}

				.tribe-events-pro-photo__event-details-wrapper {
					border-radius: <?php echo $container_border_radius ?>;
				}

				.tribe-events-pro .tribe-events-pro-photo__event-date-tag {
					border-top-left-radius: <?php echo $container_border_radius ?>;
				}

				<?php
				if (
					(int) $this->get_option( 'number_of_columns_tablet' ) > 3
					||  (int) $this->get_option( 'number_of_columns_desktop' ) > 3
					) :
				?>
				.tribe-common--breakpoint-medium.tribe-common .tribe-common-g-row--gutters {
					margin-left: -12px;
					margin-right: -12px;
				}

				.tribe-common--breakpoint-medium.tribe-common .tribe-common-g-row--gutters > .tribe-common-g-col {
					padding-left: 12px;
					padding-right: 12px;
				}
				<?php endif; ?>
			</style>
			<?php
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
					$message .= sprintf(
						__(
							'%s requires PHP version %s or newer to work. Please contact your website host and inquire about updating PHP.',
							'tribe-ext-alternative-photo-view'
						),
						$this->get_name(),
						$php_required_version
					);
					$message .= sprintf( ' <a href="%1$s">%1$s</a>', 'https://wordpress.org/about/requirements/' );
					$message .= '</p>';
					tribe_notice( 'tribe-ext-alternative-photo-view' . '-php-version', $message, [ 'type' => 'error' ] );
				}

				return false;
			}

			return true;
		}

		/**
		 * Set up the template override folder for the extension.
		 *
		 * @param                  $folders
		 * @param \Tribe__Template $template
		 *
		 * @return mixed
		 */
		function alternative_photo_view_template_locations( $folders, \Tribe__Template $template ) {
			// Which file namespace your plugin will use.
			$plugin_name = 'tribe-ext-alternative-photo-view';

			/**
			 * Which order we should load your plugin files at. Plugin in which the file was loaded from = 20.
			 * Events Pro = 25. Tickets = 17
			 */
			$priority = 5;

			// Which folder in your plugin the customizations will be loaded from.
			$custom_folder[] = 'tribe-customizations';

			// Builds the correct file path to look for.
			$plugin_path = array_merge(
				(array) trailingslashit( plugin_dir_path( __FILE__ ) ),
				(array) $custom_folder,
				array_diff( $template->get_template_folder(), [ 'src', 'views' ] )
			);

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
		 * Add stylesheet to the page.
		 */
		function safely_add_stylesheet() {
			wp_enqueue_style( 'prefix-style', plugins_url( 'src/resources/style.css', __FILE__ ) );
		}

		/**
		 * Use Tribe Autoloader for all class files within this namespace in the 'src' directory.
		 *
		 * @return Tribe__Autoloader
		 */
		public function class_loader() {
			if ( empty( $this->class_loader ) ) {
				$this->class_loader = new Tribe__Autoloader;
				$this->class_loader->set_dir_separator( '\\' );
				$this->class_loader->register_prefix(
					__NAMESPACE__ . '\\',
					__DIR__ . DIRECTORY_SEPARATOR . 'src'
				);
			}

			$this->class_loader->register_autoloader();

			return $this->class_loader;
		}

		/**
		 * Get all of this extension's options.
		 *
		 * @return array
		 */
		public function get_all_options() {
			$settings = $this->get_settings();

			return $settings->get_all_options();
		}

		/**
		 * Get a specific extension option.
		 *
		 * @param        $option
		 * @param string $default
		 *
		 * @return array
		 */
		public function get_option( $option, $default = '' ) {
			$settings = $this->get_settings();

			return $settings->get_option( $option, $default );
		}

	}
}
