<?php

namespace Tribe\Extensions\AlternativePhotoView;

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
		public function __construct( string $options_prefix ) {
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
		private function set_options_prefix( string $options_prefix ) {
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
		 * @see tribe_get_option()
		 *
		 * @param string $default
		 *
		 * @param string $key
		 *
		 * @return mixed
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
				'Example'                   => [
					'type' => 'html',
					'html' => $this->get_settings_header_text(),
				],
				'just_a_label'              => [
					'type' => 'html',
					'html' => '<p>'
						. sprintf(
							esc_html__( 'The following fields accept valid CSS values. If an invalid value is entered, then the page might break.', 'tribe-ext-alternative-photo-view' )
						)
						. '</p>',
				],
				'container_height'          => [
					'type'            => 'text',
					'label'           => esc_html__( 'Height of event container', 'tribe-ext-alternative-photo-view' ),
					'tooltip'         => sprintf( esc_html__( 'Accepts any valid number and unit. Recommended size is between 100px (landscape) and 400px (portrait).', 'tribe-ext-alternative-photo-view' ) ) . '<br/><em>' . esc_html__( 'Default value:', 'tribe-ext-alternative-photo-view' ) . ' 400px</em>',
					'validation_type' => 'alpha_numeric_with_dashes_and_underscores',
					'size'            => 'medium',
					'default'         => '400px',
				],
				'number_of_columns_desktop' => [
					'type'            => 'dropdown',
					'label'           => esc_html__( 'Number of columns on a large screen', 'tribe-ext-alternative-photo-view' ),
					'tooltip'         => esc_html__( 'The number of columns the events should be organized into on a large screen (1098px wide or larger).', 'tribe-ext-alternative-photo-view' ),
					'validation_type' => 'options',
					'size'            => 'small',
					'default'         => '3',
					'options'         => $this->get_number_of_columns_options(),
				],
				'number_of_columns_tablet'  => [
					'type'            => 'dropdown',
					'label'           => esc_html__( 'Number of columns on a tablet', 'tribe-ext-alternative-photo-view' ),
					'tooltip'         => esc_html__( 'The number of columns the events should be organized into on a tablet screen (between 810px and 1097px).', 'tribe-ext-alternative-photo-view' ),
					'validation_type' => 'options',
					'size'            => 'small',
					'default'         => '3',
					'options'         => $this->get_number_of_columns_options(),
				],
				'event_title_font_size'     => [
					'type'            => 'text',
					'label'           => esc_html__( 'Event title size', 'tribe-ext-alternative-photo-view' ),
					'tooltip'         => esc_html__( "The font size of the event title. Accepts any valid measurement.", 'tribe-ext-alternative-photo-view' ) . '<br/><em>' . esc_html__( 'Default value:', 'tribe-ext-alternative-photo-view' ) . ' 24px</em>',
					'validation_type' => 'alpha_numeric_with_dashes_and_underscores',
					'size'            => 'small',
					'default'         => '24px',
				],
				'event_title_alignment'     => [
					'type'            => 'dropdown',
					'label'           => esc_html__( 'Event title alignment', 'tribe-ext-alternative-photo-view' ),
					'tooltip'         => esc_html__( "How the event title should be aligned.", 'tribe-ext-alternative-photo-view' ) . '<br/><em>' . esc_html__( 'Default value:', 'tribe-ext-alternative-photo-view' ) . ' left</em>',
					'validation_type' => 'options',
					'size'            => 'small',
					'default'         => 'left',
					'options'         => $this->get_event_title_alignment_options(),
				],
				'container_border_radius'   => [
					'type'            => 'text',
					'label'           => esc_html__( 'Border radius', 'tribe-ext-alternative-photo-view' ),
					'tooltip'         => sprintf( esc_html__( 'The %1$sborder radius%2$s of the event container. This property can have from one to four values.', 'tribe-ext-alternative-photo-view' ), '<a href="https://www.w3schools.com/cssref/css3_pr_border-radius.asp" target="_blank">', '</a>' ) . '<br/><em>' . esc_html__( 'Default value:', 'tribe-ext-alternative-photo-view' ) . ' 16px</em>',
					'validation_type' => 'address',
					'size'            => 'medium',
					'default'         => '16px',
				],
			];

			$this->settings_helper->add_fields(
				$this->prefix_settings_field_keys( $fields ),
				'display',
				'tribeEventsDateFormatSettingsTitle',
				true
			);
		}

		private function get_number_of_columns_options() {
			return [
				'2' => '2',
				'3' => '3 (default)',
				'4' => '4',
				'5' => '5',
			];
		}

		private function get_event_title_alignment_options() {
			return [
				'left'      => 'left (default)',
				'center'    => 'center',
				'right'     => 'right',
				'justified' => 'justified',
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
		private function get_settings_header_text() {
			return '<h3>' . esc_html_x( 'Alternative Photo View Settings', 'Settings header', 'tribe-ext-alternative-photo-view' ) . '</h3>';
		}

	} // class
}
