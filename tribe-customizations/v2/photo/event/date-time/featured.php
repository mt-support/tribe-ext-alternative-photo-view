<?php
/**
 * View: Photo View - Single Event Featured Icon
 *
 * This is a template override of the file at:
 * events-calendar-pro/src/views/v2/photo/event/date-time/featured.php
 *
 * Added a <div> container around the featured marking for formatting
 *
 * See more documentation about our views templating system.
 *
 * @link    http://m.tri.be/1aiy
 *
 * @since   5.1.1
 *
 * @var WP_Post $event The event post object with properties added by the `tribe_get_event` function.
 *
 * @see     tribe_get_event() For the format of the event object.
 *
 * @version 5.1.1
 */

if ( empty( $event->featured ) ) {
	return;
}
?>
<div class="tribe-events-pro-photo__event-datetime-featured-container">
	<em
		class="tribe-events-pro-photo__event-datetime-featured-icon tribe-common-svgicon tribe-common-svgicon--featured"
		aria-label="<?php esc_attr_e( 'Featured', 'tribe-events-calendar-pro' ); ?>"
		title="<?php esc_attr_e( 'Featured', 'tribe-events-calendar-pro' ); ?>"
	>
	</em>
	<span class="tribe-events-pro-photo__event-datetime-featured-text">
	<?php esc_html_e( 'Featured', 'tribe-events-calendar-pro' ); ?>
</span>
</div>