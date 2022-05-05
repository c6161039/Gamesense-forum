<?php

/**
 * Copyright (C) 2010-2015 Visman (mio.visman@yandex.ru)
 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

if (!defined('PUN'))
	exit;

if (!$pun_user['is_guest'] && $pun_user['messages_flag'] == 1 && $pun_config['o_pms_enabled'] == '1' && $pun_user['messages_new'] > 0)
{
	if (PUN_ACTIVE_PAGE != 'pms_new' && empty($pun_config['o_pms_flasher']))
	{
		if (!isset($page_head))
			$page_head = array();

		if (file_exists(PUN_ROOT.'style/'.$pun_user['style'].'/reminder.css'))
			$page_head['reminderstyle'] = '<link rel="stylesheet" type="text/css" href="style/'.$pun_user['style'].'/reminder.css" />';
		else
			$page_head['reminderstyle'] = '<link rel="stylesheet" type="text/css" href="style/imports/reminder.css" />';

		$te = '<div id="reminderpmsnew" class="reminder" style="z-index: 101;">'."\n";
		$te.= '	<div class="remhandle">'."\n";
		$te.= '		'.$lang_common['PMnew']."\n";
		$te.= '		<div class="remcontrols" onclick="document.getElementById(\'reminderpmsnew\').style.display=\'none\';"><img title="Close" src="img/close.gif" /></div>'."\n";
		$te.= '	</div>'."\n";
		$te.= '	<div class="remcontent">'."\n";
		$te.= '		<p>'.sprintf($lang_common['PMmess'], $pun_user['messages_new']).'</p>'."\n";
		$te.= '		<p><a href="pmsnew.php">'.$lang_common['Show'].'</a></p>'."\n";
		$te.= '	</div>'."\n";
		$te.= '</div>'."\n";
		$te.= '</body>';

		$tpl_main = str_replace('</body>', $te, $tpl_main);
	}
	// update users
	$db->query('UPDATE '.$db->prefix.'users SET messages_flag=0 WHERE id='.$pun_user['id']) or error('Unable to update user', __FILE__, __LINE__, $db->error());

}
