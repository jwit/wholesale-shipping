<?php
 
/**
 * Plugin Name: WooCommerce Custom Shipping Methods
 * Plugin URI: https://github.com/jwit/wholesale-shipping/
 * Description: Custom Shipping Methods
 * Version: 1.0
 * Author: Jeff de Wit
 * Author URI: https://github.com/jwit/wholesale-shipping/
 * License: Copyright @ Jeff de Wit
 */

if ( ! defined( 'ABSPATH' ) ) {
//	exit;
}

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

	function jwwholesale_shipping_method_init() {
		if ( ! class_exists( 'WC_Shipping_JWWholesale' ) ) {
			
		class WC_Shipping_JWWholesale extends WC_Shipping_Method {

	/**
	 * Constructor. The instance ID is passed to this.
	 */
	public function __construct( $instance_id = 0 ) {
		$this->id                    = 'jwwholesale_method';
		$this->instance_id           = absint( $instance_id );
		$this->method_title          = __( 'Wholesale Shipping' );
		$this->method_description    = __( 'Wholesale shipping method.' );
		$this->supports              = array(
			'shipping-zones',
			'instance-settings',
		);

	    	$this->instance_form_fields = array(
        		'enabled' => array(
        			'title' 		=> __( 'Enable/Disable' ),
        			'type' 			=> 'checkbox',
        			'label' 		=> __( 'Enable this shipping method' ),
        			'default' 		=> 'yes',
        		),
        		'title' => array(
        			'title' 		=> __( 'Method Title' ),
        			'type' 			=> 'text',
        			'description' 	=> __( 'This controls the title which the user sees during checkout.' ),
        			'default'		=> __( 'Wholesale Shipping' ),
        			'desc_tip'		=> true
        		),
        		'cost' => array(
        			'title' 		=> __( 'Method Cost' ),
        			'type' 			=> 'price',
        			'placeholder' => wc_format_localized_price( 0 ),  
        			'description' 	=> __( 'Fixed shipping rate.' ),
        			'default'		=> __( '0' ),
        			'desc_tip'		=> true
        		),
        		'weight' => array(
        			'title' 		=> __( 'Maximum allowed weight' ),
        			'type' 			=> 'number',
        			'description' 	=> __( 'Maximum weight allowed for this shipping rate.' ),
        			'default'		=> __( '0' ),
        			'desc_tip'		=> true
        		),
        		'quantity' => array(
        			'title' 		=> __( 'Maximum Quantity' ),
        			'type' 			=> 'number',
        			'description' 	=> __( 'Maximum quantity for which this shipping rate applies.' ),
        			'default'		=> __( '0' ),
        			'desc_tip'		=> true
        		)
		);
		$this->enabled        = $this->get_option( 'enabled' );
		$this->title          = $this->get_option( 'title' );
		$this->cost           = $this->get_option( 'cost' );
		$this->weight         = $this->get_option( 'weight' );
		$this->quantity       = $this->get_option( 'quantity' );

		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
	}

	/**
	 * calculate_shipping function.
	 * @param array $package (default: array())
	 */
	public function calculate_shipping( $package = array() ) {
		
	  $weight   = 0;
	  $quantity = 0;
    $cost     = 0;

		$shipping_rate 	= $this->cost;
		$max_weight  	= $this->weight;
		$max_quantity 	= $this->quantity;

    foreach ( $package['contents'] as $item_id => $values ) 
    { 
        $_product = $values['data']; 
        $weight = $weight + $_product->get_weight() * $values['quantity']; 
        $quantity = $quantity + $values['quantity'];
    }

    $weight = wc_get_weight( $weight, 'kg' );

    if( $quantity >= $max_quantity ) {

        $cost = 0;

    } elseif ( $weight <= $max_weight ) {

        $cost = $shipping_rate;
        
    } else {
    	
    	  $cost = 0;
    }

		$this->add_rate( array(
			'id'    => $this->id . $this->instance_id,
			'label' => $this->title,
			'cost'  => $cost
		) );
		
	}
}

		//class ends here
		}
	}

	add_action( 'woocommerce_shipping_init', 'jwwholesale_shipping_method_init' );
	add_filter( 'woocommerce_shipping_methods', 'register_jwwholesale_method' );
	function register_jwwholesale_method( $methods ) {
		$methods[ 'jwwholesale_method' ] = new WC_Shipping_JWWholesale();
		return $methods;
	}

}
