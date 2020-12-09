<?php

// Some VIP functionalities

/**
 * Renew VIP scheduled task
 * @param Integer $user
 * @param Integer $plan
 * @return Null
 */
function sz_renew_vip_in_db($user, $plan)
{
    $times = 0;
    switch ($plan) {
        case VIP_888_ANNUAL_ID:
            $times = VIP_888_QTY;
            break;
        default:
            $times = VIP_REG_QTY;
            break;
    }

    update_user_meta($user, 'VIP', $times);
}
add_action('sz_cron_vip', 'sz_renew_vip_in_db', 10, 2);

/**
 * @param WC_Memberships_Membership_Plan $membership_plan
 * @param Array $arguments {
 *@type int|string $user_id user ID for the membership
 *@type int|string $user_membership_id post ID for the new user membership
 *@type bool $is_update true if the membership is being updated, false if new
 * Daniel: $is_update is always tested true
 *}
 */
function sz_manage_vip_field_in_db($membership_plan, $arguments)
{
    $plans = [VIP_888_ANNUAL_ID, VIP_ANNUAL_ID, VIP_SEMIANNUAL_ID];
    $plan = $membership_plan->id;
    if (!in_array($plan, $plans)) {
        return;
    }

    $user = $arguments['user_id'];
    $user_plan = $arguments['user_membership_id'];
    $args = [$user, $plan];

    if (wc_memberships_is_user_active_member($user, $plan)) {

        $last_deactivation_timestamp = get_post_meta($user_plan, 'activation_end', true);

        // Set up Guest Pass field for VIP 888 in db. So far only set up this field when this user membership initialized
        if ($plan === VIP_888_ANNUAL_ID && !$last_deactivation_timestamp) {
            update_user_meta($user, 'Guest', GUEST_QTY);
        }

        if (!wp_next_scheduled('sz_cron_vip', $args)) {
            date_default_timezone_set('America/Toronto');
            wp_schedule_event(strtotime('next Monday'), 'weekly', 'sz_cron_vip', $args);
        }

        // Renew the VIP times only if it was not just deactivated in the same week. This prevents from maliciously resetting the VIP count by simply pausing + resuming VIP.
        if ($last_deactivation_timestamp && date('oW', $last_deactivation_timestamp) === date('oW', time())) {
            return;
        }

        sz_renew_vip_in_db($user, $plan);

    } else {

        // Remove the old CRON
        // remove_action('sz_cron_vip', 'sz_renew_vip_in_db');
        wp_unschedule_event(wp_next_scheduled('sz_cron_vip', $args), 'sz_cron_vip', $args);
        date_default_timezone_set('UTC');

        // Add/update activation_end meta key for the date validation above
        update_post_meta($user_plan, 'activation_end', time());

        // delete_user_meta($user, 'VIP');

    }
}
add_action('wc_memberships_user_membership_saved', 'sz_manage_vip_field_in_db', 10, 2);

/**
 * Do not grant membership access to purchasers if they already hold an active membership
 * @param Boolean $grant_access
 * @param Array $args {
 *      @type int $user_id
 *      @type int $product_id
 *      @type int $order_id
 * }
 * @return Boolean $grant_access
 */
function sz_allow_only_one_active_membership($grant_access, $args)
{
    if (!wc_memberships_get_user_active_memberships($args['user_id'])) {
        return false;
    }

    return $grant_access;
}
add_filter('wc_memberships_grant_access_from_new_purchase', 'sz_allow_only_one_active_membership', 10, 2);
