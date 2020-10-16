<?php

/**
 * The Header for our theme.
 */

// Theme init - don't remove next row! Load custom options

kingler_theme_core_init_theme();
kingler_theme_profiler_add_point(esc_html__('Before Theme HTML output', 'kingler-theme'));

$theme_skin = sanitize_file_name(kingler_theme_get_custom_option('theme_skin'));
$body_scheme = kingler_theme_get_custom_option('body_scheme');

if (
    empty($body_scheme)  ||
    kingler_theme_is_inherit_option($body_scheme)
) {
    $body_scheme = 'original';
}

$body_style  = kingler_theme_get_custom_option('body_style');
$top_panel_style = kingler_theme_get_custom_option('top_panel_style');
$top_panel_position = kingler_theme_get_custom_option('top_panel_position');
$top_panel_scheme = kingler_theme_get_custom_option('top_panel_scheme');

?>
<!DOCTYPE html>

<html <?php language_attributes(); ?> class="<?php echo 'scheme_' . esc_attr($body_scheme); ?>">

<head>
	<?php wp_head(); ?>
</head>

<body <?php body_class();?>>

	<?php
    kingler_theme_profiler_add_point(esc_html__('BODY start', 'kingler-theme'));
    kingler_theme_show_layout(kingler_theme_get_custom_option('gtm_code'));

    if (($preloader=kingler_theme_get_theme_option('page_preloader'))!='') {
        $clr = kingler_theme_get_scheme_color('bg_color');
    }

    // Page preloader
    if ($preloader!='') {
        ?>
	<div id="page_preloader"></div>
	<?php
    }
    do_action('before');
    
    // Add TOC items 'Home' and "To top"
    get_template_part(kingler_theme_get_file_slug('templates/_parts/menu-toc.php'));
        
    $class = $style = '';

    if (kingler_theme_get_custom_option('bg_custom')=='yes' && ($body_style=='boxed' || kingler_theme_get_custom_option('bg_image_load')=='always')) {
        if (($img = kingler_theme_get_custom_option('bg_image_custom')) != '') {
            $style = 'background: url('.esc_url($img).') ' . str_replace('_', ' ', kingler_theme_get_custom_option('bg_image_custom_position')) . ' no-repeat fixed;';
        } elseif (($img = kingler_theme_get_custom_option('bg_pattern_custom')) != '') {
            $style = 'background: url('.esc_url($img).') 0 0 repeat fixed;';
        } elseif (($img = kingler_theme_get_custom_option('bg_image')) > 0) {
            $class = 'bg_image_'.($img);
        } elseif (($img = kingler_theme_get_custom_option('bg_pattern')) > 0) {
            $class = 'bg_pattern_'.($img);
        }

        if (($img = kingler_theme_get_custom_option('bg_color')) != '') {
            $style .= 'background-color: '.($img).';';
        }

        $class .= (kingler_theme_get_custom_option('bg_image_custom') != '' ||  kingler_theme_get_custom_option('bg_pattern_custom') != '' ? 'bg_image' : '');
    }
    ?>

	<div class="body_wrap<?php echo !empty($class) ? ' '.esc_attr($class) : ''; ?>"
		<?php echo !empty($style) ? ' style="'.esc_attr($style).'"' : ''; ?>>

		<div class="page_wrap">
			<?php

            kingler_theme_profiler_add_point(esc_html__('Before Page Header', 'kingler-theme'));

            // Top panel 'Above' or 'Over'

            if (in_array($top_panel_position, array('above', 'over'))) {
                kingler_theme_show_post_layout(
                    array(
                    'layout' => $top_panel_style,
                    'position' => $top_panel_position,
                    'scheme' => $top_panel_scheme
                    ),
                    false
                );
                kingler_theme_profiler_add_point(esc_html__('After show menu', 'kingler-theme'));
            }

            // Mobile Menu
            get_template_part(kingler_theme_get_file_slug('templates/headers/_parts/header-mobile.php'));

            // Slider
            get_template_part(kingler_theme_get_file_slug('templates/headers/_parts/slider.php'));

            // Top panel 'Below'
            if ($top_panel_position == 'below') {
                kingler_theme_show_post_layout(
                    array(
                    'layout' => $top_panel_style,
                    'position' => $top_panel_position,
                    'scheme' => $top_panel_scheme
                    ),
                    false
                );
                kingler_theme_profiler_add_point(esc_html__('After show menu', 'kingler-theme'));
            }

             // Top of page section: page title and breadcrumbs
            get_template_part(kingler_theme_get_file_slug('templates/headers/_parts/breadcrumbs.php'));

            ?>

			<div
				class="page_content_wrap page_paddings_<?php echo esc_attr(kingler_theme_get_custom_option('body_paddings')); ?>">

				<?php

                kingler_theme_profiler_add_point(esc_html__('Before Page content', 'kingler-theme'));

                // Content and sidebar wrapper
                if ($body_style!='fullscreen') {
                    kingler_theme_open_wrapper('<div class="content_wrap">');
                }

                // Main content wrapper
                kingler_theme_open_wrapper('<div class="content">');
