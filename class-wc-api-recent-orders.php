<?php

/**
 * WooCommerce API Recent Orders Class
 * 
 * Adds a /recent_orders endpoint to WooCommerce API
 *
 * @author Saurabh Shukla <saurabh@yapapaya.com>
 */
if ( !defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly
}

if ( !class_exists( 'WC_API_Recent_Orders' ) ) {

        class WC_API_Recent_Orders extends WC_API_Orders {

                /**
                 * @var string $base the route base
                 */
                protected $base = '/recent_orders';

                /**
                 * Register the routes with callback for this class
                 *
                 * GET /recent_orders/<count>
                 *
                 * @param array $routes
                 * @return array
                 */
                public function register_routes( $routes ) {

                        # GET|POST /recent_orders/
                        $routes[ $this->base ] = array(
                                array( array( $this, 'get_recent' ), WC_API_Server::READABLE )
                        );

                        # GET|POST /recent_orders/<count>
                        $routes[$this->base . '/(?P<count>\d+)'] = array(
                                array( array( $this, 'get_recent' ), WC_API_Server::READABLE )
                        );

                        return $routes;
                }

                /**
                 * Get recent orders
                 * 
                 * @param int $count The number of recent orders to retreive
                 * @return array
                 */
                public function get_recent( $count = 0 ) {

                        // it is going to be a string
                        $count = (int) $count;

                        // if it is anything other than a positive integer,
                        // retreive the default count

                        if ( $count < 1 ) {
                                $count = get_option( 'posts_per_page' );
                        }

                        // add it to the filter array
                        $filter = array(
                                'posts_per_page' => $count,
                                // forcing these, just in case
                                'order' => 'DESC',
                                'orderby' => 'date'
                        );

                        // get the orders
                        $orders = $this->get_orders( null, $filter );

                        return $orders;
                }

                /**
                 * Get all orders. Clone of the parent class's method
                 * Duplicated, otherwise the parents get_orders will call the parents'
                 * query_orders
                 *
                 * @param string $fields
                 * @param array $filter
                 * @param string $status
                 * @param int $page
                 * @return array
                 */
                public function get_orders( $fields = null, $filter = array() ) {

                        // get the WP_Query instance
                        $query = $this->query_orders( $filter );

                        // initialise the orders array
                        $orders = array();

                        // populate the orders array
                        foreach ( $query->posts as $order_id ) {

                                if ( !$this->is_readable( $order_id ) )
                                        continue;

                                $orders[] = current( $this->get_order( $order_id, $fields, $filter ) );
                        }

                        // add pagination headers
                        $this->server->add_pagination_headers( $query );

                        //return for output
                        return array( 'orders' => $orders );
                }

                /**
                 * Helper method to get order post objects,
                 * has to be redefined here just to add the posts_per_page parameter
                 *
                 * @param array $args request arguments for filtering query
                 * @return WP_Query
                 */
                private function query_orders( $args ) {

                        // set base query arguments
                        $query_args = array(
                                'fields' => 'ids',
                                'post_type' => 'shop_order',
                                'post_status' => array_keys( wc_get_order_statuses() ),
                        );

                        // add status argument
                        if (!empty( $args[ 'status' ] ) ) {

                                $statuses = 'wc-' . str_replace( ',', ',wc-', $args[' status' ] );
                                $statuses = explode( ',', $statuses );
                                $query_args[ 'post_status' ] = $statuses;

                                unset( $args[ 'status' ] );
                        }

                        $query_args = $this->merge_query_args( $query_args, $args );

                        // the merge_query_args strips the count

                        $query_args[ 'posts_per_page' ] = $args[ 'posts_per_page' ];

                        // new instance of WP_Query
                        return new WP_Query( $query_args );
                }

        }

}