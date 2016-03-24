<?php
/**
 * Plugin Name: LivePerson LiveChat + Messaging
 * Plugin URI:  https://register.liveperson.com/wordpress
 * Description: LivePerson offers a modern way to talk to visitors regardless of how they visit your website. This plugin adds it to specified pages on your site.
 * Version:     1.0.0
 * Author:      LivePerson, Inc.
 * Author URI:  http://www.liveperson.com
 * License:     GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Exit if this file was accessed directly.
if (!defined('ABSPATH')) {
  exit;
}

// Register this plugin's hooks.
add_action('admin_menu', 'liveperson_admin_menu');
add_action('admin_init', 'liveperson_admin_init');

/**
 * Hooks into admin_menu to add a LivePerson options page to the Settings menu.
 */
function liveperson_admin_menu() {
  add_options_page('LivePerson', 'LivePerson', 'manage_options', 'liveperson', 'liveperson_options_page');
}

/**
 * Hooks into admin_init to register the LivePerson settings.
 */
function liveperson_admin_init() {
  register_setting('pluginPage', 'liveperson_settings');
  add_settings_section('liveperson_pluginPage_settings', __('Section title', 'liveperson'), 'liveperson_settings_section_callback', 'pluginPage');
  add_settings_field('liveperson_account_number', __('Account number', 'liveperson'), 'liveperson_account_number_render', 'pluginPage', 'liveperson_pluginPage_settings');
}

/**
 * Renders the account number form field.
 */
function liveperson_account_number_render() {
  $options = get_option('liveperson_settings');
  echo '<input type="text" name="liveperson_settings[liveperson_account_number]" value="' . $options['liveperson_account_number'] . '"">';
}

/**
 * Renders the settings section description.
 */
function liveperson_settings_section_callback() {
  echo __('This is a section description', 'liveperson');
}

/**
 * Outputs the LivePerson options page content.
 */
function liveperson_options_page() {
  echo '<form action="options.php" method="post"><h2>LivePerson</h2>';

  settings_fields('pluginPage');
  do_settings_sections('pluginPage');
  submit_button();

  echo '</form>';
}
