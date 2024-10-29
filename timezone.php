<?php
/*
Plugin Name: Automatic Timezone
Plugin URI: http://ottodestruct.com/blog/wordpress-plugins/automatic-timezone/
Description: Automatically sets the timezone offset for Daylight Savings Time.
Author: Otto
Version: 1.7.1
Author URI: http://ottodestruct.com
License: GPL2

    Copyright 2008  Samuel Wood  (email : otto@ottodestruct.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 2, 
    as published by the Free Software Foundation. 
    
    You may NOT assume that you can use any other version of the GPL.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    
    The license for this software can likely be found here: 
    http://www.gnu.org/licenses/gpl-2.0.html
    
*/

/**
 * Internationalization functionality
 */
define('TIMEZONE_DOMAIN','timezone');
global $timezone_text_loaded;
$timezone_text_loaded = false;

function timezone_load_textdomain()
{
   global $timezone_text_loaded;
   if($timezone_text_loaded) return;

   load_plugin_textdomain(TIMEZONE_DOMAIN, PLUGINDIR.'/'.dirname(plugin_basename(__FILE__)), dirname(plugin_basename(__FILE__)));
   $timezone_text_loaded = true;
}


/**
 * timezone_activation_check() - Checks to see if the plugin is even going to work
 * 
 * For you plugin programmers:
 * This is a simple method of preventing a plugin from activating. I recommend it
 * if you are writing plugins that depend on PHP 5 or have other dependancies that
 * you can actually check for. 
 *
 */
function timezone_activation_check(){
	// check to see if we have the PHP5 functions (if so, skip the longer check)
	if( !function_exists('timezone_offset_get') || !function_exists('timezone_identifiers_list') ) {
		
		// check to see if we can still find a list of timezones somewhere
		if (@timezone_get_timezones() == false) {
			deactivate_plugins(basename(__FILE__)); // Deactivate ourself
			
			// TODO: Make a nicer "no" screen.
			wp_die("Sorry, but you can't run this plugin, as it couldn't find any way to get a list of valid timezones.");
		}
	}
}
register_activation_hook(__FILE__, 'timezone_activation_check');



/**
 * timezone_config_page() - Adds the configuration page in the admin screen
 *
 */
function timezone_config_page() {
	global $wp_version;
	timezone_load_textdomain();
	if ( current_user_can('manage_options') && function_exists('add_options_page') ) {
	
		$menutitle = '';
		if ( version_compare( $wp_version, '2.6.999', '>' ) ) {
	  		$menutitle = '<img src="'.plugins_url(dirname(plugin_basename(__FILE__))).'/clock.png" style="margin-right:4px;" />';
		}
		$menutitle .= __('Timezone', TIMEZONE_DOMAIN);
		add_options_page(__('Timezone Configuration', TIMEZONE_DOMAIN), $menutitle , 'manage_options', 'timezone-config', 'timezone_conf');
		add_filter( 'plugin_action_links', 'timezone_filter_plugin_actions', 10, 2 );
	}
}
add_action('admin_menu', 'timezone_config_page');


/**
 * timezone_filter_plugin_actions() - Adds an action link to the plugins page
 * 
 * @since 1.6
 */
function timezone_filter_plugin_actions($links, $file){
	static $this_plugin;

	if( !$this_plugin ) $this_plugin = plugin_basename(__FILE__);

	if( $file == $this_plugin ){
		$settings_link = '<a href="admin.php?page=timezone-config">' . __('Settings') . '</a>';
		$links = array_merge( array($settings_link), $links); // before other links
	}
	return $links;
}


/**
 * timezone_get_timezones() - Gets the list of timezones 
 *   (in different possible ways, depending on what is available)
 *
 */
function timezone_get_timezones() {
	if (function_exists('timezone_identifiers_list')) {
		
		// modern PHP 5.1 way
		return timezone_identifiers_list();
		
	} else if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
		
		// older PHP4 on a Linux box approach, try two possible locations for time zone info
		// this is not the best approach. PHP 5 is better.
		$zones = `find /usr/share/zoneinfo -type f`;
		if (empty($zones)) $zones = `find /usr/lib/zoneinfo -type f`;
		if (empty($zones)) return false;
		
		$zones = explode("\n", trim($zones));
	
		$zones = str_replace('/usr/share/zoneinfo/','',$zones);
		$zones = str_replace('/usr/lib/zoneinfo/','',$zones);
		
		return $zones;
		
	} else {
		
		// PHP 4 on a Windows box? No way to do this sort of thing properly on that setup.
		return false;
	}
}




/**
 * timezone_conf() - Show/handle the admin config page
 * 
 */
function timezone_conf() {
	timezone_load_textdomain();	
	// did they submit the form?
	if ( isset($_POST['submit']) ) 
	{
		// are they allowed to do that?
		if ( function_exists('current_user_can') && !current_user_can('manage_options') )
			die(__('Cheatin&#8217; uh?'));

		// check the nonce
		check_admin_referer( 'timezone-update' );
		
		// get the setting
		$timezone_string = $_POST['timezone_string'];
			
		// validate that the setting makes sense
		if ( isset( $timezone_string ) )
		{
			// check the input tz string for validity
			$all = @timezone_get_timezones();
			if (in_array($timezone_string,$all))
				update_option( 'timezone_string', $timezone_string );
		}
	}
?>
<?php if ( !empty($_POST ) ) : ?>
<div id="message" class="updated fade"><p><strong><?php _e('Timezone setting saved.', TIMEZONE_DOMAIN); ?></strong></p></div>
<?php endif; ?>
<div class="wrap">
<h2><?php _e('Timezone Configuration', TIMEZONE_DOMAIN); ?></h2>
<div class="narrow">
<form action="" method="post" id="timezone-conf">
<?php

	// some basic settings
	$current_tz = get_option('timezone_string');
	$timeOffset = timezone_get_gmt_offset($current_tz);
	
	// put the nonce in
	wp_nonce_field('timezone-update'); 
?>
<h3><label for="timezone-select"><?php _e('Timezone Selection', TIMEZONE_DOMAIN); ?></label></h3>
<p><?php 
	
	// output the current setting and some info about it
	_e('Current Timezone Selection is: ', TIMEZONE_DOMAIN);
	if ($current_tz) {
		echo $current_tz.' (UTC'.$timeOffset.')';
		echo '<br />';

		_e('This timezone is currently in ', TIMEZONE_DOMAIN);
		$now = localtime(time(),true);
		if ($now['tm_isdst']) _e('daylight savings', TIMEZONE_DOMAIN);
		else _e('standard', TIMEZONE_DOMAIN);
		_e(' time.', TIMEZONE_DOMAIN);
		echo '<br />';
		
	} else {
		_e('Not Selected', TIMEZONE_DOMAIN);
		echo '<br />';
	}

	echo '<br />';
	// output the current server time
	_e('Current Server Time (<abbr title="Coordinated Universal Time">UTC</abbr>) is: ', TIMEZONE_DOMAIN);
	echo gmdate(__('Y-m-d G:i:s')); 
	echo '<br />';
	
	if ($current_tz) {
		_e('Time in ', TIMEZONE_DOMAIN); 
		echo $current_tz; 
		_e(' is: ', TIMEZONE_DOMAIN);
		echo gmdate(__('Y-m-d G:i:s'), current_time('timestamp')); 	
		echo '<br />';
	}
	
	/* this next display stuff only works on PHP 5.2 and up. It's nice to see, but not 
		strictly necessary, so I don't see the need to duplicate it for PHP 4 */
		
	if (function_exists('timezone_transitions_get') && $current_tz) { 
		$dateTimeZoneSelected = new DateTimeZone($current_tz);
		foreach (timezone_transitions_get($dateTimeZoneSelected) as $tr) {
			if ($tr['ts'] > time()) {
			    	$found = true;
				break;
			}
		}
	
		if ($found) {
			echo '<p>';
			_e('This timezone switches to ', TIMEZONE_DOMAIN);
			$tr['isdst'] ? _e('daylight savings time', TIMEZONE_DOMAIN) : _e('standard time', TIMEZONE_DOMAIN);
			_e(' on: ', TIMEZONE_DOMAIN);
			$tz = new DateTimeZone($current_tz);
			$d = new DateTime( "@{$tr['ts']}" );
			$d->setTimezone($tz);
			echo date_i18n(__('F j, Y @ g:i a T', TIMEZONE_DOMAIN),$d->format('U')). '<br />';
			//echo $d->format(__('F j, Y @ g:i a T', TIMEZONE_DOMAIN)) . '.<br />';
			_e(' The new offset at that time will be: UTC', TIMEZONE_DOMAIN);
			echo $tr['offset'] /3600 . '</p>';
		} else {
			echo '<p>';
			_e('This timezone does not observe daylight savings time.', TIMEZONE_DOMAIN);
			echo '</p>';
		}
	}
?>
<p><?php _e('Select the Timezone you want to use. This should be the name of the nearest major city to you that shares your timezone.', TIMEZONE_DOMAIN); ?></p>
<?php if (!function_exists('timezone_identifiers_list')) : 
// on page comment note for debugging purposes.. might as well make it interesting 
?>
<!-- 
I see you're not currently using PHP 5 or higher. 

You should be.

Please consider upgrading as soon as possible. 
-->
<?php endif; ?>
<p><select id="timezone_string" name="timezone_string">
<?php
echo timezone_choice($current_tz);
?>
</select></p>
<p class="submit"><input type="submit" class="button-primary" name="submit" value="<?php _e('Update Timezone &raquo;', TIMEZONE_DOMAIN); ?>" /></p>
</form>
</div>
</div>
<?php
}



/**
 * timezone_override_offset() - Do the actual gmt_offset modification
 * 
 * Simple override of the option. We can't actually disable/remove the option on the
 * settings page (well, not nicely), so we'll just override it and force it to be 
 * what we think it should be.
 *
 */
function timezone_override_offset($notused) {
	// get the new timezone
	$current_tz = get_option('timezone_string');

	// try to figure out what the offset is
	if ($current_tz) {
		return timezone_get_gmt_offset($current_tz);
	}
	
	// no setting = do nothing
	return false;	
}
add_filter('pre_option_gmt_offset','timezone_override_offset');




/**
 * timezone_get_gmt_offset() - Attempts to figure out a GMT offset based on the time zone
 *
 * @param string $tz - which zone to get it for
 *
 */
function timezone_get_gmt_offset($tz) {
	if (empty($tz)) return false;
	
	if (function_exists('date_default_timezone_set')
	&& class_exists('DateTimeZone') 
	&& class_exists('DateTime') ) {
		@date_default_timezone_set($tz); // this line might not be needed...
		// get the offset
		$dateTimeZoneSelected = new DateTimeZone($tz);
		$dateTimeServer = new DateTime();
		$timeOffset = $dateTimeZoneSelected->getOffset($dateTimeServer);

		// convert to hours
		$timeOffset = $timeOffset / 3600;
		
		return $timeOffset;
	} else {
 		// try it the PHP 4 way... Might not work, but what the heck
		putenv("TZ=$tz");
		$timeOffset = date('O') / 100;
		
		return $timeOffset;
	}
}


/**
 * timezone_choice() - Display a nicely formatted list of timezone strings
 *
 * @param string $selectedzone - which zone should be the selected one
 *
 */
function timezone_choice($selectedzone) {
    $all = @timezone_get_timezones();

    $i = 0;
    foreach($all AS $zone) {
      $zone = explode('/',$zone);
      $zonen[$i]['continent'] = isset($zone[0]) ? $zone[0] : '';
      $zonen[$i]['city'] = isset($zone[1]) ? $zone[1] : '';
      $zonen[$i]['subcity'] = isset($zone[2]) ? $zone[2] : '';
      $i++;
    }

    asort($zonen);
    $structure = '';
    foreach($zonen AS $zone) {
      extract($zone);
      if($continent == 'Africa' || $continent == 'America' || $continent == 'Antarctica' || $continent == 'Arctic' || $continent == 'Asia' || $continent == 'Atlantic' || $continent == 'Australia' || $continent == 'Europe' || $continent == 'Indian' || $continent == 'Pacific') {
        if(!isset($selectcontinent)) {
          $structure .= '<optgroup label="'.$continent.'">'; // continent
        } elseif($selectcontinent != $continent) {
          $structure .= '</optgroup><optgroup label="'.$continent.'">'; // continent
        }

        if(isset($city) != ''){
          if (!empty($subcity) != ''){
            $city = $city . '/'. $subcity;
          }
          $structure .= "<option ".((($continent.'/'.$city)==$selectedzone)?'selected="selected "':'')." value=\"".($continent.'/'.$city)."\">".str_replace('_',' ',$city)."</option>"; //Timezone
        } else {
          if (!empty($subcity) != ''){
            $city = $city . '/'. $subcity;
          }
          $structure .= "<option ".(($continent==$selectedzone)?'selected="selected "':'')." value=\"".$continent."\">".$continent."</option>"; //Timezone
        }

        $selectcontinent = $continent;
      }
    }
    $structure .= '</optgroup>';
    return $structure;
}


/**
 * timezone_alter_settings_general() - Change the Settings->General Screen to be more clever
 * 
 */
function timezone_alter_settings_general() {
	/* Note: I wish there was a nicer way to do this, but the 
		admin pages lack the proper hooks for it. Until it 
		gets them, this works. */
	
	// check to see if we're loading the options-general page
	global $parent_file;	
	if ( $parent_file != 'options-general.php' ) return;
	
	// check to see if the timezone is even set
	$timezone_setting = get_option('timezone_string');
	if ( empty($timezone_setting)) return;
	
	// all looks good, turn on the output buffer and attach our callback
	ob_start('timezone_general_callback');
}
add_action('admin_head','timezone_alter_settings_general');


/**
 * timezone_general_callback() - Put a better comment on the Settings->General page.
 *
 * @param string $data - The output buffer holding the page data
 *
 */
function timezone_general_callback($data) {
	timezone_load_textdomain();
	return str_replace(__('Unfortunately, you have to manually update this for Daylight Savings Time. Lame, we know, but will be fixed in the future.'),
	__('Fortunately for you, you\'re using the Automatic Timezone plugin, and it has automatically set this value for you.<br />This setting will change automatically if Daylight Savings Time rolls around.', TIMEZONE_DOMAIN),
	$data);
}

/**
 * Note: There is no ending PHP tag at the end of this file for a very good reason. 
 * Leaving that out here prevents extra white space at the end of the file from 
 * screwing up WordPress' output. This is always a recommended behavior for WordPress 
 * plugins. They should never make output when they are loaded, except in special cases.
 * 
 * This is VALID in PHP, the closing tag is optional at the end of a PHP file, because
 * PHP assumes it to be at the very end of the file if it is not explicitly given.
 */
 