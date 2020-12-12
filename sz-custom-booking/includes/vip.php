<?php

// Some VIP functionalities

/**
 * Renew VIP weekly scheduled task (reset discount)
 * @param int $user
 * @param int $plan
 * @return null
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
 * @param array $arguments {
 *@type int|string $user_id user ID for the membership
 *@type int|string $user_membership_id post ID for the new user membership
 *@type bool $is_update true if the membership is being updated, false if new
 *}
 */
function sz_manage_vip_field_in_db_saved($membership_plan, $arguments)
{
    // These codes are valid for all cases, except for renewing membership in the front end. Use sz_manage_vip_field_in_db_accessed defined next for this special case.

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

        // Set up Guest Pass field for VIP 888 in db at first purchase and renewing (no manual deactivation detected).
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

        if ((new WC_Memberships_User_Membership($user_plan))->get_status() === 'expired') {

            // Clear reset restriction when expired
            delete_post_meta($user_plan, 'activation_end');
        } else {

            // Add/update activation_end meta key for the date validation above
            update_post_meta($user_plan, 'activation_end', time());

            // delete_user_meta($user, 'VIP');
        }

    }
}
add_action('wc_memberships_user_membership_saved', 'sz_manage_vip_field_in_db_saved', 10, 2);

/**
 * @param WC_Memberships_Membership_Plan $membership_plan
 * @param array $args {
 *@type int|string $user_id user ID for order
 *@type int|string $product_id product ID that grants access
 *@type int|string $order_id order ID
 *@type int|string $user_membership_id post ID for the new user membership
 *}
 */
function sz_manage_vip_field_in_db_accessed($membership_plan, $arguments)
{
    // For the case of membership purchase in the front end (new or renewed)

    $plans = [VIP_888_ANNUAL_ID, VIP_ANNUAL_ID, VIP_SEMIANNUAL_ID];
    $plan = $membership_plan->id;
    if (!in_array($plan, $plans)) {
        return;
    }

    $user = $arguments['user_id'];
    $user_plan = $arguments['user_membership_id'];
    $args = [$user, $plan];

    // Set up Guest Pass field for VIP 888 in db at first purchase and renewing.
    if ($plan === VIP_888_ANNUAL_ID) {
        update_user_meta($user, 'Guest', GUEST_QTY);
    }

    if (!wp_next_scheduled('sz_cron_vip', $args)) {
        date_default_timezone_set('America/Toronto');
        wp_schedule_event(strtotime('next Monday'), 'weekly', 'sz_cron_vip', $args);
    }

    sz_renew_vip_in_db($user, $plan);

}
add_action('wc_memberships_grant_membership_access_from_purchase', 'sz_manage_vip_field_in_db_accessed', 10, 2);

/**
 * Do not grant membership access to purchasers if they already hold an active membership
 * @param bool $grant_access
 * @param array $args {
 *      @type int $user_id
 *      @type int $product_id
 *      @type int $order_id
 * }
 * @return bool $grant_access
 */
function sz_allow_only_one_active_membership($grant_access, $args)
{
    if (wc_memberships_get_user_active_memberships($args['user_id'])) {
        return false;
    }

    return $grant_access;
}
add_filter('wc_memberships_grant_access_from_new_purchase', 'sz_allow_only_one_active_membership', 10, 2);

/**
 * Add purchase restriction when purchasing membership
 * @param array $passed
 * @param int $product_id
 * @param int $quantity
 * @return bool
 */
function sz_add_membership_purchase_restriction($passed, $product_id, $quantity)
{
    if ($product_id == VIP_PURCHASE_ID) {

        // Block purchase if holding an active membership
        $membership = wc_memberships_get_user_active_memberships()[0];
        if (!is_null($membership)) {
            $plan_name = $membership->get_plan()->get_name();
            wc_add_notice(__("You are holding an active membership: $plan_name. Only one active membership allowed.", 'woocommerce'), 'error');
            return false;
        }

        // Allow only one membership per purchase
        foreach (sz_get_cart() as $cart_item) {
            if ($cart_item['product_id'] === VIP_PURCHASE_ID) {
                wc_add_notice(__("Cannot purchase multiple memberships at the same time!"), 'error');
                return false;
            }
        }
    }

    return $passed;
}
add_filter('woocommerce_add_to_cart_validation', 'sz_add_membership_purchase_restriction', 10, 3);
