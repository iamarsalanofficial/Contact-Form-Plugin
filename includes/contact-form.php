<?php

if(!defined('ABSPATH'))
{
die ('You cannot be here');
}



add_shortcode('contact', 'show_contact_form');
add_action('rest_api_init', 'create_rest_endpoint');
add_action('init', 'create_submissions_page');
add_action('add_meta_boxes', 'create_meta_box');
add_filter('manage_submission_posts_columns', 'custom_submission_columns');
add_action('manage_submission_posts_custom_column', 'fill_submission_columns', 10, 2);
add_action('init', 'setup_search');

function setup_search()
{
    global $type;
    if ($type == 'submission') {
        add_filter('posts_search', 'submission_search_override', 10, 2);
    }
}

function submission_search_override($search, $query)
{
    global $wpdb;
    if ($query->is_main_query() && !empty($query->query['s'])) {
        $sql = "
        OR EXISTS (
        SELECT * FROM {$wpdb->postmeta} WHERE post_id={$wpdb->posts}.ID 
        AND meta_value LIKE %s
        )";
        $like = '%' . $wpdb->esc_like($query->query['s']) . '%';
        $search = preg_replace("#\({$wpdb->posts}.post_title LIKE [^)]+\)\K#", $wpdb->prepare($sql, $like), $search);
    }
    return $search;
}

function fill_submission_columns($column, $post_id)
{
    switch ($column) {
        case 'name':
            echo get_post_meta($post_id, 'name', true);
            break;
        case 'email':
            echo get_post_meta($post_id, 'email', true);
            break;
        case 'phone':
            echo get_post_meta($post_id, 'phone', true);
            break;
        case 'message':
            echo get_post_meta($post_id, 'message', true);
            break;
    }
}

function custom_submission_columns($columns)
{
    $columns = array(
        'name' => __('Name', 'options-plugin'),
        'email' => __('Email', 'options-plugin'),
        'phone' => __('Phone', 'options-plugin'),
        'message' => __('Message', 'options-plugin')
    );
    return $columns;
}

function create_meta_box()
{
    add_meta_box('custom_contact_form', 'submission', 'display_submission', 'submission');
}

function display_submission()
{
    $postmetas = get_post_meta(get_the_ID());

    echo '<ul>';
    echo '<li><strong>Name:</strong> ' . get_post_meta(get_the_ID(), 'name', true) . '</li>';
    echo '<li><strong>Email:</strong> ' . get_post_meta(get_the_ID(), 'email', true) . '</li>';
    echo '<li><strong>Phone:</strong> ' . get_post_meta(get_the_ID(), 'phone', true) . '</li>';
    echo '<li><strong>Message:</strong> ' . get_post_meta(get_the_ID(), 'message', true) . '</li>';
    echo '</ul>';
}

function create_submissions_page()
{
    $args = [
        'public' => true,
        'has_archive' => true,
        'labels' => [
            'name' => 'submissions',
            'singular_name' => 'submission'
        ],
        'supports' => false,
        'capability_type' => 'post',
        'capabilities' => array(
            'create_posts' => false,
        ),
        'map_meta_cap' => true,
    ];
    register_post_type('submission', $args);
}

function show_contact_form()
{
    // Use wp_nonce_field to generate a nonce field
    $nonce = wp_nonce_field('wp_rest', '_wpnonce', true, false);
    // Include the form HTML, and add the nonce field
    include MY_PLUGIN_PATH . '/includes/template/contact-form.php';
    echo $nonce;
}

function create_rest_endpoint()
{
    register_rest_route('v1/contact-form', 'submit', array(
        'methods' => 'POST',
        'callback' => 'handle_enquiry',
        'permission_callback' => '__return_true' // You can add your permission callback here
    ));
}

function handle_enquiry($data)
{
    $params = $data->get_params();

    $field_name = sanitize_text_field($params['name']);
    $field_email = sanitize_email($params['email']);
    $field_phone = sanitize_text_field($params['phone']);
    $field_message = sanitize_textarea_field($params['message']);


    if (!isset($params['_wpnonce']) || !wp_verify_nonce($params['_wpnonce'], 'wp_rest')) {
        return new WP_REST_Response('Message not sent. Invalid nonce.', 422);
    }

    unset($params['_wpnonce']);
    unset($params['_wp_httpreferer']);

    // send the email message
    $headers = [];

    $admin_email = get_bloginfo('admin_email');
    $admin_name = get_bloginfo('name');

    $headers[] = "From: {$admin_name} <{$admin_email}>";
    $headers[] = "Reply-to: {$field_name} <{$field_email}>";
    $headers[] = "Content-Type: html/text";

    $subject = "New Enquiry from {$field_name}";

    $message = '';
    $message = "<h1>Message has been sent from {$field_name}</h1> <br />";

    $postarr = [
        'post_title' => $field_name,
        'post_type' => 'submission',
        'post_status' => 'publish'
    ];

    $post_id = wp_insert_post($postarr);

    foreach ($params as $label => $value)
     {
        
        switch($label)
        {
            case 'message':
                $value = sanitize_textarea_field($value);
            break;
            case 'email':
                    $value = sanitize_email($value);
            break;

            default:
            $value = sanitize_text_field($value), $value ;
        }

        add_post_meta($post_id, $label, sanitize_text_field($value), $value );

        $message .=  sanitize_text_field( ucfirst($label)) . ':' . $value . '<br />';

    }


    wp_mail($admin_email, $subject, $message, $headers);


    // Process the form data here
    return new WP_REST_Response('Message sent successfully.', 200);
}
