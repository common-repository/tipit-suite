<?php

/*
Plugin Name: Tipit Suite
Version: 0.6
Plugin URI: http://coenjacobs.net/wordpress/plugins/tipit-suite
Description: Makes it easy to display a link to your TipiT tipjar in posts. Also includes a sidebar widget and a customizable shortcode.
Author: Coen Jacobs
Author URI: http://coenjacobs.net/
*/

// Determine plugin directory
$tipitsuitepath = WP_CONTENT_URL.'/plugins/'.plugin_basename(dirname(__FILE__)).'/';

/**
 * tipit_widget Class
 */
class tipit_widget extends WP_Widget {
    /** constructor */
    function tipit_widget() {
        parent::WP_Widget(false, $name = 'TipiT tipjar');	
    }

    /** @see WP_Widget::widget */
    function widget($args, $instance) {		
        extract( $args );
        ?>
              <?php echo $before_widget; ?>
                  <?php echo $before_title
                      . $instance['title']
                      . $after_title; 
                   
                   $instance['url'] = str_replace('http://', '', $instance['url']);
                   
                   global $tipitsuitepath;
                   echo "<p><a href=\"http://tipit.to/".$instance['url']."\"><img src=\"".$tipitsuitepath."images/button_150.png\"></a></p>"; ?>
                  
              <?php echo $after_widget; ?>
        <?php
    }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {				
        return $new_instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {				
        $title = esc_attr($instance['title']);
        $url = esc_attr($instance['url']);
	if($title == null)
	{
		$title = "Please tip this website!";
	}
        ?>
            <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Call for action:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label></p>
            <p><label for="<?php echo $this->get_field_id('url'); ?>"><?php _e('Tipjar name:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('url'); ?>" name="<?php echo $this->get_field_name('url'); ?>" type="text" value="<?php echo $url; ?>" /></label></p>
        <?php 
    }

}

// Add a menu for the plugin
function tipit_suite_admin_menu() {
	add_options_page('TipiT Suite', 'TipiT Suite', 8, 'TipiT Suite', 'tipit_suite_submenu');
}
add_action('admin_menu', 'tipit_suite_admin_menu');

function tipit_suite_message($message) {
	echo "<div id=\"message\" class=\"updated fade\"><p>$message</p></div>\n";
}

/**
 * Displays the tipit-suite admin menu
 */
function tipit_suite_submenu() {
	global $tipit_suite_known_sites, $tipit_suite_date, $tipit_suitepluginpath;
	
	if (isset($_REQUEST['save']) && $_REQUEST['save']) {
		check_admin_referer('tipit-suite-config');
		
		foreach ( array('usetargetblank', 'tagline', 'url') as $val ) {
			if ( !$_POST[$val] )
				update_option( 'tipit-suite_'.$val, '');
			else
				update_option( 'tipit-suite_'.$val, $_POST[$val] );
		}
		
		/**
		 * Update conditional displays
		 */
		$conditionals = Array();
		if (!$_POST['conditionals'])
			$_POST['conditionals'] = Array();
		
		$curconditionals = get_option('tipit-suite_conditionals');
		
		if (!array_key_exists('is_home',$curconditionals)) {
			$curconditionals['is_home'] = false;
		}
		if (!array_key_exists('is_single',$curconditionals)) {
			$curconditionals['is_single'] = false;
		}
		if (!array_key_exists('is_page',$curconditionals)) {
			$curconditionals['is_page'] = false;
		}
		foreach($curconditionals as $condition=>$toggled)
			$conditionals[$condition] = array_key_exists($condition, $_POST['conditionals']);
			
		update_option('tipit-suite_conditionals', $conditionals);

		tipit_suite_message(__("Saved changes.", 'tipit-suite'));
	}
	
	/**
	 * Display options.
	 */
?>
<form action="<?php echo attribute_escape( $_SERVER['REQUEST_URI'] ); ?>" method="post">
<?php
	if ( function_exists('wp_nonce_field') )
		wp_nonce_field('tipit-suite-config');
?>

<div class="wrap">

	<?php screen_icon(); ?>
	<h2><?php _e("TipiT Suite Options", 'tipit-suite'); ?></h2>

	<div style="float: right; width: 25%; background: #DEDEDE; color: #000">
		<div style="padding: 10px;">
			<b>About this plugin</b>
			<p style="line-height: 160%;">TipiT Suite is developed by <a href="http://coenjacobs.net/">Coen Jacobs</a>. If you like it, please consider making a small <a href="http://coenjacobs.net/donate"><b>donation</b> to the author</a> or if you're on Twitter; <a href="http://twitter.com/coenjacobs">follow <b>@coenjacobs</b></a>!</p>
			<b>Need support?</b>
			<p style="line-height: 160%;">Please use the special <a href="http://wordpress.org/tags/tipit-suite?forum_id=10">support forum</a> for this plugin at WordPress.org for support.</p>
		</div>
	</div>
	
	<div style="float: left; width: 75%;">
		<table class="form-table">	
			<tr>
				<th scope="row" valign="top">
					<?php _e("Call to action", "tipit-suite"); ?>
				</th>
				<td>
					<?php
						if(attribute_escape(stripslashes(get_option('tipit-suite_tagline'))) == null)
						{
							$form_title = "Please tip this website!";
						} else {
							$form_title = attribute_escape(stripslashes(get_option('tipit-suite_tagline')));
						}
					?>
					<?php _e("Change the text displayed as the link.", 'tipit-suite'); ?><br/>
					<input size="80" type="text" name="tagline" value="<?php echo $form_title; ?>" />
				</td>
			</tr>
			<tr>
				<th scope="row" valign="top">
					<?php _e("Tipjar name", "tipit-suite"); ?>
				</th>
				<td>
					<?php _e("Change the tipjar that you want to pass on to TipiT.", 'tipit-suite'); ?><br/>
					<input size="80" type="text" name="url" value="<?php echo attribute_escape(stripslashes(get_option('tipit-suite_url'))); ?>" />
				</td>
			</tr>
			<tr>
				<th scope="row" valign="top">
					<?php _e("Position:", "tipit-suite"); ?>
				</th>
				<td>
					<?php _e("The link to your TipiT tipjar can be displayed at the end of each blog post, and posts may show on many different types of pages. Please select where you want to display the link.", 'tipit_suite'); ?><br/>
					<br/>
					<?php
					/**
					 * Load conditions under which tipit-suite displays
					 */
					$conditionals = get_option('tipit-suite_conditionals');
					?>
					<input type="checkbox" name="conditionals[is_home]"<?php echo ($conditionals['is_home']) ? ' checked="checked"' : ''; ?> /> <?php _e("Front page", 'tipit-suite'); ?><br/>
					<input type="checkbox" name="conditionals[is_single]"<?php echo ($conditionals['is_single']) ? ' checked="checked"' : ''; ?> /> <?php _e("Individual blog posts", 'tipit-suite'); ?><br/>
					<input type="checkbox" name="conditionals[is_page]"<?php echo ($conditionals['is_page']) ? ' checked="checked"' : ''; ?> /> <?php _e('Individual WordPress "Pages"', 'tipit-suite'); ?><br/>
				</td>
			</tr>
			<tr>
				<th scope="row" valign="top">
					<?php _e("Open in new window:", "tipit_suite"); ?>
				</th>
				<td>
					<input type="checkbox" name="usetargetblank" <?php checked( get_option('tipit-suite_usetargetblank'), true ); ?> /> <?php _e("Use <code>target=_blank</code> on links? (Forces links to open a new window)", "tipit_suite"); ?>
				</td>		
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>
					<span class="submit"><input name="save" value="<?php _e("Save Changes", 'tipit-suite'); ?>" type="submit" /></span>
				</td>
			</tr>
		</table>
	</div>


</div>
</form>
<?php
}

// Shortcode function
function tipit_shortcode($atts) {
	extract(shortcode_atts(array(
	    "url" 	=> '',
	    "text" 	=> 'Throw a tip in my tipjar!'
	), $atts));
	
	$url = str_replace('http://', '', $url);
	
	$content .= "<p><a href=\"http://tipit.to/".$url."\">".$text."</a></p>";
	
	return $content;
}

function tipit_display_hook($content='') {
	$conditionals = get_option('tipit-suite_conditionals');
	if ((is_home()     and $conditionals['is_home']) or
	    (is_single()   and $conditionals['is_single']) or
	    (is_page()     and $conditionals['is_page'])) {
		$content .= tipit_suite_display();
	}
	return $content;
}

function tipit_suite_display($content='') {
	$tagline = get_option("tipit-suite_tagline");
	if(get_option("tipit-suite_usetargetblank")) {
		$extra = " target=\"_blank\"";
	} else {
		$extra = "";
	}
	$url = str_replace('http://', '', get_option("tipit-suite_url"));
	$content .= "<p><a href=\"http://tipit.to/".$url."\"".$extra.">".$tagline."</a></p>";
	return $content;
}

function tipit_suite_plugin_actions( $links, $file ){
	static $this_plugin;
	if ( ! $this_plugin ) $this_plugin = plugin_basename(__FILE__);
	
	if ( $file == $this_plugin ){
		$settings_link = '<a href="options-general.php?page=TipiT Suite">' . __('Settings') . '</a>';
		array_unshift( $links, $settings_link ); // before other links
	}
	return $links;
}
add_filter( 'plugin_action_links', 'tipit_suite_plugin_actions', 10, 2 );

add_filter('the_content', 'tipit_display_hook');
add_filter('the_excerpt', 'tipit_display_hook');

// Register the widget
add_action('widgets_init', create_function('', 'return register_widget("tipit_widget");'));

// Register the shortcode
add_shortcode('tipit', 'tipit_shortcode');

?>