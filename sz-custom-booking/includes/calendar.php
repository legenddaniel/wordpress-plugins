<?php

/**
 * Sync unpaid orders to google calendar
 * @param array $statuses - Order statuses that should be synced
 * @return array
 */
function sz_sync_unpaid_order_to_google($statuses)
{
    $statuses[] = 'unpaid';
    return $statuses;
}
// add_filter('woocommerce_booking_is_paid_statuses', 'sz_sync_unpaid_order_to_google');
