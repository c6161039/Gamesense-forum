<?php

/**
 * Copyright (C) 2008-2012 FluxBB
 * based on code by Rickard Andersson copyright (C) 2002-2008 PunBB
 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

// Make sure no one attempts to run this script "directly"
if (!defined('PUN'))
	exit;

// Send no-cache headers
header('Expires: Thu, 21 Jul 1977 07:30:00 GMT'); // When yours truly first set eyes on this world! :)
header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache'); // For HTTP/1.0 compatibility

// Send the Content-type header in case the web server is setup to send something else
header('Content-type: text/html; charset=utf-8');

// Prevent site from being embedded in a frame unless FORUM_FRAME_OPTIONS is set
// to a valid X-Frame-Options header value or false
if (defined('FORUM_FRAME_OPTIONS'))
{
	if (preg_match('/^(?:allow-from|deny|sameorigin)/i', FORUM_FRAME_OPTIONS))
		header('X-Frame-Options: '.FORUM_FRAME_OPTIONS);
}
else
	header('X-Frame-Options: deny');

// Load the template
if (defined('PUN_ADMIN_CONSOLE'))
	$tpl_file = 'admin.tpl';
else if (defined('PUN_HELP'))
	$tpl_file = 'help.tpl';
else
	$tpl_file = 'main.tpl';

if (file_exists(PUN_ROOT.'style/'.$pun_user['style'].'/'.$tpl_file))
{
	$tpl_file = PUN_ROOT.'style/'.$pun_user['style'].'/'.$tpl_file;
	$tpl_inc_dir = PUN_ROOT.'style/'.$pun_user['style'].'/';
}
else
{
	$tpl_file = PUN_ROOT.'include/template/'.$tpl_file;
	$tpl_inc_dir = PUN_ROOT.'include/user/';
}

$tpl_main = file_get_contents($tpl_file);

// START SUBST - <pun_include "*">
preg_match_all('%<pun_include "([^"]+)">%i', $tpl_main, $pun_includes, PREG_SET_ORDER);

foreach ($pun_includes as $cur_include)
{
	ob_start();

	$file_info = pathinfo($cur_include[1]);
	
	if (!in_array($file_info['extension'], array('php', 'php4', 'php5', 'inc', 'html', 'txt'))) // Allow some extensions
		error(sprintf($lang_common['Pun include extension'], pun_htmlspecialchars($cur_include[0]), basename($tpl_file), pun_htmlspecialchars($file_info['extension'])));
		
	if (strpos($file_info['dirname'], '..') !== false) // Don't allow directory traversal
		error(sprintf($lang_common['Pun include directory'], pun_htmlspecialchars($cur_include[0]), basename($tpl_file)));

	// Allow for overriding user includes, too.
	if (file_exists($tpl_inc_dir.$cur_include[1]))
		require $tpl_inc_dir.$cur_include[1];
	else if (file_exists(PUN_ROOT.'include/user/'.$cur_include[1]))
		require PUN_ROOT.'include/user/'.$cur_include[1];
	else
		error(sprintf($lang_common['Pun include error'], pun_htmlspecialchars($cur_include[0]), basename($tpl_file)));

	$tpl_temp = ob_get_contents();
	$tpl_main = str_replace($cur_include[0], $tpl_temp, $tpl_main);
	ob_end_clean();
}
// END SUBST - <pun_include "*">


// START SUBST - <pun_language>
$tpl_main = str_replace('<pun_language>', $lang_common['lang_identifier'], $tpl_main);
// END SUBST - <pun_language>


// START SUBST - <pun_content_direction>
$tpl_main = str_replace('<pun_content_direction>', $lang_common['lang_direction'], $tpl_main);
// END SUBST - <pun_content_direction>


// START SUBST - <pun_head>
ob_start();

// Define $p if it's not set to avoid a PHP notice
$p = isset($p) ? $p : null;

// Is this a page that we want search index spiders to index?
if (!defined('PUN_ALLOW_INDEX'))
	echo '<meta name="ROBOTS" content="NOINDEX, FOLLOW" />'."\n";

?>
<title><?php echo generate_page_title($page_title, $p) ?></title>
<link rel="stylesheet" type="text/css" href="style/<?php echo $pun_user['style'].'.css' ?>" />

<?php

if (basename($_SERVER['PHP_SELF']) == 'viewtopic.php')
{
	if (file_exists(PUN_ROOT.'lang/'.$pun_user['language'].'/like_mod.php'))
		require PUN_ROOT.'lang/'.$pun_user['language'].'/like_mod.php';
	else
		require PUN_ROOT.'lang/English/like_mod.php';
		
	echo '<script src="//code.jquery.com/jquery-1.11.0.min.js"></script>'."\n"; // Delete this line if jQuery is already loaded
	echo '<script src="//code.jquery.com/jquery-migrate-1.2.1.min.js"></script>'."\n"; // This as well
	echo '<script type="text/javascript" src="include/like_mod.js"></script>'."\n";
	echo '<script type="text/javascript">$(document).ready(function(){$(\'a.like\').wsLiker(\''.$pun_user['id'].'\', \''.pun_htmlspecialchars($pun_user['username']).'\', \''.$db->escape($lang_like_mod['Like']).'\', \''.$db->escape($lang_like_mod['Unlike']).'\', \''.$db->escape($lang_like_mod['Like this post']).'\');});</script>'."\n";
}

if (defined('PUN_ADMIN_CONSOLE'))
{
	if (file_exists(PUN_ROOT.'style/'.$pun_user['style'].'/base_admin.css'))
		echo '<link rel="stylesheet" type="text/css" href="style/'.$pun_user['style'].'/base_admin.css" />'."\n";
	else
		echo '<link rel="stylesheet" type="text/css" href="style/imports/base_admin.css" />'."\n";
}

if (isset($required_fields))
{
	// Output JavaScript to validate form (make sure required fields are filled out)

?>
<script type="text/javascript">
/* <![CDATA[ */
function process_form(the_form)
{
	var required_fields = {
<?php
	// Output a JavaScript object with localised field names
	$tpl_temp = count($required_fields);
	foreach ($required_fields as $elem_orig => $elem_trans)
	{
		echo "\t\t\"".$elem_orig.'": "'.addslashes(str_replace('&#160;', ' ', $elem_trans));
		if (--$tpl_temp) echo "\",\n";
		else echo "\"\n\t};\n";
	}
?>
	if (document.all || document.getElementById)
	{
		for (var i = 0; i < the_form.length; ++i)
		{
			var elem = the_form.elements[i];
			if (elem.name && required_fields[elem.name] && !elem.value && elem.type && (/^(?:text(?:area)?|password|file)$/i.test(elem.type)))
			{
				alert('"' + required_fields[elem.name] + '" <?php echo $lang_common['required field'] ?>');
				elem.focus();
				return false;
			}
		}
	}
	return true;
}
/* ]]> */
</script>
<?php

}

// New PMS
require PUN_ROOT.'include/pms_new/pmsnheader.php';
$page_head['colorize_groups'] = '<style type="text/css">'.$GLOBALS['pun_colorize_groups']['style'].'</style>'; // need $GLOBALS for message func
if (!empty($page_head))
	echo implode("\n", $page_head)."\n";

$tpl_temp = trim(ob_get_contents());
$tpl_main = str_replace('<pun_head>', $tpl_temp, $tpl_main);
ob_end_clean();
// END SUBST - <pun_head>


// START SUBST - <body>
if (isset($focus_element))
{
	$tpl_main = str_replace('<body onload="', '<body onload="document.getElementById(\''.$focus_element[0].'\').elements[\''.$focus_element[1].'\'].focus();', $tpl_main);
	$tpl_main = str_replace('<body>', '<body onload="document.getElementById(\''.$focus_element[0].'\').elements[\''.$focus_element[1].'\'].focus()">', $tpl_main);
}
// END SUBST - <body>


// START SUBST - <pun_page>
$tpl_main = str_replace('<pun_page>', htmlspecialchars(basename($_SERVER['SCRIPT_NAME'], '.php')), $tpl_main);
// END SUBST - <pun_page>


$tpl_main = str_replace('<pun_title>', '
			<h1><a href="index.php">afk<span>boost</span></a></h1>
			<div id="brddesc"><p><span>Get good. Get vertigo boost panel.</span></p></div>
		', $tpl_main);


// START SUBST - <pun_desc>
$tpl_main = str_replace('<pun_desc>', '<div id="brddesc">'.$pun_config['o_board_desc'].'</div>', $tpl_main);
// END SUBST - <pun_desc>


// START SUBST - <pun_navlinks>
$links = array();

// Index should always be displayed
$links[] = '<li id="navnotifications"><span class="fake-link" id="notifications" href="notifications.php"><i class="fa fa-lg fa-bell" id="notifications-badge" data-after-content="0"></i></span></li>';
$links[] = '<li id="navindex"'.((PUN_ACTIVE_PAGE == 'index') ? ' class="isactive"' : '').'><a href="index.php">'.$lang_common['Index'].'</a></li>';

if ($pun_user['g_read_board'] == '1' && $pun_user['g_view_users'] == '1')
	//$links[] = '<li id="navuserlist"'.((PUN_ACTIVE_PAGE == 'userlist') ? ' class="isactive"' : '').'><a href="userlist.php">'.$lang_common['User list'].'</a></li>';

if ($pun_config['o_rules'] == '1' && (!$pun_user['is_guest'] || $pun_user['g_read_board'] == '1' || $pun_config['o_regs_allow'] == '1'))
	$links[] = '<li id="navrules"'.((PUN_ACTIVE_PAGE == 'rules') ? ' class="isactive"' : '').'><a href="misc.php?action=rules">'.$lang_common['Rules'].'</a></li>';

if ($pun_user['g_read_board'] == '1' && $pun_user['g_search'] == '1')
	$links[] = '<li id="navsearch"'.((PUN_ACTIVE_PAGE == 'search') ? ' class="isactive"' : '').'><a href="search.php">'.$lang_common['Search'].'</a></li>';

if ($pun_user['is_guest'])
{
	$links[] = '<li id="navregister"'.((PUN_ACTIVE_PAGE == 'register') ? ' class="isactive"' : '').'><a href="register.php">'.$lang_common['Register'].'</a></li>';
	$links[] = '<li id="navlogin"'.((PUN_ACTIVE_PAGE == 'login') ? ' class="isactive"' : '').'><a href="login.php">'.$lang_common['Login'].'</a></li>';
}
else
{
	$links[] = '<li id="navprofile"'.((PUN_ACTIVE_PAGE == 'profile') ? ' class="isactive"' : '').'><a href="profile.php?id='.$pun_user['id'].'">'.$lang_common['Profile'].'</a></li>';
// New PMS
	if ($pun_config['o_pms_enabled'] == '1' && ($pun_user['g_pm'] == 1 || $pun_user['messages_new'] > 0))
		$links[] = '<li id="navpmsnew"'.(((PUN_ACTIVE_PAGE == 'pms_new') || ($pun_user['messages_new'] > 0)) ? ' class="isactive"' : '').'><a href="pmsnew.php">'.$lang_common['PM'].(($pun_user['messages_new'] > 0) ? ' ('.$pun_user['messages_new'].(empty($pun_config['o_pms_flasher']) ? '' : '&nbsp;<img style="border: 0 none; vertical-align: middle;" src="img/flasher.gif" alt="flasher" />' ).')' : '').'</a></li>';
// New PMS

	
	if($pun_user['g_id'] == 4){
	 $links[] = '<li id="premium"'.((PUN_ACTIVE_PAGE == 'premium') ? ' class="isactive"' : '').'><a class="usergroup-1" href="payment.php?game=csgo">Buy GameSense</a></li>';
   
	 }else{
	$links[] = '<li id="premium"'.((PUN_ACTIVE_PAGE == 'premium') ? ' class="isactive"' : '').'><a class="usergroup-5" href="profile.php?section=premium&amp;id='.$pun_user['id'].'">Premium</a></li>';
   	
    }
	
	if ($pun_user['is_admmod'])
			$links[] = '<li id="navadmin"'.((PUN_ACTIVE_PAGE == 'admin') ? ' class="isactive"' : '').'><a class="usergroup-2" href="admin_index.php">'.$lang_common['Admin'].'</a></li>';

	$links[] = '<li id="navlogout"><a href="login.php?action=out&amp;id='.$pun_user['id'].'&amp;csrf_token='.pun_csrf_token().'">'.$lang_common['Logout'].'</a></li>';
}

// Are there any additional navlinks we should insert into the array before imploding it?
if ($pun_user['g_read_board'] == '1' && $pun_config['o_additional_navlinks'] != '')
{
	if (preg_match_all('%([0-9]+)\s*=\s*(.*?)\n%s', $pun_config['o_additional_navlinks']."\n", $extra_links))
	{
		// Insert any additional links into the $links array (at the correct index)
		$num_links = count($extra_links[1]);
		for ($i = 0; $i < $num_links; ++$i)
			array_splice($links, $extra_links[1][$i], 0, array('<li id="navextra'.($i + 1).'">'.$extra_links[2][$i].'</li>'));
	}
}

$tpl_temp = '<div id="brdmenu" class="inbox">'."\n\t\t\t".'<ul>'."\n\t\t\t\t".implode("\n\t\t\t\t", $links)."\n\t\t\t".'</ul>'."\n\t\t".'</div>';
$tpl_main = str_replace('<pun_navlinks>', $tpl_temp, $tpl_main);
// END SUBST - <pun_navlinks>


// START SUBST - <pun_status>
$page_statusinfo = $page_topicsearches = array();

if ($pun_user['is_guest'])
	$page_statusinfo = '<p class="conl">'.$lang_common['Not logged in'].'</p>';
else
{
	$page_statusinfo[] = '<li><span>'.$lang_common['Logged in as'].' <strong>'.pun_htmlspecialchars($pun_user['username']).'</strong></span></li>';
if ($pun_user['group_id'] != 4){ 

		$datehis1 = new DateTime($pun_user['csgo']);	
$datehis2 = new DateTime('now');				

				
				


if ($pun_user['csgo'] != null && $datehis1>$datehis2 ){ 
	 $date = new DateTime($pun_user['csgo']);						
$resultdate = format_time($date->getTimestamp(), false, null, null, false, true);
  $page_statusinfo[] = '<li><span><strong>Premium (Counter-Strike: Global Offensive)</strong> until '.$resultdate.' [<a href="payment.php?game=csgo">extend subscription</a>]</span></li>';
 $page_statusinfo[] = '<li><span><a href="discord.php">Join us on discord</a> • <a href="viewtopic.php?id=11">Common issues</a></span></li>';


  } else {
	  
	  $page_statusinfo[] = '<li><span><a href="payment.php?game=csgo">Extend subscription</a></span></li>';

  }
  } else {

$page_statusinfo[] = '<li><span><a href="payment.php?game=csgo">Buy premium</a></span></li>';
  }  

	if ($pun_user['is_admmod'])
	{
		if ($pun_config['o_report_method'] == '0' || $pun_config['o_report_method'] == '2')
		{
			$result_header = $db->query('SELECT 1 FROM '.$db->prefix.'reports WHERE zapped IS NULL') or error('Unable to fetch reports info', __FILE__, __LINE__, $db->error());

			if ($db->result($result_header))
				$page_statusinfo[] = '<li class="reportlink"><span><strong><a href="admin_reports.php">'.$lang_common['New reports'].'</a></strong></span></li>';
		}

		if ($pun_config['o_maintenance'] == '1')
			$page_statusinfo[] = '<li class="maintenancelink"><span><strong><a href="admin_options.php#maintenance">'.$lang_common['Maintenance mode enabled'].'</a></strong></span></li>';
	}

	if ($pun_user['g_read_board'] == '1' && $pun_user['g_search'] == '1')
	{
		$page_topicsearches[] = '<a href="search.php?action=show_replies" title="'.$lang_common['Show posted topics'].'">'.$lang_common['Posted topics'].'</a>';
		$page_topicsearches[] = '<a href="search.php?action=show_new" title="'.$lang_common['Show new posts'].'">'.$lang_common['New posts header'].'</a>';
	}
}

// Quick searches
if ($pun_user['g_read_board'] == '1' && $pun_user['g_search'] == '1')
{
	$page_topicsearches[] = '<a href="search.php?action=show_recent" title="'.$lang_common['Show active topics'].'">'.$lang_common['Active topics'].'</a>';
	$page_topicsearches[] = '<a href="search.php?action=show_unanswered" title="'.$lang_common['Show unanswered topics'].'">'.$lang_common['Unanswered topics'].'</a>';
}


// Generate all that jazz
$tpl_temp = '<div id="brdwelcome" class="inbox">';

// The status information
if (is_array($page_statusinfo))
{
	$tpl_temp .= "\n\t\t\t".'<ul class="conl">';
	$tpl_temp .= "\n\t\t\t\t".implode("\n\t\t\t\t", $page_statusinfo);
	$tpl_temp .= "\n\t\t\t".'</ul>';
}
else
	$tpl_temp .= "\n\t\t\t".$page_statusinfo;

// Generate quicklinks
if (!empty($page_topicsearches))
{
	$tpl_temp .= "\n\t\t\t".'<ul class="conr">';
	$tpl_temp .= "\n\t\t\t\t".'<li><span>'.$lang_common['Topic searches'].' '.implode(' | ', $page_topicsearches).'</span></li>';
	$tpl_temp .= "\n\t\t\t".'</ul>';
}

$tpl_temp .= "\n\t\t\t".'<div class="clearer"></div>'."\n\t\t".'</div>';
$tpl_main = str_replace('<pun_status>', $tpl_temp, $tpl_main);
// END SUBST - <pun_status>

ob_start();
// New PMS
	if (basename($_SERVER['PHP_SELF']) == "index.php" && !$pun_user['is_guest'] && $pun_config['o_pms_enabled'] == '1' && ($pun_user['messages_new'] > 0)){
	?>	<div class='notice-bar'><a href='pmsnew.php'>You have unread private messages!</a></div>
	<?php }
	$mytopid = $pun_user['id'];
	$result447 = $db->query("SELECT * FROM `codes` WHERE `by`='$mytopid' AND `used`='0'") or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
   // $invby = $db->fetch_assoc($result447);
	if (!$pun_user['is_guest'] && basename($_SERVER['PHP_SELF']) == "index.php" && $db->num_rows($result447)){
	?>	<div class='notice-bar'><a href='profile.php?section=premium&id=<?php echo $mytopid ?>'>You have unused invitation codes!</a></div>
	<?php }
	$result4478o = $db->query("SELECT `used_version` FROM `users` WHERE `id`='$mytopid'") or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
	$used = $db->fetch_assoc($result4478o);
	$result4478op = $db->query("SELECT `version` FROM `versions` WHERE `id`='1'") or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
	$usedp = $db->fetch_assoc($result4478op);
	if($used['used_version'] != $usedp['version'] && basename($_SERVER['PHP_SELF']) == "index.php" && !$pun_user['is_guest'] && $pun_user['g_id'] != 4){
	
	?>	<div class='notice-bar'><a href='profile.php?section=premium&id=<?php echo $mytopid ?>'>New client update is AVAILABLE!!</a></div>
	<?php }
	
	// START SUBST - <pun_announcement>
	if (!$pun_user['is_guest']) {
if ($pun_config['o_announcement'] == '1')
{
	?>
	
	<div id="announce" class="block">
	<div class="hd"><h2><span><?php echo $lang_common['Announcement'] ?></span></h2></div>
	<div class="box">
		<div id="announce-block" class="inbox">
			<div class="usercontent"><?php echo $pun_config['o_announcement_message'] ?></div>
		</div>
	</div>
</div>

<?php }
}
// New PMS
$tpl_temp = trim(ob_get_contents());
	$tpl_main = str_replace('<pun_announcement>', $tpl_temp, $tpl_main);
ob_end_clean();




// END SUBST - <pun_announcement>


// START SUBST - <pun_main>
ob_start();

define('PUN_HEADER', 1);
