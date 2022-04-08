<?php
/**
 * Wrapper for the theme
 *
 * @author Deepen.
 * @since 2.0.0
 * @version 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$template = video_conference_zoom_get_current_theme_slug();

switch ( $template ) {
	case 'Divi' :
		echo '</div></div></div>';
		break;
	case 'twentyten' :
		echo '</div></div>';
		break;
	case 'twentyeleven' :
		echo '</div>';
		get_sidebar( 'shop' );
		echo '</div>';
		break;
	case 'twentytwelve' :
		echo '</div></div>';
		break;
	case 'twentythirteen' :
		echo '</div></div>';
		break;
	case 'twentyfourteen' :
		echo '</div></div></div>';
		get_sidebar( 'content' );
		break;
	case 'twentyfifteen' :
		echo '</div></div>';
		break;
	case 'twentysixteen' :
		echo '</main></div>';
		break;
	case 'twentynineteen' :
		echo '</main></section>';
		break;
	case 'busify-pro' :
		echo '</div><!-- #primary -->';
		break;
	default :
		echo '</main></div>';
		break;
}
