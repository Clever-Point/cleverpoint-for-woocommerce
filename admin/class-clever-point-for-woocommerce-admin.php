<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://cleverpoint.gr/
 * @since      1.0.0
 *
 * @package    Clever_Point_For_Woocommerce
 * @subpackage Clever_Point_For_Woocommerce/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Clever_Point_For_Woocommerce
 * @subpackage Clever_Point_For_Woocommerce/admin
 * @author     Clever Point <info@cleverpoint.gr>
 */
class Clever_Point_For_Woocommerce_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Clever_Point_For_Woocommerce_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Clever_Point_For_Woocommerce_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/clever-point-for-woocommerce-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Clever_Point_For_Woocommerce_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Clever_Point_For_Woocommerce_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
        wp_enqueue_script('selectWoo');
        wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/clever-point-for-woocommerce-admin.js', array( 'jquery','selectWoo' ), $this->version, false );
        wp_enqueue_script($this->plugin_name);
        wp_localize_script($this->plugin_name, 'clever_point_ajax_object', array('ajax_url' => admin_url('admin-ajax.php'),'lang_include'=>__("Include",$this->plugin_name),'lang_exclude'=>__("Exclude",$this->plugin_name)));
	}

    public function clever_point_orders_metabox($post_type, $post) {
        if ($post_type !== "shop_order") return;

        $order = wc_get_order($post->ID);
        $shipping_methods=$order->get_shipping_methods();
        $shipping_method = array_shift($shipping_methods);
        $shipping_method_id = $shipping_method['method_id'];

        if ($shipping_method_id!=='clever_point_shipping_class') {
            return;
        }

        if (get_option('clever_point_voucher_management','woocommerce')!="woocommerce")
            return;

        add_meta_box(
            'clever-point-metabox',
            __('Clever Point', $this->plugin_name),
            [$this, 'clever_point_orders_metabox_content'],
            'shop_order', 'side', 'core'
        );
    }

    function clever_point_add_section( $sections ) {
        $sections['clevepoint'] = __( 'Clever Point', 'clever-point-for-woocommerce' );
        return $sections;
    }

    function clever_point_all_settings( $settings, $current_section ) {
        if ( $current_section == 'clevepoint' ) {
            $settings_slider = array();
            // Add Title to the Settings
            $settings_slider[] = array(
                'name' => __( 'Clever Point Appearance settings', 'clever-point-for-woocommerce' ),
                'type' => 'title',
                'desc' => __( 'The following options are used to configure Clever Point map.', 'clever-point-for-woocommerce' ),
                'id' => 'clevepoint' );

            $settings_slider[] = array(
                'id' => 'clever_point_api_key',
                'title'       => __('Clever point API key','clever-point-for-woocommerce'),
                'type'        => 'text',
                'desc_tip' => '',
                'default'     => '',
            );
            $settings_slider[] = array(
                'id' => 'clever_point_google_key',
                'title'       => __('Google Maps key','clever-point-for-woocommerce'),
                'type'        => 'text',
                'desc_tip' => __('This is used by Clever Point to display Google Maps correctly','clever-point-for-woocommerce'),
                'default'     => '',
            );
            $settings_slider[] = array(
                'id' => 'clever_point_arcgis_key',
                'title'       => __('ARCGIS Map Key','clever-point-for-woocommerce'),
                'type'        => 'text',
                'desc_tip' => __('This is used by Clever Point to display ARCGIS map correctly','clever-point-for-woocommerce'),
                'default'     => '',
            );

            $settings_slider[] = array(
                'id' => 'clever_point_trigger_method',
                'title'   => __( 'Trigger', 'clever-point-for-woocommerce' ),
                'type'    => 'select',
                'class'   => 'wc-enhanced-select',
                'default' => 'embed',
                'options' => array(
                    'embed' => __( 'Embed', 'clever-point-for-woocommerce' ),
                    'modal'    => _x( 'Modal', 'Trigger', 'clever-point-for-woocommerce' ),
                ),
            );

            $settings_slider[] = array(
                'id' => 'clever_point_modal_width',
                'title'   => __( 'Map Width (in px)', 'clever-point-for-woocommerce' ),
                'type'    => 'number',
                'default' => '400',
            );

            $settings_slider[] = array(
                'id' => 'clever_point_modal_height',
                'title'   => __( 'Map Height (in px)', 'clever-point-for-woocommerce' ),
                'type'    => 'number',
                'default' => '400',
            );

            $settings_slider[] = array(
                'id' => 'clever_point_header',
                'title'       => __('Header','clever-point-for-woocommerce'),
                'type'        => 'checkbox',
                'desc'     => __( 'Header on map', 'clever-point-for-woocommerce' ),
            );
            $settings_slider[] = array(
                'id' => 'clever_point_single_select',
                'title'       => __('Single Select','clever-point-for-woocommerce'),
                'type'        => 'checkbox',
                'desc'     => __( 'Single select on map', 'clever-point-for-woocommerce' ),
            );
            $settings_slider[] = array(
                'id' => 'clever_point_display_address_bar',
                'title'       => __('Address bar','clever-point-for-woocommerce'),
                'type'        => 'checkbox',
                'default' => "yes",
                'desc'     => __( 'Display address bar on map', 'clever-point-for-woocommerce' ),
            );
            $settings_slider[] = array(
                'id' => 'clever_point_display_point_list',
                'title'       => __('Point List','clever-point-for-woocommerce'),
                'type'        => 'checkbox',
                'desc'     => __( 'Display point list on map', 'clever-point-for-woocommerce' ),
            );
            $settings_slider[] = array(
                'id' => 'clever_point_display_point_info_type',
                'title'       => __('Info type','clever-point-for-woocommerce'),
                'type'        => 'select',
                'default' => "docked",
                'desc'     => __( 'Display type of point info on map', 'clever-point-for-woocommerce' ),
                'options' => array(
                    'docked' => __( 'Docked', $this->plugin_name ),
                    'floating' => __( 'Floating', $this->plugin_name ),
                    'dockedSmall'    =>  __( 'Docked (small)', $this->plugin_name)
                ),
            );

            $settings_slider[]=array(
                'type' => 'sectionend',
                'id' => 'clevepoint_settings_end_first',
            );

            $settings_slider[] = array(
                'name' => __( 'Clever Point Settings', 'clever-point-for-woocommerce' ),
                'type' => 'title',
                'desc' => __( 'The following options are used to configure Clever Point.', 'clever-point-for-woocommerce' ),
                'id' => 'clevepoint' );

            $settings_slider[] = array(
                'id' => 'clever_point_voucher_management',
                'title'   => __( 'Voucher management method', 'clever-point-for-woocommerce' ),
                'type'    => 'select',
                'class'   => 'wc-enhanced-select',
                'default' => 'woocommerce',
                'options' => array(
                    'woocommerce' => __( 'WooCommerce', 'clever-point-for-woocommerce' ),
                    'platform'    => _x( 'Platform', 'Trigger', 'clever-point-for-woocommerce' ),
                ),
            );

            $settings_slider[] = array(
                'id' => 'clever_point_tax_status',
                'title'   => __( 'Tax status', 'woocommerce' ),
                'type'    => 'select',
                'class'   => 'wc-enhanced-select',
                'default' => 'taxable',
                'options' => array(
                    'taxable' => __( 'Taxable', 'woocommerce' ),
                    'none'    => _x( 'None', 'Tax status', 'woocommerce' ),
                ),
            );

            $settings_slider[] = array(
                'id' => 'clever_point_charges',
                'title'       => __('Charges','clever-point-for-woocommerce'),
                'type'        => 'checkbox',
                'default' => "yes",
                'desc'     => __( 'Add service fee as order fee', 'clever-point-for-woocommerce' ),
            );

            $settings_slider[] = array(
                'id' => 'clever_point_test_mode',
                'title'       => __('Test Mode','clever-point-for-woocommerce'),
                'type'        => 'checkbox',
                'default' => "yes",
                'desc'     => __( 'Test Mode', 'clever-point-for-woocommerce' ),
            );

            $terms=get_terms( 'product_cat', array( 'get' => 'all' ));
            $settings_slider[] = array(
                'id' => 'clever_point_categories_not_to_list',
                'title'       => __('## categories','clever-point-for-woocommerce'),
                'type'        => 'multiselect',
                'class' => 'selectWoo',
                'options' => wp_list_pluck( $terms, 'name', 'term_id' ),
            );
            $settings_slider[] = array(
                'id' => 'clever_point_categories_not_to_list_invert',
                'title'       => __('Invert categories','clever-point-for-woocommerce'),
                'type'        => 'checkbox',
                'default' => "",
            );

            $terms=get_terms( 'product_tag', array( 'get' => 'all' ));
            $settings_slider[] = array(
                'id' => 'clever_point_tags_not_to_list',
                'title'       => __('## tags','clever-point-for-woocommerce'),
                'type'        => 'multiselect',
                'class' => 'selectWoo',
                'options' => wp_list_pluck( $terms, 'name', 'term_id' ),
            );
            $settings_slider[] = array(
                'id' => 'clever_point_tags_not_to_list_invert',
                'title'       => __('Invert tags','clever-point-for-woocommerce'),
                'type'        => 'checkbox',
                'default' => "",
            );

            $terms=get_terms( 'product_shipping_class', array( 'get' => 'all' ));
            $settings_slider[] = array(
                'id' => 'clever_point_shipping_classes_not_to_list',
                'title'       => __('## Shipping classes','clever-point-for-woocommerce'),
                'type'        => 'multiselect',
                'class' => 'selectWoo',
                'options' => wp_list_pluck( $terms, 'name', 'term_id' ),
            );
            $settings_slider[] = array(
                'id' => 'clever_point_shipping_classes_not_to_list_invert',
                'title'       => __('Invert shipping classes','clever-point-for-woocommerce'),
                'type'        => 'checkbox',
                'default' => "",
            );

            $settings_slider[]=array(
                'type' => 'sectionend',
                'id' => 'clevepoint_settings_end',
            );

            $settings_slider[] = array( 'type' => 'sectionend', 'id' => 'clevepoint' );
            return $settings_slider;

        } else {
            return $settings;
        }
    }

    public function clever_point_orders_metabox_content($post) {
        $order = wc_get_order($post->ID);
        if (empty($order)) return;

        $shipping_methods=$order->get_shipping_methods();
        $shipping_method = array_shift($shipping_methods);
        $shipping_method_id = $shipping_method['method_id'];

        if ($shipping_method_id!=='clever_point_shipping_class') {
            return;
        }

        $_clever_point_response=$order->get_meta('_clever_point_response');
        $lock_me= !empty($_clever_point_response) ? 'disabled' : '';

        $weight = 0;
        foreach ($order->get_items() as $item) {
            $product_variation_id = $item['variation_id'];
            if ($product_variation_id) {
                $product = wc_get_product($item['variation_id']);
            } else {
                $product = wc_get_product($item['product_id']);
            }
            if ($product) {
                $weight += floatval($product->get_weight()) * $item->get_quantity();
            }
        }
        ?>
        <div class="clever-point-field-group">
            <div class="clever-point-field">
                <?php _e("Clever Point Station", $this->plugin_name); ?>:
                <?php echo (empty($order->get_meta('clever_point_station_details')) ? '-' : "<br>".$order->get_meta('clever_point_station_details')) ;?>
            </div>
            <div class="clever-point-field">
                <?php _e("Voucher", $this->plugin_name); ?>: <?php echo($_clever_point_response['ShipmentAwb'] ?? '-'); ?><br>
            </div>
            <div class="clever-point-field">
                <?php _e('Status',$this->plugin_name);?> <?php echo($_clever_point_response['ShipmentStatus'] ?? '-'); ?>
            </div>
            <div class="clever-point-field">
                <?php
                if (!empty($_clever_point_response['ShipmentAwb'])) {
                    if ( false === ( $clever_point_tracking = get_transient( 'clever_point_tracking_order_'.$order->get_id() ) ) ) {
                        $args = array(
                            'headers' => array(
                                'Authorization' => 'ApiKey ' . get_option('clever_point_api_key', null),
                            ),
                        );
                        $request = wp_remote_get(CLEVER_POINT_API_ENDPOINT . "/ShipmentTracking/" . $_clever_point_response['ShipmentAwb'], $args);

                        if (!is_wp_error($request) && wp_remote_retrieve_response_code($request) == 200) {
                            $response = wp_remote_retrieve_body($request);
                            if ($response) {
                                $response_array = json_decode($response, true, 512, JSON_UNESCAPED_UNICODE);
                                if (!empty($response_array['Content']['TrackingData'])) {
                                    $clever_point_tracking = end($response_array['Content']['TrackingData'])['TrackingNote'];
                                }
                            }
                        }
                        set_transient( 'clever_point_tracking_order_'.$order->get_id(), $clever_point_tracking, 30 * MINUTE_IN_SECONDS );
                    }
                }
                ?>
                <?php _e('Track & Trace',$this->plugin_name);?>: <?php echo ($clever_point_tracking ?? '-'); ?>
            </div>
        </div>
        <div class="clever-point-field-group">
            <div class="clever-point-field">
                <div class="clever-point-field-label">
                    <label for="clever_point_parcels"><?php echo __('Parcels', $this->plugin_name); ?></label>
                </div>
                <div class="clever-point-field-input">
                    <input  <?php echo $lock_me;?>  id="clever_point_parcels" value="<?php echo ($order->get_meta('clever_point_parcels')>0 ? $order->get_meta('clever_point_parcels') : 1); ?>" type="text" name="clever_point_parcels">
                </div>
            </div>
            <div class="clever-point-field">
                <div class="clever-point-field-label">
                    <label for="clever_point_weight"><?php echo __('Weight (in kg)', $this->plugin_name); ?></label>
                </div>
                <div class="clever-point-field-input">
                    <input  <?php echo $lock_me;?>  id="clever_point_weight" value="<?php echo(($order->get_meta( 'clever_point_weight')>0 ? $order->get_meta( 'clever_point_weight') : $weight>0) ? $weight : 0.5); ?>" type="text" name="clever_point_weight">
                </div>
            </div>
            <?php if ($order->get_payment_method() == "cod") : ?>
                <div class="clever-point-field">
                    <div class="clever-point-field-label">
                        <label for="clever_point_cod"><?php echo __('Cash on delivery price', $this->plugin_name); ?></label>
                    </div>
                    <div class="clever-point-field-input">
                        <input  <?php echo $lock_me;?>  id="clever_point_cod" type="text" placeholder="0.0" value="<?php echo($order->get_meta('clever_point_cod') ? number_format($order->get_meta('clever_point_cod'), 2) : $order->get_total()); ?>" name="clever_point_cod">
                    </div>
                </div>
            <?php else: ?>
                <input type="hidden" name="clever_point_cod" value="0">
            <?php endif; ?>
            <div class="clever-point-field">
                <div class="clever-point-field-label">
                    <label for="clever_point_courier_voucher"><?php echo __('Courier Voucher', $this->plugin_name); ?></label>
                </div>
                <div class="clever-point-field-input">
                    <input  <?php echo $lock_me;?>  id="clever_point_courier_voucher" value="<?php echo ($order->get_meta('clever_point_courier_voucher') ?? ''); ?>" type="text" name="clever_point_courier_voucher">
                </div>
            </div>
            <div class="clever-point-field">
                <div class="clever-point-field-label">
                    <label for="clever_point_courier"><?php echo __('Courier', $this->plugin_name); ?></label>
                </div>
                <div class="clever-point-field-input">
                    <?php
                        if ( false === $carriers = get_transient( "clever_point_couriers_transient" ) ) {
                            $args = array(
                                'headers' => array(
                                    'Authorization' => 'ApiKey ' . get_option('clever_point_api_key', null),
                                ),
                            );
                            $request = wp_remote_get(CLEVER_POINT_API_ENDPOINT . "/Shipping/GetCarriers", $args);
                            if (is_wp_error($request) || wp_remote_retrieve_response_code($request) != 200) {
                                die();
                            }
                            $response = wp_remote_retrieve_body($request);

                            if ($response) {
                                $response_array = json_decode($response, true, 512, JSON_UNESCAPED_UNICODE);


                                if ($response_array['ResultType']=='Success') {
                                    foreach ($response_array['Content'] as $result_content) {
                                        $carriers[$result_content['Id']]=$result_content['Name'];
                                    }
                                }
                            }
                            set_transient( "clever_point_couriers_transient", $carriers, 15 * MINUTE_IN_SECONDS );
                        }
                    $selected=!empty($order->get_meta('clever_point_courier')) ? $order->get_meta('clever_point_courier') : null;
                    ?>
                    <select <?php echo $lock_me;?> name="clever_point_courier" id="clever_point_courier" placeholder="<?php echo __('Courier Company', $this->plugin_name) ?>" style="display:block;width: 100%">
                        <option value=''><?php echo __('Pick the preferred carrier',$this->plugin_name) ?></option>
                        <?php
                        foreach ($carriers as $k=>$v) {
                            echo "<option value='$k'".($selected==$k ? 'selected' : '') .">$v</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="clever-point-field">
                <div class="clever-point-field-label">
                    <label for="clever_point_comments"><?php _e('Comments', $this->plugin_name); ?></label>
                </div>
                <div class="clever-point-field-input">
                    <textarea  <?php echo $lock_me;?>  name="clever_point_comments" id="clever_point_comments" cols="25" rows="5"><?php echo !empty(get_post_meta($post->ID, 'clever_point_comments', true)) ? get_post_meta($post->ID, 'clever_point_comments', true) : $order->get_customer_note(); ?></textarea>
                </div>
            </div>
            <p>
                <button <?php echo $lock_me;?>  data-order="<?php echo $post->ID; ?>" type="button" class="button has-spinner" id="clever_point_create_voucher" data-error="<?php _e('There was an error issuing the voucher.',$this->plugin_name);?>" data-success="<?php _e('Vouchers have been created!',$this->plugin_name);?>"><?php _e('Create voucher',
                        $this->plugin_name); ?> <span class="we_spinner"></span> </button>
        </div>

        <?php
        $lock_me= !empty($_clever_point_response) ? '' : 'disabled';
        ?>
        <div class="clever-point-field-group">
            <div class="clever-point-field">
                <div class="clever-point-field-label">
                    <label for="clever_point_print_voucher_type"><?php echo __('Print voucher', $this->plugin_name); ?></label>
                </div>
                <select id="clever_point_print_voucher_type" name="clever_point_print_voucher_type" >
                    <option value="singlepdf"><?php _e('Single (A4 - 1 / page)');?></option>
                    <option value="singlepdf_a5"><?php _e('Single (A5 - 1 / page)');?></option>
                    <option value="image_double"><?php _e('Double (A4 - 2 / page)');?></option>
                    <option value="image"><?php _e('Triple (A4 - 3 / page)');?></option>
                    <option value="voucher_quad"><?php _e('Quadruple (A4 - 4 / page)');?></option>
                    <option value="image10"><?php _e('Single (A7 - 1 / page)');?></option>
                </select>
                <div class="clever-point-field-label">
                    <button <?php echo $lock_me;?> data-order="<?php echo $post->ID; ?>" type="button" class="button has-spinner" id="clever_point_print_voucher" <?php echo(!empty($_clever_point_response['ShipmentAwb']) ? '' : 'disabled') ?>>
                        <?php _e('Print voucher', $this->plugin_name); ?> <span class="we_spinner"></span></button>
                </div>
            </div>
        </div>

        <div class="clever-point-field-group">
            <div class="clever-point-field-label">
                <label for="clever_point_cancel_voucher"><?php echo __('Cancel voucher', $this->plugin_name); ?></label>
            </div>
            <div class="clever-point-field">
                <button <?php echo $lock_me;?> data-order="<?php echo $post->ID; ?>" type="button" class="button has-spinner clever_point_cancel_voucher" data-success="<?php _e('Voucher has been cancelled',$this->plugin_name);?>" id="clever_point_cancel_voucher">
                    <?php _e('Cancel voucher', $this->plugin_name); ?> <span class="we_spinner"></span></button><br>
            </div>
        </div>

        <?php
    }

    function clever_point_create_voucher_process($args) {
        $order_id = isset($args['order_id']) ? sanitize_text_field($args['order_id']) : null;
        $order=wc_get_order($order_id);
        if (!$order)
            return;

        $comments = isset($args['comments']) ? sanitize_text_field($args['comments']) : apply_filters('clever_point_voucher_customer_note',$order->get_customer_note());;
        $comments = apply_filters('clever_point_voucher_custom_comments',$comments,$order_id);
        $cod = isset($args['cod']) ? floatval($args['cod']) : 0;
        $weight = isset($args['weight']) ? floatval($args['weight']) : 0;
        $parcels = isset($args['parcels']) ? floatval($args['parcels']) : 1;
        $courier = isset($args['courier']) ? sanitize_text_field($args['courier']) : '';
        $courier_voucher = isset($args['courier_voucher']) ? sanitize_text_field($args['courier_voucher']) : '';

        if (empty($courier) || empty($courier_voucher)) {
            wp_send_json(__("Voucher or Carrier is missing",$this->plugin_name));
        }

        $weight_per_parcel=round($weight/$parcels,2);
        $first_name=!empty($order->get_shipping_first_name()) ? $order->get_shipping_first_name() : $order->get_billing_first_name();
        $last_name=!empty($order->get_shipping_last_name()) ? $order->get_shipping_last_name() : $order->get_billing_last_name();
        $address_1=!empty($order->get_shipping_address_1()) ? $order->get_shipping_address_1() : $order->get_billing_address_1();
        $state=!empty($order->get_shipping_state()) ? $order->get_shipping_state() : $order->get_billing_state();
        $city=!empty($order->get_shipping_city()) ? $order->get_shipping_city() : $order->get_billing_city();
        $post_code=!empty($order->get_shipping_postcode()) ? $order->get_shipping_postcode() : $order->get_billing_postcode();
        $country = !empty($order->get_shipping_country()) ? $order->get_shipping_country() : $order->get_billing_country();
        $order->update_meta_data('clever_point_parcels',$parcels);
        $order->update_meta_data('clever_point_courier',$courier);
        $order->update_meta_data('clever_point_courier_voucher',$courier_voucher);
        $order->save();

        $Shipping=[];
        $Shipping['ItemsDescription']="Order {$order->get_id()}";
        if (!empty($comments))
            $Shipping['PickupComments']=$comments;

        $Shipping['Consignee']=[
            'ContactName'=>"$first_name $last_name",
            'Address'=>"$address_1",
            'Area'=>WC()->countries->get_states( $country )[$state],
            'City'=>$city,
            'PostalCode'=>$post_code,
            'Phones'=>$order->get_billing_phone(),
            'NotificationPhone'=>$order->get_billing_phone(),
            'Emails'=>$order->get_billing_email(),
            'ShipmentCost'=> $order->get_total()-$order->get_total_refunded(),
            'CustomerReferenceId'=>$order->get_id()
        ];
        $Shipping['DeliveryStation']=$order->get_meta('_clever_point_station_id');

        if ($cod>0) {
            $Shipping['CODs']=[];
            array_push($Shipping['CODs'],['Amount'=>['CurrencyCode'=>'EUR','Value'=>$cod]]);
        }

        $Shipping['Items']=[];

        for ($x = 1; $x <= $parcels; $x++) {
            $to_push = [
                'Description'=>__('Order','')." $x/$parcels",
                'IsFragile'=>'false',
                'Weight'=>[
                    'UnitType'=>'kg',
                    'Value' => $weight_per_parcel > 0 ? $weight_per_parcel : 0.5
                ]
            ];
            array_push($Shipping['Items'],$to_push);
        }

        if ( false === $carriers = get_transient( "clever_point_couriers_transient" ) ) {
            $args = array(
                'headers' => array(
                    'Authorization' => 'ApiKey ' . get_option('clever_point_api_key', null),
                ),
            );
            $request = wp_remote_get(CLEVER_POINT_API_ENDPOINT . "/Shipping/GetCarriers", $args);
            if (is_wp_error($request) || wp_remote_retrieve_response_code($request) != 200) {
                die();
            }
            $response = wp_remote_retrieve_body($request);

            if ($response) {
                $response_array = json_decode($response, true, 512, JSON_UNESCAPED_UNICODE);


                if ($response_array['ResultType']=='Success') {
                    foreach ($response_array['Content'] as $result_content) {
                        $carriers[$result_content['Id']]=$result_content['Name'];
                    }
                }
            }
            set_transient( "clever_point_couriers_transient", $carriers, 15 * MINUTE_IN_SECONDS );
        }

        $Shipping['ExternalCarrierId']=$courier;
        $Shipping['ExternalCarrierName']=$carriers[$courier];
        $Shipping['ShipmentAwb']=$courier_voucher;

        $args = array(
            'headers'     => array(
                'Authorization' => 'ApiKey ' . get_option( 'clever_point_api_key',null),
            ),
            'body' => $Shipping
        );
        $request = wp_remote_post( CLEVER_POINT_API_ENDPOINT."/Shipping", $args );

        if ( is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) != 200 ) {
            die();
        }

        $response = wp_remote_retrieve_body( $request );
        if ($response) {
            $response_array=json_decode($response, true, 512, JSON_UNESCAPED_UNICODE);
            if ($response_array['ResultType']=="Success") {
                $order->update_meta_data('_clever_point_response',$response_array['Content']);
                $order->save();
                return 'success';
            }else {
                wp_send_json(implode(',', array_column($response_array['Messages'], 'Code')));
            }
        }
    }

    function clever_point_create_voucher() {
        $order_id = isset($_POST['order_id']) ? sanitize_text_field($_POST['order_id']) : null;
        $order=wc_get_order($order_id);
        if (!$order)
            return;

        $comments = isset($_POST['comments']) ? sanitize_text_field($_POST['comments']) : apply_filters('clever_point_voucher_customer_note',$order->get_customer_note());;
        $cod = isset($_POST['cod']) ? sanitize_text_field($_POST['cod']) : 0;
        $weight = isset($_POST['weight']) ? sanitize_text_field($_POST['weight']) : 0;
        $parcels = isset($_POST['parcels']) ? sanitize_text_field($_POST['parcels']) : 1;
        $courier = isset($_POST['courier']) ? sanitize_text_field($_POST['courier']) : '';
        $courier_voucher = isset($_POST['courier_voucher']) ? sanitize_text_field($_POST['courier_voucher']) : '';
        wp_send_json($this->clever_point_create_voucher_process(['order_id'=>$order_id,'comments'=>$comments,'cod'=>$cod, 'weight'=>$weight, 'parcels'=>$parcels,'courier'=>$courier,'courier_voucher'=>$courier_voucher]));
    }

    function clever_point_cancel_voucher() {
        $order_id = isset($_POST['order_id']) ? sanitize_text_field($_POST['order_id']) : null;
        $order=wc_get_order($order_id);
        if ($order) {
            $_clever_point_response=$order->get_meta('_clever_point_response');
            $awbs=$_clever_point_response['ShipmentAwb'];
            $args = array(
                'headers' => array(
                    'Authorization' => 'ApiKey ' . get_option('clever_point_api_key', null),
                ),
            );
            $request = wp_remote_post(CLEVER_POINT_API_ENDPOINT . "/Shipping/$awbs/Cancel", $args);

            if (is_wp_error($request) || wp_remote_retrieve_response_code($request) != 200) {
                die();
            }

            $response = wp_remote_retrieve_body($request);
            if ($response) {
                $response_array = json_decode($response, true, 512, JSON_UNESCAPED_UNICODE);
                $order->delete_meta_data('_clever_point_response');
                $order->save();
                if ($response_array['ResultType']=="Success" && $response_array['Content']['ShipmentStatus']=="Cancel") {
                    wp_send_json(['success'=>1]);
                }else {
                    wp_send_json(implode(',', array_column($response_array['Messages'], 'Description')));
                }
            }
        }
    }

    function clever_point_plugin_action_links($links, $file)
    {
        static $this_plugin;
        if (!$this_plugin) {
            $this_plugin = ( dirname(plugin_basename(__FILE__), 2) . '/' . $this->plugin_name . '.php' );
        }
        if ($file == $this_plugin) {
            $settings_link = '<a href="' . admin_url("admin.php?page=wc-settings&tab=shipping&section=clevepoint").'">'.__('Settings',$this->plugin_name).'</a>';
            $support_link = '<a target="_blank" href="https://cleverpoint.gr">'.__('Support',$this->plugin_name).'</a>';
            array_unshift($links, $settings_link, $support_link);
        }
        return $links;
    }

    function clever_point_print_voucher() {
        $print_type = isset($_POST['print_type']) ? sanitize_text_field($_POST['print_type']) : 'singlepdf';
        $order_id = isset($_POST['order_id']) ? sanitize_text_field($_POST['order_id']) : null;
        $order=wc_get_order($order_id);
        if ($order) {
            $_clever_point_response=$order->get_meta('_clever_point_response');
            $awbs=$_clever_point_response['ShipmentAwb'];

            $args = array(
                'headers' => array(
                    'Authorization' => 'ApiKey ' . get_option('clever_point_api_key', null),
                ),
            );
            $request = wp_remote_get(CLEVER_POINT_API_ENDPOINT . "/Vouchers/?awbs=" . $awbs."&template=$print_type", $args);

            if (is_wp_error($request) || wp_remote_retrieve_response_code($request) != 200) {
                die();
            }

            $response = wp_remote_retrieve_body($request);
            if ($response) {
                $response_array = json_decode($response, true, 512, JSON_UNESCAPED_UNICODE);
                if ($response_array['ResultType'] == "Success") {
                    $file = wp_upload_bits("$awbs.pdf", null, base64_decode($response_array['Content']['Document']));
                    wp_send_json($file);
                } else {

                }
            }
        }
    }
}