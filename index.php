<?php
/**
 * Plugin Name: WooCommerce API Recent Orders Endpoint
 * Plugin URI: 
 * Description: Adds a recent orders endpoint to the WooCommerce API
 * Version: 1.0
 * Author: Saurabh Shukla
 * Author URI: http://hookrefineandtinker.com
 */










/**
 * Hooks into WooCommerce API and loads the recent orders' API class
 * 
 * @author: Saurabh Shukla <saurabh@yapapaya.com>
 */
class WC_API_Recent_Orders_Loader{
        
        public function init(){
                // load after WooCommerce API has loaded
                // so that we can extend the orders endpoint class
                add_action( 'woocommerce_api_loaded', array( $this, 'load' ) );
        }
        
        public function load(){
                // include the class file
                require_once plugin_dir_path( __FILE__ ).'class-wc-api-recent-orders.php';
        
                // filter the array of default api classes
                add_filter( 'woocommerce_api_classes', array( $this, 'register' ) );
        }
        
        public function register( $api_classes=array() ){
                
                // add our class to the existing API endpoints
                array_push( $api_classes, 'WC_API_Recent_Orders' );
        
                return $api_classes;
        }
}










//instantiate and initialise our loader class
$wc_api_recent_order_loader = new WC_API_Recent_Orders_Loader();
$wc_api_recent_order_loader->init();