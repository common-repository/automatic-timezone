<?php
/**
 * Uninstall functionality for automatic-timezone plugin.
 * 
 * Removes the plugin cleanly in WP 2.7 and up
 */

// first, check to make sure that we are indeed uninstalling
if ( !defined('WP_UNINSTALL_PLUGIN') ) {
    exit();
}

// delete the option that the plugin added
delete_option('timezone_string');

