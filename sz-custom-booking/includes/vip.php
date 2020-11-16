<?php

// VIP cron

/**
 * Renew VIP scheduled task
 * @param Integer $user
 * @return Null
 */
function sz_renew_vip_in_db($user)
{
    update_user_meta($user, 'VIP', 2);
}
add_action('sz_cron_vip', 'sz_renew_vip_in_db');

/**
 * VIP scheduled task
 * @param Mixed $user_membership
 * @param String $old_status
 * @param String $new_status
 * @return Null
 */
function manage_vip_field_in_db($user_membership, $old_status, $new_status)
{
    $plan = $user_membership->get_plan_id();
    if ($plan != VIP_ANNUAL_ID && $plan != VIP_SEMIANNUAL_ID) {
        return;
    }

    $user = $user_membership->get_user_id();
    $user_plan = $user_membership->get_id();
    $args = [$user];

    if (wc_memberships_is_user_active_member($user, $plan)) {

        $user = get_current_user_id();

        if (!wp_next_scheduled('sz_cron_vip', $args)) {
            date_default_timezone_set('America/Toronto');
            wp_schedule_event(strtotime('next Monday'), 'weekly', 'sz_cron_vip', $args);
        }

        // Renew the VIP times only if it was not just deactivated in the same week. This prevents from maliciously resetting the VIP count by simply pausing + resuming VIP.
        $last_deactivation_timestamp = get_post_meta($user_plan, 'activation_end', true);
        if ($last_deactivation_timestamp && date('oW', $last_deactivation_timestamp) === date('oW', time())) {
            return;
        }

        sz_renew_vip_in_db($user);

    } else {

        //remove_action('sz_cron_vip', 'sz_renew_vip_in_db');

        wp_unschedule_event(wp_next_scheduled('sz_cron_vip', $args), 'sz_cron_vip', $args);

        date_default_timezone_set('UTC');

        // Add/update activation_end meta key for the date validation above
        update_post_meta($user_plan, 'activation_end', time());

        // delete_user_meta($user, 'VIP');

    }
}
add_action('wc_memberships_user_membership_status_changed', 'manage_vip_field_in_db', 10, 3);
