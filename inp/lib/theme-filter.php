<?php
/**
 * @author : Jegtheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'jnews_view_counter_number_format', '__return_false' );

add_action( 'jnews_after_body', 'jnews_body_background_ads' );

function jnews_body_background_ads() {
	$ads_html = null;
	$enabled  = get_theme_mod( 'jnews_background_ads_enable', true );

	if ( $enabled ) {
		$type = get_theme_mod( 'jnews_background_ads_type', 'image' );

		if ( $type === 'code' ) {
			$code     = get_theme_mod( 'jnews_background_ads_code', '' );
			$ads_html = "<div class='ads_code'>" . $code . "</div>";
		}

		if ( $type === 'shortcode' ) {
			$shortcode = get_theme_mod( 'jnews_background_ads_shortcode', '' );
			$ads_html  = "<div class='ads_shortcode'>" . do_shortcode( $shortcode ) . "</div>";
		}


		echo jnews_sanitize_by_pass( $ads_html );
	}
}

/** Browser Color */

add_action( 'wp_head', 'jnews_browser_color', null );

function jnews_browser_color() {
	$browser_color = get_theme_mod( 'jnews_mobile_browser_color', '' );
	if ( ! empty( $browser_color ) ) {
		$html =
			"<meta name=\"theme-color\" content=\"{$browser_color}\">
             <meta name=\"msapplication-navbutton-color\" content=\"{$browser_color}\">
             <meta name=\"apple-mobile-web-app-status-bar-style\" content=\"{$browser_color}\">";

		echo jnews_sanitize_output( $html );
	}
}

/** Navigation for Page Split */

add_filter( 'wp_link_pages', 'jnews_link_page', null, 2 );

function jnews_link_page( $output, $args ) {
	global $page, $numpages, $multipage, $more;

	$end_size = 1;
	$mid_size = 1;
	$dots     = false;

	if ( $multipage ) {

		$paging_text = sprintf( jnews_return_translation( 'Page %s of %s', 'jnews', 'page_s_of_s' ), $page, $numpages );

		$output =
			"<div class=\"jeg_pagelinks jeg_pagination jeg_pagenav_1 jeg_alignleft no_navtext\">
                <span class=\"page_info\">{$paging_text}</span>
                <div class=\"nav_link\">";


		if ( $page > 1 ) {
			$prev_url = jnews_get_page_link_url( $page - 1 );
			$output   .= "<a class=\"page_nav prev\" href=\"{$prev_url}\"><span class=\"navtext\">" . jnews_return_translation( 'Prev', 'jnews', 'prev' ) . "</span></a>";
		}


		for ( $n = 1; $n <= $numpages; $n ++ ) {
			if ( $n == $page ) {
				$output .= "<span class=\"page_number active\">{$n}</span>";
			} else {
				if ( $n <= $end_size || ( $page && $n >= $page - $mid_size && $n <= $page + $mid_size ) || $n > $numpages - $end_size ) {
					$loop_url = jnews_get_page_link_url( $n );
					$output   .= "<a class=\"page_number\" href=\"{$loop_url}\">{$n}</a>";
					$dots     = true;
				} else if ( $dots ) {
					$output .= "<span class=\"page_number dots\">...</span>";
					$dots   = false;
				}
			}
		}

		if ( $page < $numpages ) {
			$next_url = jnews_get_page_link_url( $page + 1 );
			$output   .= "<a class=\"page_nav next\" href=\"{$next_url}\"><span class=\"navtext\">" . jnews_return_translation( 'Next', 'jnews', 'next' ) . "</span></a>";
		}

		$output .=
			"</div>
            </div>";
	}

	return $output;
}

function jnews_get_page_link_url( $i ) {
	global $wp_rewrite;
	$post       = get_post();
	$query_args = array();

	if ( 1 == $i ) {
		$url = get_permalink();
	} else {
		if ( '' == get_option( 'permalink_structure' ) || in_array( $post->post_status, array(
				'draft',
				'pending'
			) ) ) {
			$url = add_query_arg( 'page', $i, get_permalink() );
		} elseif ( 'page' == get_option( 'show_on_front' ) && get_option( 'page_on_front' ) == $post->ID ) {
			$url = trailingslashit( get_permalink() ) . user_trailingslashit( "$wp_rewrite->pagination_base/" . $i, 'single_paged' );
		} else {
			$url = trailingslashit( get_permalink() ) . user_trailingslashit( $i, 'single_paged' );
		}
	}

	if ( is_preview() ) {

		if ( ( 'draft' !== $post->post_status ) && isset( $_GET['preview_id'], $_GET['preview_nonce'] ) ) {
			$query_args['preview_id']    = wp_unslash( (int) sanitize_text_field( $_GET['preview_id'] ) );
			$query_args['preview_nonce'] = wp_unslash( sanitize_text_field( $_GET['preview_nonce'] ) );
		}

		$url = get_preview_post_link( $post, $query_args, $url );
	}

	return $url;
}

/** JNews Popup Script */

add_filter( 'jnews_single_popup_script', 'jnews_popup_script' );

function jnews_popup_script() {
	return get_theme_mod( 'jnews_single_popup_script', 'magnific' );
}

/** Set attachment image size */

add_filter( 'wp_get_attachment_image_attributes', 'jnews_image_size', null, 2 );

/**
 * @param $attr
 * @param $attachment
 * @return mixed
 */
function jnews_image_size( $attr, $attachment ) {
	if ( jnews_popup_script() === 'photoswipe' ) {
		$image                    = wp_get_attachment_image_src( $attachment->ID, 'full' );
		$attr['data-full-width']  = $image[1];
		$attr['data-full-height'] = $image[2];
	}

	return $attr;
}

/** photoswipe additional tag */

add_action( 'wp_footer', 'jnews_photoswipe_tag', 9 );

function jnews_photoswipe_tag() {
	if ( jnews_popup_script() === 'photoswipe' ) {
		$html =
			"<div class=\"pswp\" tabindex=\"-1\" role=\"dialog\" aria-hidden=\"true\">
                <div class=\"pswp__bg\"></div>
                <div class=\"pswp__scroll-wrap\">
                    <div class=\"pswp__container\">
                        <div class=\"pswp__item\"></div>
                        <div class=\"pswp__item\"></div>
                        <div class=\"pswp__item\"></div>
                    </div>
                    <div class=\"pswp__ui pswp__ui--hidden\">
                        <div class=\"pswp__top-bar\">
                            <div class=\"pswp__counter\"></div>
                            <button class=\"pswp__button pswp__button--close\" title=\"" . jnews_return_translation( 'Close (Esc)', 'jnews', 'close_esc' ) . "\"></button>
                            <button class=\"pswp__button pswp__button--share\" title=\"" . jnews_return_translation( 'Share', 'jnews', 'share' ) . "\"></button>
                            <button class=\"pswp__button pswp__button--fs\" title=\"" . jnews_return_translation( 'Toggle fullscreen', 'jnews', 'toggle_fullscreen' ) . "\"></button>
                            <button class=\"pswp__button pswp__button--zoom\" title=\"" . jnews_return_translation( 'Zoom in/out', 'jnews', 'zoom_in_out' ) . "\"></button>
                            <div class=\"pswp__preloader\">
                                <div class=\"pswp__preloader__icn\">
                                    <div class=\"pswp__preloader__cut\">
                                        <div class=\"pswp__preloader__donut\"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class=\"pswp__share-modal pswp__share-modal--hidden pswp__single-tap\">
                            <div class=\"pswp__share-tooltip\"></div>
                        </div>
                        <button class=\"pswp__button pswp__button--arrow--left\" title=\"" . jnews_return_translation( 'Previous (arrow left)', 'jnews', 'previous_arrow_left' ) . "\">
                        </button>
                        <button class=\"pswp__button pswp__button--arrow--right\" title=\"" . jnews_return_translation( 'Next (arrow right)', 'jnews', 'next_arrow_right' ) . "\">
                        </button>
                        <div class=\"pswp__caption\">
                            <div class=\"pswp__caption__center\"></div>
                        </div>
                    </div>
                </div>
            </div>";

		echo apply_filters( 'jnews_photoswipe_tag', $html );
	}
}


/** Share Output Modification */

add_filter( 'jnews_top_share_output', 'jnews_top_share_output', null, 2 );
add_filter( 'jnews_float_share_output', 'jnews_float_share_output', null, 2 );
add_filter( 'jnews_bottom_share_output', 'jnews_bottom_share_output', null, 2 );
add_filter( 'jnews_show_view_tag', 'jnews_show_view_tag', null, 2 );
add_filter( 'jnews_show_share_tag', 'jnews_show_share_tag', null, 2 );

function jnews_get_share_position( $post_id ) {
	$position = get_theme_mod( 'jnews_single_share_position', 'top' );

	if ( is_page( $post_id ) ) {
		$position = vp_metabox( 'jnews_single_page.share_position', 'top', $post_id );
	}

	if ( vp_metabox( 'jnews_single_post.override.0.share_position', 'top', $post_id ) && vp_metabox( 'jnews_single_post.override_template', false, $post_id ) ) {
		$position = vp_metabox( 'jnews_single_post.override.0.share_position', 'top', $post_id );
	}

	return $position;
}

function jnews_top_share_output( $output, $post_id ) {
	if ( apply_filters( 'jnews_allow_override_share', true ) ) {
		$position = jnews_get_share_position( $post_id );

		if ( $position === 'top' || $position === 'topbottom' ) {
			return $output;
		}

		return null;
	}

	return $output;
}

function jnews_float_share_output( $output, $post_id ) {
	if ( apply_filters( 'jnews_allow_override_share', true ) ) {
		$position = jnews_get_share_position( $post_id );

		if ( $position === 'float' || $position === 'floatbottom' ) {
			return $output;
		}

		return null;
	}

	return $output;
}

function jnews_bottom_share_output( $output, $post_id ) {
	if ( apply_filters( 'jnews_allow_override_share', true ) ) {
		$position = jnews_get_share_position( $post_id );

		if ( $position === 'bottom' || $position === 'topbottom' || $position === 'floatbottom' ) {
			return $output;
		}

		return null;
	}

	return $output;
}

function jnews_show_view_tag( $bool, $post_id ) {
	if ( ! is_page( $post_id ) ) {
		if ( ( vp_metabox( 'jnews_single_post.override.0.share_position', 'top', $post_id ) === 'top' || vp_metabox( 'jnews_single_post.override.0.share_position', 'top', $post_id ) === 'topbottom' ) && vp_metabox( 'jnews_single_post.override_template', false, $post_id ) ) {
			if ( vp_metabox( 'jnews_single_post.override.0.show_view_counter', false, $post_id ) ) {
				return true;
			} else {
				return false;
			}
		} elseif ( get_theme_mod( 'jnews_single_show_view_counter', true ) ) {
			return true;
		}
	}

	return false;
}

function jnews_show_share_tag( $bool, $post_id ) {
	if ( ! is_page( $post_id ) ) {
		if ( ( vp_metabox( 'jnews_single_post.override.0.share_position', 'top', $post_id ) === 'top' || vp_metabox( 'jnews_single_post.override.0.share_position', 'top', $post_id ) === 'topbottom' ) && vp_metabox( 'jnews_single_post.override_template', false, $post_id ) ) {
			if ( vp_metabox( 'jnews_single_post.override.0.show_share_counter', false, $post_id ) ) {
				return true;
			} else {
				return false;
			}
		} elseif ( get_theme_mod( 'jnews_single_show_share_counter', true ) ) {
			return true;
		}
	}

	return false;
}


/** Filter Search (option on Customizer) */

add_filter( 'pre_get_posts', 'jnews_search_filter' );

function jnews_search_filter( $query ) {
	if ( is_search() && ! is_admin() && ! is_archive() ) {
		if ( isset( $query->query['s'] ) && get_theme_mod( 'jnews_search_only_post', true ) ) {
			$query->set( 'post_type', 'post' );
		}
	}

	return $query;
}

/** WooCommerce */

add_filter( 'loop_shop_columns', 'jnews_woo_loop_columns' );
add_filter( 'loop_shop_per_page', 'jnews_woo_loop_per_page' );
add_filter( 'woocommerce_output_related_products_args', 'jnews_woo_related_product' );
add_filter( 'woocommerce_breadcrumb_defaults', 'jnews_woo_breadcrumb_delimiter' );


function jnews_woo_breadcrumb_delimiter( $defaults ) {
	$direction = ( is_rtl() ? 'fa-angle-left' : 'fa-angle-right' );
	$defaults['delimiter'] = '<i class="fa ' . $direction . '"></i>';

	return $defaults;
}

function jnews_woo_related_product() {
	if ( jnews_can_render_woo_widget() ) {
		return array(
			'posts_per_page' => 3,
			'columns'        => 3,
			'orderby'        => 'rand'
		);
	} else {
		return array(
			'posts_per_page' => 4,
			'columns'        => 4,
			'orderby'        => 'rand'
		);
	}
}

function jnews_woo_loop_columns() {
	if ( jnews_can_render_woo_widget() ) {
		return 3;
	} else {
		return 4;
	}
}

function jnews_woo_loop_per_page() {
	return 12;
}


/** JSON LD Social */

add_filter( 'jnews_json_ld_social', 'jnews_social_json_ld' );

function jnews_social_json_ld() {
	/** @var array $socials */
	$socials      = get_theme_mod( 'jnews_social_icon', array(
		array(
			'social_icon' => 'facebook',
			'social_url'  => 'http://facebook.com',
		),
		array(
			'social_icon' => 'twitter',
			'social_url'  => 'http://twitter.com',
		),
	) );
	$social_array = array();

	foreach ( $socials as $social ) {
		if ( ! empty( $social['social_url'] ) ) {
			$social_array[] = $social['social_url'];
		}
	}

	return $social_array;
}

/** Background Ad CSS */

add_filter( 'jnews_generate_inline_style', 'jnews_additonal_background_ads_css' );

function jnews_additonal_background_ads_css( $generated_style ) {
	$url              = esc_url( get_theme_mod( 'jnews_background_ads_url', '' ) );
	$additional_style = '';

	if ( ! empty( $url ) ) {
		$additional_style .= ".jeg_boxed .jeg_header { position: static; } .jeg_boxed .jeg_container { position: relative; }";
	}

	$sticky = get_theme_mod( 'jnews_ads_mobile_sticky_enable', false );

	if ( $sticky && wp_is_mobile() ) {
		$additional_style .= ".jeg_viewport { padding-bottom: 70px; }";

	}

	return $additional_style . $generated_style;
}

/** OEMBEED HTML */

add_filter( 'embed_oembed_html', 'jnews_custom_oembed_filter', 10, 4 );

function jnews_custom_oembed_filter( $html, $url, $attr, $post_ID ) {
	$geturl = parse_url( $url );

	if ( strstr( $geturl['host'], 'youtube.com' ) || strstr( $geturl['host'], 'youtu.be' ) || strstr( $geturl['host'], 'vimeo.com' ) ) {
		return '<div class="jeg_video_container jeg_video_content">' . $html . '</div>';
	}

	if ( strstr( $geturl['host'], 'vine.co' ) ) {
		return '<div class="jeg_video_container jeg_video_content jeg_video_square">' . $html . '</div>';
	}

	return $html;
}

/**
 * Facebook Instant Article
 */

function jnews_exclude_strip_shortcodes( $tags_to_remove, $content = '' ) {
	if ( ! is_array( $tags_to_remove ) ) {
		return $tags_to_remove;
	}

	$exclude_shortcodes = array( 'audio', 'caption', 'embed', 'gallery', 'playlist', 'video' );

	foreach ( $exclude_shortcodes as $exclude_shortcode ) {
		if ( ( $key = array_search( $exclude_shortcode, $tags_to_remove ) ) !== false ) {
			unset( $tags_to_remove[ $key ] );
		}
	}

	return $tags_to_remove;
}

function jnews_gallery_shortcode_size_atts( $out, $pairs, $atts ) {
	if ( ! isset( $atts['size'] ) || $atts['size'] == 'thumbnail' ) {
		$out['size'] = 'large';
	}

	return $out;
}

function jnews_strip_shortcodes() {
	jnews_remove_filters( 'embed_oembed_html', 'jnews_custom_oembed_filter' );

	add_filter( 'strip_shortcodes_tagnames', 'jnews_exclude_strip_shortcodes' );
	add_filter( 'shortcode_atts_gallery', 'jnews_gallery_shortcode_size_atts', 10, 3 );

	if ( defined( 'JNEWS_GALLERY' ) ) {
		do_action( 'jnews_render_element', 'gallery', 'gallery_shortcode' );
	}

	add_filter( 'the_content', function ( $content ) {
		return strip_shortcodes( $content );
	} );

	if ( defined( 'JNEWS_REVIEW' ) ) {
		jnews_remove_filters( 'the_content', 'review_content', 2 );
	}
}

function jnews_ia_subtitle( $subtitle ) {
	$post_id       = get_the_ID();
	$post_subtitle = get_post_meta( $post_id, 'post_subtitle', true );

	if ( ! empty( $post_subtitle ) ) {
		$subtitle = $post_subtitle;
	}

	return $subtitle;
}

/**
 * Facebook Instant Article
 */
if ( defined( 'IA_PLUGIN_VERSION' ) ) {
	add_action( 'instant_articles_before_transform_post', 'jnews_strip_shortcodes' );
	add_filter( 'instant_articles_subtitle', 'jnews_ia_subtitle' );
}


/**
 * Override logout url
 */
add_filter( 'logout_url', 'jnews_logout_url', 10, 2 );

function jnews_logout_url() {
	$args                = array( 'action' => 'logout' );
	$args['redirect_to'] = esc_url( jnews_home_url_multilang( '/' ) );

	$logout_url = add_query_arg( $args, site_url( 'wp-login.php', 'login' ) );
	$logout_url = wp_nonce_url( $logout_url, 'log-out' );

	return $logout_url;
}


/**
 * SSL checking
 */
add_filter( 'jnews_get_permalink', 'jnews_get_secure_permalink' );
add_filter( 'jnews_social_counter_post_url', 'jnews_get_secure_permalink' );

function jnews_get_secure_permalink( $url ) {
	if ( is_ssl() ) {
		$url = preg_replace( "/^http:/i", "https:", $url );
	} else {
		$url = preg_replace( "/^https:/i", "http:", $url );
	}

	return $url;
}


add_filter( 'widget_title', 'jnews_html_widget_title' );

function jnews_html_widget_title( $title ) {
	//HTML tag opening/closing brackets
	$title = str_replace( array( '[', '&lt;' ), '<', $title );
	$title = str_replace( array( '[/', '&lt;/' ), '</', $title );

	// bold
	$title = str_replace( array( 'strong]', 'strong&gt;' ), 'strong>', $title );

	// italic
	$title = str_replace( array( 'em]', 'em&gt;' ), 'em>', $title );
	$title = str_replace( array( 'i]', 'i&gt;' ), 'i>', $title );

	return $title;
}

add_filter( 'wp_kses_allowed_html', 'jnews_allowed_html' );

// Extend Allowed HTML WP KSES
function jnews_allowed_html( $allowedtags ) {
    $allowedtags['br']   = array_merge( isset( $allowedtags['br'] ) ? $allowedtags['br'] : [], [] );
    $allowedtags['ul']   = array_merge( isset( $allowedtags['ul'] ) ? $allowedtags['ul'] : [], [
        'class' => true,
        'style' => true
    ] );
    $allowedtags['ol']   = array_merge( isset( $allowedtags['ol'] ) ? $allowedtags['ol'] : [], [] );
    $allowedtags['li']   = array_merge( isset( $allowedtags['li'] ) ? $allowedtags['li'] : [], [] );
    $allowedtags['a']    = array_merge( isset( $allowedtags['a'] ) ? $allowedtags['a'] : [], [
        'href'   => true,
        'title'  => true,
        'target' => true,
        'class'  => true,
        'style'  => true
    ] );
    $allowedtags['span'] = array_merge( isset( $allowedtags['span'] ) ? $allowedtags['span'] : [], [
        'class' => true,
        'style' => true
    ] );
    $allowedtags['i']    = array_merge( isset( $allowedtags['i'] ) ? $allowedtags['i'] : [], [
        'class' => true
    ] );
    $allowedtags['div']  = array_merge( isset( $allowedtags['div'] ) ? $allowedtags['div'] : [], [
        'id'         => true,
        'class'      => true,
        'data-id'    => true,
        'data-video' => true
    ] );
    $allowedtags['img']  = array_merge( isset( $allowedtags['img'] ) ? $allowedtags['img'] : [], [
        'class'  => true,
        'src'    => true,
        'alt'    => true,
        'srcset' => true,
        'width'  => true,
        'height' => true
    ] );

	return $allowedtags;
}


add_action( 'jnews_admin_dashboard_child', 'jnews_theme_admin_dashboard_child' );

function jnews_theme_admin_dashboard_child( $param ) {
	if ( $param['4'] !== 'customize.php' ) {
		if ( count( $param ) === 5 ) {
			add_theme_page( $param[1], $param[2], $param[3], $param[4] );
		} else {
			add_theme_page( $param[1], $param[2], $param[3], $param[4], $param[5] );
		}
	}
}

/**
 * Disable Sidefeed on Page
 */
add_filter( 'theme_mod_jnews_sidefeed_enable', 'jnews_disable_sidefeed_on_page' );

function jnews_disable_sidefeed_on_page( $enable ) {
	if ( get_theme_mod( 'jnews_sidefeed_disable_page', false ) && is_page() ) {
		return false;
	}

	return $enable;
}

/*** Mobile truncate ***/

if ( ! function_exists( 'jnews_enable_mobile_truncate' ) ) {
	function jnews_enable_mobile_truncate() {
		if ( get_post_type( get_the_ID() ) !== 'post' ) {
			return false;
		}

		if ( is_customize_preview() ) {
			return get_theme_mod( 'jnews_mobile_truncate', false ) && is_single();
		} else {
			return get_theme_mod( 'jnews_mobile_truncate', false ) && wp_is_mobile() && is_single();
		}
	}
}

add_filter( 'jnews_content_class', function ( $value, $id ) {
	if ( jnews_enable_mobile_truncate() ) {
		$value .= ' mobile-truncate';
	}
	
	if ( get_theme_mod( 'jnews_entry_link_underline', false ) ) {
		$value .= ' jeg_link_underline';
	}

	return $value;
}, null, 2 );

add_filter( 'the_content', function ( $content ) {
	if ( jnews_enable_mobile_truncate() ) {
		$truncate = "<div class='truncate-read-more'><span>" . jnews_return_translation( 'Continue Reading', 'jnews', 'continue_reading' ) . "</span></div>";

		return $content . $truncate;
	}

	return $content;
}, 999 );


/**
 * Changing canonical URL 
 * 
 * note : kenapa diubah ke home URL?
 */
/* if ( ! function_exists( 'jnews_get_canonical_url' ) ) {

	add_filter( 'get_canonical_url', 'jnews_get_canonical_url' );

	function jnews_get_canonical_url() {
		return esc_url( home_url( '/' ) );
	}

} */


/**
 * Comment Privacy Policy
 */
if ( ! function_exists( 'jnews_coment_custom_fields' ) ) {
	if ( get_theme_mod( 'jnews_gdpr_comment_enable', false ) ) {
		add_filter( 'comment_form_default_fields', 'jnews_coment_custom_fields' );
	}

	function jnews_coment_custom_fields( $fields ) {
		$required_email = get_option( 'require_name_email' );
		$required_field = ( $required_email ? " required='required'" : '' );

		$fields['privacy_policy'] = "<p class=\"comment-form-privacy_policy\">
										<label for=\"privacy_policy\">" . get_theme_mod( 'jnews_gdpr_comment_label', esc_html__( 'Privacy Policy Agreement', 'jnews' ) ) . " " . ( $required_email ? '<span class="required">*</span>' : '' ) . "</label>
										<input id=\"privacy_policy\" name=\"privacy_policy\" " . $required_field . " type='checkbox'> " . get_theme_mod( 'jnews_gdpr_comment_text', __( 'I agree to the Terms &amp; Conditions and <a href="#">Privacy Policy</a>.', 'jnews' ) ) . "
									</p>";

		return $fields;
	}
}

if ( ! function_exists( 'jnews_coment_login_custom_fields' ) ) {
	if ( is_user_logged_in() && get_theme_mod( 'jnews_gdpr_comment_enable', false ) ) {
		add_filter( 'comment_form_field_comment', 'jnews_coment_login_custom_fields' );
	}

	function jnews_coment_login_custom_fields( $content ) {
		$required_email = get_option( 'require_name_email' );
		$required_field = ( $required_email ? " required='required'" : '' );

		$field = "<p class=\"comment-form-privacy_policy\">
					<label for=\"privacy_policy\">" . get_theme_mod( 'jnews_gdpr_comment_label', esc_html__( 'Privacy Policy Agreement', 'jnews' ) ) . " " . ( $required_email ? '<span class="required">*</span>' : '' ) . "</label>
					<input id=\"privacy_policy\" name=\"privacy_policy\" " . $required_field . " type='checkbox'> " . get_theme_mod( 'jnews_gdpr_comment_text', __( 'I agree to the Terms &amp; Conditions and <a href="#">Privacy Policy</a>.', 'jnews' ) ) . "
				</p>";

		return $content . $field;
	}
}

if ( ! function_exists( 'jnews_save_comment_privacy_policy_meta' ) ) {
	if ( get_theme_mod( 'jnews_gdpr_comment_enable', false ) ) {
		add_action( 'comment_post', 'jnews_save_comment_privacy_policy_meta' );
	}

	function jnews_save_comment_privacy_policy_meta( $comment_id ) {
		if ( ( isset( $_POST['privacy_policy'] ) ) && ( $_POST['privacy_policy'] != '' ) ) {
			$privacy_policy = wp_filter_nohtml_kses( $_POST['privacy_policy'] );
			add_comment_meta( $comment_id, 'privacy_policy', $privacy_policy );
		}
	}
}

if ( ! function_exists( 'jnews_footer_cookie_law_policy' ) ) {
	if ( ! is_user_logged_in() && get_theme_mod( 'jnews_gdpr_cookie_enable', false ) ) {
		add_action( 'wp_footer', 'jnews_footer_cookie_law_policy' );
	}

	function jnews_footer_cookie_law_policy() {
		$output = "<div class=\"jnews-cookie-law-policy\">" . get_theme_mod( 'jnews_gdpr_cookie_text', __( 'This website uses cookies. By continuing to use this website you are giving consent to cookies being used. Visit our <a href="#">Privacy and Cookie Policy</a>.', 'jnews' ) ) . " <button data-expire=\"" . get_theme_mod( 'jnews_gdpr_cookie_expire', 7 ) . "\" class=\"btn btn-cookie\">" . get_theme_mod( 'jnews_gdpr_cookie_button', esc_html__( 'I Agree', 'jnews' ) ) . "</button></div>";

		echo jnews_sanitize_by_pass( $output );
	}
}

if ( ! function_exists( 'jnews_set_elementor_stretched_section_container_post' ) ) {
	add_action( 'template_redirect ', 'jnews_set_elementor_stretched_section_container_post' );
	/**
	 * Set Elementor stretched section parent
	 */
	function jnews_set_elementor_stretched_section_container_post() {
		$parent_section = get_option( 'elementor_stretched_section_container', '' );
		if ( is_singular( 'post' ) ) {
			if ( '.jeg_main_content' !== $parent_section ) {
				update_option( 'elementor_stretched_section_container', '.jeg_main_content' );
			}
		} else {
			update_option( 'elementor_stretched_section_container', '.jeg_vc_content' );
		}
	}
}

/**
 * Social author for co author plus
 *
 */

if ( class_exists( 'CoAuthors_Plus' ) ) {
	foreach ( array_merge( jnews_get_author_social(), [ 'description' => 'description' ] ) as $social => $value ) {
		add_filter( "get_the_author_{$social}", function ( $value, $user_id, $original_user_id ) use ( $social ) {
			global $coauthors_plus;
			if ( $coauthors_plus->guest_authors->get_guest_author_by( 'ID', $original_user_id ) ) {
				$pm_key = $coauthors_plus->guest_authors->get_post_meta_key( $social );
				return get_post_meta( $original_user_id, $pm_key, true );
			}
			return $value;
		}, 10, 3 );
	}
}

/**
 * START RSS Element Section
 */

if ( ! function_exists( 'jnews_get_rss_post_id' ) ) {
	function jnews_get_rss_post_id( $post_id = 'rss_post' ) {
		$rss_post = 'rss_post';
		return $post_id === 'rss_post' ? $rss_post : $post_id === $rss_post;
	}
}

if ( ! function_exists( 'jnews_rss_comment' ) ) {
	function jnews_rss_comment( $comments_number, $post_id ) {
		if ( jnews_get_rss_post_id( $post_id ) ) {
			$comments_number = 0;
		}

		return $comments_number;
	}

	add_filter( 'jnews_get_comments_number', 'jnews_rss_comment', 10, 2 );
}

if ( ! function_exists( 'jnews_rss_view' ) ) {
	function jnews_rss_view( $view, $post_id ) {
		if ( jnews_get_rss_post_id( $post_id ) ) {
			$view = 0;
		}

		return $view;
	}

	add_filter( 'jnews_get_total_fake_view', 'jnews_rss_view', 10, 2 );
}

if ( ! function_exists( 'jnews_set_rss_trending' ) ) {
	function jnews_set_rss_trending( $value, $post_id, $key ) {
		return jnews_get_rss_post_id( $post_id ) && $key === 'jnews_single_post.trending_post' ? false : $value;
	}

	if ( defined( 'JNEWS_ESSENTIAL' ) ) {
		if ( version_compare( JNEWS_ESSENTIAL_VERSION, '9.0.0', '>=' ) ) {
			add_filter( 'jeg_vp_metabox', 'jnews_set_rss_trending', 10, 3 );
		}
	}
}

if ( ! function_exists( 'jnews_set_rss_review' ) ) {
	function jnews_set_rss_review( $value, $post_id, $key ) {
		return jnews_get_rss_post_id( $post_id ) && $key === 'jnews_review.enable_review' ? false : $value;
	}
	if ( defined( 'JNEWS_ESSENTIAL' ) ) {
		if ( version_compare( JNEWS_ESSENTIAL_VERSION, '9.0.0', '>=' ) ) {
			add_filter( 'jeg_vp_metabox', 'jnews_set_rss_review', 10, 3 );
		}
	}
}

/**
 * END RSS Element Section
 */

/**
 * START Disable Widgets Block Editor
 *
 * Note: Temporary fix for WP 5.8 pdate
 */
add_filter( 'gutenberg_use_widgets_block_editor', '__return_false' );
add_filter( 'use_widgets_block_editor', '__return_false' );

/**
 * END Disable Widgets Block Editor
 */

/**
 * START Override category post permalink
 */

 if ( ! function_exists( 'jnews_post_category_permalink' ) ) {
	function jnews_post_category_permalink( $cat, $cats, $post ) {
		if ( get_theme_mod( 'jnews_single_override_category_permalink', false ) && $term = jnews_get_primary_category( $post->ID ) ) {
			$cat = get_term( $term );
		}

		return $cat;
	}

	add_filter( 'post_link_category', 'jnews_post_category_permalink', 10, 3 );
}

/**
 * END Override category post permalink
 */

/**
 * START JNews Sponsor Meta
 */

if ( ! function_exists( 'jnews_sponsor_meta' ) ) {
	/**
	 * JNews Sponsor Meta.
	 *
	 * @param int $post_id Post ID.
	 */
	function jnews_sponsor_meta( $post_id ) {
		if ( isset( $_POST['jnews_single_post'] ) && isset( $_POST['jnews_single_post']['sponsored_post'] ) ) {
			update_post_meta( $post_id, 'jnews_sponsor_post', $_POST['jnews_single_post']['sponsored_post'] );
		}
	}

	add_action( 'edit_post_post', 'jnews_sponsor_meta' );
	add_action( 'save_post_post', 'jnews_sponsor_meta' );
}

/**
 * END JNews Sponsor Meta
 */

/**
 * START Comment Captcha
 */

if ( ! function_exists( 'jnews_comment_render_captcha' ) ) {
	/**
	 * Render Captcha on Comment Field
	 *
	 * @param array|string $default The default comment form arguments if {@see comment_form_defaults} and The content of the comment textarea field if {@see comment_form_field_comment}
	 *
	 * @return array|string
	 */
	function jnews_comment_render_captcha( $default ) {
		$is_login       = is_user_logged_in();
		$current_filter = current_filter();

		if ( ! $is_login && 'comment_form_defaults' === $current_filter ) {
			$default['fields']['jeg_captcha'] = \JNews\Captcha::getInstance()->render_element( 'comment', false );
		}
		if ( $is_login && 'comment_form_field_comment' === $current_filter ) {
			$default .= \JNews\Captcha::getInstance()->render_element( 'comment', false );
		}
		return $default;
	}
	add_filter( 'comment_form_defaults', 'jnews_comment_render_captcha' );
	add_filter( 'comment_form_field_comment', 'jnews_comment_render_captcha' );
}

if ( ! function_exists( 'jnews_comment_process_captcha' ) ) {
	/**
	 * Process Captcha
	 * 
	 * @param array $data Comment data.
	 * 
	 * @return array
	 */
	function jnews_comment_process_captcha( $data ) {
		\JNews\Captcha::getInstance()->check_recaptcha( true, 'comment' );
		return $data;
	}
	if ( ! is_admin() ) {
		add_filter( 'preprocess_comment', 'jnews_comment_process_captcha', 20 );
	}
}

/**
 * END Comment Captcha
 */

 /**
 * START filter Google+
 */
if ( ! function_exists( 'jnews_remove_google_plus' ) ) {

	/**
	 * Remove Google+
	 *
	 * @param array $buttons List of button.
	 *
	 * @return array
	 */
	function jnews_remove_google_plus( $buttons ) {
		foreach ( $buttons as $key => $button ) {
			if ( in_array( $button['social_share'], array( 'googleplus', 'google_plus', 'google-plus', 'google+' ), true ) ) {
				unset( $buttons[ $key ] );
			}
		}
		return $buttons;
	}

	add_filter( 'jnews_single_share_main_button_list', 'jnews_remove_google_plus' );
	add_filter( 'jnews_single_share_secondary_button_list', 'jnews_remove_google_plus' );
}
/**
 * END filter Google+
 */

/**
 * Return false to prevent admin acess filters from WooCommerce
 * See b9tnQg1W
 */

if ( class_exists( 'WooCommerce' ) ) {
	add_filter( 'woocommerce_prevent_admin_access', '__return_false' );
}

/**
 * End prevent admin acess filters from WooCommerce
 */
