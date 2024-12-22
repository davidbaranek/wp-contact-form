=== Whitepaper Block ===
Contributors: David Baránek
Tags: whitepaper, contact form, Gutenberg, block editor
Requires at least: 5.8
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A custom Gutenberg block for WordPress that allows users to input their contact details to request a whitepaper. Submitted data is sent to Claris Connect via a webhook, adding the contact to your FileMaker system.

== Description ==

**Whitepaper Block** is a custom block for the WordPress block editor that integrates seamlessly into your pages or posts. It provides a clean, user-friendly form where visitors can submit their contact information to receive a whitepaper. 

Key features:
- Collects first name, last name, email, and other optional details.
- Sends form data to a Claris Connect webhook for integration with FileMaker.
- Displays validation messages for required fields.
- Provides success or error feedback after submission.

This block is ideal for generating leads, distributing resources, and streamlining contact management through FileMaker.

== Installation ==

1. Upload the plugin folder to the `/wp-content/plugins/` directory or install it via the WordPress Plugins menu.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Add the "Whitepaper" block to any post or page using the WordPress block editor.
4. Configure the Claris Connect webhook endpoint in your backend settings or script.

== Usage ==

1. Insert the "Whitepaper" block into any post or page.
2. Customize the form fields or design using WordPress block editor options.
3. On form submission, the data will be sent to the Claris Connect webhook for processing.
4. A success or error message will be displayed to the user.

== Block Details ==

- **Block Name:** Whitepaper Block
- **Category:** Widgets
- **Icon:** admin-page
- **Attributes:**
  - `type`: A customizable field indicating the type of whitepaper being requested (default: "dentapreg").

== Development Notes ==

The form submission leverages the WordPress REST API and requires the Claris Connect webhook URL to be configured. Ensure proper validation and security measures (e.g., nonce) are in place for production.

**Dependencies:**
- Claris Connect webhook
- FileMaker system for contact management

== Changelog ==

= 1.0.0 =
* Initial release of the Whitepaper Block.

== License ==

This plugin is licensed under the GPLv2 or later. See [License URI](https://www.gnu.org/licenses/gpl-2.0.html) for more details.