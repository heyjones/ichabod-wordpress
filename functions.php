<?php

namespace ichabod;

if ( ! defined ( 'ABSPATH' ) ) exit;
if ( ! defined ( 'WPINC' ) ) die;

define ( 'ICHABOD_URL', 'http://localhost:3000' );

add_action ( 'init', __NAMESPACE__ . '\\init' );

function init() {
    if ( ! is_admin() ) {
        add_action ( 'wp', __NAMESPACE__ . '\\wp' );
        add_filter ( 'wp_headers', __NAMESPACE__ . '\\wp_headers', 99 );
    }
    register_nav_menu ( 'ichabod', __( 'Ichabod' ) );
    add_action ( 'rest_api_init', __NAMESPACE__ . '\\rest_api_init', 99 );
}

function wp() {
    global $wp_query;
    /**
     * Simplify the response.
     * By default, wp_query contains a bunch of stuff we don't need.
     * It's possible that we might need to reinstate some of these down the line.
     */
    $site = array (
        'title' => get_bloginfo( 'name' ),
        'description' => get_bloginfo ( 'description' ),
        'url' => url ( get_bloginfo( 'url' ) ),
    );
    $posts = array ();
    if ( ! empty ( $wp_query->posts ) ) {
        foreach ( $wp_query->posts as $post ) {
            $posts[] = post ( $post );
        }
    }
    $post = array ();
    if ( ! empty ( $wp_query->post ) ) {
        $post = post ( $wp_query->post );
    }
    $query = array (
        'site' => $site,
        'posts' => $posts,
        'post' => $post,
        'is_embed' => $wp_query->is_embed,
        'is_404' => $wp_query->is_404,
        'is_search' => $wp_query->is_search,
        /**
         * Front page
         * For some reason, is_front_page doesn't exist in $wp_query.
         * We're going to add it so that we can render the front page template.
         */
        'is_front_page' => is_front_page(),
        'is_home' => $wp_query->is_home,
        'is_privacy_policy' => $wp_query->is_privacy_policy,
        'is_post_type_archive' => $wp_query->is_post_type_archive,
        'is_tax' => $wp_query->is_tax,
        'is_attachment' => $wp_query->is_attachment,
        'is_single' => $wp_query->is_single,
        'is_page' => $wp_query->is_page,
        'is_singular' => $wp_query->is_singular,
        'is_category' => $wp_query->is_category,
        'is_tag' => $wp_query->is_tag,
        'is_author' => $wp_query->is_author,
        'is_date' => $wp_query->is_date,
        'is_archive' => $wp_query->is_archive,
    );
    /** Send it! */
    wp_send_json ( $query );
}

function post ( $post ) {
    return array (
        'id' => $post->ID,
        'type' => $post->post_type,
        'title' => $post->post_title,
        'date' => $post->post_date,
        'date_gmt' => $post->post_date,
        'modified' => $post->post_modified,
        'modified_gmt' => $post->post_modified,
        'excerpt' => $post->post_excerpt,
        'content' => wpautop ( $post->post_content ),
        'permalink' => url ( get_the_permalink ( $post->ID ) ),
    );
}

function url ( $url ) {
    return str_replace ( get_bloginfo ( 'url' ), ICHABOD_URL, $url );
}

function wp_headers ( $headers ) {
    $headers['Access-Control-Allow-Origin'] = ICHABOD_URL;
    return $headers;
}

function rest_api_init () {
    /**
     * Navigation route
     * We need to expose our menu to the front end.
     */
    register_rest_route ( 'ichabod', 'navigation', array (
        'methods' => 'GET',
        'callback' => __NAMESPACE__ . '\\rest_api_ichabod_navigation',
    ) );
    /**
     * CORS issues
     * The client isn't allowed to fetch our endpoint... :/
     * This is definitely going to need to be handled better, I'm assuming it would break all kinds of stuff.
     * https://joshpress.net/access-control-headers-for-the-wordpress-rest-api/
     */
    remove_filter ( 'rest_pre_serve_request', 'rest_send_cors_headers' );
    add_filter ( 'rest_pre_serve_request', function ( $value ) {
        header ( 'Access-Control-Allow-Origin: ' . ICHABOD_URL );
        header ( 'Access-Control-Allow-Methods: GET' );
        header ( 'Access-Control-Allow-Credentials: true' );
        return $value;
    } );
}

function rest_api_ichabod_navigation () {
    $nav = array ();
    $items = wp_get_nav_menu_items ( 'Ichabod' );
    foreach ( $items as $item ) {
        $permalink = str_replace ( get_home_url(), ICHABOD_URL, $item->url );
        $nav[] = array (
            'title' => $item->title,
            'permalink' => $permalink,
            'target' => $item->target,
        );
    }
    wp_send_json ( $nav );
}