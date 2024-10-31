<?php
/*
Plugin Name: Readability
Plugin URI: http://wolnaelekcja.pl/wp-readability/
Description: Plugin which lets you to show posts in stylish, legible pop-up window, like in Safari.
Version: 0.1.3
Author: Piotr Sochalewski
Author URI: http://wolnaelekcja.pl/
License: GPL2
*/

/*  Copyright 2011 Piotr Sochalewski (piotr@wolnaelekcja.pl)

	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

if (!defined('READABILITY_INIT')) define('READABILITY_INIT', 1);
else return;

$readability_settings = array();

function readability_register_settings() {
	register_setting('readability_settings', 'readability_link_title');
	register_setting('readability_settings', 'readability_word_count');
}

function readability_init() {
	global $readability_settings;

	if ( is_admin() )
		add_action( 'admin_init', 'readability_register_settings' );

	// 'Link' to plugin feature and some other stuff!
	add_filter('the_content', 'readability_button');
	add_filter('admin_menu', 'readability_isadmin_settings');

	// jQuery has to be there instead of reader_js(). Codex says that…
	wp_enqueue_script('jquery');
	add_action('wp_head', 'readability_js');

	// Nice const. Probably I don't need this now.
	// $locale = defined(WPLANG) ? WPLANG : 'en_US';
	
	add_option('readability_link_title', 'Readability');
	add_option('readability_word_count', '100');
	
	$readability_settings['readability_link_title'] = get_option('readability_link_title');
	$readability_settings['readability_word_count'] = get_option('readability_word_count');
}

function readability_js() {
	// jQuery(document).ready(function($)) instead of $(document).ready(function()) because of "no conflict" mode.
	$plugin_path = WP_PLUGIN_URL.'/'.str_replace("/".basename(__FILE__),"",plugin_basename(__FILE__));
	echo <<<END
	<script type="text/javascript" src="$plugin_path/source/jquery.fancybox.js"></script>
	<link rel="stylesheet" type="text/css" href="$plugin_path/style.css" media="screen" />
	<link rel="stylesheet" type="text/css" href="$plugin_path/source/jquery.fancybox.css" media="screen" />
	<link rel="stylesheet" type="text/css" href="$plugin_path/source/jquery.fancybox.pack.css" media="screen" />
	
	<script type="text/javascript">		
		jQuery(document).ready(function($) {
			$("#areadability").fancybox({
				'overlayOpacity' : 0.8,
				'overlayColor'   : '#000000'
			});
		});
	</script>

END;
}

function readability_button($content) {
	// Show it, but only if is_single() or is_page().
	if( is_single() || is_page() )
		$content = readability_return_reader() . $content;

	return $content;
}

function readability_return_reader() {
	global $readability_settings;
	
	// Get post ID.	
	global $post;
	$post_id = get_post($post->ID);
	
	// Get post title and formatted content.
	$title = $post_id->post_title;
	$content = wpautop($post_id->post_content);
	
	// Return only if word count isn't low.
	if($readability_settings['readability_word_count'] == 0 || str_word_count($content) > $readability_settings['readability_word_count']) {
		// Great typography by PHP Typography.
		// Only if wp-Typography is not activated.
		require_once(ABSPATH.'/wp-admin/includes/plugin.php');
		if (!is_plugin_active('wp-typography/wp-typography.php')) {
			include_once('php-typography/php-typography.php');
			$typography = new phpTypography();
			if (defined(WPLANG))
				$typography->set_hyphenation_language(WPLANG);
			$content = $typography->process($content);
		}

		return '<small><a href="#readability" id="areadability">'.$readability_settings['readability_link_title'].'</a></small><div style="width: 650px; display:none;"><div id="readability"><h1 class="title">'.$title.'</h1>'.$content.'</div></div>';

	}
}

function readability_isadmin_settings() {
	add_options_page('Readability Options', 'Readability', 8, __FILE__, 'readability_settings_page');
}

function readability_settings_page() {
	global $readability_settings;
?>
	
	<div class="wrap">
    <h2>Readability <small>by <a href="http://wolnaelekcja.pl/" target="_blank">Piotr Sochalewski</a></small></h2>

    <form method="post" action="options.php">

    <?php settings_fields('readability_settings'); ?>
    
    <table class="form-table">
    	<tr valign="top">
            <th scope="row"><h3>Main settings</h3></th>
		</tr>
    
    	<tr valign="top">
            <th scope="row">Link title:</th>
            <td style="line-height: 100%;"><input size="21" type="text" name="readability_link_title" value="<?php echo get_option('readability_link_title'); ?>" /></td>
        </tr>
        
        <tr valign="top">
            <th scope="row">Min word count to activate:</th>
            <td style="line-height: 100%;"><input size="10" type="number" name="readability_word_count" value="<?php echo get_option('readability_word_count'); ?>" /><br /><small>0 to always on.</small></td>
        </tr>
        
        <tr valign="top">
            <th scope="row"><h3>Help & Support</h3></th>
		</tr>
		
		<tr valign="top">
            <th scope="row" colspan="2"><strong><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=sproject%40sproject%2ename&lc=PL&item_name=Readability%20WordPress%20plugin&item_number=readability%2ddonate&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donate_SM%2egif%3aNonHosted" target="_blank" style="color: rgb(255,0,0)">Donate to this plugin</a></strong></th>
        </tr>
        <tr valign="top">
            <th scope="row" colspan="2"><a href="http://wolnaelekcja.pl/wp-readability" target="_blank">Read the plugin homepage and its comments</a></th>
        </tr>
    </table>
    
    <p class="submit">
		<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
	</p>
	
	</form>
	</div>

<?php	
}

readability_init();
?>