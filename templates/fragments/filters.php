<?php
/**
 * This template can be overridden by copying it to yourtheme/video-conferencing-zoom/fragments/filters.php.
 *
 * @author      Deepen Bajracharya (CodeManas)
 * @since       3.6.0
 * @version     3.6.0
 */

global $vczapi;
?>
<form class="vczapi-filters" method="GET">
    <div class="vczapi-wrap vczapi-filters-wrapper">
        <div class="vczapi-col-3">
            <span><?php _e( 'Showing all', 'video-conferencing-with-zoom-api' ); ?>&nbsp;<?php echo $vczapi['found_posts']; ?>&nbsp;<?php _e( 'results', 'video-conferencing-with-zoom-api' ); ?></span>
        </div>
		<?php
		if ( ! empty( $vczapi['terms'] ) ) {
			?>
            <div class="vczapi-col-3">
                <select name="taxonomy" class="vczapi-taxonomy-ordering vczapi-form-control">
                    <option value="category_order"><?php _e( 'All Category', 'video-conferencing-with-zoom-api' ); ?></option>
					<?php foreach ( $vczapi['terms'] as $term ) { ?>
                        <option value="<?php esc_attr_e( $term->slug ); ?>" <?php echo ! empty( $vczapi['query']['tax'] ) ? selected( $term->slug, $vczapi['query']['tax'], false ) : false; ?>><?php esc_html_e( $term->name ); ?></option>
					<?php } ?>
                </select>
            </div>
			<?php
		}
		?>
        <div class="vczapi-col-3">
            <select name="orderby" class="vczapi-ordering vczapi-form-control">
                <option value="show_all" <?php echo ! empty( $vczapi['query']['order'] ) ? selected( 'show_all', esc_attr( $vczapi['query']['order'] ), false ) : false; ?>><?php _e( 'Default Sorting', 'video-conferencing-with-zoom-api' ); ?></option>
                <option value="latest" <?php echo ! empty( $vczapi['query']['order'] ) ? selected( 'latest', esc_attr( $vczapi['query']['order'] ), false ) : false; ?>><?php _e( 'Sort Descending', 'video-conferencing-with-zoom-api' ); ?></option>
                <option value="past" <?php echo ! empty( $vczapi['query']['order'] ) ? selected( 'past', esc_attr( $vczapi['query']['order'] ), false ) : false; ?>><?php _e( 'Sort Ascending', 'video-conferencing-with-zoom-api' ); ?></option>
            </select>
        </div>
        <div class="vczapi-col-3">
            <input type="text" placeholder="<?php _e( 'Search..', 'video-conferencing-with-zoom-api' ); ?>" class="vczapi-searching vczapi-form-control" value="<?php echo ! empty( $vczapi['query']['s'] ) ? esc_html( $vczapi['query']['s'] ) : ''; ?>" name="search">
        </div>
    </div>
</form>
