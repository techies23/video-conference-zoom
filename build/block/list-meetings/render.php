<div <?php echo get_block_wrapper_attributes(); ?>>
	<?php
	$shortcode = isset( $attributes['shortcodeType'] ) && ( $attributes['shortcodeType'] == 'webinar' ) ? 'zoom_list_webinars' : 'zoom_list_meetings';
	if ( isset( $attributes['postsToShow'] ) && ! empty( $attributes['postsToShow'] ) ) {
		$shortcode .= ' per_page="' . $attributes['postsToShow'] . '"';
	}
	if ( isset( $attributes['orderBy'] ) && ! empty( $attributes['orderBy'] ) ) {
		$shortcode .= ' order="' . $attributes['orderBy'] . '"';
	}
	if ( isset( $attributes['showFilter'] ) && ! empty( $attributes['showFilter'] ) ) {
		$shortcode .= ' filter="' . $attributes['showFilter'] . '"';
	}
	if ( isset( $attributes['selectedCategory'] ) && is_array( $attributes['selectedCategory'] ) && ! empty( $attributes['selectedCategory'] ) ) {
		$categories_string = '';
		$category_count    = count( $attributes['selectedCategory'] );
		$separator         = ( $category_count > 1 ) ? ',' : '';
		foreach ( $attributes['selectedCategory'] as $index => $category ) {
			if ( $category['value'] == '' ) {
				continue;
			}
			$separator         = ( $index + 1 ) ? $separator : '';
			$categories_string .= $category['value'] . $separator;
		}
		unset( $separator );

		if ( ! empty( $categories_string ) ) {
			$shortcode .= ' category="' . $categories_string . '"';
		}
	}

	if ( isset( $attributes['selectedAuthor'] ) && ! empty( $attributes['selectedAuthor'] ) ) {
		$shortcode .= ' author="' . $attributes['selectedAuthor'] . '"';
	}

	if ( isset( $attributes['displayType'] ) && ! empty( $attributes['displayType'] ) ) {
		$shortcode .= ' type="' . $attributes['displayType'] . '"';
	}

	if ( isset( $attributes['columns'] ) && ! empty( $attributes['columns'] ) ) {
		$shortcode .= ' cols="' . $attributes['columns'] . '"';
	}

	echo do_shortcode( '[' . $shortcode . ']' );
	?>
</div>
