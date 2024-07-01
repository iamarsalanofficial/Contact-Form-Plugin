<?php

add_shortcode('contact', 'show_contact_form');
add_action('rest_api_init', 'create_rest_endpoint');
add_action('init', 'create_submissions_page');
add_action('add_meta_boxes', 'create_meta_box');


function create_meta_box() 
{
    add_meta_box ('custom_contact_form', 'submission', 'display_submission', 'submission');
}


function display_submission()
{
    $postmetas = get_post_meta( get_the_ID());
    echo '<ul>';
    foreach($postmetas as $key => $value )
    {
        ray($value);
        echo '<li>'. $key. ':' . $value[0] . '</li>';
    }
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
        'supports' => false
        // 'capability_type' => 'post',
        // 'capability' => [ 'create_posts' => false],
        // 'capabilities' => ['create_posts' => 'do_not_allow'], 
        
    ];
    register_post_type('submission', $args);
}

function show_contact_form() {
    // Use wp_nonce_field to generate a nonce field
    $nonce = wp_nonce_field('wp_rest', '_wpnonce', true, false);
    // Include the form HTML, and add the nonce field
    include MY_PLUGIN_PATH . '/includes/template/contact-form.php';
    echo $nonce;
}

function create_rest_endpoint() {
    register_rest_route('v1/contact-form', 'submit', array(
        'methods' => 'POST',
        'callback' => 'handle_enquiry',
        'permission_callback' => '__return_true' // You can add your permission callback here
    ));
}

function handle_enquiry($data) {
    $params = $data->get_params();
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
    $headers[] = "Reply-to: {$params['name']} <{$params['email']}>";
    $headers[] = "Content-Type: html/text";    


    $subjet = "New Enquiry from {$params['name']}";


    $message = '';
    $message = "<h1>Message has been sent from {$params['name']}</h1> <br />";

    $postarr = [
        'post_title' => $params['name'],
        'post_type' => 'submission'
    ];

    $post_id = wp_insert_post($postarr);


    foreach($params as $label => $value)
    {
        $message .= ucfirst($label) . ':' . $value . '<br />';
        add_post_meta($post_id,  $label, $value);
    }

  

    
    wp_mail( $admin_email,  $subjet, $message, $headers);


     // Process the form data here
     return new WP_REST_Response('Message sent successfully.', 200);

}
