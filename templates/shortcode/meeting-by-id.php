<?php
/**
 * Shortcode template: Meeting by Zoom Meeting ID
 *
 * @var array $args Context passed via vczapi_get_template().
 */

use Codemanas\VczApi\Helpers\Date;
use Codemanas\VczApi\Helpers\Links;
use Codemanas\VczApi\Helpers\MeetingType;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$args      = is_array( $args ?? null ) ? $args : [];
$meetingId = $args['id'] ?? '';
$joinUrl   = $args['join_url'] ?? '';
$password  = $args['password'] ?? '';
$status    = $args['status'] ?? 'waiting';
$timezone  = $args['timezone'] ?? '';
$type      = $args['type'] ?? null;

$joinBtn    = ! empty( $joinUrl ) ? Links::getPwdEmbeddedJoinLink( $joinUrl, $password ) : '';
$joinViaWeb = ! empty( $meetingId ) ? Links::getJoinViaBrowserJoinLinks( [ 'link_only' => true ], $meetingId ) : '';

// Resolve closest upcoming occurrence for fixed-time recurring meetings
$closestOccurrence = false;
if ( ! empty( $type ) && MeetingType::is_recurring_fixed_time_meeting( $type ) && ! empty( $args['occurrences'] ) ) {
	try {
		$now = new \DateTime( 'now -1 hour', new \DateTimeZone( $timezone ?: 'UTC' ) );
		foreach ( $args['occurrences'] as $occurrence ) {
			if ( ( $occurrence->status ?? '' ) === 'available' ) {
				$startDate = new \DateTime( $occurrence->start_time, new \DateTimeZone( $timezone ?: 'UTC' ) );
				if ( $startDate >= $now ) {
					$closestOccurrence = $occurrence->start_time;
					break;
				}
			}
		}
	} catch ( \Exception $e ) {
		$closestOccurrence = false;
	}
}
?>

<table class="vczapi-shortcode-meeting-table">
    <tbody>
    <tr class="vczapi-shortcode-meeting-table__row vczapi-shortcode-meeting-table__row--id">
        <th><?php esc_html_e( 'Meeting ID', 'video-conferencing-with-zoom-api' ); ?></th>
        <td><?php echo ! empty( $meetingId ) ? esc_html( $meetingId ) : 'N/A'; ?></td>
    </tr>

    <tr class="vczapi-shortcode-meeting-table__row vczapi-shortcode-meeting-table__row--topic">
        <th><?php esc_html_e( 'Topic', 'video-conferencing-with-zoom-api' ); ?></th>
        <td><?php echo ! empty( $args['topic'] ) ? esc_html( $args['topic'] ) : 'N/A'; ?></td>
    </tr>

    <tr class="vczapi-shortcode-meeting-table__row vczapi-shortcode-meeting-table__row--status">
        <th><?php esc_html_e( 'Status', 'video-conferencing-with-zoom-api' ); ?></th>
        <td>
			<?php
			if ( 'waiting' === $status ) {
				esc_html_e( 'Waiting - Not started', 'video-conferencing-with-zoom-api' );
			} elseif ( 'started' === $status ) {
				esc_html_e( 'Meeting is in Progress', 'video-conferencing-with-zoom-api' );
			} else {
				echo esc_html( $status );
			}
			?>
            <p class="vczapi-shortcode-meeting-table__description small-description">
				<?php esc_html_e( 'Refresh is needed to change status.', 'video-conferencing-with-zoom-api' ); ?>
            </p>
        </td>
    </tr>

	<?php if ( ! empty( $type ) && MeetingType::is_recurring_fixed_time_meeting( $type ) ) : ?>
		<?php if ( ! empty( $args['occurrences'] ) ) : ?>
            <tr class="vczapi-shortcode-meeting-table__row vczapi-shortcode-meeting-table__row--type">
                <th><?php esc_html_e( 'Type', 'video-conferencing-with-zoom-api' ); ?></th>
                <td><?php esc_html_e( 'Recurring Meeting', 'video-conferencing-with-zoom-api' ); ?></td>
            </tr>
            <tr class="vczapi-shortcode-meeting-table__row vczapi-shortcode-meeting-table__row--occurrences">
                <th><?php esc_html_e( 'Occurrences', 'video-conferencing-with-zoom-api' ); ?></th>
                <td><?php echo esc_html( (string) count( $args['occurrences'] ) ); ?></td>
            </tr>
            <tr class="vczapi-shortcode-meeting-table__row vczapi-shortcode-meeting-table__row--next-start-time">
                <th><?php esc_html_e( 'Next Start Time', 'video-conferencing-with-zoom-api' ); ?></th>
                <td>
					<?php
					if ( $closestOccurrence ) {
						echo esc_html( Date::dateConverter( $closestOccurrence, $timezone, 'F j, Y @ g:i a' ) );
					} else {
						esc_html_e( 'Meeting has ended !', 'video-conferencing-with-zoom-api' );
					}
					?>
                </td>
            </tr>
		<?php else : ?>
            <tr class="vczapi-shortcode-meeting-table__row vczapi-shortcode-meeting-table__row--start-time">
                <th><?php esc_html_e( 'Start Time', 'video-conferencing-with-zoom-api' ); ?></th>
                <td><?php esc_html_e( 'Meeting has ended !', 'video-conferencing-with-zoom-api' ); ?></td>
            </tr>
		<?php endif; ?>

	<?php elseif ( ! empty( $type ) && MeetingType::is_recurring_no_fixed_time_meeting( $type ) ) : ?>
        <tr class="vczapi-shortcode-meeting-table__row vczapi-shortcode-meeting-table__row--start-time">
            <th><?php esc_html_e( 'Start Time', 'video-conferencing-with-zoom-api' ); ?></th>
            <td><?php esc_html_e( 'This is a meeting with no Fixed Time.', 'video-conferencing-with-zoom-api' ); ?></td>
        </tr>

	<?php elseif ( ! empty( $type ) && MeetingType::is_pmi( $type ) ) : ?>
        <tr class="vczapi-shortcode-meeting-table__row vczapi-shortcode-meeting-table__row--type">
            <th><?php esc_html_e( 'Type', 'video-conferencing-with-zoom-api' ); ?></th>
            <td><?php esc_html_e( 'Personal Meeting Room', 'video-conferencing-with-zoom-api' ); ?></td>
        </tr>

	<?php elseif ( ! empty( $args['start_time'] ) ) : ?>
        <tr class="vczapi-shortcode-meeting-table__row vczapi-shortcode-meeting-table__row--start-time">
            <th><?php esc_html_e( 'Start Time', 'video-conferencing-with-zoom-api' ); ?></th>
            <td><?php echo esc_html( Date::dateConverter( $args['start_time'], $timezone, 'F j, Y @ g:i a' ) ); ?></td>
        </tr>
	<?php endif; ?>

	<?php if ( ! empty( $timezone ) ) : ?>
        <tr class="vczapi-shortcode-meeting-table__row vczapi-shortcode-meeting-table__row--timezone">
            <th><?php esc_html_e( 'Timezone', 'video-conferencing-with-zoom-api' ); ?></th>
            <td><?php echo esc_html( $timezone ); ?></td>
        </tr>
	<?php endif; ?>

	<?php if ( ! empty( $args['duration'] ) ) : ?>
        <tr class="vczapi-shortcode-meeting-table__row vczapi-shortcode-meeting-table__row--duration">
            <th><?php esc_html_e( 'Duration', 'video-conferencing-with-zoom-api' ); ?></th>
            <td><?php echo esc_html( (string) $args['duration'] ); ?></td>
        </tr>
	<?php endif; ?>

	<?php if ( ! empty( $joinBtn ) || ! empty( $joinViaWeb ) ) : ?>
        <tr class="vczapi-shortcode-meeting-table__row vczapi-shortcode-meeting-table__row--join-btns">
            <th><?php esc_html_e( 'Links', 'video-conferencing-with-zoom-api' ); ?></th>
            <td>
				<?php if ( ! empty( $joinBtn ) ) : ?>
                    <a href="<?php echo esc_url( $joinBtn ); ?>" target="_blank" rel="noopener noreferrer nofollow" class="vczapi-post-card__btn vczapi-post-card__btn--join">
						<?php esc_html_e( 'Join Meeting', 'video-conferencing-with-zoom-api' ); ?>
                    </a>
				<?php endif; ?>

				<?php if ( ! empty( $joinViaWeb ) ) : ?>
                    <a href="<?php echo esc_url( $joinViaWeb ); ?>" target="_blank" rel="noopener noreferrer nofollow" class="vczapi-post-card__btn vczapi-post-card__btn--browser">
						<?php esc_html_e( 'Join via Browser', 'video-conferencing-with-zoom-api' ); ?>
                    </a>
				<?php endif; ?>
            </td>
        </tr>
	<?php endif; ?>
    </tbody>
</table>
