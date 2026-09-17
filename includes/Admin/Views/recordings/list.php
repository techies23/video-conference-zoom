<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$args         = ! empty( $args ) && is_array( $args ) ? $args : [];
$defaultUsers = ! empty( $args['default_users'] ) ? $args['default_users'] : [];
$hostId       = ! empty( $args['host_id'] ) ? $args['host_id'] : '';

?>
<div class="wrap vczapi-recordings">
    <h2 class="vczapi-recordings__title"><?php _e( "Recordings", "video-conferencing-with-zoom-api" ); ?></h2>

    <p class="vczapi-notification vczapi-error vczapi-recordings__notification">
        <strong><?php _e( "The maximum range can be a month. If no value is provided for this field, the default will be current date. For example, if you make the API request on June 30, 2020, without providing the “from” parameter, by default the value of ‘from’ field will be “2020-05-30” and the value of the ‘to’ field will be “2020-06-30”.", "video-conferencing-with-zoom-api" ); ?></strong>
    </p>

    <?php if ( current_user_can( 'manage_options' ) ) { ?>
        <div class="vczapi-recordings__filter-wrapper">
            <div class="vczapi-recordings__filter-item vczapi-recordings__filter-item--left">
                <form action="" class="vczapi-datepicker-admin vczapi-recordings__date-form" method="POST">
                    <label class="vczapi-recordings__date-label"><?php _e( 'Enter the date to check:', 'video-conferencing-with-zoom-api' ); ?></label>
                    <input name="date" id="vczapi-check-recording-date" class="vczapi-datetimepicker vczapi-recordings__date-input" data-date-format="Y-m-d" data-enable-time="false" autocomplete="off"/>
                    <input type="submit" name="check-recordings" class="vczapi-recordings__submit-btn" value="<?php _e( 'Check', 'video-conferencing-with-zoom-api' ); ?>">
                </form>
            </div>

            <div class="vczapi-recordings__filter-item vczapi-recordings__filter-item--right">
                <?php
                \Codemanas\VczApi\Helpers\FormHelper::fields(
                        'host_id',
                        [
                                "type"              => "select",
                                "input_class"       => [ "vczapi-choices", "vczapi-recordings__host-select" ],
                                "options"           => $defaultUsers,
                                'custom_attributes' => [
                                        'data-api-action'  => 'vczapi_get_user_by_query',
                                        'data-placeholder' => __( 'Search host by name or email...', 'video-conferencing-with-zoom-api' ),
                                        'data-searchable'  => 'true',
                                ],
                        ],
                        $hostId
                );
                ?>
            </div>
        </div>
    <?php } ?>

    <!-- Reusable Table Component Wrapper -->
    <div class="vczapi-table-wrapper">
        <table id="vczapi_recordings_table" class="vczapi-table">
            <thead class="vczapi-table__head">
            <tr class="vczapi-table__row vczapi-table__row--head">
                <th class="vczapi-table__cell vczapi-table__cell--head vczapi-table__cell--align-left"><?php _e( 'Meeting ID', 'video-conferencing-with-zoom-api' ); ?></th>
                <th class="vczapi-table__cell vczapi-table__cell--head vczapi-table__cell--align-left"><?php _e( 'Topic', 'video-conferencing-with-zoom-api' ); ?></th>
                <th class="vczapi-table__cell vczapi-table__cell--head vczapi-table__cell--align-left"><?php _e( 'Duration', 'video-conferencing-with-zoom-api' ); ?></th>
                <th class="vczapi-table__cell vczapi-table__cell--head vczapi-table__cell--align-left"><?php _e( 'Recorded', 'video-conferencing-with-zoom-api' ); ?></th>
                <th class="vczapi-table__cell vczapi-table__cell--head vczapi-table__cell--align-left"><?php _e( 'Size', 'video-conferencing-with-zoom-api' ); ?></th>
                <th class="vczapi-table__cell vczapi-table__cell--head vczapi-table__cell--align-left"><?php _e( 'Action', 'video-conferencing-with-zoom-api' ); ?></th>
            </tr>
            </thead>
            <tbody class="vczapi-table__body" id="vczapi_recordings_table_body">
            <tr class="vczapi-table__row vczapi-table__row--empty">
                <td class="vczapi-table__cell vczapi-table__cell--empty" colspan="6"><?php _e( 'Loading recordings...', 'video-conferencing-with-zoom-api' ); ?></td>
            </tr>
            </tbody>
        </table>
    </div>
</div>