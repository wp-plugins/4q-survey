<?php
/*
Plugin Name: iPerceptions Customer Feedback Surveys
Plugin URI: 
Description: The best way to place your customer feedback surveys on your WordPress website. You can place the code site wide, or on a page by page basis.
Author: iPerceptions
Version: 1.02
Author URI: http://www.iperceptions.com
*/

add_action('plugins_loaded', 'pluginIP_loaded');
function pluginIP_loaded()
{
	add_action('admin_init', 'pluginIP_admin_init');
	add_action('admin_menu', 'pluginIP_admin_menu');
	add_action('wp_footer', 'pluginIP_inject');
}

function pluginIP_admin_init()
{
	define('PLUGINIP_FOLDER',str_replace('\\','/',dirname(__FILE__)));
	define('PLUGINIP_PATH','/' . substr(PLUGINIP_FOLDER,stripos(PLUGINIP_FOLDER,'wp-content')));

	add_meta_box('pluginIP_edit_post', 'iPerceptions Survey', 'pluginIP_edit_post', 'post', 'normal', 'high');
	add_meta_box('pluginIP_edit_post', 'iPerceptions Survey', 'pluginIP_edit_post', 'page', 'normal', 'high');

	add_action('save_post','pluginIP_save_post');

	add_filter('plugin_action_links', 'pluginIP_action_links', 10, 2 );
}


function pluginIP_admin_menu() {
	if(function_exists('add_options_page'))
		add_options_page(__('iPerceptions Survey Configuration', 'pluginIP'), __('iPerceptions Survey', 'pluginIP'), 'manage_options', 'IP_options_menu', 'pluginIP_options_menu' );
		add_menu_page(__('iPerceptions Survey Manager', 'pluginIP'), __('Survey Manager', 'pluginIP'), 'manage_options', 'IPS', 'pluginIP_IPSFrame');
}

function pluginIP_inject()
{
	global $post;

	$update = false;
	if ( pluginIP_get_sitewide() )
		$update = true;
	if ( isset($post->ID) )
		if ( get_post_meta($post->ID, '_IP', true) )
			$update = true;
			
	if ($update == true) {
		$html_insert = pluginIP_get_html_insert();	
		if ($html_insert) echo "\n" . $html_insert . "\n";
	}
}

function pluginIP_edit_post()
{
	global $post;
	if (isset($post->ID)) $checked = get_post_meta($post->ID, '_IP', TRUE);
	else $checked = false;
?>
<div><p><img src="<?php echo site_url(); ?><?php echo PLUGINIP_PATH."/"; ?>IP_30x33.png" width="30" height="33" style="vertical-align: middle;" /> <input type="checkbox" name="IP_checkbox" value="true"<?php if ($checked) echo ' checked="checked"';?> /><span  style="vertical-align: middle;"> Enable iPerceptions on this page</span></p></div>
<?php
}

function pluginIP_save_post($post_id)
{
	if ( isset($_POST['post_type']) ){
	if ($_POST['post_type'] == 'page')  {
		if (!current_user_can('edit_page', $post_id)) return $post_id;
	} else {
		if (!current_user_can('edit_post', $post_id)) return $post_id;
	}

	if ($_POST['IP_checkbox'] == 'true') {
		add_post_meta($post_id, '_IP', 'true', TRUE);
	} else {
		delete_post_meta($post_id, '_IP');
	}
	}
}

function pluginIP_IPSFrame()
{


?>
<div class="wrap"> 
	<div id="icon-options-general" class="icon32"><br /></div> 
	<h2><?php _e('iPerceptions Survey Portal', 'pluginIP')?></h2> 
</div>
	To deploy a survey on your wordpress site.  Select your survey and click on "Collect > Invitation Code". Copy the code into iPerceptions Wordpress settings found at "Settings > iPerceptions Survey" in the Wordpress administration.
	<iframe sandbox="allow-same-origin allow-scripts allow-popups allow-forms"
    src="https://ips-portal.iperceptions.com/"
    style="border: 0; width:100%; height:100%;"></iframe>

<?php

}
function pluginIP_options_menu()
{

	if (isset($_POST['pluginIP_options_update'] ) ) {
		$sitewide = ($_POST['IP_sitewide_checkbox'] == 'true');
		$html_insert = stripslashes(htmlspecialchars_decode($_POST['pluginIP_html_insert']));
		pluginIP_update_sitewide($sitewide);
		pluginIP_update_html_insert($html_insert);
	} else {
		$sitewide = pluginIP_get_sitewide();
		$html_insert = pluginIP_get_html_insert();
	}
?>
<div class="wrap"> 
	
	<table><tr>
	<td valign="middle" width="100%"> <form name="pluginIP_options" method="post">
		<input type="hidden" name="pluginIP_options_update" value="true" />
		<br>
		<img src="<?php echo site_url(); ?><?php echo PLUGINIP_PATH."/"; ?>iPerceptions-Logotype.png" />
		<br>
		<p><h2>iPerceptions survey platform makes it quick and easy to discover visitor insights and drive action to improve your site.</h2><br>

<h3>With an iPerceptions survey you can:</h3>
<ul>
	<li>• <b>Increase conversion</b> by better understanding what your visitors  want</li>
	<li>• <b>Head off negative social media</b> by creating a direct dialog with visitor</li>
	<li>• <b>Better engage visitors</b> by tracking and measuring their level of engagement with different content</li>
</ul></p>
		<br>
		<p>To obtain your iPerceptions Survey code, first log into your iPerceptions account using the Survey Manager on the right hand side. Select your survey and click on "Collect > Invitation Code". Copy the code into the box below and push "save".<br/>
		<textarea name="pluginIP_html_insert" rows="6" cols="100"><?php echo htmlspecialchars($html_insert); ?></textarea></p>
				<p><input type="checkbox" name="IP_sitewide_checkbox" value="true"<?php if ($sitewide) echo ' checked="checked"';?> /> <?php _e('Click to enable iPerceptions on every page of your website', 'pluginIP')?></p>
		<p>If you do not wish to deploy iPerceptions site wide, you can selectively place the iPerceptions code on any given page by checking the "Enable iPerceptions on this page" on the appropriate "Edit Page" interface.</p>

		<p><input type="submit" class="button-primary" name="Submit" value="<?php _e('Save All iPerception Options', 'pluginIP'); ?>" /></p>
	</form></td>
	</tr></table>
</div>
<?php
}

function pluginIP_get_html_insert()
{
	$html_insert = get_option( 'pluginIP_html_insert' );
	if (!$html_insert) $html_insert = '';
	return $html_insert;
}

function pluginIP_update_html_insert($html_insert)
{
	if (!is_string($html_insert)) $html_insert = "";
	update_option('pluginIP_html_insert', $html_insert);
}

function pluginIP_get_sitewide()
{
	$sitewide = get_option( 'pluginIP_sitewide' );
	return ($sitewide == 'true');
}

function pluginIP_update_sitewide($sitewide)
{
	if ($sitewide) update_option('pluginIP_sitewide', 'true');
	else update_option('pluginIP_sitewide', 'false');
}

function pluginIP_textarea_e ( $input ) {
	echo str_replace( array('<', '&'), array('&lt;', '&amp;'), $input);
}

function pluginIP_action_links( $links, $file )
{
	if ( $file == plugin_basename( __FILE__ ) ) {
		$link = '<a href="'.admin_url('options-general.php?page=IP_options_menu').'">Settings</a>';
		array_unshift( $links, $link );
	}
	return $links;
}
?>