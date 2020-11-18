<?php
/*
 * Plugin Name: Plugin Conflict Fixer
 * Version: 1.0.0
 * Description: Plugin that fixes the conflict between Groups and Screets Live Chat. Do not remove this plugin if you are using both Groups and Screets Live Chat, unless they make patches to fix this compatibility issue.
 * Author: Daniel Siyuan Zuo
 * Author URI: https://github.com/legenddaniel
 * Text Domain: plugin-conflict-fixer
 */

defined('ABSPATH') or exit;

/**
 * Manually require the neccessary function both plugins need
 * @return Null
 */
function sz_fix_conflict()
{
    if (function_exists('wp_get_current_user')) {
        return;
    }
    require_once ABSPATH . 'wp-includes/pluggable.php';
}

sz_fix_conflict();
