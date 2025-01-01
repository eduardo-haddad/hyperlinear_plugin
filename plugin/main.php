<?php

/**
 * Plugin Name: Hyperlinear Plugin
 * Description: Adds users to a Brevo (Sendinblue) contact list via a REST API endpoint.
 * Author: Eduardo Di Nardo
 * Version: 1.0
 */

require_once __DIR__ . '/helpers.php';

$env_file = plugin_dir_path(__FILE__) . '.env';

function hyperlinear_newsletter_signup() {
    $first_name = sanitize_text_field($_GET['first_name'] ?? '');
    $last_name = sanitize_text_field($_GET['last_name'] ?? '');
    $email = sanitize_email($_GET['email'] ?? '');
    $contact_list_id = sanitize_text_field($_GET['list_id'] ?? '');

    // Validate required parameters
    if (empty($first_name) || empty($last_name) || empty($email)) {
        wp_send_json(['status' => 400, 'message' => 'Missing required parameters: first_name, last_name, or email.']);
    }

    if (empty($contact_list_id)) {
        wp_send_json(['status' => 400, 'message' => 'Brevo list ID is required.']);
    }

    // Call Brevo API
    $response = hyperlinear_brevio_insert_contact($email, $first_name, $last_name, $contact_list_id);

    // Check for connection errors
    if (is_wp_error($response)) {
        wp_send_json(['status' => 500, 'message' => 'Failed to connect to Brevo API.']);
    }

    $status_code = wp_remote_retrieve_response_code($response);
    $response_body = json_decode(wp_remote_retrieve_body($response), true);

    // Handle Brevo API response
    if ($status_code === 201) {
        wp_send_json(['status' => 200, 'message' => "Thank you! The email {$email} was added to our contact list."]);
    } elseif ($status_code === 400 && isset($response_body['code']) && $response_body['code'] === 'duplicate_parameter') {
        wp_send_json(['status' => 409, 'message' => 'Sorry, the provided email is already registered.']);
    } else {
        wp_send_json(['status' => 500, 'message' => 'Unexpected error.', 'error' => $response_body]);
    }
}

function hyperlinear_brevio_insert_contact($email, $first_name, $last_name, $contact_list_id) {
    // Load .env variables
    global $env_file;

    if (load_env_file($env_file)) {
        $api_key = $_ENV['BREVO_API_KEY'] ?? null;
        $brevo_endpoint = $_ENV['BREVO_ENDPOINT'] ?? null;
    } else {
        error_log('Failed to load .env file.');
    }

    $body = [
        'email' => $email,
        'attributes' => [
            'FIRSTNAME' => $first_name,
            'LASTNAME' => $last_name
        ],
        'listIds' => [(int)$contact_list_id]
    ];

    $args = [
        'body' => json_encode($body),
        'headers' => [
            'Content-Type' => 'application/json',
            'api-key' => $api_key
        ]
    ];

    // Insert contact into Brevo
    return wp_remote_post($brevo_endpoint, $args);
}

add_action('template_redirect', function () {
    if (is_page('newsletter-signup')) {
        hyperlinear_newsletter_signup();
    }
});
