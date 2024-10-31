<?php
/*
Plugin Name: Plugin Manager
Plugin URI: http://www.drunkadmin.com/projects/plugin-manager
Description: Plugin Manager lets you to view, download and install plugins from wordpress.org from an AJAX'ed interface, instead of manually downloading, extracting and uploading each plugin.
Version: 0.7.10
Author: Utkarsh Kukreti
Author URI: http://www.DrunkAdmin.com

Copyright 2008  Utkarsh Kukreti  (admin AT drunkadmin DOT com)

This file is part of Plugin Manager.

Plugin Manager is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Plugin Manager is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Plugin Manager.  If not, see <http://www.gnu.org/licenses/>.
*/



if ( !defined('WP_CONTENT_URL') )
	define( 'WP_CONTENT_URL', get_option('siteurl') . '/wp-content');
if ( !defined('WP_CONTENT_DIR') )
	define( 'WP_CONTENT_DIR', WINABSPATH . 'wp-content' );

define('PM_FOLDER', plugin_basename( dirname(__FILE__)) );
define('PM_ABSPATH', WP_CONTENT_DIR.'/plugins/'.plugin_basename( dirname(__FILE__)).'/' );
define('PM_URLPATH', WP_CONTENT_URL.'/plugins/'.plugin_basename( dirname(__FILE__)).'/' );


register_activation_hook( __FILE__ ,'PluginManager_Activate');
register_deactivation_hook( __FILE__, 'PluginManager_DeActivate' );
function PluginManager_Activate ()
{
	@set_time_limit(360);
	global $wpdb;
	
	$table_name = $wpdb->prefix . "PluginManager";
	$sql = "DROP table if exists  " . $table_name . ";";
	$wpdb->query( $sql );

	if($wpdb->get_var("show tables like '$table_name'") != $table_name)
	{
	
		$sql = "CREATE TABLE " . $table_name . " (
		id int(11) NOT NULL auto_increment,
		PRIMARY KEY  (id),
		Name text NOT NULL,
		PluginLink text NOT NULL,
		DownloadLink text NOT NULL,
		Description text NOT NULL,
		Version text NOT NULL,
		Updated text NOT NULL,
		Downloads int(11) NOT NULL,
		UniqueID text NOT NULL);";
		
		$wpdb->query( $sql );
		
		$Dump = unserialize(file_get_contents(PM_ABSPATH . '/dump.txt'));

		foreach($Dump as $Name => $Data)
		{
			// 64 Char Random Key
			$UniqueID = md5(rand(0,1000000000)) . md5(rand(0,1000000000));
			$sql = "INSERT INTO $table_name (Name, PluginLink, DownloadLink, Description, Version, Updated, Downloads, UniqueID) VALUES ('$Name', '$Data[Link]', '$Data[Download]', '$Data[Description]', '$Data[Version]', '$Data[Updated]',  " . str_replace(",", "", $Data[Downloads]) . ", '$UniqueID' );";
			$wpdb->query( $sql );
		}   
	}
}
function PluginManager_DeActivate ()
{
	global $wpdb;
	$table_name = $wpdb->prefix . "PluginManager";
	$sql = "DROP table if exists  " . $table_name . ";";
	$wpdb->query( $sql );
}
function AddToMenu()
{
    add_menu_page('Plugin Manager', 'Plugin Manager', 10, __file__, 'Page');
}
add_action('admin_menu', 'AddToMenu');

function Page()
{
	?>
<style type="text/css" media="screen">@import"<?php echo PM_URLPATH . 'style.css'; ?>"; ></style>
<script type="text/javascript" src="<?php echo PM_URLPATH . 'jquery.js'; ?>"></script>
<script type="text/javascript" src="<?php echo PM_URLPATH . 'flexigrid.js'; ?>"></script>
<div align="center" style="font-size:16px; padding:16px; margin:16px;"><a href="http://www.drunkadmin.com/projects/plugin-manager/">PluginManager <span style="font-size:9px">v0.7</span></a> by <a href="http://www.drunkadmin.com">Utkarsh Kukreti</a> <span style="float:right; font-size:11px;"><a href="http://www.drunkadmin.com/forums/">Support Forums</a>&nbsp;&nbsp;&nbsp;<a href="http://www.drunkadmin.com/contact-me/">Contact Me</a></span></div>
<table id="Content" ></table>

<script type="text/javascript">

jQuery("#Content").flexigrid (
{
	url: '<?php echo PM_URLPATH . 'post.php'; ?>',
	dataType: 'xml',
	colModel :
	[
		{display: 'ID', name : 'id', width : 50, sortable : true, align: 'center'},
		{display: 'Name', name : 'Name', width : 350, sortable : true, align: 'left'},
		{display: 'Description', name : 'Description', hide:true, width : 350, visible:false, sortable : false, align: 'left'},
		{display: 'Version', name : 'Version', width : 75, sortable : false, align: 'left'},
		{display: 'Updated', name : 'Updated', width : 75, sortable : true, align: 'center'},
		{display: 'Downloads', name : 'Downloads', width : 75, sortable : true, align: 'right'},
		{display: 'Download', name : 'Download', width : 110, sortable : false, align: 'center'},
		{display: 'Activate', name : 'Activate', width : 90, sortable : false, align: 'center'}
	],
	searchitems :
	[
		{display: 'Name', name : 'Name', isdefault: true},
		{display: 'Description', name : 'Description'}
	],
	sortname: "id",
	sortorder: "asc",
	usepager: true,
	title: 'Plugin Manager',
	useRp: true,
	rp: 20,
	rpOptions: [10,20,50,100],
	showTableToggleBtn: false,
	height:450,
	width: 1000,
	method: 'GET'
	}
);
function Download(uid)
{
	jQuery("#D" + uid).val("Downloading...");
	jQuery.ajax
	(
		{
			type: "POST",
			url: '<?php echo PM_URLPATH . '/do.php'; ?>',
			data: "do=download&id="+uid,
			success:
			function (data)
			{
				jQuery("#D" + uid).val(data);
				jQuery("#A" + uid)[0].disabled = false;
			}
		}
	);
}
function Activate(uid)
{
	jQuery("#A" + uid).val("Activating...");
	jQuery.ajax
	(
		{
			type: "POST",
			url: '<?php echo PM_URLPATH . '/do.php'; ?>',
			data: "do=activate&id="+uid,
			success:
			function (data)
			{
				jQuery("#A" + uid).val(data);
			}
		}
	);
}
function Deactivate(uid)
{
	jQuery("#A" + uid).val("Deactivating...");
	jQuery.ajax
	(
		{
			type: "POST",
			url: '<?php echo PM_URLPATH . '/do.php'; ?>',
			data: "do=deactivate&id="+uid,
			success:
			function (data)
			{
				jQuery("#A" + uid).val(data);
			}
		}
	);
}
</script>

</body>
</html>

    <?php
}
?>