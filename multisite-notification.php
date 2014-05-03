<?php
/**
 * Plugin Name: Multisite Networkwide Notifications
 * Plugin URI: http://stboston.com
 * Description: Lets a network administrator publish a message to be displayed on all blogs with an expiration date and time. Uses shortcodes or loops into wp_head().
 * Version: 0.1
 * Author: Stirling Technologies (Brian Hanna, Chris Paganelli)
 * Author URI: http://stboston.com
 * License: GPL2
 */
 
 /*  Copyright 2014  Stirling Technolgies  (email : info@stboston.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

//-------------------------------------------------------------------------------------

//Enqueue the Admin Styles and Scripts
function add_backend_nwn_scripts(){
	$options = get_site_option('nwn_options'); 
	global $blog_id;
	if((is_network_admin()) || (($options['primary_admin']) && ($blog_id == 1))){
	wp_enqueue_script('datepickerjs', plugins_url('js/jquery.datetimepicker.js',__FILE__), array('jquery'));
	wp_enqueue_style('datepickercss', plugins_url('css/jquery.datetimepicker.css',__FILE__));
	wp_enqueue_style('custom', plugins_url('css/nwn.css',__FILE__));
	wp_enqueue_style('iconcss', plugins_url('css/grey-theme/jquery.fonticonpicker.min.css',__FILE__));
	wp_enqueue_style('fontello', plugins_url('css/fontello.css',__FILE__));
	wp_enqueue_script('iconjs', plugins_url('js/jquery.fonticonpicker.js',__FILE__), array('jquery'));
	wp_enqueue_style('datatablescss', 'http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/css/jquery.dataTables.csscss/fontello.css');
	wp_enqueue_script('datatablesjs', 'http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/jquery.dataTables.min.js', array('jquery'));
		}
	}
	
add_action('admin_enqueue_scripts','add_backend_nwn_scripts');

//Enqueue the Front End Scripts
function add_frontend_nwn_scripts(){
	wp_enqueue_script ('jquery');
	wp_enqueue_script('notifybar',plugins_url('js/notifybar.js',__FILE__), array('jquery'));
	wp_enqueue_style('notifybar',plugins_url('css/notifybar.css',__FILE__));
}

add_action('wp_enqueue_scripts','add_frontend_nwn_scripts');

//-------------------------------------------------------------------------------------

// Add menu option to the network settings menu (superadmins only)
add_action('network_admin_menu', 'add_nwn_settings_page');
function add_nwn_settings_page() {
  add_submenu_page(
       'settings.php',
       'Network Wide Notification',
       'Network Wide Notification',
       'manage_network_options',
       'nwn-settings',
       'networkwide_message_form'
  );    
}

// Add menu option to the primary blog settings menu for admins

add_action('admin_menu','add_nwn_settings_page_primary_blog');
function add_nwn_settings_page_primary_blog(){
$options = get_site_option('nwn_options'); 
$blog_id = get_current_blog_id();
if (($blog_id == '1') && ($options['primary_admin'])) {
	add_options_page( 
		'Network Wide Notification',
       'Network Wide Notification',
       'manage_options',
       'nwn-settings-2',
       'networkwide_message_form' 
	);

}
}

//Set Default Values
function nwn_defaults(){
	$default = array(
		'message' => '',
		'timeDate' => '',
		'color_scheme' => 'blue',
		'shortcode' => '',
		'headerbar' => '',
		'on_off' => 'off',
		'site_id' => ''
	);
return $default;
}

//Code to run when plugin is activated
register_activation_hook(__FILE__,'nwn_plugin_install');
add_action('admin_init', 'nwn_plugin_redirect');

function nwn_plugin_activate() {
    add_option('nwn_plugin_do_activation_redirect', true);
}

function nwn_plugin_redirect() {
    if (get_option('nwn_plugin_do_activation_redirect', false)) {
        delete_option('nwn_plugin_do_activation_redirect');
        wp_redirect('settings.php?page=nwn-settings');
    }
}

function nwn_plugin_install() {
    add_site_option('nwn_options', nwn_defaults());
	nwn_plugin_activate();
}

//Update the options
if(isset($_POST['nwn_update'])){
	update_site_option('nwn_options', nwn_updates());
}

function nwn_updates() {
	$options = $_POST['nwn_options'];
	$site_id = implode(',', $_POST['site_id']);
	    $update_val = array(
		'message' => $options['message'],
		'timeDate' => $options['timeDate'],
		'color_scheme' => $options['color_scheme'],
    	'shortcode' => $options['shortcode'],
    	'headerbar' => $options['headerbar'],
    	'on_off' => $options['on_off'],
    	'on_off_2' => (isset($options['on_off_2'])) ? 'off' : 'on',
    	'site_id' => $site_id,
    	'primary_admin' => $options['primary_admin']
    );

return $update_val;
}

// Add Settings Form

function networkwide_message_form(){
  // Get values from the DB
  $options = get_site_option('nwn_options');  
  wp_nonce_field('update-options');
  ?>
  <script>
  jQuery(document).ready(function($){

//Date Picker JS

  $('#datetimepicker').datetimepicker({
  format:'m/d/Y H:i'
  });

//Icon Picker JS

   	$('#myselect').fontIconPicker({
		source: ['icon-heart', 'icon-lemon', 'icon-user', 'icon-tag', 'icon-help'],
		emptyIcon: true,
		hasSearch: false
	});


//Select All JS

  $('#selectall').click(function () {
        $('.selectedId').prop('checked', isChecked('selectall'));
    });
    function isChecked(checkboxId) {
    var id = '#' + checkboxId;
    return $(id).is(":checked");
}
function resetSelectAll() {
    // if all checkbox are selected, check the selectall checkbox
    // and viceversa
    if ($(".selectedId").length == $(".selectedId:checked").length) {
        $("#selectall").attr("checked", "checked");
    } else {
        $("#selectall").removeAttr("checked");
    }

    if ($(".selectedId:checked").length > 0) {
        $('#edit').attr("disabled", false);
    } else {
        $('#edit').attr("disabled", true);
    }
}

//Live Preview JS

	$('input#nwn_options[message]').keyup(function () { alert('test'); });

  });

// Color Scheme Toggle JS

  jQuery('label').click(function($){
    $(this).children('span').addClass('input-checked');
    $(this).parent('.toggle').siblings('.toggle').children('label').children('span').removeClass('input-checked');
});

//Datatables

jQuery(document).ready( function ($) {
  var table = $('#sites').DataTable();
} );

  </script>
  

  <!-- Start Districtwide Message Form -->
<h2>Network Wide Notification</h2>
<div id="nwn-settings">
<div id="nwn-form">
<?php
if(isset($_POST['nwn_update'])){ ?>
    <div id="message" class="updated">
        <p>
        Settings updated.
        </p>
    </div>
<?php } ?>
<form method="post">
 <table class="form-table left-side" id="nwn-prefs">
  	<tbody>

<!-- Message -->
	    <tr valign="top">
	    	<th scope="row">
			    <label>Network Wide Message</label> 
		    </th>
		    <td id="message-p">
			    <input type="text" name="nwn_options[message]" id="nwn_options[message]" value="<?php echo $options['message']; ?>" size="50"></textarea><br />
			    <p class="description">This message will be displayed across every site on the network once activated, in the location specified below.</p>
	    	</td>
	    </tr>
	    
<!-- Icon -->
	    <tr valign="top">
	    	<th scope="row">
			    <label>Icon</label>
		    </th>
		    <td>
		<select id="myselect" name="myselect" class="myselect">
			<option value="">No icon</option>
			<option>icon-user</option>
			<option>icon-search</option>
			<option>icon-right-dir</option>
			<option>icon-star</option>
			<option>icon-cancel</option>
			<option>icon-help-circled</option>
			<option>icon-info-circled</option>
			<option>icon-eye</option>
			<option>icon-tag</option>
			<option>icon-bookmark</option>
			<option>icon-heart</option>
			<option>icon-thumbs-down-alt</option>
			<option>icon-upload-cloud</option>
			<option>icon-phone-squared</option>
			<option>icon-cog</option>
			<option>icon-wrench</option>
			<option>icon-volume-down</option>
			<option>icon-down-dir</option>
			<option>icon-up-dir</option>
			<option>icon-left-dir</option>
			<option>icon-thumbs-up-alt</option>
		</select>

			</td>
	    </tr>
	    
<!-- Message Expiration Time/Date -->
	    <tr valign="top">
	    	<th scope="row">
			    <label>Message Expiration Time/Date</label>
		    </th>
		    <td>
			    <input name="nwn_options[timeDate]" id="datetimepicker" value="<?php echo $options['timeDate']; ?>" type="text"><br />
			     <p class="description">Set a time in the future. The message will display up until that time.</p>
	    	</td>
	    </tr>
	    
<!-- Display Location -->
	     <tr valign="top">
	    	<th scope="row">
			    <label>Display Location</label>
		    </th>
		    <td id="location-p">
			    <input type="checkbox" name="nwn_options[shortcode]" class="shortcode-p" id="nwn_options[shortcode]" value="shortcode" <?php if ($options['shortcode'] == "shortcode") echo "checked"; ?>>Shortcode [display_notification]<br />
			    <input type="checkbox" name="nwn_options[headerbar]" id="nwn_options[headerbar]" value="headerbar" <?php if ($options['headerbar'] == "headerbar") echo "checked"; ?>>Header Notification Bar
			     <p class="description">Set a location for the message to display.</p>
	    	</td>
	    </tr>
	    
<!-- Color Scheme -->
	    <tr valign="top">
	    	<th scope="row">
			    <label>Color Scheme</label>
		    </th>
		    <td id="color_scheme">
			    <label class="green"><input type="radio" name="nwn_options[color_scheme]" id="nwn_options[color_scheme]" value="#2ecc71" <?php if ($options['color_scheme'] == "#2ecc71") echo "checked"; ?>><span>Green</span></label>
			    <label class="blue"><input type="radio" name="nwn_options[color_scheme]" id="nwn_options[color_scheme]" value="#3498db" <?php if ($options['color_scheme'] == "#3498db") echo "checked"; ?>><span>Blue</span></label>
			    <label class="red"><input type="radio" name="nwn_options[color_scheme]" id="nwn_options[color_scheme]" value="#e74c3c" <?php if ($options['color_scheme'] == "#e74c3c") echo "checked"; ?>><span>Red</span></label>
			    <label class="purple"><input type="radio" name="nwn_options[color_scheme]" id="nwn_options[color_scheme]" value="#9b59b6" <?php if ($options['color_scheme'] == "#9b59b6") echo "checked"; ?>><span>Purple</span></label>
			    <label class="yellow"><input type="radio" name="nwn_options[color_scheme]" id="nwn_options[color_scheme]" value="#f1c40f" <?php if ($options['color_scheme'] == "#f1c40f") echo "checked"; ?>><span>Yellow</span></label>
			    <label class="grey"><input type="radio" name="nwn_options[color_scheme]" id="nwn_options[color_scheme]" value="#95a5a6" <?php if ($options['color_scheme'] == "#95a5a6") echo "checked"; ?>><span>Grey</span></label>
	    	</td>
	    </tr>
<!-- Select which site(s) to display on: -->
	    <tr valign="top">
	    	<th scope="row">
			    <label>Select which site(s) to display on: </label>
			</th>
			<td>
			<table id="sites">
				<thead>
					<th>Select</th>
					<th>Site Name</th>
					<th>URL</th>
				</thead>
				<tbody>
					<?php
					$site_list = wp_get_sites( 0, 'all' );
					$site_id_pieces = explode(',', $options['site_id']);
					foreach ($site_list AS $site) {
					$site_id = $site['blog_id'];
					$site_details = get_blog_details($site_id);
					?>
					<tr><td><input type="checkbox" class="selectedId" name="site_id[]" value="<?php echo $site['blog_id']; ?>" <?php if (in_array($site_id, $site_id_pieces)) echo "checked"; ?>></td><td><?php echo $site_details->blogname; ?></td><td><?php echo $site['domain'].$site['path'] ?></td></tr>
					<?php } ?>
				</tbody>
			</table>
			
			<!-- <input type="checkbox" id="selectall">Select all</input><br /> -->
			</td>
		</tr>

<!-- Message On/Off -->
	    <tr valign="top">
	    	<th scope="row">
			    <label>Turn message: </label>
			</th>
			<td>
			    <div class="onoffswitch">
			    <input type="checkbox" name="nwn_options[on_off]" class="onoffswitch-checkbox" id="nwn_options[on_off]" <?php if ($options['on_off'] == "on") echo "checked"; ?>>
				    <label class="onoffswitch-label" for="nwn_options[on_off]">
				        <div class="onoffswitch-inner"></div>
				        <div class="onoffswitch-switch"></div>
				    </label>
				</div>
			</td>
		</tr>
<!-- Primary Blog Admin Access -->
<?php if (is_network_admin()){ ?>
	    <tr valign="top">
	    	<th scope="row">
			    <label>Allow Admin Control on Primary Blog?: </label>
			</th>
			<td>
			   <input type="checkbox" name="nwn_options[primary_admin]" id="nwn_options[primary_admin]" <?php if ($options['primary_admin']) echo 'checked'; ?> >
			</td>
		</tr>
<?php } ?>
	</tbody>
</table>
<input type="submit" name="nwn_update" id="nwn_update" class="button-primary" value="Update Message & Preview">
<input type="submit" name="nwn_options[on_off_2]" id="nwn_options[on_off_2]" <?php if ($options['on_off_2'] == "on"): echo "class='button-primary nwn-red' value='Turn Off'"; else: echo "class='button-primary nwn-green' value='Turn On'"; endif;?>>
</form>
</div>

<!-- Preview Area -->

<div id="nwn-preview">
	<h3>Live Preview</h3>
	<div class="browser">
		<?php if (($options['headerbar'] == 'headerbar') && ($options['shortcode'] == 'shortcode')): ?>
		<div class="header" style="background-color:<?php echo $options['color_scheme'];?>">
			<p class="message-preview"><?php echo $options['message']; ?></p>
		</div>
		<div class="spreview" style="color:<?php echo $options['color_scheme'];?>">
				<p class="message-preview"><?php echo $options['message']; ?></p>
		</div>
	<?php elseif ($options['headerbar']): ?>
		<div class="header" style="background-color:<?php echo $options['color_scheme'];?>">
			<p class="message-preview"><?php echo $options['message']; ?></p>
		</div>
	<?php elseif ($options['shortcode'] == 'shortcode'): ?>
		<div class="spreview" style="color:<?php echo $options['color_scheme'];?>">
			<p class="message-preview"><?php echo $options['message']; ?></p>
		</div>
	<?php else: ?>
	<div class="nothing">You haven't selected a location yet!</div>
	<?php endif;
	?>
	</div>
</div>
</div>
<script type="text/javascript">
jQuery('#color_scheme input').change(function($){
   var css = jQuery(this).attr("value");
   jQuery(".header").css('background-color',jQuery(this).val());
   jQuery(".spreview").css('color',jQuery(this).val());
});
jQuery('#message-p input').keyup(function($){
	var keyed = jQuery(this).val();
      jQuery('.message-preview').html(keyed);
});
// jQuery('#location-p input#nwn_options[shortcode]').change(function($){
// 	var keyed = jQuery(this).val();
//       jQuery('.message-preview').html(keyed);
// });
jQuery('.shortcode-p').change(function($){
  if (this.checked) {
    jQuery('#spreview').fadeIn('slow');
  } else {
    jQuery('#spreview').fadeOut('slow');
  }                   
});
</script>
  <?php

}


//-------------------------------------------------------------------------------------

//Top bar notification
function notifybar(){
	global $blog_id;
	$options = get_site_option('nwn_options');
	$now = strtotime("now");
	$site_id_pieces = explode(',', $options['site_id']);
	$unixTimeDate = strtotime($options['timeDate']);
if ($unixTimeDate >= $now && $options['on_off']){
if (in_array($blog_id, $site_id_pieces)){
if ($options['headerbar'] == 'headerbar'){
?>  
<style type="text/css">
#notifybar{top:0px;}
#notifybar .notifybar_topsec .notifybar_center .notifybar_block {color:#f2f2f2;}
#notifybar .notifybar_topsec .notifybar_center .notifybar_button {color:#9b59b6;}
body{margin-top: 45px;}

</style>

	<div id="notifybar">
		<a class="nbar_downArr" href="#nbar_downArr" style="display:none;"></a>
		<div class="notifybar_topsec" style="background-color:<?php echo $options['color_scheme']; ?>;">
			<div class="notifybar_center">
				<div class="notifybar_block"><?php echo $options['message']; ?></div>
			</div>
			<a href="JavaScript:void(0);" class="notifybar_close"></a>
		</div>
	</div>
	<a href="JavaScript:void(0);" class="notifybar_botsec" id="nbar_downArr" style="background-color:blue"></a>
<?php } ?>
<script type="text/javascript">
	$(document).ready(function(){
		$('body').prepend('<div class="notifybar_push"></div>');
		$('#notifybar').notifybar({staytime:'6000'});
	});
</script>
<?php 

}
}
}
add_action('wp_footer', 'notifybar');

function display_notification() {

	global $blog_id;
	$options = get_site_option('nwn_options');
	$now = strtotime("now");
	$site_id_pieces = explode(',', $options['site_id']);
	$unixTimeDate = strtotime($options['timeDate']);
if ($unixTimeDate >= $now && $options['on_off']){
if (in_array($blog_id, $site_id_pieces)){
if ($options['shortcode'] == 'shortcode'){
	$text = "<h4 class='message' style='color:" . $options['color_scheme'] . "'>" . $options['message'] . "</h4>";
	return $text;
	}
	
	else return null;
	
}
}
}
add_shortcode( 'display_notification', 'display_notification' );

?>