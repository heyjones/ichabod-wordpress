<?php

namespace ichabod;

if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! defined( 'WPINC' ) ) die;

add_action ( 'init', __NAMESPACE__ . '\\init' );

function init() {
    if ( ! is_admin() ) {
        add_action( 'wp', __NAMESPACE__ . '\\wp' );
    }
}

function wp() {
    global $wp_query;
    /**
     * Simplify the response.
     * By default, wp_query contains a bunch of stuff we don't need.
     * It's possible that we might need to reinstate some of these down the line.
     */
    unset ( $wp_query->query );
    unset ( $wp_query->query_vars );
    unset ( $wp_query->queried_object );
    unset ( $wp_query->queried_object_id );
    unset ( $wp_query->request );
    unset ( $wp_query->tax_query );
    unset ( $wp_query->meta_query );
    unset ( $wp_query->date_query );
    /**
     * Post permalinks
     * Since we're depending on WordPress for our routing, React won't know where the post should live.
     * It might be worth applying a filter to the permalink.
     */
    if ( ! empty ( $wp_query->posts ) ) {
        foreach ( $wp_query->posts as $post ) {
            $post->permalink = get_the_permalink ( $post->ID );
        }
    }
    /** Send it! */
    wp_send_json ( $wp_query );
}