<?php
/*
 * Plugin Name: HandyStore Bug Fix
 * Version: 1.0.0
 * Description: Plugin that fixes the unfunctioning of HandyStore theme
 * Author: Daniel Siyuan Zuo
 * Author URI: https://github.com/legenddaniel
 * Text Domain: handystore-bug-fix
 */

defined('ABSPATH') or exit;

if (!function_exists('handy_get_option')):
    function handy_get_option($name, $default = false)
{

        $option_name = '';

        // Gets option name as defined in the theme
        if (function_exists('optionsframework_option_name')) {
            $option_name = optionsframework_option_name();
        }

        // Fallback option name
        if ('' == $option_name) {
            $option_name = get_option('stylesheet');
            $option_name = preg_replace("/\W/", "_", strtolower($option_name));
        }

        // Get option settings from database
        $options = get_option($option_name);

        // Return specific option
        if (isset($options[$name])) {
            return $options[$name];
        }

        return $default;
    }
endif;

if (!function_exists('pt_show_layout')):
function pt_show_layout() {
	global $wp_query;

	/* Set the layout to default. */
	$layout = 'two-col-right';

	/* Get vendor store pages */
	$vendor_shop = '';
	if ( class_exists('WCV_Vendors') ) {
		$vendor_shop = urldecode( get_query_var( 'vendor_shop' ) );
	}

	/* Page variable */
	$current_page = '';
	// if front page
	if (is_page() && is_front_page()) {
		$current_page = 'front_page';
	}
	// if simple page
	elseif (is_page()) {
		$current_page = 'page';
	}
	// if single post
	elseif ( is_single() && !( class_exists('Woocommerce') && is_product() ) && !$vendor_shop ) {
		$current_page = 'single_post';
	}
	// if shop page
	elseif ( ( class_exists('Woocommerce') && is_shop() && !$vendor_shop ) ||
			 ( is_archive() && class_exists('Woocommerce') && is_shop() && !$vendor_shop ) ||
			 ( class_exists('Woocommerce') && is_product_category() ) ||
			 ( class_exists('Woocommerce') && is_product_tag() ) ||
			 ( class_exists('Woocommerce') && is_product_taxonomy() )
		   ) {
		$current_page = 'shop_page';
	}
	// if single product
	elseif ( ( class_exists('Woocommerce') && is_product() ) ||
			 ( is_singular() && class_exists('Woocommerce') && is_product() )
		   ) {
		$current_page = 'single_product';
	}
	// if vendor page
	elseif ( $vendor_shop && $vendor_shop !='' )
		     {
		$current_page = 'vendor_store';
	}
	// if blog pages (blog, archives, search, etc)
	elseif ( is_home() || is_category() || is_tag() || is_tax() || is_archive() || is_search() ) {
		$current_page = 'blog_page';
	}

	/* Get current layout */
	if ($wp_query->get_queried_object() instanceof WP_Term){
		$term_id = $wp_query->get_queried_object_id();
		$current_object_layout = pt_get_term_layout( $term_id );
	}
	else {
		$post_id = $wp_query->get_queried_object_id();
    $current_object_layout = pt_get_post_layout( $post_id );
	}

    /* Global layout options from admin panel */
    $global_shop_layout = (handy_get_option('shop_layout') != '') ? handy_get_option('shop_layout') : 'two-col-right';
    $global_product_layout = (handy_get_option('product_layout') != '') ? handy_get_option('product_layout') : 'two-col-right';
    $global_front_layout = (handy_get_option('front_layout') != '') ? handy_get_option('front_layout') : 'two-col-right';
    $global_page_layout = (handy_get_option('page_layout') != '') ? handy_get_option('page_layout') : 'two-col-right';
    $global_blog_layout = (handy_get_option('blog_layout') != '') ? handy_get_option('blog_layout') : 'two-col-right';
    $global_single_layout = (handy_get_option('single_layout') != '') ? handy_get_option('single_layout') : 'two-col-right';
    $global_vendor_layout = (handy_get_option('vendor_layout') != '') ? handy_get_option('vendor_layout') : 'two-col-right';

	switch ($current_page) {
		case 'front_page':
			if ( isset($global_front_layout) && $global_front_layout == $current_object_layout ) {
				$layout = $current_object_layout;
			} elseif ( isset($global_front_layout) && $current_object_layout === 'default' ) {
				$layout = $global_front_layout;
			} else {
				$layout = $current_object_layout;
			}
			break;
		case 'page':
			if ( isset($global_page_layout) && $global_page_layout == $current_object_layout ) {
				$layout = $current_object_layout;
			} elseif ( isset($global_page_layout) && $current_object_layout === 'default' ) {
				$layout = $global_page_layout;
			} else {
				$layout = $current_object_layout;
			}
			break;
		case 'single_post':
			$layout = $global_single_layout;
			break;
		case 'single_product':
			$layout = $global_product_layout;
			break;
		case 'vendor_store':
			$layout = $global_vendor_layout;
			break;
		case 'shop_page':
			if ( isset($global_shop_layout) && $global_shop_layout == $current_object_layout ) {
				$layout = $current_object_layout;
			} elseif ( isset($global_shop_layout) && $current_object_layout === 'default' ) {
				$layout = $global_shop_layout;
			} else {
				$layout = $current_object_layout;
			}
			break;
		case 'blog_page':
			if ( isset($global_blog_layout) && $global_blog_layout == $current_object_layout ) {
				$layout = $current_object_layout;
			} elseif ( isset($global_blog_layout) && $current_object_layout === 'default' ) {
				$layout = $global_blog_layout;
			} else {
				$layout = $current_object_layout;
			}
			break;
		default:
			$layout = $current_object_layout;
			if ($current_object_layout === 'default') {
				$layout = 'one-col';
			}
	}

	/* Return the layout and allow plugin/theme developers to override it. */
	return esc_attr( apply_filters( 'get_theme_layout', "layout-{$layout}" ) );
}
endif;

if (!function_exists('pt_get_post_layout')):
function pt_get_post_layout( $post_id ) {
	/* Get the post layout. */
	$layout = get_post_meta( $post_id, '-pt-layout', true );
	/* Return the layout if one is found.  Otherwise, return 'default'. */
	return ( !empty( $layout ) ? $layout : 'default' );
		return $layout;
}
endif;