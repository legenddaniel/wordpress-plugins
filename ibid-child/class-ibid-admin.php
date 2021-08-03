<?php

defined('ABSPATH') || exit;

class Ibid_Auction_Admin
{
    private $tab = 'custom-auction';

    private $contract_amount = 3;

    private $point_min = 0;
    private $point_max = 100;

    public function __construct()
    {
        add_action('admin_enqueue_scripts', [$this, 'init_assets']);

        add_action('show_user_profile', [$this, 'add_contract_config']);
        add_action('edit_user_profile', [$this, 'add_contract_config']);
        add_action('personal_options_update', [$this, 'save_contract_config']);
        add_action('edit_user_profile_update', [$this, 'save_contract_config']);

        add_filter('woocommerce_settings_tabs_array', [$this, 'add_woocommerce_settings_tab']);
        add_action('woocommerce_settings_tabs_' . $this->tab, [$this, 'add_woocommerce_settings_content']);
        add_action('woocommerce_update_options' . $this->tab, [$this, 'update_woocommerce_settings_content']);
    }

    public function init_assets()
    {
        wp_enqueue_style(
            'admin-style',
            get_stylesheet_directory_uri() . '/admin-style.css',
            array(),
            wp_get_theme()->parent()->get('Version')
        );

        // For table row add-delete in WooCommerce->Settings->Custom Auction
        if (is_admin() && sanitize_title_for_query($_GET['page']) === 'wc-settings' && sanitize_title_for_query($_GET['tab']) === $this->tab) {
            $sig = 'points-setting';
            wp_enqueue_script(
                $sig,
                get_stylesheet_directory_uri() . "/$sig.js"
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
     * Save the contract field in db
     * @param int $user_id
     */
    public function save_contract_config($user_id)
    {
        update_user_meta(
            $user_id,
            'sz_wc_authorize_net_cim_contract_type',
            sanitize_text_field($_POST['auction-contract'])
        );
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
        woocommerce_admin_fields($this->create_woocommerce_settings_content());

        ?>
    <table class="wc_input_table widefat buyer" data-min="<?=esc_attr__($this->point_min)?>" data-max="<?=esc_attr__($this->point_max)?>">
	    <thead>
		    <tr>
                <th>Points</th>
                <th>Service Fee (% on the final bidding price)</th>
		    </tr>
	    </thead>
        <tbody>
<?php
$buyer_service_fee = get_option($this->tab . '_buyer') ?: [['point' => null, 'fee' => null]];
        $l = count($buyer_service_fee);
        for ($i = 0; $i < $l; $i++) {
            ?>
            <tr>
                <td class="auction-points">
                    <span id="point-min-<?=esc_attr__($i)?>"><?=esc_html__(isset($buyer_service_fee[$i + 1]) ? $buyer_service_fee[$i + 1]['point'] : $this->point_min)?></span>
                    <span>-</span>
                    <input type="number" min="<?=esc_attr__($this->point_min)?>" max="<?=esc_attr__($this->point_max)?>" step="1" value="<?=esc_html__($buyer_service_fee[$i]['point'] ?? $this->point_max)?>" id="point-max-<?=esc_attr__($i)?>" />
                </td>
                <td>
                    <input type="number" min="0" step="1" value="<?=esc_html__($buyer_service_fee[$i]['fee'])?>" id="fee-<?=esc_attr__($i)?>"/>
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
                    <button type="button" class="button minus"><?php _e('Remove selected row(s)', 'woocommerce');?></button>
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
        woocommerce_update_options($this->create_woocommerce_settings_content());
    }

    /**
     * Content template
     * @return array
     */
    private function create_woocommerce_settings_content()
    {
        $settings = array(
            'section_title-seller' => array(
                'name' => __('Auction Service Fee Settings - Seller', 'woocommerce'),
                'type' => 'title',
            ),
        );
        for ($i = 1; $i <= $this->contract_amount; $i++) {
            $settings = array_merge(
                $settings,
                [
                    'section_subtitle-' . $i => array(
                        'name' => 'Contract ' . $i,
                        'type' => 'title',
                    ),
                    'title-' . $i => array(
                        'name' => 'Title',
                        'type' => 'text',
                        'desc' => 'This is some helper text',
                        'id' => $this->tab . '-title-' . $i,
                    ),
                    'description-' . $i => array(
                        'name' => __('Description', 'woocommerce'),
                        'type' => 'text',
                        'desc' => __('This is some helper text', 'woocommerce'),
                        'id' => $this->tab . '_description-' . $i,
                    ),
                    'section_end-' . $i => array(
                        'type' => 'sectionend',
                    ),
                ]
            );
        }

        $settings['section_title-buyer'] = array(
            'name' => __('Auction Service Fee Settings - Buyer', 'woocommerce'),
            'type' => 'title',
        );

        return $settings;
    }
}
