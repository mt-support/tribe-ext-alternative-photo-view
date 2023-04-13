<?php
/**
 * View: Photo Event
 *
 * This is a template override of the file at:
 * events-calendar-pro/src/views/v2/photo/event.php
 *
 * See more documentation about our views templating system.
 *
 * @link    https://evnt.is/1aiy
 *
 * @version 5.0.0
 *
 * @var WP_Post $event           The event post object with properties added by the `tribe_get_event` function.
 * @var string  $placeholder_url The url for the placeholder image if a featured image does not exist.
 * @var string  $image_url       The url for the event thumbnail
 *
 * @see     tribe_get_event() For the format of the event object.
 */

$classes = get_post_class( [ 'tribe-common-g-col', 'tribe-events-pro-photo__event' ], $event->ID );

if ( ! empty( $event->featured ) ) {
	$classes[] = 'tribe-events-pro-photo__event--featured';
}

// featured image
$image_url = $event->thumbnail->exists ? $event->thumbnail->full->url : $placeholder_url;
?>

<article <?php tribe_classes( $classes ) ?>>

	<div class="tribe-events-pro-photo__event-details-wrapper" style="background-image: url('<?php echo esc_url( $image_url ); ?>');">
		<div class="row-date-time">
			<?php $this->template( 'photo/event/date-tag', [ 'event' => $event ] ); ?>
			<?php $this->template( 'photo/event/date-time', [ 'event' => $event ] ); ?>
		</div>
		<div class="row-title">
			<?php $this->template( 'photo/event/date-time/featured' ); ?>
			<?php $this->template( 'photo/event/title', [ 'event' => $event ] ); ?>
			<?php $this->template( 'photo/event/cost', [ 'event' => $event ] ); ?>
		</div>
	</div>

</article>
