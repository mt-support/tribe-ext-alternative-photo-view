<?php
/**
 * View: Photo View
 *
 * This is a template override of the file at:
 * events-calendar-pro/src/views/v2/photo.php
 *
 * Added CSS class for the container.
 * Added variable for the event categories. (Not yet used.)
 *
 * See more documentation about our views templating system.
 *
 * @link https://evnt.is/1aiy
 *
 * @version  5.0.2
 *
 * @var string   $rest_url             The REST URL.
 * @var string   $rest_nonce           The REST nonce.
 * @var int      $should_manage_url    int containing if it should manage the URL.
 * @var string   $placeholder_url      The url for the placeholder image if a featured image does not exist.
 * @var array    $events               The array containing the events.
 * @var string   $today_url            URL pointing to the today link for this view.
 * @var string   $prev_url             URL pointing to the prev page link for this view.
 * @var string   $next_url             URL pointing to the next page link for this view.
 * @var bool     $disable_event_search Boolean on whether to disable the event search.
 * @var string[] $container_classes    Classes used for the container of the view.
 * @var array    $container_data       An additional set of container `data` attributes.
 * @var string   $breakpoint_pointer   String we use as pointer to the current view we are setting up with breakpoints.
 */

$header_classes = [ 'tribe-events-header' ];
if ( empty( $disable_event_search ) ) {
	$header_classes[] = 'tribe-events-header--has-event-search';
}
$container_classes[] = 'alternative-photo-view';

$event_categories = get_terms( [
	                    'taxonomy'   => 'tribe_events_cat',
	                    'hide_empty' => false,
                    ] );
?>
<div
	<?php tribe_classes( $container_classes ); ?>
	data-js="tribe-events-view"
	data-view-rest-nonce="<?php echo esc_attr( $rest_nonce ); ?>"
	data-view-rest-url="<?php echo esc_url( $rest_url ); ?>"
	data-view-manage-url="<?php echo esc_attr( $should_manage_url ); ?>"
	<?php foreach ( $container_data as $key => $value ) : ?>
		data-view-<?php echo esc_attr( $key ) ?>="<?php echo esc_attr( $value ) ?>"
	<?php endforeach; ?>
	<?php if ( ! empty( $breakpoint_pointer ) ) : ?>
		data-view-breakpoint-pointer="<?php echo esc_attr( $breakpoint_pointer ); ?>"
	<?php endif; ?>
>
	<div class="tribe-common-l-container tribe-events-l-container">
		<?php $this->template( 'components/loader', [ 'text' => __( 'Loading...', 'tribe-events-calendar-pro' ) ] ); ?>

		<?php $this->template( 'components/json-ld-data' ); ?>

		<?php $this->template( 'components/data' ); ?>

		<?php $this->template( 'components/before' ); ?>

		<header <?php tribe_classes( $header_classes ); ?>>
			<?php $this->template( 'components/messages' ); ?>

			<?php $this->template( 'components/breadcrumbs' ); ?>

			<?php $this->template( 'components/events-bar' ); ?>

			<?php $this->template( 'photo/top-bar' ); ?>
		</header>

		<?php $this->template( 'components/filter-bar' ); ?>

		<div class="tribe-events-pro-photo">

			<div class="tribe-common-g-row tribe-common-g-row--gutters">

				<?php foreach ( $events as $event ) : ?>
					<?php $this->setup_postdata( $event ); ?>

					<?php $this->template( 'photo/event', [ 'event' => $event, 'event_categories' => $event_categories ] ); ?>

				<?php endforeach; ?>

			</div>

		</div>

		<?php $this->template( 'photo/nav' ); ?>

		<?php $this->template( 'components/ical-link' ); ?>

		<?php $this->template( 'components/after' ); ?>
	</div>
</div>

<?php $this->template( 'components/breakpoints' ); ?>
