<?php
/*
Plugin Name: Dyslexic Fonts
Version: 0.16
Plugin URI: 
Description: Adds a checkbox to the User Profile screen, allowing logged-in users to change the fonts used across the site to a dyslexic friendly one (https://opendyslexic.com/).
Author: Keith Drakard
Author URI: http://drakard.com/

TODO: configurable CSS to apply the font - allow IDs and classes to be excluded, don't rely on !important
TODO: if it's ever hosted on googlefonts etc, have an option to load from there instead
*/


class DyslexicPlugin {

	public function __construct() {
		if (is_admin()) {
			load_plugin_textdomain('Dyslexic_Fonts', false, dirname(plugin_basename(__FILE__)).'/languages');
			add_action('show_user_profile', array($this, 'add_short_form_table_cb'), 5);
			add_action('edit_user_profile', array($this, 'add_short_form_table_cb'), 5); 
			add_action('personal_options_update', array($this, 'save_profile_fields'));
			add_action('edit_user_profile_update', array($this, 'save_profile_fields'));
			add_action('admin_footer', array($this, 'add_font_face'));
		
		} else {
			add_action('wp_footer', array($this, 'add_font_face'));
		}
	}
	
	public function add_font_face() {
		global $current_user;
		if ($current_user->ID AND get_user_meta($current_user->ID, '_more_readable', true)) {			
			// NOTE: will not work on IE8 and below until I convert the .otf file to .ttf and then to .eot
			// NOTE: v0.16 - and now won't anyway because I'm using :not() to avoid icons.
			echo '<style type="text/css">
	@font-face { font-family:"OpenDyslexic";src:url("'.plugins_url('/fonts/OpenDyslexic-Regular.otf', __FILE__).'") format("opentype"); }
	@font-face { font-family:"OpenDyslexic";src:url("'.plugins_url('/fonts/OpenDyslexic-Italic.otf', __FILE__).'") format("opentype"); font-style:italic; }
	@font-face { font-family:"OpenDyslexic";src:url("'.plugins_url('/fonts/OpenDyslexic-Bold.otf', __FILE__).'") format("opentype"); font-weight:bold; }
	@font-face { font-family:"OpenDyslexic";src:url("'.plugins_url('/fonts/OpenDyslexic-BoldItalic.otf', __FILE__).'") format("opentype"); font-style:italic;font-weight:bold; }
	*:not([class*="icon"]) { font-family:"OpenDyslexic"!important; }
</style>';
		}
	}
	
	public function add_short_form_table_cb() {
		global $current_user; 

		$is_set = get_user_meta($current_user->ID, '_more_readable', true);

		$no = ' selected'; $yes = '';
		if ($is_set) {
			$no = ''; $yes = ' selected';
		}
		
		wp_nonce_field('save_personalisation_meta', '_personalisation_nonce');

		$output = '<div id="personalisation">'
				. '<h3>'.__('Site Personalisation', 'Dyslexic_Fonts').'</h3>'
				. '<table class="form-table"><tbody>'
				. '<tr title="'.__('If you have reading difficulties, enable this to use a different font across the site.', 'Dyslexic_Fonts').'">'
				. '<th><label for="dyslexic-font">'.__('More readable font?', 'Dyslexic_Fonts').'</label></th>'
				. '<td><select id="dyslexic-font" name="_more_readable">'
				. '<option value="0"'.$no.'>'.__('No', 'Dyslexic_Fonts').'</option><option value="1"'.$yes.'>'.__('Yes', 'Dyslexic_Fonts').'</option>'
				. '</select>'
				. '<span class="description">'
				. sprintf(__('This makes the site use a <a href="%s" target="_blank">font designed for people with reading difficulties</a>.', 'Dyslexic_Fonts'), 'https://opendyslexic.org/')
				. '</span></td></tr></tbody></table></div>';

		// in case you've got some custom user layout already, here's a filter hook
		$output = apply_filters('dyslexic_fonts', $output, $is_set);

		echo $output;

	}
	
	public function save_profile_fields($user_id) {
		if (	(! current_user_can('edit_user', $user_id))
			OR	(! isset($_POST['_personalisation_nonce'] ) OR ! wp_verify_nonce($_POST['_personalisation_nonce'], 'save_personalisation_meta'))
		) return $user_id;
		
		$set_me = (bool) $_POST['_more_readable'];
		update_user_meta($user_id, '_more_readable', $set_me);
	}
	
}

$Dyslexic = new DyslexicPlugin();
