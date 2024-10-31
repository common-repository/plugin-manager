<?php
/*
Copyright 2008-2009  Utkarsh Kukreti  (admin AT drunkadmin DOT com)

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
include('../../../wp-load.php');
include('../../../wp-admin/includes/admin.php');

WP_Filesystem();
$UID = $_POST['id'];
$do = $_POST['do'];
if(preg_match("#[0-9a-fA-F]{64}#",$UID) ==false)
die("Error");

$sql = "SELECT DownloadLink, PluginLink FROM " . $wpdb->prefix . "PluginManager" .  " WHERE UniqueID = '" . ($UID) . "'";
$Result = $wpdb->get_results($sql, ARRAY_A) or die (mysql_error());
$DownloadLink = $Result[0][DownloadLink];
$PluginLink = $Result[0][PluginLink];

if($DownloadLink)
{
	
	if($do == "download")
	{
		// Download Plugin File
		$T = download_url($DownloadLink);
		if ( is_wp_error($T) )
		{
			error_log("download failed: " . $T->get_error_message());
			echo "Failed! - " . $T->get_error_message();
		}
		else
		{
			// Extract Plugin to /wp-content/plugins/
			$return = unzip_file($T, ABSPATH . "/wp-content/plugins/");
			if ( is_wp_error($return) )
			{
				error_log("unzip error: " . $return->get_error_message());
				echo "Failed! - " . $return->get_error_message();
			}
			else
				echo "Downloaded :)";
			unlink($T);
		}
		// Delete Temporary File
		
	}
	else if ($do == "activate")
	{
		$Plugins = get_plugins();
		preg_match("#http://wordpress.org/extend/plugins/([^/]+)/#", $PluginLink, $M);
		foreach($Plugins as $Path=>$Data)	
		{	
			if(strpos($Path,$M[1]) !== false) // If they Match
			{
				$FinalP = $Path;
				break;
			}
		}
		activate_plugin($FinalP);
		echo "Activated :)";
	}
	else if ($do == "deactivate")
	{
		$Plugins = get_plugins();
		preg_match("#http://wordpress.org/extend/plugins/([^/]+)/#", $PluginLink, $M);
		foreach($Plugins as $Path=>$Data)	
		{	
			if(strpos($Path,$M[1]) !== false) // If they Match
			{
				$FinalP = $Path;
				break;
			}
		}
		deactivate_plugins($FinalP);
		echo "Deactivated :)";
	}
	else die("Error");
}
else die("Error");

?>