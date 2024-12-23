<?php

/**
 * Plugin Name:       Contact form
 * Description:       Simple contact form for Gutenberg
 * Requires at least: 6.6
 * Requires PHP:      7.2
 * Version:           0.1.0
 * Author:            David Baranek
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       contact-form
 *
 * @package CreateBlock
 */

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Add plugin settings page
add_action('admin_menu', function () {
    add_options_page(
        'Contact Form Settings',
        'Contact form',
        'manage_options',
        'contact-form-settings',
        'contact_form_settings_page'
    );
});

// Add a "Settings" link to the plugins page
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'contact_form_add_settings_link');

function contact_form_add_settings_link($links)
{
    $settings_link = '<a href="' . admin_url('options-general.php?page=contact-form-settings') . '">Settings</a>';
    array_unshift($links, $settings_link);
    return $links;
}

// Render the settings page
function contact_form_settings_page()
{
    if (! current_user_can('manage_options')) {
        return;
    }

    if (isset($_POST['contact_form_recaptcha_site_key']) || isset($_POST['contact_form_recaptcha_secret_key']) || isset($_POST['contact_form_webhook_endpoint'])) {
        update_option('contact_form_recaptcha_site_key', sanitize_text_field($_POST['contact_form_recaptcha_site_key']));
        update_option('contact_form_recaptcha_secret_key', sanitize_text_field($_POST['contact_form_recaptcha_secret_key']));
        update_option('contact_form_webhook_endpoint', sanitize_text_field($_POST['contact_form_webhook_endpoint']));
        update_option('contact_form_email', sanitize_text_field($_POST['contact_form_email']));
        echo '<div class="updated"><p>Settings saved.</p></div>';
    }

    $site_key = get_option('contact_form_recaptcha_site_key', '');
    $secret_key = get_option('contact_form_recaptcha_secret_key', '');
    $webhook_endpoint = get_option('contact_form_webhook_endpoint', '');
    $contact_form_email = get_option('contact_form_email', '');

    echo '<div class="wrap">';
    echo '<h1>Contact Form Settings</h1>';
    echo '<form method="post" action="">';
    echo '<table class="form-table">';
    echo '<tr valign="top">';
    echo '<th scope="row">reCAPTCHA Site Key</th>';
    echo '<td><input type="text" name="contact_form_recaptcha_site_key" value="' . esc_attr($site_key) . '" class="regular-text" /></td>';
    echo '</tr>';
    echo '<tr valign="top">';
    echo '<th scope="row">reCAPTCHA Secret Key</th>';
    echo '<td><input type="text" name="contact_form_recaptcha_secret_key" value="' . esc_attr($secret_key) . '" class="regular-text" /></td>';
    echo '</tr>';
    echo '<tr valign="top">';
    echo '<th scope="row">Webhook endpoint URL</th>';
    echo '<td><input type="text" name="contact_form_webhook_endpoint" value="' . esc_attr($webhook_endpoint) . '" class="regular-text" /></td>';
    echo '</tr>';
    echo '<tr>';
    echo '<th scope="row">Email where submissions will be sent</th>';
    echo '<td><input type="text" name="contact_form_email" value="' . esc_attr($contact_form_email) . '" class="regular-text" /></td>';
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
function contact_form_block_init()
{
    $site_key = get_option('contact_form_recaptcha_site_key', '');

    register_block_type(__DIR__ . '/build', [
        'render_callback' => 'render_contact_form_block',
        'attributes' => [
            'siteKey' => [
                'type' => 'string',
                'default' => $site_key,
            ]
        ],
    ]);
}
add_action('init', 'contact_form_block_init');

/**
 * Enqueue reCAPTCHA script only on pages where the contact_form block is present.
 */
function enqueue_recaptcha_script()
{
    if (is_admin()) {
        return; // Do not enqueue script in admin area.
    }

    // Check if the current page contains the contact_form block.
    if (has_block('bardav/contact-form')) {
        $site_key = get_option('contact_form_recaptcha_site_key', '');
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
    register_rest_route('contact-form/v1', '/submit/', [
        'methods'  => 'POST',
        'callback' => 'process_contact_form_submission',
        'permission_callback' => '__return_true', // Open to all users
    ]);
});

/**
 * Handles the contact submission, sends an email, and calls a webhook.
 */
function process_contact_form_submission(WP_REST_Request $request)
{

    $data = $request->get_json_params();

    // Validate required fields
    if (empty($data['first_name']) || empty($data['last_name']) || empty($data['email']) || empty($data['message'])) {
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
    $message     = sanitize_text_field($data['message']);

    $email_subject = 'New contact form submission';
    $email_body    = generate_email_template($first_name, $last_name, $email, $message);
    $admin_email = get_option('contact_form_email', '');

    $headers = ['Content-Type: text/html; charset=UTF-8'];
    if (!wp_mail($admin_email, $email_subject, $email_body, $headers)) {
        return new WP_Error('email_failed', 'We have a problem with email delivery. Please check that you have entered your email correctly or try again later.', ['status' => 500]);
    }

    // Call the webhook
    $webhook_url = get_option('contact_form_webhook_endpoint', '');
    if (!empty($webhook_url)) {

        $response = wp_remote_post($webhook_url, [
            'body'    => json_encode($data),
            'headers' => ['Content-Type' => 'application/json'],
        ]);
    
    
        if (is_wp_error($response)) {
            return new WP_Error('webhook_failed', 'There was an error with your request. Please try again later.', ['status' => 500]);
        }
    }

    return rest_ensure_response(['message' => 'The contact_form was sent to your email!']);
}

/**
 * Verifies the reCAPTCHA token with Google.
 *
 * @param string $token The reCAPTCHA token from the client.
 * @return bool|WP_Error True if the token is valid, WP_Error otherwise.
 */
function verify_recaptcha($token)
{
    $secret_key = get_option('contact_form_recaptcha_secret_key', '');; // Replace with your actual secret key
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
 * Generates an email template for a new contact form submission.
 *
 * @param string $first_name The first name of the user.
 * @param string $last_name The last name of the user.
 * @param string $email The email of the user.
 * @param string $message The message submitted by the user.
 *
 * @return string The HTML email template.
 */
function generate_email_template($first_name, $last_name, $email, $message)
{
    $html = '
    <html>
        <head>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                }
                table {
                    width: 100%;
                    border-collapse:separate;
                    border-radius: 5px;
                    border: 1px solid #ddd;
                    border-spacing: 0;
                }
                th, td {
                    padding: 10px;
                    text-align: left;
                    border-top: 1px solid #ddd;
                }
                th {
                    background-color: #f4f4f4;
                }
                tr:first-child th, tr:first-child td {
                    border-top: none;
                }
                .message {
                    margin-top: 20px;
                    padding: 10px;
                    background-color: #f9f9f9;
                    border: 1px solid #ddd;
                    border-radius: 5px;
                }
            </style>
        </head>
        <body>
            <h2>New Contact Form Submission</h2>
            <table>
                <tr>
                    <th>First Name</th>
                    <td>' . esc_html($first_name) . '</td>
                </tr>
                <tr>
                    <th>Last Name</th>
                    <td>' . esc_html($last_name) . '</td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td>' . esc_html($email) . '</td>
                </tr>
            </table>
            <h3>Message:</h3>
            <div class="message">
                ' . nl2br(esc_html($message)) . '
            </div>
        </body>
    </html>
    ';

    return $html;
}
