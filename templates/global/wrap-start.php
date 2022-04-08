<?php
/**
 * Wrapper end for theme.
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
		echo '<div id="main-content"><div class="container"><div id="content-area" class="clearfix">';
		break;
	case 'twentyten' :
		echo '<div id="container"><div id="content" role="main">';
		break;
	case 'twentyeleven' :
		echo '<div id="primary"><div id="content" role="main" class="twentyeleven">';
		break;
	case 'twentytwelve' :
		echo '<div id="primary" class="site-content"><div id="content" role="main" class="twentytwelve">';
		break;
	case 'twentythirteen' :
		echo '<div id="primary" class="site-content"><div id="content" role="main" class="entry-content twentythirteen">';
		break;
	case 'twentyfourteen' :
		echo '<div id="primary" class="content-area"><div id="content" role="main" class="site-content twentyfourteen"><div class="tfwc">';
		break;
	case 'twentyfifteen' :
		echo '<div id="primary" role="main" class="content-area twentyfifteen"><div id="main" class="site-main t15wc">';
		break;
	case 'twentysixteen' :
		echo '<div id="primary" class="content-area twentysixteen"><main id="main" class="site-main" role="main">';
		break;
	case 'twentynineteen' :
		echo '<section id="primary" class="content-area twentynineteen"><main id="main" class="site-main" role="main">';
		break;
	case 'busify-pro' :
		echo '<div id="primary" class="content-area">';
		break;
	default :
		echo '<div class="content-area container"><main id="main" class="site-main" role="main">';
		break;
}
