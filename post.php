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

$page = $wpdb->escape($_GET['page']);
$rp = $wpdb->escape($_GET['rp']);
$sortname = $wpdb->escape($_GET['sortname']);
$sortorder = $wpdb->escape($_GET['sortorder']);
$query = $wpdb->escape($_GET['query']);
$qtype = $wpdb->escape($_GET['qtype']);

if (!$sortname) $sortname = 'id';
if (!$sortorder) $sortorder = 'asc';

$sort = "ORDER BY $sortname $sortorder";

if (!$page) $page = 1;
if (!$rp) $rp = 10;

$start = (($page-1) * $rp);

$limit = "LIMIT $start, $rp";

$where = "";
if ($query) $where = " WHERE $qtype LIKE '%$query%' ";

$sql = "SELECT * FROM " . $wpdb->prefix . "PluginManager" .  " $where $sort $limit";

$result = $wpdb->get_results($sql, ARRAY_A) or die (mysql_error());

$countsql = "SELECT COUNT(id) FROM " . $wpdb->prefix . "PluginManager";

$count = $wpdb->get_results($countsql, ARRAY_A) or die (mysql_error());

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT" ); 
header("Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . "GMT" ); 
header("Cache-Control: no-cache, must-revalidate" ); 
header("Pragma: no-cache" );
header("Content-type: text/xml");

$Plugins = get_plugins();
$ActivePlugins = get_option('active_plugins');

$xml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
$xml .= "<rows>";
$xml .= "<page>$page</page>";
$xml .= "<total>" . $count[0]['COUNT(id)'] . "</total>";
foreach($result as $T)
{
	$xml .= "<row id='".$T['id']."'>";
	$xml .= "<cell><![CDATA[". $T['id']. "]]></cell>";	
	$xml .= "<cell><![CDATA[" . "<a href='$T[PluginLink]' title='$T[Description]'>$T[Name]</a>]]></cell>";		
	$xml .= "<cell><![CDATA[<small>".utf8_encode($T['Description'])."</small>]]></cell>";		
	$xml .= "<cell><![CDATA[".utf8_encode($T['Version'])."]]></cell>";	
	$xml .= "<cell><![CDATA[".utf8_encode($T['Updated'])."]]></cell>";
	$xml .= "<cell><![CDATA[".utf8_encode($T['Downloads'])."]]></cell>";

	/* Plugin Present */
	preg_match("#http://wordpress.org/extend/plugins/([^/]+)/#", $T[PluginLink], $M);

	$Downloaded = false;
	$Activated = false;
	foreach($Plugins as $Path=>$Data)	
	{	
		if(strpos($Path,$M[1]) !== false) // If they Match
		{
			$Downloaded = true;
			break;
		}
	}
	if($Downloaded)
	{
		foreach($ActivePlugins as $Path)	
		{	
			if(strpos($Path,$M[1]) !== false) // If they Match
			{
				$Activated = true;
			}
		}
	}
	if($Downloaded)
		$xml .= "<cell><![CDATA[". '<input id = "D' . trim($T['UniqueID']) . '"type="button" class="button" value="Downloaded"/>' ."]]></cell>";	
	else
		$xml .= "<cell><![CDATA[". '<input id = "D' . trim($T['UniqueID']) . '"type="button" class="button" value="Download" onclick="Download(\''  . $T['UniqueID'] . '\');"  />' ."]]></cell>";	
	
	if(!$Downloaded)
		$xml .= "<cell><![CDATA[". '<input id = "A' . trim($T['UniqueID']) . '"type="button" class="button" value="Activate" disabled=disabled onclick="Activate(\''  . $T['UniqueID'] . '\');"  />' ."]]></cell>";	
	else if($Activated)
		$xml .= "<cell><![CDATA[". '<input id = "A' . trim($T['UniqueID']) . '"type="button" class="button" value="Deactivate" onclick="Deactivate(\''  . $T['UniqueID'] . '\');"  />' ."]]></cell>>";	
	else
		$xml .= "<cell><![CDATA[". '<input id = "A' . trim($T['UniqueID']) . '"type="button" class="button" value="Activate" onclick="Activate(\''  . $T['UniqueID'] . '\');"  />' ."]]></cell>";	
	
	$xml .= "</row>";		
}

$xml .= "</rows>";
echo $xml;
?>