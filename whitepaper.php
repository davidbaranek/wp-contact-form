<?php

/**
 * Plugin Name:       Whitepaper
 * Description:       Block for sending whitepaper to a customer..
 * Requires at least: 6.6
 * Requires PHP:      7.2
 * Version:           0.1.3
 * Author:            David Baranek
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       whitepaper
 *
 * @package CreateBlock
 */

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Add plugin settings page
add_action('admin_menu', function () {
    add_options_page(
        'Whitepaper Settings',
        'Whitepaper',
        'manage_options',
        'whitepaper-settings',
        'whitepaper_settings_page'
    );
});

// Add a "Settings" link to the plugins page
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'whitepaper_add_settings_link');

function whitepaper_add_settings_link($links)
{
    $settings_link = '<a href="' . admin_url('options-general.php?page=whitepaper-settings') . '">Settings</a>';
    array_unshift($links, $settings_link);
    return $links;
}

// Render the settings page
function whitepaper_settings_page()
{
    if (! current_user_can('manage_options')) {
        return;
    }

    if (isset($_POST['whitepaper_recaptcha_site_key']) || isset($_POST['whitepaper_recaptcha_secret_key']) || isset($_POST['whitepaper_webhook_endpoint'])) {
        update_option('whitepaper_recaptcha_site_key', sanitize_text_field($_POST['whitepaper_recaptcha_site_key']));
        update_option('whitepaper_recaptcha_secret_key', sanitize_text_field($_POST['whitepaper_recaptcha_secret_key']));
        update_option('whitepaper_webhook_endpoint', sanitize_text_field($_POST['whitepaper_webhook_endpoint']));
        echo '<div class="updated"><p>Settings saved.</p></div>';
    }

    $site_key = get_option('whitepaper_recaptcha_site_key', '');
    $secret_key = get_option('whitepaper_recaptcha_secret_key', '');
    $webhook_endpoint = get_option('whitepaper_webhook_endpoint', '');

    echo '<div class="wrap">';
    echo '<h1>Whitepaper Settings</h1>';
    echo '<form method="post" action="">';
    echo '<table class="form-table">';
    echo '<tr valign="top">';
    echo '<th scope="row">reCAPTCHA Site Key</th>';
    echo '<td><input type="text" name="whitepaper_recaptcha_site_key" value="' . esc_attr($site_key) . '" class="regular-text" /></td>';
    echo '</tr>';
    echo '<tr valign="top">';
    echo '<th scope="row">reCAPTCHA Secret Key</th>';
    echo '<td><input type="text" name="whitepaper_recaptcha_secret_key" value="' . esc_attr($secret_key) . '" class="regular-text" /></td>';
    echo '</tr>';
    echo '<tr valign="top">';
    echo '<th scope="row">Webhook endpoint URL</th>';
    echo '<td><input type="text" name="whitepaper_webhook_endpoint" value="' . esc_attr($webhook_endpoint) . '" class="regular-text" /></td>';
    echo '</tr>';
    echo '</table>';
    echo '<p><input type="submit" class="button-primary" value="Save Changes" /></p>';
    echo '</form>';
    echo '</div>';
}

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */
function create_block_whitepaper_block_init()
{
    $site_key = get_option('whitepaper_recaptcha_site_key', '');

    register_block_type(__DIR__ . '/build', [
        'render_callback' => 'render_whitepaper_block',
        'attributes' => [
            'siteKey' => [
                'type' => 'string',
                'default' => $site_key,
            ],
            "template" => [
                "type" => "string",
                "default"=> "dentapreg"
            ]
        ],
    ]);
}
add_action('init', 'create_block_whitepaper_block_init');

/**
 * Enqueue reCAPTCHA script only on pages where the Whitepaper block is present.
 */
function enqueue_recaptcha_script()
{
    if (is_admin()) {
        return; // Do not enqueue script in admin area.
    }

    // Check if the current page contains the whitepaper block.
    if (has_block('adm/whitepaper')) {
        $site_key = get_option('whitepaper_recaptcha_site_key', '');
        if (! empty($site_key)) {
            wp_enqueue_script(
                'recaptcha-script',
                'https://www.google.com/recaptcha/api.js?render=' . esc_attr($site_key),
                [],
                null,
                true
            );
        }
    }
}
add_action('wp_enqueue_scripts', 'enqueue_recaptcha_script');

// Register the REST API endpoint
add_action('rest_api_init', function () {
    register_rest_route('whitepaper/v1', '/submit/', [
        'methods'  => 'POST',
        'callback' => 'process_whitepaper_submission',
        'permission_callback' => '__return_true', // Open to all users
    ]);
});

/**
 * Handles the contact submission, sends an email, and calls a webhook.
 */
function process_whitepaper_submission(WP_REST_Request $request)
{

    $data = $request->get_json_params();

    // Validate required fields
    if (empty($data['first_name']) || empty($data['last_name']) || empty($data['email']) || empty($data['type'])) {
        return new WP_Error('missing_fields', 'All fields are required.', ['status' => 400]);
    }
    if (empty($data['recaptchaToken'])) {
        return new WP_Error('missing_recaptcha_token', "You did not submit reCAPTCHA token.", ['status' => 400]);
    }

    // Verify reCAPTCHA token
    $recaptcha_token = $data['recaptchaToken'];
    $recaptcha_response = verify_recaptcha($recaptcha_token);

    if (is_wp_error($recaptcha_response)) {
        return $recaptcha_response;
    }

    $first_name  = sanitize_text_field($data['first_name']);
    $last_name   = sanitize_text_field($data['last_name']);
    $email       = sanitize_email($data['email']);
    $type       = sanitize_text_field($data['type']);
    $subscribe   = isset($data['subscribe']) ? filter_var($data['subscribe'], FILTER_VALIDATE_BOOLEAN) : false;

    $email_subject = 'Your Whitepaper Is Ready to Download!';
    $email_body    = generate_email_template($first_name, $last_name, $email, $subscribe, $type);

    $headers = ['Content-Type: text/html; charset=UTF-8'];
    if (!wp_mail($email, $email_subject, $email_body, $headers)) {
        return new WP_Error('email_failed', 'We have a problem with email delivery. Please check that you have entered your email correctly or try again later.', ['status' => 500]);
    }

    // Call the webhook for sync contac to FileMaker and Mailchimp
    $webhook_url = get_option('whitepaper_webhook_endpoint', '');
    $response = wp_remote_post($webhook_url, [
        'body'    => json_encode($data),
        'headers' => ['Content-Type' => 'application/json'],
    ]);


    if (is_wp_error($response)) {
        return new WP_Error('webhook_failed', 'There was an error with your request. Please try again later.', ['status' => 500]);
    }

    return rest_ensure_response(['message' => 'The whitepaper was sent to your email!']);
}

/**
 * Verifies the reCAPTCHA token with Google.
 *
 * @param string $token The reCAPTCHA token from the client.
 * @return bool|WP_Error True if the token is valid, WP_Error otherwise.
 */
function verify_recaptcha($token)
{
    $secret_key = get_option('whitepaper_recaptcha_secret_key', '');; // Replace with your actual secret key
    $response = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', [
        'body' => [
            'secret'   => $secret_key,
            'response' => $token,
        ],
    ]);

    if (is_wp_error($response)) {
        return new WP_Error('recaptcha_failed', 'Unable to verify reCAPTCHA. Please try again later.', ['status' => 500]);
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);

    if (empty($body['success']) || $body['success'] !== true || $body['score'] < 0.5) {
        return new WP_Error('recaptcha_invalid', 'reCAPTCHA verification failed. You might be a bot.', ['status' => 403]);
    }

    return true;
}

/**
 * Loads the email template.
 */
function generate_email_template($first_name, $last_name, $email, $subscribe, $type)
{
    ob_start();
    if ($type === "dentapreg") {
        include plugin_dir_path(__FILE__) . 'email-template-dentapreg.php';
    } else {
        include plugin_dir_path(__FILE__) . 'email-template-fibrafill.php';
    }
    return ob_get_clean();
}
