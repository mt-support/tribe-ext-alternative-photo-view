<?php
/**
 * View: Photo View - Single Event Date Time
 *
 * This is a template override of the file at:
 * events-calendar-pro/src/views/v2/photo/event/date-time.php
 *
 * See more documentation about our views templating system.
 *
 * @link    https://evnt.is/1aiy
 *
 * @since   5.0.0
 * @since   5.1.1 Moved icons out to separate templates.
 *
 * @var WP_Post $event            The event post object with properties added by the `tribe_get_event` function.
 * @var obj     $date_formats     Object containing the date formats.
 * @var string  $time_format      The time format settings of The Events Calendar
 *
 * @see     tribe_get_event() For the format of the event object.
 *
 * @version 5.1.1
 */

$time_format = tribe_get_time_format();
?>
<div class="tribe-events-pro-photo__event-datetime tribe-common-b2">

	<?php
	if ( $event->multiday ) :
		$date_one = date_create( $event->dates->start_display->format( 'Y-m-d' ) );
		$date_two = date_create( $event->dates->end_display->format( 'Y-m-d' ) )->modify( '+1 day' );
		$nt       = date_diff( $date_one, $date_two, true );

		if ( ! $event->all_day ) {
			echo esc_html( $event->dates->start_display->format( $time_format ) ) . ' | ';
		}
		printf(
			esc_html__( '%d-day event', 'tribe-ext-alternative-photo-view' ),
			$nt->format( "%a" )
		);
		//echo $nt->format( "%a" ) . '-day event';
	?>

	<?php elseif ( $event->all_day ) : ?>
		<time datetime="<?php echo esc_attr( $event->dates->start_display->format( 'Y-m-d' ) ) ?>">
			<?php esc_attr_e( 'All day', 'tribe-events-calendar-pro' ); ?>
		</time>

	<?php else : ?>
		<time datetime="<?php echo esc_attr( $event->dates->start_display->format( 'H:i' ) ) ?>">
			<?php echo esc_html( $event->dates->start_display->format( $time_format ) ) ?>
		</time>
	<?php endif; ?>

	<?php $this->template( 'photo/event/date-time/recurring', [ 'event' => $event ] ); ?>
</div>
