<?php
add_action( 'woocommerce_shipping_init','clever_point_for_woocommerce_shipping_class' );
function clever_point_for_woocommerce_shipping_class() {

    if (!class_exists('Clever_Point_for_WooCommerce_Shipping')) {
        class Clever_Point_for_WooCommerce_Shipping extends WC_Shipping_Method {
            public function __construct($instance_id = 0) {
                $this->instance_id = absint($instance_id);
                $this->id = 'clever_point_shipping_class';
                $this->method_title = __('Clever Point','clever-point-for-woocommerce');
                $this->method_description = __('Allows you to offer shipping via Clever Point.','clever-point-for-woocommerce');
                $this->supports = array(
                    'shipping-zones',
                    'instance-settings',
                    'instance-settings-modal',
                );
                $this->title = __('Clever Point','clever-point-for-woocommerce');
                $this->enabled = 'yes';
                $this->init();
            }

            function init() {
                $this->init_form_fields();
                $this->init_settings();

                $this->title      = __('Pick up from Clever Point','clever-point-for-woocommerce');
                $this->tax_status = $this->get_option( 'tax_status' );
                $this->cost_up_to_2kg = $this->get_option( 'cost_up_to_2kg' );
                $this->cost_for_free = $this->get_option( 'cost_for_free' );
                $this->cost_for_extra_kgs = $this->get_option( 'cost_for_extra_kgs' );

                add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
            }

            function init_form_fields() {
                $this->instance_form_fields = array(
                    'cost_up_to_2kg'       => array(
                        'title'       => __( 'Shipping cost (up to 2kg)', 'clever-point-for-woocommerce' ),
                        'type'        => 'price',
                        'placeholder' => wc_format_localized_price( 0 ),
                        'description' => '',
                        'default'     => '0',
                        'desc_tip'    => true,
                    ),
                    'cost_for_free'       => array(
                        'title'       => __( 'Free shipping for (up to 2kg)', 'clever-point-for-woocommerce' ),
                        'type'        => 'price',
                        'placeholder' => wc_format_localized_price( 0 ),
                        'description' => '',
                        'default'     => '0',
                        'desc_tip'    => true,
                    ),
                    'cost_for_extra_kgs'       => array(
                        'title'       => __( 'Shipping cost (for extra kg)', 'clever-point-for-woocommerce' ),
                        'type'        => 'price',
                        'placeholder' => wc_format_localized_price( 0 ),
                        'description' => '',
                        'default'     => '0',
                        'desc_tip'    => true,
                    ),
                );
            }

            public function calculate_shipping($package = array()) {
                $cost = 0;
                $total = WC()->cart->get_cart_contents_total();
                $weight= WC()->cart->get_cart_contents_weight();

                if ($weight<=2) {
                    $cost = $this->cost_up_to_2kg;
                    if ($total>$this->cost_for_free) {
                        $cost=0;
                    }
                }else {
                    $cost = $this->cost_up_to_2kg + ($this->cost_for_extra_kgs * ceil($weight-2));
                    if ($total>$this->cost_for_free) {
                        $cost-=$this->cost_up_to_2kg;
                    }
                }

                $amount=0;
                if (get_option('clever_point_charges','yes')=='yes') {
                    if ( false === $amount = get_transient( "clever_point_short_transient" ) ) {
                        $args = array(
                            'headers' => array(
                                'Authorization' => 'ApiKey ' . get_option('clever_point_api_key', null),
                            ),
                        );
                        $request = wp_remote_get(CLEVER_POINT_API_ENDPOINT . "/Shipping/GetPrices", $args);
                        if (is_wp_error($request) || wp_remote_retrieve_response_code($request) != 200) {
                            die();
                        }
                        $amount=0;
                        $response = wp_remote_retrieve_body($request);

                        if ($response) {
                            $response_array = json_decode($response, true, 512, JSON_UNESCAPED_UNICODE);

                            if ($response_array['ResultType']=='Success') {
                                foreach ($response_array['Content'] as $result_content) {
                                    if ($result_content['Type']=="Pickup") {
                                         $amount = $result_content['Price']['Value'];
                                    }
                                }
                            }
                        }
                        set_transient( "clever_point_short_transient", $amount, 15 * MINUTE_IN_SECONDS );
                    }

                    if ( !WC()->cart->display_prices_including_tax() ) {
                        $taxes = WC_Tax::get_rates_for_tax_class( get_option('woocommerce_shipping_tax_class') );
                        if (get_option( 'clever_point_tax_status','taxable' )=='taxable' && is_array($taxes)) {
                            $first_tax=reset($taxes);
                            $amount = $amount/(1+($first_tax->tax_rate/100));
                        }
                    }
                }

                $this->add_rate(
                    array(
                        'id'      => $this->id,
                        'label'   => $this->title,
                        'cost'    => [
                            'shipping_cost'=>$cost,
                            'service_cost'=>$amount,
                        ],
                        'package' => $package,
                        'taxes'   => get_option( 'clever_point_tax_status','taxable' ) =='taxable',
                    )
                );
            }

            public function is_available($package) {
                if ($package['destination']['country'] == "GR") {
                    $is_available = true;
                } else {
                    $is_available = false;
                }

                if (empty(get_option( 'clever_point_api_key',null))) {
                    $is_available = false;
                }
                return apply_filters('woocommerce_shipping_' . $this->id . '_is_available', $is_available, $package, $this);
            }
        }
    }

    add_filter( 'woocommerce_shipping_methods','add_clever_point_shipping_method' );
    function add_clever_point_shipping_method( $methods ) {
        $methods['clever_point_shipping_class'] = 'Clever_Point_for_WooCommerce_Shipping';
        return $methods;
    }
}


add_filter('webexpert_acs_order_tracking_change_url','webexpert_custom_shipping_url',10,2);
add_filter('webexpert_couriercenter_order_tracking_change_url','webexpert_custom_shipping_url',10,2);
add_filter('webexpert_elta_order_tracking_change_url','webexpert_custom_shipping_url',10,2);
add_filter('webexpert_speedex_order_tracking_change_url','webexpert_custom_shipping_url',10,2);
add_filter('webexpert_geniki_order_tracking_change_url','webexpert_custom_shipping_url',10,2);

function webexpert_custom_shipping_url($url,$order) {
    return get_permalink(get_page_by_path('parakolouthhsh-parangelias'))."?order_id={$order->get_id()}&email_or_phone={$order->get_billing_email()}";
}