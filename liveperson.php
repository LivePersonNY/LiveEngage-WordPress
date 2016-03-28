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
 *
 * @todo Add checkboxes to filter tag visibility by user role.
 * @todo Add checkboxes to filter tag visibility by post type.
 * @todo Add textarea to filter tag visibility by path.
 */
function liveperson_admin_init() {
  register_setting('pluginPage', 'liveperson_settings');
  add_settings_section('liveperson_pluginPage_settings', __('Section title', 'liveperson'), 'liveperson_settings_section_callback', 'pluginPage');
  add_settings_field('liveperson_account_number', __('Account number', 'liveperson'), 'liveperson_account_number_field_render', 'pluginPage', 'liveperson_pluginPage_settings');
}

/**
 * Renders the account number form field.
 */
function liveperson_account_number_field_render() {
  echo '<input type="text" name="liveperson_settings[liveperson_account_number]" value="' . liveperson_account_number() . '"">';
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

/**
 * Returns the site's LivePerson account number.
 */
function liveperson_account_number() {
  $options = get_option('liveperson_settings');
  return $options['liveperson_account_number'];
}

/**
 * Returns the LivePerson JavaScript tag for the given accoutn number.
 */
function liveperson_javascript_tag($account_number) {
  return "<script>window.lpTag=window.lpTag||{};if(typeof window.lpTag._tagCount==='undefined'){window.lpTag={site:'" . $account_number . "'||'',section:lpTag.section||'',autoStart:lpTag.autoStart===false?false:true,ovr:lpTag.ovr||{},_v:'1.5.1',_tagCount:1,protocol:location.protocol,events:{bind:function(app,ev,fn){lpTag.defer(function(){lpTag.events.bind(app,ev,fn);},0);},trigger:function(app,ev,json){lpTag.defer(function(){lpTag.events.trigger(app,ev,json);},1);}},defer:function(fn,fnType){if(fnType==0){this._defB=this._defB||[];this._defB.push(fn);}else if(fnType==1){this._defT=this._defT||[];this._defT.push(fn);}else{this._defL=this._defL||[];this._defL.push(fn);}},load:function(src,chr,id){var t=this;setTimeout(function(){t._load(src,chr,id);},0);},_load:function(src,chr,id){var url=src;if(!src){url=this.protocol+'//'+((this.ovr&&this.ovr.domain)?this.ovr.domain:'lptag.liveperson.net')+'/tag/tag.js?site='+this.site;}var s=document.createElement('script');s.setAttribute('charset',chr?chr:'UTF-8');if(id){s.setAttribute('id',id);}s.setAttribute('src',url);document.getElementsByTagName('head').item(0).appendChild(s);},init:function(){this._timing=this._timing||{};this._timing.start=(new Date()).getTime();var that=this;if(window.attachEvent){window.attachEvent('onload',function(){that._domReady('domReady');});}else{window.addEventListener('DOMContentLoaded',function(){that._domReady('contReady');},false);window.addEventListener('load',function(){that._domReady('domReady');},false);}if(typeof(window._lptStop)=='undefined'){this.load();}},start:function(){this.autoStart=true;},_domReady:function(n){if(!this.isDom){this.isDom=true;this.events.trigger('LPT','DOM_READY',{t:n});}this._timing[n]=(new Date()).getTime();},vars:lpTag.vars||[],dbs:lpTag.dbs||[],ctn:lpTag.ctn||[],sdes:lpTag.sdes||[],ev:lpTag.ev||[]};lpTag.init();}else{window.lpTag._tagCount+=1;}</script>";
}

/**
 * Hooks into wp_footer to add the JavaScript tag on appropriate pages.
 */
function liveperson_wp_footer() {
  echo liveperson_javascript_tag(liveperson_account_number());
}
