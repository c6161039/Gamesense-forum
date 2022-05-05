<?php

/**
 * Copyright (C) 2010-2013 Visman (visman@inbox.ru)
 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

// Make sure no one attempts to run this script "directly"
if (!defined('PUN'))
	exit;

// Load language file
if (file_exists(PUN_ROOT.'lang/'.$admin_language.'/admin_plugin_poll.php'))
	require PUN_ROOT.'lang/'.$admin_language.'/admin_plugin_poll.php';
else
	require PUN_ROOT.'lang/English/admin_plugin_poll.php';

// Tell admin_loader.php that this is indeed a plugin and that it is loaded
define('PUN_PLUGIN_LOADED', 1);
define('PLUGIN_VERSION', '1.3.3');
define('PLUGIN_URL', pun_htmlspecialchars(get_base_url(true).'/admin_loader.php?plugin='.$_GET['plugin']));

// If the "Show text" button was clicked
if (isset($_POST['show_text']))
{

	$en_poll = isset($_POST['enable_poll']) ? intval($_POST['enable_poll']) : 0;
	$en_poll = ($en_poll == 1) ? 1 : 0;
	
	$db->query('UPDATE '.$db->prefix.'config SET conf_value=\''.$en_poll.'\' WHERE conf_name=\'o_poll_enabled\'') or error('Unable to update board config', __FILE__, __LINE__, $db->error());

	if ($en_poll == 1)
	{
		$poll_max_ques = isset($_POST['poll_max_ques']) ? $_POST['poll_max_ques'] : $pun_config['o_poll_max_ques'];
		$poll_max_field = isset($_POST['poll_max_field']) ? $_POST['poll_max_field'] : $pun_config['o_poll_max_field'];
		$poll_time = isset($_POST['poll_time']) ? $_POST['poll_time'] : $pun_config['o_poll_time'];
		$poll_term = isset($_POST['poll_term']) ? $_POST['poll_term'] : $pun_config['o_poll_term'];
		$poll_guest = isset($_POST['poll_guest']) ? $_POST['poll_guest'] : 0;
		$poll_guest = ($poll_guest == '1') ? 1 : 0;
		$poll_max_ques = ($poll_max_ques < 1) ? 1 : $poll_max_ques;
		$poll_max_ques = ($poll_max_ques > 10) ? 10 : $poll_max_ques;
		$poll_max_field = ($poll_max_field < 2) ? 2 : $poll_max_field;
		$poll_max_field = ($poll_max_field > 90) ? 90 : $poll_max_field;

		$db->query('UPDATE '.$db->prefix.'config SET conf_value=\''.intval($poll_max_ques).'\' WHERE conf_name=\'o_poll_max_ques\'') or error('Unable to update board config', __FILE__, __LINE__, $db->error());
		$db->query('UPDATE '.$db->prefix.'config SET conf_value=\''.intval($poll_max_field).'\' WHERE conf_name=\'o_poll_max_field\'') or error('Unable to update board config', __FILE__, __LINE__, $db->error());
		$db->query('UPDATE '.$db->prefix.'config SET conf_value=\''.intval($poll_time).'\' WHERE conf_name=\'o_poll_time\'') or error('Unable to update board config', __FILE__, __LINE__, $db->error());
		$db->query('UPDATE '.$db->prefix.'config SET conf_value=\''.intval($poll_term).'\' WHERE conf_name=\'o_poll_term\'') or error('Unable to update board config', __FILE__, __LINE__, $db->error());
		$db->query('UPDATE '.$db->prefix.'config SET conf_value=\''.intval($poll_guest).'\' WHERE conf_name=\'o_poll_guest\'') or error('Unable to update board config', __FILE__, __LINE__, $db->error());
	}

	if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
		require PUN_ROOT.'include/cache.php';

	generate_config_cache();

	redirect(PLUGIN_URL, $lang_admin_plugin_poll['Plugin redirect']);

}
else
{
	// Display the admin navigation menu
	generate_admin_menu($plugin);

	$cur_index = 1;

?>
	<div class="plugin blockform">
		<h2><span><?php echo $lang_admin_plugin_poll['Plugin title'].' v.'.PLUGIN_VERSION ?></span></h2>
		<div class="box">
			<div class="inbox">
				<p><?php echo $lang_admin_plugin_poll['Explanation 1'] ?></p>
				<p><?php echo $lang_admin_plugin_poll['Explanation 2'] ?></p>
			</div>
		</div>

		<h2 class="block2"><span><?php echo $lang_admin_plugin_poll['Form title'] ?></span></h2>
		<div class="box">
			<form id="example" method="post" action="<?php echo PLUGIN_URL.'&amp;'.time() ?>">
				<p class="submittop"><input type="submit" name="show_text" value="<?php echo $lang_admin_plugin_poll['Show text button'] ?>" tabindex="<?php echo ($cur_index++) ?>" /></p>
				<div class="inform">
					<fieldset>
						<legend><?php echo $lang_admin_plugin_poll['Legend1'] ?></legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
								<tr>
									<td>
										<label><input type="checkbox" name="enable_poll" value="1" tabindex="<?php echo ($cur_index++) ?>"<?php echo ($pun_config['o_poll_enabled'] == '1') ? ' checked="checked"' : '' ?> />&#160;&#160;<?php echo $lang_admin_plugin_poll['Q1'] ?></label>
									</td>
								</tr>
							</table>
						</div>
					</fieldset>
				</div>
<?php
if ($pun_config['o_poll_enabled'] == '1')
{
?>
				<div class="inform">
					<fieldset>
						<legend><?php echo $lang_admin_plugin_poll['Legend3'] ?></legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
								<tr>
									<td>
										<span><input type="text" name="poll_max_ques" value="<?php echo pun_htmlspecialchars($pun_config['o_poll_max_ques']) ?>"  tabindex="<?php echo ($cur_index++) ?>" size="4" maxlength="2" />&#160;&#160;<?php echo $lang_admin_plugin_poll['Q2'] ?></span>
									</td>
								</tr>
								<tr>
									<td>
										<span><input type="text" name="poll_max_field" value="<?php echo pun_htmlspecialchars($pun_config['o_poll_max_field']) ?>"  tabindex="<?php echo ($cur_index++) ?>" size="4" maxlength="2" />&#160;&#160;<?php echo $lang_admin_plugin_poll['Q3'] ?></span>
									</td>
								</tr>
								<tr>
									<td>
										<span><input type="text" name="poll_time" value="<?php echo pun_htmlspecialchars($pun_config['o_poll_time']) ?>"  tabindex="<?php echo ($cur_index++) ?>" size="8" maxlength="8" />&#160;&#160;<?php echo $lang_admin_plugin_poll['Q4'] ?></span>
									</td>
								</tr>
								<tr>
									<td>
										<span><input type="text" name="poll_term" value="<?php echo pun_htmlspecialchars($pun_config['o_poll_term']) ?>"  tabindex="<?php echo ($cur_index++) ?>" size="4" maxlength="2" />&#160;&#160;<?php echo $lang_admin_plugin_poll['Q5'] ?></span>
									</td>
								</tr>
								<tr>
									<td>
										<label><input type="checkbox" name="poll_guest" value="1" tabindex="<?php echo ($cur_index++) ?>"<?php echo ($pun_config['o_poll_guest'] == '1') ? ' checked="checked"' : '' ?> />&#160;&#160;<?php echo $lang_admin_plugin_poll['Q6'] ?></label>
									</td>
								</tr>
							</table>
						</div>
					</fieldset>
				</div>
<?php
}
?>
				<p class="submitend"><input type="submit" name="show_text" value="<?php echo $lang_admin_plugin_poll['Show text button'] ?>" tabindex="<?php echo ($cur_index++) ?>" /></p>
			</form>
		</div>
	</div>
<?php
}