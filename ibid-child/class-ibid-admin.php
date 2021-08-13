<?php

defined('ABSPATH') || exit;

class Ibid_Auction_Admin
{
    private $tab = 'custom-auction';
    private $self_service_fee = 'auction_self_service_fee';
    private $service_fee_delegation_buyer = 'auction_service_fee_delegation_buyer';
    private $service_fee_delegation_seller = 'auction_service_fee_delegation_seller';

    public function __construct()
    {
        add_action('admin_enqueue_scripts', [$this, 'init_assets']);

        add_action('show_user_profile', [$this, 'add_contract_config']);
        add_action('edit_user_profile', [$this, 'add_contract_config']);

        add_filter('woocommerce_settings_tabs_array', [$this, 'add_woocommerce_settings_tab']);
        add_action('woocommerce_settings_tabs_' . $this->tab, [$this, 'add_woocommerce_settings_content']);
        add_action('woocommerce_update_options_' . $this->tab, [$this, 'update_woocommerce_settings_content']);

        add_action('woocommerce_product_options_auction', [$this, 'add_service_fee_delegation_option']);
        add_action('woocommerce_process_product_meta', [$this, 'save_service_fee_delegation_option'], 10, 2);
    }

    public function init_assets()
    {
        wp_enqueue_style(
            'admin-style',
            get_stylesheet_directory_uri() . '/admin-style.css',
            array(),
            wp_get_theme()->parent()->get('Version')
        );

        global $pagenow;

        // For table row add-delete in WooCommerce->Settings->Custom Auction
        if (
            $pagenow === 'admin.php' &&
            isset($_GET['page']) &&
            sanitize_title_for_query($_GET['page']) === 'wc-settings' &&
            isset($_GET['tab']) &&
            sanitize_title_for_query($_GET['tab']) === $this->tab
        ) {
            wp_enqueue_script(
                'points-setting',
                get_stylesheet_directory_uri() . "/points-setting.js"
            );
        }

        // For service fee fields display in product editing page
        if (
            in_array($pagenow, ['post.php', 'post-new.php']) &&
            isset($_GET['action']) &&
            sanitize_title_for_query($_GET['action']) === 'edit'
        ) {
            wp_enqueue_script(
                'service-fee-field',
                get_stylesheet_directory_uri() . "/service-fee-field.js"
            );
        }
    }

    /**
     * Display user contract type selection in user edit page
     * @param WP_User $profileuser
     */
    public function add_contract_config($profileuser)
    {
        ?>
<h2>Auction Contract</h2>
<table class="form-table" role="presentation">
	<tbody>
        <tr class="user-contract-wrap">
		    <th>
                <label for="auction-contract">Contract Type <span class="description">(required)</span></label>
            </th>
		    <td>
                <select name="auction-contract" id="auction-contract" required>
                    <option value="1">Contract 1</option>
                    <option value="2">Contract 2</option>
                    <option value="3">Contract 3</option>
                </select>
            </td>
	    </tr>
	</tbody>
</table>
        <?php
}

    /**
     * Add a tab to woocommerce settings
     * @param array $tabs
     * @return array
     */
    public function add_woocommerce_settings_tab($tabs)
    {
        $tabs[$this->tab] = 'Custom Auction';
        return $tabs;
    }

    /**
     * Add the content
     */
    public function add_woocommerce_settings_content()
    {
        // woocommerce_admin_fields($this->create_woocommerce_settings_content());

        ?>
    <table class="wc_input_table widefat buyer" data-min="<?=esc_attr__(POINT_MIN)?>" data-max="<?=esc_attr__(POINT_MAX)?>">
	    <thead>
		    <tr>
                <th>Points</th>
                <th>Service Fee (%)</th>
		    </tr>
	    </thead>
        <tbody>
<?php
$buyer_service_fee = get_option($this->self_service_fee) ?: [['point' => null, 'fee' => null]];
        $l = count($buyer_service_fee);
        for ($i = 0; $i < $l; $i++) {
            ?>
            <tr>
                <td class="auction-points">
                    <span id="point-min-<?=esc_attr__($i)?>"><?=esc_html__(isset($buyer_service_fee[$i + 1]) ? $buyer_service_fee[$i + 1]['point'] + 1 : POINT_MIN)?></span>
                    <span>-</span>
                    <input type="number" step="1" value="<?=esc_html__($buyer_service_fee[$i]['point'] ?? POINT_MAX)?>" id="point-max-<?=esc_attr__($i)?>" name="point-max-<?=esc_attr__($i)?>"/>
                </td>
                <td>
                    <input type="number" min="0" step="1" value="<?=esc_html__($buyer_service_fee[$i]['fee'])?>" id="fee-<?=esc_attr__($i)?>" name="fee-<?=esc_attr__($i)?>" />
                </td>
            </tr>
<?php
}
        ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="9">
                    <button type="button" class="button plus"><?php _e('Add new row', 'woocommerce');?></button>
                    <button type="button" class="button minus"><?php _e('Remove last row', 'woocommerce');?></button>
                    <button type="button" class="button reset"><?php _e('Reset table', 'woocommerce');?></button>
                </th>
            </tr>
        </tfoot>
    </table>
        <?php
}

    /**
     * Save the content
     */
    public function update_woocommerce_settings_content()
    {
        // woocommerce_update_options($this->create_woocommerce_settings_content());

        $options = [];
        foreach ($_POST as $k => $v) {
            if (strpos($k, 'point-max-') === 0) {
                $index = preg_replace('/.*-(\d+)$/', '$1', $k);
                $options[$index]['point'] = sanitize_text_field($v);
            } else if (strpos($k, 'fee-') === 0) {
                $index = preg_replace('/.*-(\d+)$/', '$1', $k);
                $options[$index]['fee'] = sanitize_text_field($v);
            }
        }
        update_option(sanitize_text_field($this->self_service_fee), $options);
    }

    /**
     * Content template
     * @return array
     */
    // private function create_woocommerce_settings_content()
    // {
    //     $settings = array(
    //         'section_title-seller' => array(
    //             'name' => __('Auction Service Fee Settings - Seller', 'woocommerce'),
    //             'type' => 'title',
    //         ),
    //     );
    //     for ($i = 1; $i <= CONTRACT_AMOUNT; $i++) {
    //         $settings = array_merge(
    //             $settings,
    //             [
    //                 'section_subtitle-' . $i => array(
    //                     'name' => 'Contract ' . $i,
    //                     'type' => 'title',
    //                 ),
    //                 'title-' . $i => array(
    //                     'name' => 'Title',
    //                     'type' => 'text',
    //                     'desc' => 'This is some helper text',
    //                     'id' => $this->tab . '-title-' . $i,
    //                 ),
    //                 'description-' . $i => array(
    //                     'name' => __('Description', 'woocommerce'),
    //                     'type' => 'text',
    //                     'desc' => __('This is some helper text', 'woocommerce'),
    //                     'id' => $this->tab . '-description-' . $i,
    //                 ),
    //                 'section_end-' . $i => array(
    //                     'type' => 'sectionend',
    //                 ),
    //             ]
    //         );
    //     }

    //     $settings['section_title-buyer'] = array(
    //         'name' => __('Auction Service Fee Settings - Buyer', 'woocommerce'),
    //         'type' => 'title',
    //     );

    //     return $settings;
    // }

    /**
     * Max service fee setting in Woocommerce product editing page
     */
    public function add_service_fee_delegation_option()
    {
        global $post;
        $buyer = get_post_meta($post->ID, $this->service_fee_delegation_buyer, true) ?: ['type' => '', 'fee' => '', 'max' => ''];
        $seller = get_post_meta($post->ID, $this->service_fee_delegation_seller, true) ?: ['type' => '', 'fee' => '', 'threshold' => ''];

        // Display is controlled by `service-fee-field.js`
        echo '<h3 class="auction-product-edit-title">Service Fee - Buyer</h3>';
        woocommerce_wp_select(
            array(
                'id' => '_auction_service_fee_delegation_buyer_type',
                'label' => __('Service Fee Type', 'woocommerce'),
                'value' => esc_attr__($buyer['type'], 'wc_simple_auction'),
                'options' => [
                    'percentage' => 'Percentage',
                    'percentage_max' => 'Percentage with Max',
                    'fixed' => 'Fixed',
                ],
            )
        );
        woocommerce_wp_text_input(
            array(
                'id' => '_auction_service_fee_delegation_buyer',
                'label' => __('Service Fee', 'wc_simple_auctions'),
                'value' => esc_attr__($buyer['fee'], 'wc_simple_auction'),
            )
        );
        woocommerce_wp_text_input(
            array(
                'id' => '_auction_service_fee_delegation_buyer_max',
                'label' => __('Maximum', 'wc_simple_auctions'),
                'value' => esc_attr__($buyer['max'], 'wc_simple_auction'),
            )
        );
        echo '<h3 class="auction-product-edit-title">Service Fee - Seller</h3>';
        woocommerce_wp_select(
            array(
                'id' => '_auction_service_fee_delegation_seller_type',
                'label' => __('Service Fee Type', 'woocommerce'),
                'value' => esc_attr__($seller['type'], 'wc_simple_auction'),
                'options' => [
                    'percentage' => 'Percentage',
                    'fixed' => 'Fixed',
                ],
            )
        );
        woocommerce_wp_text_input(
            array(
                'id' => '_auction_service_fee_delegation_seller',
                'label' => __('Service Fee', 'wc_simple_auctions'),
                'value' => esc_attr__($seller['fee'], 'wc_simple_auction'),
            )
        );
        woocommerce_wp_text_input(
            array(
                'id' => '_auction_service_fee_delegation_seller_threshold',
                'label' => __('Threshold Upon Minimum Fee', 'wc_simple_auctions'),
                'value' => esc_attr__($seller['threshold'], 'wc_simple_auction'),
                'desc_tip' => true,
                'description' => __('Service fee will be $50 + contract fee + tax ($71) if the total sale is less or equal than this input value', 'woocommerce'),
            )
        );
    }

    /**
     * Save service fee for delegation auction in product edit page
     * @param int $id
     * @param WC_Product $product
     */
    public function save_service_fee_delegation_option($id, $product)
    {
        if (!isset($_POST['product-type']) || sanitize_text_field($_POST['product-type']) !== 'auction') {
            return;
        }

        $type = sanitize_text_field($_POST['_auction_service_fee_delegation_buyer_type']);
        update_post_meta(
            $id,
            $this->service_fee_delegation_buyer,
            [
                'type' => $type,
                'fee' => sanitize_text_field($_POST['_auction_service_fee_delegation_buyer']),
                'max' => $type === 'percentage_max' ? sanitize_text_field($_POST['_auction_service_fee_delegation_buyer_max']) : null,
            ]
        );

        update_post_meta(
            $id,
            $this->service_fee_delegation_seller,
            [
                'type' => sanitize_text_field($_POST['_auction_service_fee_delegation_seller_type']),
                'fee' => sanitize_text_field($_POST['_auction_service_fee_delegation_seller']),
                'threshold' => sanitize_text_field($_POST['_auction_service_fee_delegation_seller_threshold']),
            ]
        );
    }
}
