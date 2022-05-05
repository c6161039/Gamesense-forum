<?php

if (!defined('PUN')) exit;
define('PUN_QJ_LOADED', 1);
$forum_id = isset($forum_id) ? $forum_id : 0;

?>				<form id="qjump" method="get" action="viewforum.php">
					<div><label><span><?php echo $lang_common['Jump to'] ?><br /></span>
					<select name="id" onchange="window.location=('viewforum.php?id='+this.options[this.selectedIndex].value)">
						<optgroup label="General">
							<option value="14"<?php echo ($forum_id == 14) ? ' selected="selected"' : '' ?>>Announcements</option>
							<option value="15"<?php echo ($forum_id == 15) ? ' selected="selected"' : '' ?>>General talk</option>
							<option value="16"<?php echo ($forum_id == 16) ? ' selected="selected"' : '' ?>>Spotlight</option>
						</optgroup>
						<optgroup label="Premium lounge">
							<option value="17"<?php echo ($forum_id == 17) ? ' selected="selected"' : '' ?>>CS:GO Discussion</option>
							<option value="19"<?php echo ($forum_id == 19) ? ' selected="selected"' : '' ?>>Feedback</option>
							<option value="20"<?php echo ($forum_id == 20) ? ' selected="selected"' : '' ?>>Lua workshop</option>
							<option value="23"<?php echo ($forum_id == 23) ? ' selected="selected"' : '' ?>>CS:GO Lua scripts</option>
							<option value="24"<?php echo ($forum_id == 24) ? ' selected="selected"' : '' ?>>Link too CS:GO Lua API</option>
							<option value="25"<?php echo ($forum_id == 25) ? ' selected="selected"' : '' ?>>Marketplace</option>
						</optgroup>
					</select></label>
					<input type="submit" value="<?php echo $lang_common['Go'] ?>" accesskey="g" />
					</div>
				</form>
