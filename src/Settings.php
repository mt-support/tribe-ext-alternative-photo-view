<?php

namespace Tribe\Extensions\Daystrip;

use Tribe__Settings_Manager;

if ( ! class_exists( Settings::class ) ) {
	/**
	 * Do the Settings.
	 */
	class Settings {

		/**
		 * The Settings Helper class.
		 *
		 * @var Settings_Helper
		 */
		protected $settings_helper;

		/**
		 * The prefix for our settings keys.
		 *
		 * @see get_options_prefix() Use this method to get this property's value.
		 *
		 * @var string
		 */
		private $options_prefix = '';

		/**
		 * Settings constructor.
		 *
		 * @param string $options_prefix Recommended: the plugin text domain, with hyphens converted to underscores.
		 */
		public function __construct( $options_prefix ) {
			$this->settings_helper = new Settings_Helper();

			$this->set_options_prefix( $options_prefix );

			// Add settings specific to OSM
			add_action( 'admin_init', [ $this, 'add_settings' ] );
		}

		/**
		 * Allow access to set the Settings Helper property.
		 *
		 * @see get_settings_helper()
		 *
		 * @param Settings_Helper $helper
		 *
		 * @return Settings_Helper
		 */
		public function set_settings_helper( Settings_Helper $helper ) {
			$this->settings_helper = $helper;

			return $this->get_settings_helper();
		}

		/**
		 * Allow access to get the Settings Helper property.
		 *
		 * @see set_settings_helper()
		 */
		public function get_settings_helper() {
			return $this->settings_helper;
		}

		/**
		 * Set the options prefix to be used for this extension's settings.
		 *
		 * Recommended: the plugin text domain, with hyphens converted to underscores.
		 * Is forced to end with a single underscore. All double-underscores are converted to single.
		 *
		 * @see get_options_prefix()
		 *
		 * @param string $options_prefix
		 */
		private function set_options_prefix( $options_prefix ) {
			$options_prefix = $options_prefix . '_';

			$this->options_prefix = str_replace( '__', '_', $options_prefix );
		}

		/**
		 * Get this extension's options prefix.
		 *
		 * @see set_options_prefix()
		 *
		 * @return string
		 */
		public function get_options_prefix() {
			return $this->options_prefix;
		}

		/**
		 * Given an option key, get this extension's option value.
		 *
		 * This automatically prepends this extension's option prefix so you can just do `$this->get_option( 'a_setting' )`.
		 *
		 * @param string $key
		 *
		 * @param string $default
		 *
		 * @return mixed
		 * @see tribe_get_option()
		 *
		 */
		public function get_option( $key = '', $default = '' ) {
			$key = $this->sanitize_option_key( $key );

			return tribe_get_option( $key, $default );
		}

		/**
		 * Get an option key after ensuring it is appropriately prefixed.
		 *
		 * @param string $key
		 *
		 * @return string
		 */
		private function sanitize_option_key( $key = '' ) {
			$prefix = $this->get_options_prefix();

			if ( 0 === strpos( $key, $prefix ) ) {
				$prefix = '';
			}

			return $prefix . $key;
		}

		/**
		 * Get an array of all of this extension's options without array keys having the redundant prefix.
		 *
		 * @return array
		 */
		public function get_all_options() {
			$raw_options = $this->get_all_raw_options();

			$result = [];

			$prefix = $this->get_options_prefix();

			foreach ( $raw_options as $key => $value ) {
				$abbr_key            = str_replace( $prefix, '', $key );
				$result[ $abbr_key ] = $value;
			}

			return $result;
		}

		/**
		 * Get an array of all of this extension's raw options (i.e. the ones starting with its prefix).
		 *
		 * @return array
		 */
		public function get_all_raw_options() {
			$tribe_options = Tribe__Settings_Manager::get_options();

			if ( ! is_array( $tribe_options ) ) {
				return [];
			}

			$result = [];

			foreach ( $tribe_options as $key => $value ) {
				if ( 0 === strpos( $key, $this->get_options_prefix() ) ) {
					$result[ $key ] = $value;
				}
			}

			return $result;
		}

		/**
		 * Given an option key, delete this extension's option value.
		 *
		 * This automatically prepends this extension's option prefix so you can just do `$this->delete_option( 'a_setting' )`.
		 *
		 * @param string $key
		 *
		 * @return mixed
		 */
		public function delete_option( $key = '' ) {
			$key = $this->sanitize_option_key( $key );

			$options = Tribe__Settings_Manager::get_options();

			unset( $options[ $key ] );

			return Tribe__Settings_Manager::set_options( $options );
		}

		/**
		 * Adds a new section of fields to Events > Settings > General tab, appearing after the "Map Settings" section
		 * and before the "Miscellaneous Settings" section.
		 */
		public function add_settings() {
			$fields = [
				'Example'   => [
					'type' => 'html',
					'html' => $this->get_example_intro_text(),
				],
				'full_width' => [
					'type'            => 'checkbox_bool',
					'label'           => esc_html__( 'Full width strip', 'tribe-ext-daystrip' ),
					'tooltip'         => sprintf( esc_html__( 'By default and if it fits, the strip appears next to the datepicker on the right. If set to full width, then the daystrip will appear below the datepicker.', 'tribe-ext-daystrip' ) ),
					'validation_type' => 'boolean',
				],
				'number_of_days' => [
					'type'            => 'text',
					'label'           => esc_html__( 'Number of days to show', 'tribe-ext-daystrip' ),
					'tooltip'         => sprintf( esc_html__( 'The number of days to be shown on the daystrip. Best is if it is an odd number, and bigger than 2.', 'tribe-ext-daystrip' ) ) . '<br/><em>' . esc_html__( 'Default value:', 'tribe-ext-daystrip') . ' 9</em>',
					'validation_type' => 'positive_int',
					'size'            => 'small',
					'default'         => 9,
				],
				'behavior' => [
					'type'            => 'dropdown',
					'label'           => esc_html__( 'Behavior', 'tribe-ext-daystrip' ),
					'tooltip'         => esc_html__( 'Choose how you would like the day strip to behave.', 'tribe-ext-daystrip' ),
					'validation_type' => 'options',
					'size'            => 'small',
					'default'         => 'default',
					'options'         => $this->behavior_options(),
				],
				'start_date' => [
					'type'            => 'text',
					'label'           => esc_html__( 'Start date', 'tribe-ext-daystrip' ),
					'tooltip'         => sprintf( esc_html__( "Use YYYY-MM-DD format. Works only with the option '%sShow fixed number of days starting on a specific date%s'.", 'tribe-ext-daystrip' ), '<em>', '</em>' ) . '<br/><em>' . esc_html__( 'Default value:', 'tribe-ext-daystrip') . ' 2</em>',
					'validation_type' => 'alpha_numeric_with_dashes_and_underscores',
					'size'            => 'medium',
				],
				'length_of_day_name' => [
					'type'            => 'text',
					'label'           => esc_html__( 'Length of the day name', 'tribe-ext-daystrip' ),
					'tooltip'         => sprintf( esc_html__( 'Defines how long the day name should be, e.g. if set to %s then day names will be like Mo, Tu, etc. A value of %s or empty value will hide the day names. A value of %s will show the full day name.', 'tribe-ext-daystrip' ), '<code>2</code>', '<code>0</code>', '<code>-1</code>' ),
					'validation_type' => 'int',
					'size'            => 'small',
					'default'         => 2,
				],
				'just_a_label' => [
					'type'            => 'html',
					'html' => '<p>'
					          . sprintf(
						          esc_html__( 'The following two fields accept the date format options available to the PHP %s function.', 'tribe-ext-daystrip' ),
						          '<a href="https://wordpress.org/support/article/formatting-date-and-time/" target="_blank"><code>date()</code></a>'
					          )
					          . '</p>',
				],
				'date_format' => [
					'type'            => 'text',
					'label'           => esc_html__( 'Date format', 'tribe-ext-daystrip' ),
					'tooltip'         => sprintf( esc_html__( 'Examples: %1$s - 1, %2$s - 01, %3$s - 1st, %4$s - hide', 'tribe-ext-daystrip' ),
					                              '<code>j</code>',
					                              '<code>d</code>',
					                              '<code>jS</code>',
					                              '<code>0</code>',
					),
					'validation_type' => 'alpha_numeric',
					'size'            => 'small',
					'default'         => 'j',
				],
				'month_format' => [
					'type'            => 'text',
					'label'           => esc_html__( 'Month format', 'tribe-ext-daystrip' ),
					'tooltip'         => sprintf( esc_html__( 'Examples: %1$s - Jan., %2$s - January, %3$s - 01, %4$s - 1, %5$s - hide', 'tribe-ext-daystrip' ),
					                              '<code>M</code>',
					                              '<code>F</code>',
					                              '<code>m</code>',
					                              '<code>n</code>',
					                              '<code>0</code>',
					),
					'validation_type' => 'alpha_numeric',
					'size'            => 'small',
					'default'         => 'M',
				],
				'hide_event_marker' => [
					'type'            => 'checkbox_bool',
					'label'           => esc_html__( 'Hide event marker', 'tribe-ext-daystrip' ),
					'tooltip'         => sprintf( esc_html__( 'Enabling this option will hide the blue dot event marker from the daystrip.', 'tribe-ext-daystrip' ) ),
					'validation_type' => 'boolean',
				],
			];

			$this->settings_helper->add_fields(
				$this->prefix_settings_field_keys( $fields ),
				'display',
				'tribeEventsDateFormatSettingsTitle',
				true
			);
		}

		private function behavior_options() {
			return [
				'default'          => esc_html__( 'Selected day always in the middle of the strip', 'tribe-ext-daystrip' ),
				'forward'          => esc_html__( 'Only show days forward from the selected day', 'tribe-ext-daystrip' ),
				'fixed_from_today' => esc_html__( 'Show fixed number of days starting today', 'tribe-ext-daystrip' ),
				'fixed_from_date'  => esc_html__( 'Show fixed number of days starting on a specific date', 'tribe-ext-daystrip' ),
				'current_week'     => esc_html__( 'Current week (forces 7 days)', 'tribe-ext-daystrip' ),
				//'next_week'        => esc_html__( 'Next week (forces 7 days)', 'tribe-ext-daystrip' ), // @TODO This needs to be fixed
			];
	}
		/**
		 * Add the options prefix to each of the array keys.
		 *
		 * @param array $fields
		 *
		 * @return array
		 */
		private function prefix_settings_field_keys( array $fields ) {
			$prefixed_fields = array_combine(
				array_map(
					function ( $key ) {
						return $this->get_options_prefix() . $key;
					}, array_keys( $fields )
				),
				$fields
			);

			return (array) $prefixed_fields;
		}

		/**
		 * Here is an example of getting some HTML for the Settings Header.
		 *
		 * @return string
		 */
		private function get_example_intro_text() {
			return '<h3>' . esc_html_x( 'Day Strip Extension Settings', 'Settings header', 'tribe-ext-daystrip' ) . '</h3>';
		}

	} // class
}
