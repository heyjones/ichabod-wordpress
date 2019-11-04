<?php

namespace ichabod;

if ( ! defined ( 'ABSPATH' ) ) exit;
if ( ! defined ( 'WPINC' ) ) die;

define ( 'ICHABOD_URL', 'http://localhost:3000' );

add_action ( 'init', __NAMESPACE__ . '\\init' );

function init() {
    if ( ! is_admin() ) {
        add_action ( 'wp', __NAMESPACE__ . '\\wp' );
        add_filter ( 'wp_headers', __NAMESPACE__ . '\\wp_headers' );
    }
}

function wp() {
    global $wp_query;
    /**
     * Post permalinks
     * Since we're depending on WordPress for our routing, React won't know where the post should live.
     * It might be worth applying a filter to the permalink so it would update the view link in wp-admin.
     * We may also want to consider simplifying the data being returned from the post object as a lot of it is probably useless.
     */
    if ( ! empty ( $wp_query->posts ) ) {
        foreach ( $wp_query->posts as $post ) {
            $permalink = get_the_permalink ( $post->ID );
            $post->permalink = str_replace ( get_home_url(), ICHABOD_URL, $permalink );
        }
    }
    /**
     * Simplify the response.
     * By default, wp_query contains a bunch of stuff we don't need.
     * It's possible that we might need to reinstate some of these down the line.
     */
    $query = array (
        'posts' => $wp_query->posts,
        'post' => $wp_query->post,
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

function wp_headers ( $headers ) {
    $headers['Access-Control-Allow-Origin'] = ICHABOD_URL;
    return $headers;
}
