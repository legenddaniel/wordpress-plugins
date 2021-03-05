<?php

if (!defined('ABSPATH')) {
    exit;
}

class New_Point_Account extends New_Point
{
    public function __construct()
    {

        // Add and display custom new points content in My Account
        add_action('init', array($this, 'add_points_endpoint'));
        add_filter('query_vars', array($this, 'add_points_query_vars'));
        add_filter('woocommerce_account_menu_items', array($this, 'display_endpoint_in_account'));
        add_action('woocommerce_account_new-points_endpoint', array($this, 'display_endpoint_content'));
    }

    /**
     * Add custom endpoint
     * @return void
     */
    public function add_points_endpoint()
    {
        add_rewrite_endpoint('new-points', EP_ROOT | EP_PAGES);
    }

    /**
     * Add new query var
     * @param array $vars
     * @return array
     */
    public function add_points_query_vars($vars)
    {
        $vars[] = 'new-points';
        return $vars;
    }

    /**
     * Display new-points tab in My Account
     * @param array
     * @return array
     */
    public function display_endpoint_in_account($items)
    {
        $items['new-points'] = 'Point Earned Ratio';
        // $items['new-points'] = 'Total Expense & Point Earned Ratio';
        return $items;
    }

    /**
     * Display html content for new-points endpoint in My Account
     * @return void
     */
    public function display_endpoint_content()
    {
        $user = get_current_user_id();
        $total_amount = $this->get_total_amount($user);
        $ratio = $this->process_ratio($this->get_ratio($total_amount));

        ?>
            <h2>Point Earned Ratio</h2>
            <!-- <h2>Total Expense & Point Earned Ratio</h2> -->
        <?php

        if (!$ratio) {
            ?>
                <p style="font-size: 2rem; margin-bottom: 0;">You are one step to our rewards!</p>
                <p>Purchase anything and start earning points!</p>
                <p>Some introduction here or another page to introduce</p>
            <?php
            return;
        }

        $member = '';
        switch (+$ratio) {
            case 2:
                $member = 'Platinum';
                break;
            case 1.5:
                $member = 'Gold';
                break;
            default:
                $member = 'Silver';
                break;
        }

        if (false):
        ?>
            <p style="font-size: 2rem">As a <strong><?=esc_html__($member);?></strong> member, you have spent <strong>USD$<?=esc_html__($total_amount);?></strong> on Moditec</p>
            <p style="font-size: 2rem"><strong><?=esc_html__($ratio);?></strong> <?= $ratio == 1 ? 'Point is' : 'Points are';?> earned for every USD$1 you spend</p>
            <p>Some introduction here or another page to introduce</p>
        <?php
        endif;
        ?>
            <p style="font-size: 2rem">As a <strong><?=esc_html__($member);?></strong> member, <strong><?=esc_html__($ratio);?></strong> <?= $ratio == 1 ? 'Point is' : 'Points are';?> earned for every USD$1 you spend</p>
        <?php
    }

}
