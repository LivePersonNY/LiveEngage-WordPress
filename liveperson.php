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
add_action('wp_footer', 'liveperson_wp_footer');

// Register this plugin's filters.
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'liveperson_settings_link');

/**
 * Hooks into admin_menu to add a LivePerson options page to the Settings menu.
 */
function liveperson_admin_menu() {
  add_options_page('LivePerson', 'LivePerson', 'manage_options', 'liveperson', 'liveperson_options_page');
}

/**
 * Adds the settings link to the LivePerson row on the plugins page.
 */
function liveperson_settings_link($links) {
  $settings_link = '<a href="options-general.php?page=liveperson">' . __('Settings', 'liveperson') . '</a>';
  array_unshift($links, $settings_link);
  return $links;
}

/**
 * Hooks into admin_init to register the LivePerson settings.
 */
function liveperson_admin_init() {
  register_setting('pluginPage', 'liveperson_settings');

  // Add the account identification section and field.
  add_settings_section('liveperson_pluginPage_settings', '', 'liveperson_settings_section_callback', 'pluginPage');
  add_settings_field('liveperson_account_number', __('Account number', 'liveperson'), 'liveperson_account_number_field_render', 'pluginPage', 'liveperson_pluginPage_settings');

  // Add the visibility settings fields.
  add_settings_field('liveperson_role_visibility', __('Role visibility', 'liveperson'), 'liveperson_role_visibility_checkboxes_render', 'pluginPage', 'liveperson_pluginPage_settings');
  add_settings_field('liveperson_post_visibility', __('Post visibility', 'liveperson'), 'liveperson_post_visibility_checkboxes_render', 'pluginPage', 'liveperson_pluginPage_settings');
  add_settings_field('liveperson_path_visibility', __('Path visibility', 'liveperson'), 'liveperson_path_visibility_textarea_render', 'pluginPage', 'liveperson_pluginPage_settings');
}

/**
 * Renders the settings section description.
 */
function liveperson_settings_section_callback() {
  // Empty for now but may be used for a branded HTML header in the future.
}

/**
 * Renders the account number form field.
 */
function liveperson_account_number_field_render() {
  echo '<input type="text" name="liveperson_settings[liveperson_account_number]" value="' . liveperson_account_number() . '"">';
}

/**
 * Renders the role visibility checkboxes.
 */
function liveperson_role_visibility_checkboxes_render() {
  // Output a heading for the checkboxes.
  echo '<p><strong>' . __('Do not show the livechat widget for users with the selected roles:') . '</strong></p>';

  echo '<p>';

  // Loop over all available roles on the site...
  $wp_roles = wp_roles();

  foreach ($wp_roles->roles as $key => $value) {
    // Create the HTML ID, name, and label for the checkbox.
    $id = 'edit-liveperson-role-visibility-' . $key;
    $name = 'liveperson_settings[liveperson_role_visibility][' . $key . ']';
    $label = htmlspecialchars($value['name'], ENT_QUOTES, 'UTF-8');

    // Output the actual checkbox HTML itself.
    echo '<p class="form-item-checkbox"><input id="' . $id . '" type="checkbox" name="' . $name . '" ' . checked(TRUE, liveperson_role_visibility_disabled($key), FALSE) . '/><label for="' . $id . '">' . $label . '</label></p>';
  }

  echo '</p>';
}

/**
 * Renders the post visibility checkboxes.
 */
function liveperson_post_visibility_checkboxes_render() {
  // Output a heading for the checkboxes.
  echo '<p><strong>' . __('Do not show the livechat widget on posts of the selected types:') . '</strong></p>';

  echo '<p>';

  // Loop over all public post types on the site...
  foreach (get_post_types(array('public' => TRUE), 'objects') as $key => $value) {
    // Create the HTML ID, name, and label for the checkbox.
    $id = 'edit-liveperson-role-visibility-' . $key;
    $name = 'liveperson_settings[liveperson_post_visibility][' . $key . ']';
    $label = htmlspecialchars($value->labels->singular_name, ENT_QUOTES, 'UTF-8');

    // Output the actual checkbox HTML itself.
    echo '<p class="form-item-checkbox"><input id="' . $id . '" type="checkbox" name="' . $name . '" ' . checked(TRUE, liveperson_post_visibility_disabled($key), FALSE) . '/><label for="' . $id . '">' . $label . '</label></p>';
  }

  echo '</p>';
}

/**
 * Renders the path visibility textarea.
 */
function liveperson_path_visibility_textarea_render() {
  echo '<p><strong>' . __('Do not show the livechat widget for paths matching the following patterns:') . '</strong></p>';
  echo '<p><textarea id="edit-liveperson-path-visibility" name="liveperson_settings[liveperson_path_visibility]" cols="70" rows="6">' . liveperson_path_visibility_patterns() . '</textarea></p>';
  echo '<p><small>Enter one WordPress path per line without a preceding slash. Use the \'*\' character as a wildcard<br />and <em class="placeholder">&lt;front&gt;</em> for the front page. Example paths are <em class="placeholder">about/</em> for an about page path and <em class="placeholder">category/*</em><br />for every category path.</small></p>';
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

/**
 * Returns the site's LivePerson account number.
 */
function liveperson_account_number() {
  $options = get_option('liveperson_settings');
  return $options['liveperson_account_number'];
}

/**
 * Determines whether or not livechat should be disabled for users having a
 * particular role.
 *
 * @param $role
 *   The machine name of the role whose visibility should be checked.
 *
 * @return
 *   Boolean indicating whether or not livechat should be disabled for the role.
 */
function liveperson_role_visibility_disabled($role) {
  $options = get_option('liveperson_settings');
  return isset($options['liveperson_role_visibility'][$role]) && $options['liveperson_role_visibility'][$role] === 'on';
}

/**
 * Determines whether or not livechat should be disabled on posts of a
 * particular type.
 *
 * @param $post_type
 *   The machine name of the post type whose visibility should be checked.
 *
 * @return
 *   Boolean indicating whether or not livechat should be disabled for the post.
 */
function liveperson_post_visibility_disabled($post_type) {
  $options = get_option('liveperson_settings');
  return isset($options['liveperson_post_visibility'][$post_type]) && $options['liveperson_post_visibility'][$post_type] === 'on';
}

/**
 * Returns a string of path patterns the livechat widget should not be shown on.
 */
function liveperson_path_visibility_patterns() {
  $options = get_option('liveperson_settings');
  return $options['liveperson_path_visibility'];
}

/**
 * Check if a path matches any pattern in a set of patterns.
 *
 * @param $path
 *   The path to match.
 * @param $patterns
 *   String containing a set of patterns separated by \n, \r or \r\n.
 *
 * @return
 *   Boolean value: TRUE if the path matches a pattern, FALSE otherwise.
 *
 * @see drupal_match_path() (in Drupal 7.x)
 */
function liveperson_match_path($path, $patterns) {
  static $regexps;

  if (!isset($regexps[$patterns])) {
    // Convert path settings to a regular expression.
    // Therefore replace newlines with a logical or and /* with asterisks
    $to_replace = array(
      '/(\r\n?|\n)/', // newlines
      '/\\\\\*/',     // asterisks
    );
    $replacements = array(
      '|',
      '.*',
    );
    $patterns_quoted = preg_quote($patterns, '/');
    $regexps[$patterns] = '/^(' . preg_replace($to_replace, $replacements, $patterns_quoted) . ')$/';
  }
  return (bool) preg_match($regexps[$patterns], $path);
}

/**
 * Returns a boolean indicating whether or not the page request used https.
 *
 * @see http://stackoverflow.com/questions/1283327/how-to-get-url-of-current-page-in-php#comment49052642_25651479
 */
function liveperson_is_https() {
  if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    return TRUE;
  }

  if ($_SERVER['SERVER_PORT'] == 443) {
    return TRUE;
  }

  return FALSE;
}

/**
 * Returns the full URL for the current page request.
 */
function liveperson_request_url() {
  if (liveperson_is_https()) {
    $protocol = 'https';
  }
  else {
    $protocol = 'http';
  }

  return $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

/**
 * Returns the WordPress path for the current page request with no preceding /.
 */
function liveperson_request_path() {
  return substr(liveperson_request_url(), strlen(home_url()) + 1);
}

/**
 * Returns the LivePerson JavaScript tag for the given account number.
 */
function liveperson_javascript_tag($account_number) {
  return "<script>window.lpTag=window.lpTag||{};if(typeof window.lpTag._tagCount==='undefined'){window.lpTag={site:'" . $account_number . "'||'',section:lpTag.section||'',autoStart:lpTag.autoStart===false?false:true,ovr:lpTag.ovr||{},_v:'1.5.1',_tagCount:1,protocol:location.protocol,events:{bind:function(app,ev,fn){lpTag.defer(function(){lpTag.events.bind(app,ev,fn);},0);},trigger:function(app,ev,json){lpTag.defer(function(){lpTag.events.trigger(app,ev,json);},1);}},defer:function(fn,fnType){if(fnType==0){this._defB=this._defB||[];this._defB.push(fn);}else if(fnType==1){this._defT=this._defT||[];this._defT.push(fn);}else{this._defL=this._defL||[];this._defL.push(fn);}},load:function(src,chr,id){var t=this;setTimeout(function(){t._load(src,chr,id);},0);},_load:function(src,chr,id){var url=src;if(!src){url=this.protocol+'//'+((this.ovr&&this.ovr.domain)?this.ovr.domain:'lptag.liveperson.net')+'/tag/tag.js?site='+this.site;}var s=document.createElement('script');s.setAttribute('charset',chr?chr:'UTF-8');if(id){s.setAttribute('id',id);}s.setAttribute('src',url);document.getElementsByTagName('head').item(0).appendChild(s);},init:function(){this._timing=this._timing||{};this._timing.start=(new Date()).getTime();var that=this;if(window.attachEvent){window.attachEvent('onload',function(){that._domReady('domReady');});}else{window.addEventListener('DOMContentLoaded',function(){that._domReady('contReady');},false);window.addEventListener('load',function(){that._domReady('domReady');},false);}if(typeof(window._lptStop)=='undefined'){this.load();}},start:function(){this.autoStart=true;},_domReady:function(n){if(!this.isDom){this.isDom=true;this.events.trigger('LPT','DOM_READY',{t:n});}this._timing[n]=(new Date()).getTime();},vars:lpTag.vars||[],dbs:lpTag.dbs||[],ctn:lpTag.ctn||[],sdes:lpTag.sdes||[],ev:lpTag.ev||[]};lpTag.init();}else{window.lpTag._tagCount+=1;}</script>";
}

/**
 * Hooks into wp_footer to add the JavaScript tag on appropriate pages.
 */
function liveperson_wp_footer() {
  // Ensure the current user is supposed to see the livechat.
  $current_user = wp_get_current_user();
  $roles = $current_user->roles;
  $role = array_shift($roles);

  if (liveperson_role_visibility_disabled($role)) {
    return;
  }

  // Ensure the current post type should have the livechat widget on it.
  if (liveperson_post_visibility_disabled(get_post_type())) {
    return;
  }

  // Ensure the widget isn't disabled on the front page.
  $patterns = liveperson_path_visibility_patterns();

  if (is_front_page() && liveperson_match_path('<front>', liveperson_path_visibility_patterns())) {
    return;
  }

  // Otherwise ensure the widget shouldn't be disabled on the current page.
  if (liveperson_match_path(liveperson_request_path(), $patterns)) {
    return;
  }

  // Assuming all checks passed, now echo the LivePerson JavaScript tag.
  echo liveperson_javascript_tag(liveperson_account_number());
}
