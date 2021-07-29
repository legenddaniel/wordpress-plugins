<?php

defined('ABSPATH') || exit;

class Ibid_Auction_Admin
{
    private $tab = 'auction-contract';

    public function __construct()
    {
        add_action('show_user_profile', [$this, 'add_contract_config']);
        add_action('edit_user_profile', [$this, 'add_contract_config']);
        add_action('personal_options_update', [$this, 'save_contract_config']);
        add_action('edit_user_profile_update', [$this, 'save_contract_config']);

        add_filter('woocommerce_settings_tabs_array', [$this, 'add_woocommerce_settings_tab']);
        add_action('woocommerce_settings_tabs_' . $this->tab, [$this, 'add_woocommerce_settings_content']);
        add_action('woocommerce_update_options_' . $this->tab, [$this, 'update_woocommerce_settings_content']);
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
        $tabs[$this->tab] = 'Auction Contract';
        return $tabs;
    }

    /**
     * Add the content
     */
    public function add_woocommerce_settings_content()
    {
        woocommerce_admin_fields($this->create_woocommerce_settings_content());
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
     * @return string
     */
    private function create_woocommerce_settings_content()
    {
        $settings = array(
            'section_title' => array(
                'name' => __('Section Title', 'woocommerce-settings-tab-demo'),
                'type' => 'title',
                'desc' => '',
                'id' => 'wc_settings_tab_demo_section_title',
            ),
            'title' => array(
                'name' => __('Title', 'woocommerce-settings-tab-demo'),
                'type' => 'text',
                'desc' => __('This is some helper text', 'woocommerce-settings-tab-demo'),
                'id' => 'wc_settings_tab_demo_title',
            ),
            'description' => array(
                'name' => __('Description', 'woocommerce-settings-tab-demo'),
                'type' => 'textarea',
                'desc' => __('This is a paragraph describing the setting. Lorem ipsum yadda yadda yadda. Lorem ipsum yadda yadda yadda. Lorem ipsum yadda yadda yadda. Lorem ipsum yadda yadda yadda.', 'woocommerce-settings-tab-demo'),
                'id' => 'wc_settings_tab_demo_description',
            ),
            'section_end' => array(
                'type' => 'sectionend',
                'id' => 'wc_settings_tab_demo_section_end',
            ),
        );

        return apply_filters('wc_' . $this->tab . '_settings', $settings);
    }
}
