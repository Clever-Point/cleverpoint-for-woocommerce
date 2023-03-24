<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://cleverpoint.gr/
 * @since      1.0.0
 *
 * @package    Clever_Point_For_Woocommerce
 * @subpackage Clever_Point_For_Woocommerce/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Clever_Point_For_Woocommerce
 * @subpackage Clever_Point_For_Woocommerce/public
 * @author     Clever Point <info@cleverpoint.gr>
 */
class Clever_Point_For_Woocommerce_Public {

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/clever-point-for-woocommerce-public.css', array(), $this->version, 'all' );
        if (get_option('clever_point_trigger_method','embed')=="modal") {
            wp_add_inline_style($this->plugin_name, '
    .modal__container {
    max-width: ' . intval(get_option('clever_point_modal_width', 400) + 60) . 'px;
    height: ' . get_option('clever_point_modal_height', 400) . 'px;
    }');
        }
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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

        wp_register_script( "micromodal", plugin_dir_url( __FILE__ ) . 'js/micromodal.min.js', [], $this->version, false );
        wp_register_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/clever-point-for-woocommerce-public.js', array( 'jquery'), $this->version, false );
        if (get_option('clever_point_test_mode',null)=='yes') {
            wp_register_script("cleverpoint-map", 'https://test.cleverpoint.gr/portal/content/clevermap_v2/script/cleverpoint-map.js', [], $this->version, false);
        }else {
            wp_register_script("cleverpoint-map", 'https://platform.cleverpoint.gr/portal/content/clevermap_v2/script/cleverpoint-map.js', [], $this->version, false);
        }

        if (is_checkout() || is_cart()) {
            wp_enqueue_script($this->plugin_name);
            wp_enqueue_script('micromodal');
            wp_enqueue_script('cleverpoint-map');
        }
    }

    function clever_point_shipping_maps_displayed() {
        global $order;
        if (empty(get_option( 'clever_point_api_key',null))) {
            return;
        }

        $chosen_shipping_methods = WC()->session->get( 'chosen_shipping_methods' );
        if (strpos( $chosen_shipping_methods[0], ':') !== false) {
            $chosen_shipping_method = substr( $chosen_shipping_methods[0], 0, strpos( $chosen_shipping_methods[0], ':' ) );
        }else {
            $chosen_shipping_method = $chosen_shipping_methods[0];
        }
        $customer_data = WC()->session->get('customer');
        $chosen_payment_method = WC()->session->get('chosen_payment_method');
        ?>
        <div class="shop_table clevermap-container" data-total="<?php echo WC()->cart->total;?>" style="<?php echo ($chosen_shipping_method == 'clever_point_shipping_class' ? 'display:block;' : '');?>">
            <div id="clevermap-output">

            </div>
            <script>
                var clever_data = {
                    selector: '#clevermap',
                    cleverPointKey: '<?php echo get_option( 'clever_point_api_key','');?>',
                    googleMapKey: '<?php echo get_option( 'clever_point_google_key','');?>',
                    arcgisMapKey: '<?php echo get_option( 'clever_point_arcgis_key','');?>',
                    header: '<?php echo get_option( 'clever_point_header','no')=='yes';?>',
                    singleSelect: '<?php echo get_option( 'clever_point_single_select','no')=='yes';?>',
                    defaultAddress: '<?php echo $customer_data['address_1'];?>',
                    display: {
                        addressBar: <?php echo get_option( 'clever_point_display_address_bar','no')=='yes' ? 'true' : 'false';?>,
                        pointList: <?php echo get_option( 'clever_point_display_point_list','no')=='yes'  ? 'true' : 'false';?>,
                        pointInfoType: '<?php echo get_option( 'clever_point_display_point_info_type','docked');?>'
                    },onclear: () => {
                        document.getElementById("clever_point_station_id").value='';
                        document.getElementById("clever_point_station_details").value='';
                        document.getElementById("clever_point_prefix").value='';
                        document.getElementById("clevermap-output").innerHTML = '';
                        if (get_option('clever_point_trigger_method','embed')==="modal") {
                            MicroModal.close('modal-1');
                        }
                    },
                    onselect: (point) => {
                        document.getElementById("clever_point_station_id").value = point.StationId;
                        document.getElementById("clever_point_station_details").value = point.Name + "\n" + point.AddressLine1  + "\n" + point.City + ", " + point.ZipCode + "\n" + point.Phones;
                        document.getElementById("clever_point_prefix").value = point.Prefix;

                        document.getElementById("clevermap-output").innerHTML = "<div class='inner'><strong><?php _e('Selected collection point','clever-point-for-woocommerce');?></strong><br>" + point.Name + "<br>" + point.AddressLine1  + "<br>" + point.City + ", " + point.ZipCode + "<br>" + point.Phones;
                        document.getElementById("clevermap-output").innerHTML += "</div></div>";
                    },
                    filters: {
                        codAmount: '<?php echo $chosen_payment_method=='cod' ? WC()->cart->total : 0;?>'
                    }};
                jQuery(document.body).on('change', 'input[name="payment_method"]', function() {
                    jQuery('#clevermap-container').html("");
                    var e = jQuery('<div id="clevermap" style="height: 500px"></div>');
                    if (jQuery(this).val()==='cod') {
                        clever_data.filters.codAmount=jQuery('.clevermap-container').data('total');
                    }else {
                        clever_data.filters.codAmount=0;
                    }
                    jQuery("#clevermap-container").html(e);
                    clevermap(clever_data);
                });
            </script>

            <div class="modal micromodal-slide" id="what_is_cleverpoint" aria-hidden="true">
                <div class="modal__overlay" tabindex="-1" data-micromodal-close>
                    <div class="modal__container what_modal" role="dialog" aria-modal="true" aria-labelledby="modal-1-title">
                        <main class="modal__content" id="modal-1-content">
                            <header class="modal__header">
                                <a class="modal__close" aria-label="Close modal" data-micromodal-close></a>
                            </header>
                            <div id="modal-1-content">
                                <p><strong>Παραλαβή από Clever Point</strong></p>
                                <p>Παράλαβε το δέμα σου όποτε θες εσύ θες χωρίς να περιμένεις τον Courier από ένα σημείο δίπλα σου!</p>
                                <p>Επίλεξε ένα σημείο Clever Point και το δέμα σου θα σταλεί εκεί, από όπου μπορείς να παραλάβεις σε διευρυμένο ωράριο, όποτε σε εξυπηρετεί για να μην περιμένεις τον courier στο σπίτι!</p>
                                <p>Κάθε κατάστημα Clever Point λειτουργεί σαν προσωπική σου "θυρίδα".</p>
                                <p>Μόλις το δέμα αφιχθεί στο σημείο, θα ενημερωθείς με SMS & Email και μπορείς να προγραμματίσεις όποτε θες την παραλαβή του.</p>
                                <p>Το δέμα παραμένει στο σημείο για 7 ημέρες.</p>
                                <p>Περισσότερες πληροφορίες εδώ : <a href="https://cleverpoint.gr/service-leitougria/" target="_blank">https://cleverpoint.gr/service-leitougria/</a></p>
                                <p><strong>Τι είναι το Clever Point;</strong></p>
                                <p>Το Clever Point είναι ένα δίκτυο σημείων που αποτελείται από καταστήματα όπως Πρακτορεία ΟΠΑΠ, Mini Market, ανθοπωλεία, βενζινάδικα, κάβες κ.α. τα οποία λειτουργούν σε διευρυμένο ωράριο.</p>
                                <p>Βρες το κοντινότερό σου σημείο εδώ : <a target="_blank" href="https://cleverpoint.gr/clever-map/">https://cleverpoint.gr/clever-map/</a></p>
                                <p><small>*Σημείωση : To Clever Point δεν είναι εταιρεία Courier, αλλά μόνο τα σημεία εξυπηρέτησης. Τα δέματα μεταφέρονται προς το σημείο με την εκάστοτε μεταφορική που συνεργάζεται το e-Shop από το οποίο ψωνίζετε.</small></p>
                            </div>
                        </main>
                    </div>
                </div>
            </div>
        <?php
        if (get_option('clever_point_trigger_method','embed')=="embed") {
            ?>
            <div id="clevermap-container">
                <div id="clevermap" style="height: 500px"></div>
            </div>
            <script>
                clevermap(clever_data);
            </script>
            <?php
        }else { ?>
            <div class="modal micromodal-slide" id="modal-1" aria-hidden="true">
                <div class="modal__overlay" tabindex="-1" data-micromodal-close>
                    <div class="modal__container" role="dialog" aria-modal="true" aria-labelledby="modal-1-title">
                        <main class="modal__content" id="modal-1-content">
                            <header class="modal__header">
                                <a class="modal__close" aria-label="Close modal" data-micromodal-close></a>
                            </header>
                            <div id="clevermap-container">
                            <div id="clevermap" style="width:<?php echo get_option( 'clever_point_modal_width',400);?>px;height: <?php echo get_option( 'clever_point_modal_height',400);?>px"></div>
                            </div>
                        </main>
                    </div>
                </div>
            </div>
            <a id="cleverpoint-modal-trigger" data-micromodal-trigger="modal-1">CleverPoint</a>
            <script>
                MicroModal.init({
                    onShow: modal => console.info(`${modal.id} is shown`), // [1]
                    onClose: modal => console.info(`${modal.id} is hidden`), // [2]
                    openClass: 'is-open', // [5]
                    disableScroll: true, // [6]
                    disableFocus: false, // [7]
                    awaitOpenAnimation: false, // [8]
                    awaitCloseAnimation: false, // [9]
                    debugMode: true // [10]
                });

                <?php if ($chosen_shipping_method == 'clever_point_shipping_class' ? 'display:block;' : '') { ?>
                    setTimeout(function(){
                        MicroModal.show('modal-1');
                    }, 500);


                clevermap(clever_data);
                <?php } ?>
            </script>
            <?php
        }
        ?>
        </div>
<?php }
    function clever_point_station_hidden_field( $checkout ) {
        echo '<div id="clever_point_hidden_checkout_fields">
            <input type="hidden" class="input-hidden" name="clever_point_station_id" id="clever_point_station_id" value="">
            <input type="hidden" class="input-hidden" name="clever_point_station_details" id="clever_point_station_details" value="">
            <input type="hidden" class="input-hidden" name="clever_point_prefix" id="clever_point_prefix" value="">
    </div>';
    }

    function save_custom_checkout_hidden_field( $order_id ) {
        if ( ! empty( $_POST['clever_point_station_id'] ) )
            update_post_meta( $order_id, '_clever_point_station_id', sanitize_text_field( $_POST['clever_point_station_id'] ) );
        if ( ! empty( $_POST['clever_point_station_details'] ) )
            update_post_meta( $order_id, 'clever_point_station_details', sanitize_text_field( $_POST['clever_point_station_details'] ) );
        if ( ! empty( $_POST['clever_point_prefix'] ) )
            update_post_meta( $order_id, '_clever_point_prefix', sanitize_text_field( $_POST['clever_point_prefix'] ) );
    }

    function validate_clever_point($fields, $errors) {
        $chosen_shipping_methods = WC()->session->get( 'chosen_shipping_methods' );
        if (strpos( $chosen_shipping_methods[0], ':') !== false) {
            $chosen_shipping_method = substr( $chosen_shipping_methods[0], 0, strpos( $chosen_shipping_methods[0], ':' ) );
        }else {
            $chosen_shipping_method = $chosen_shipping_methods[0];
        }
        if ($chosen_shipping_method ==='clever_point_shipping_class' && empty($_POST[ 'clever_point_station_id' ])) {
            $pick_now= '';
            if (get_option('clever_point_trigger_method', 'embed') == "modal") {
                $pick_now = '<a href="#" id="cleverpoint-modal-validate-trigger">'.__('Pick now','clever-point-for-woocommerce').'</a>';
            }
            $errors->add('clever_point_station_id',sprintf(__('No Clever Point station has been chosen. %s','clever-point-for-woocommerce'),$pick_now,'error'));
        }
    }

    function clever_point_change_cart_shipping_method_full_label( $label, $method ) {
        if ($method->get_method_id()!='clever_point_shipping_class')
            return $label;

        $label = $method->get_label();

        $label.=" <a class='clever-point-icons clever-point-what-opener' href='#what_is_cleverpoint'><img width='12' height='12' src='".plugin_dir_url(__DIR__)."/assets/question.png'></a>";

        if (get_option('clever_point_trigger_method','embed')=="modal") {
            $label.=" <a class='clever-point-icons clever-point-map-opener' href='#map'>".__('Change point',$this->plugin_name)."</a>";
        }

        $amount=0.0;
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
                $response = wp_remote_retrieve_body($request);
                if ($response) {
                    $response_array = json_decode($response, true, 512, JSON_UNESCAPED_UNICODE);
                    $amount = (float) str_replace(",",".",$response_array['Content'][0]['Price']['Value']);
                }
                set_transient( "clever_point_short_transient", $amount, 15 * MINUTE_IN_SECONDS );
            }

            if ( !WC()->cart->display_prices_including_tax() ) {
                $taxes = WC_Tax::get_rates_for_tax_class( get_option('woocommerce_shipping_tax_class') );
                if (get_option( 'clever_point_tax_status','taxable' )=='taxable' && is_array($taxes)) {
                    $first_tax=reset($taxes);
                    $amount = (float) $amount/(1+($first_tax->tax_rate/100));
                }
            }

            if ($amount>0) {
                $label.="<br><small>".__('Service cost','clever-point-for-woocommerce').": ".wc_price($amount)."</small>";
            }
        }
        if ($method->cost + $method->get_shipping_tax()>=0) {
            $label.="<br><small>".__('Shipping cost','clever-point-for-woocommerce').": ".wc_price(abs($method->cost + $method->get_shipping_tax() - (float)$amount))."</small>";
        }
        return $label;
    }

    public function hide_shipping_method_based_on_options($rates, $package) {
        if ( is_admin() && ! defined( 'DOING_AJAX' ) )
            return $rates;

        $shipping_classes = get_option('clever_point_shipping_classes_not_to_list',[]);
        $product_cats = get_option('clever_point_categories_not_to_list',[]);
        $product_tags  = get_option('clever_point_tags_not_to_list',[]);

        $clever_point_categories_not_to_list_invert=get_option('clever_point_categories_not_to_list_invert','no');
        $clever_point_tags_not_to_list_invert=get_option('clever_point_tags_not_to_list_invert','no');
        $clever_point_shipping_classes_not_to_list_invert=get_option('clever_point_shipping_classes_not_to_list_invert','no');

        $disable_clever_point = false;

        foreach( $package['contents'] as $key => $values ) {
            if (!empty($shipping_classes)) {
                if ($clever_point_shipping_classes_not_to_list_invert=="no") {
                    if( in_array( $values[ 'data' ]->get_shipping_class_id(), $shipping_classes ) )
                        $disable_clever_point = true;
                }else {
                    if( !in_array( $values[ 'data' ]->get_shipping_class_id(), $shipping_classes ) )
                        $disable_clever_point = true;
                }
            }
            if (!empty($shipping_classes)) {
                if ($clever_point_categories_not_to_list_invert=="no") {
                    if( in_array( $values[ 'data' ]->get_category_ids(), $product_cats ) )
                        $disable_clever_point = true;
                }else {
                    if(!in_array( $values[ 'data' ]->get_category_ids(), $product_cats ) )
                        $disable_clever_point = true;
                }
            }
            if (!empty($shipping_classes)) {
                if ($clever_point_tags_not_to_list_invert=="no") {
                    if( in_array( $values[ 'data' ]->get_tag_ids(), $product_tags ) )
                        $disable_clever_point = true;
                }else {
                    if( !in_array( $values[ 'data' ]->get_tag_ids(), $product_tags ) )
                        $disable_clever_point = true;
                }
            }
        }

        if( $disable_clever_point ) {
            foreach( $rates as $key => $option ) {
                if( $option->method_id == 'clever_point_shipping_class' )
                    unset( $rates[ $key ] );
            }
        }

        return $rates;
    }
}