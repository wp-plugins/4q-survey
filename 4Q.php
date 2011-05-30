<?php
/*
Plugin Name: 4Q Plugin
Plugin URI: 
Description: The best way to place your 4Q Survey code on your WordPress website. You can place the code site wide, or on a page by page basis.
Author: iPerceptions
Version: 0.51
Author URI: http://www.iperceptions.com
*/

add_action('plugins_loaded', 'plugin4Q_loaded');
function plugin4Q_loaded()
{
	add_action('admin_init', 'plugin4Q_admin_init');
	add_action('admin_menu', 'plugin4Q_admin_menu');
	add_action('wp_footer', 'plugin4Q_inject');
}

function plugin4Q_admin_init()
{
	define('PLUGIN4Q_FOLDER',str_replace('\\','/',dirname(__FILE__)));
	define('PLUGIN4Q_PATH','/' . substr(PLUGIN4Q_FOLDER,stripos(PLUGIN4Q_FOLDER,'wp-content')));

	add_meta_box('plugin4Q_edit_post', '4Q Survey', 'plugin4Q_edit_post', 'post', 'normal', 'high');
	add_meta_box('plugin4Q_edit_post', '4Q Survey', 'plugin4Q_edit_post', 'page', 'normal', 'high');

	add_action('save_post','plugin4Q_save_post');

	add_filter('plugin_action_links', 'plugin4Q_action_links', 10, 2 );
}


function plugin4Q_admin_menu() {
	if(function_exists('add_options_page'))
		add_options_page(__('4Q Survey Configuration', 'plugin4Q'), __('4Q Survey', 'plugin4Q'), 'manage_options', '4Q_options_menu', 'plugin4Q_options_menu' );
}

function plugin4Q_inject()
{
	global $post;

	$update = false;
	if ( plugin4Q_get_sitewide() )
		$update = true;
	if ( isset($post->ID) )
		if ( get_post_meta($post->ID, '_4Q', true) )
			$update = true;
			
	if ($update == true) {
		$html_insert = plugin4Q_get_html_insert();	
		if ($html_insert) echo "\n" . $html_insert . "\n";
	}
}

function plugin4Q_edit_post()
{
	global $post;
	if (isset($post->ID)) $checked = get_post_meta($post->ID, '_4Q', TRUE);
	else $checked = false;
?>
<div><p><img src="<?php echo PLUGIN4Q_PATH."/"; ?>4Q_30x33.png" width="30" height="33" style="vertical-align: middle;" /> <input type="checkbox" name="4Q_checkbox" value="true"<?php if ($checked) echo ' checked="checked"';?> /><span  style="vertical-align: middle;"> Enable 4Q on this page</span></p></div>
<?php
}

function plugin4Q_save_post($post_id)
{
	if ($_POST['post_type'] == 'page')  {
		if (!current_user_can('edit_page', $post_id)) return $post_id;
	} else {
		if (!current_user_can('edit_post', $post_id)) return $post_id;
	}

	if ($_POST['4Q_checkbox'] == 'true') {
		add_post_meta($post_id, '_4Q', 'true', TRUE);
	} else {
		delete_post_meta($post_id, '_4Q');
	}
}

function plugin4Q_options_menu()
{

	if (isset($_POST['plugin4Q_options_update'] ) ) {
		$sitewide = ($_POST['4Q_sitewide_checkbox'] == 'true');
		$html_insert = stripslashes(htmlspecialchars_decode($_POST['plugin4Q_html_insert']));
		plugin4Q_update_sitewide($sitewide);
		plugin4Q_update_html_insert($html_insert);
	} else {
		$sitewide = plugin4Q_get_sitewide();
		$html_insert = plugin4Q_get_html_insert();
	}
?>
<div class="wrap"> 
	<div id="icon-options-general" class="icon32"><br /></div> 
	<h2><?php _e('4Q Survey Configuration', 'plugin4Q')?></h2> 
	<table><tr>
	<td valign="middle"><img src="<?php echo PLUGIN4Q_PATH."/"; ?>4Q_125x140.png" width="125" height="140" /></td>
	<td valign="middle" width="100%"> <form name="plugin4Q_options" method="post">
		<input type="hidden" name="plugin4Q_options_update" value="true" />
		<p><input type="checkbox" name="4Q_sitewide_checkbox" value="true"<?php if ($sitewide) echo ' checked="checked"';?> /> <?php _e('Click to enable 4Q on every page of your website', 'plugin4Q')?></p>
		<p>If you do not wish to deploy 4Q site wide, you can selectively place the 4Q code on any given page by checking the "Enable 4Q on this page" on the appropriate "Edit Page" interface.</p>
		<p>To obtain your 4Q Survey code, first log into your 4Q account. Select your survey and click on "Publish > Get Survey Code". Copy the code into the box below and push "save".<br/>
		<textarea name="plugin4Q_html_insert" rows="6" cols="100"><?php echo htmlspecialchars($html_insert); ?></textarea></p>
		
		<p><input type="submit" class="button-primary" name="Submit" value="<?php _e('Save All 4Q Options', 'plugin4Q'); ?>" /></p>
	</form></td>
	</tr></table>
</div>
<?php
}

function plugin4Q_get_html_insert()
{
	$html_insert = get_option( 'plugin4Q_html_insert' );
	if (!$html_insert) $html_insert = '';
	return $html_insert;
}

function plugin4Q_update_html_insert($html_insert)
{
	if (!is_string($html_insert)) $html_insert = "";
	update_option('plugin4Q_html_insert', $html_insert);
}

function plugin4Q_get_sitewide()
{
	$sitewide = get_option( 'plugin4Q_sitewide' );
	return ($sitewide == 'true');
}

function plugin4Q_update_sitewide($sitewide)
{
	if ($sitewide) update_option('plugin4Q_sitewide', 'true');
	else update_option('plugin4Q_sitewide', 'false');
}

function plugin4Q_textarea_e ( $input ) {
	echo str_replace( array('<', '&'), array('&lt;', '&amp;'), $input);
}

function plugin4Q_action_links( $links, $file )
{
	if ( $file == plugin_basename( __FILE__ ) ) {
		$link = '<a href="'.admin_url('options-general.php?page=4Q_options_menu').'">Settings</a>';
		array_unshift( $links, $link );
	}
	return $links;
}
?>