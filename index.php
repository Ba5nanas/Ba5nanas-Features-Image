<?php

/*
  Plugin Name: Ba5nanas Featured Image URL
  Plugin URI:
  Description:
  Version: 1.0
  Author: Ba5nanas
  Author URI: http://themeforest.net/user/Ba5nanas
  License: MIT
 */

function ba5nanas_featured_image_add_meta_box() {

    $screens = array('post', 'page');

    foreach ($screens as $screen) {

        add_meta_box(
                'ba5nanas_featured_image', 'Ba5nanas Featured Image', 'ba5nanas_featured_image_meta_box_callback', $screen
        );
    }
}

add_action('add_meta_boxes', 'ba5nanas_featured_image_add_meta_box');

/**
 * Prints the box content.
 * 
 * @param WP_Post $post The object for the current post/page.
 */
function ba5nanas_featured_image_meta_box_callback($post) {

    // Add an nonce field so we can check for it later.
    wp_nonce_field('myplugin_meta_box', 'myplugin_meta_box_nonce');

    /*
     * Use get_post_meta() to retrieve an existing value
     * from the database and use the value for the form.
     */
    $value = get_post_meta($post->ID, '_ba5nanas_featured_image_meta', true);

    echo '<label for="">';
    _e('URL Image', 'myplugin_textdomain');
    echo '</label> ';
    echo '<input type="text" id="myplugin_new_field" style="width:100%;" name="myplugin_new_field" value="' . esc_attr($value) . '" size="25" />';
}

/**
 * When the post is saved, saves our custom data.
 *
 * @param int $post_id The ID of the post being saved.
 */
function ba5nanas_featured_image_save_meta_box_data($post_id) {

    /*
     * We need to verify this came from our screen and with proper authorization,
     * because the save_post action can be triggered at other times.
     */

    // Check if our nonce is set.
    if (!isset($_POST['myplugin_meta_box_nonce'])) {
        return;
    }

    // Verify that the nonce is valid.
    if (!wp_verify_nonce($_POST['myplugin_meta_box_nonce'], 'myplugin_meta_box')) {
        return;
    }

    // If this is an autosave, our form has not been submitted, so we don't want to do anything.
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Check the user's permissions.
    if (isset($_POST['post_type']) && 'page' == $_POST['post_type']) {

        if (!current_user_can('edit_page', $post_id)) {
            return;
        }
    } else {

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
    }

    /* OK, it's safe for us to save the data now. */

    // Make sure that it is set.
    if (!isset($_POST['myplugin_new_field'])) {
        return;
    }

    // Sanitize user input.
    $my_data = sanitize_text_field($_POST['myplugin_new_field']);

    // Update the meta field in the database.
    update_post_meta($post_id, '_ba5nanas_featured_image_meta', $my_data);
}

add_action('save_post', 'ba5nanas_featured_image_save_meta_box_data');

function ba5nanas_featured_image_action_callback($html = "", $post_id = "", $post_thumbnail_id = "", $size = array(), $attr = array()) {
    global $post;
    $value = get_post_meta($post->ID, '_ba5nanas_featured_image_meta', true);
    return "<img src='{$value}' class='ba5nanas-features-image'>";
}

add_filter( 'post_thumbnail_html', 'ba5nanas_featured_image_action_callback');
