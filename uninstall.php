<?php
/**
   * Responsive Posts Widget when the plugin is uninstalled.
   *
   * @package   Responsive_Posts_Widget
   * @author    Mahabub Masan
   * @license   MIT License
   * @link      http://bdwebteam.com/
   * @copyright 2015
 */

// If uninstall, not called from WordPress, then exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Delete plugin settings
delete_option( 'Responsive_Posts_Widget' );
delete_site_option( 'Responsive_Posts_Widget' );