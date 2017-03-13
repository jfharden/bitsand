<?php
/*-----------------------------------------------------------------------------
 | Bitsand - an online booking system for Live Role Play events
 |
 | File admin/admin_update_core.php
 |     Author: Pete Allison
 |  Copyright: (C) 2006 - 2015 The Bitsand Project
 |             (http://github.com/PeteAUK/bitsand)
 |
 | Bitsand is free software; you can redistribute it and/or modify it under the
 | terms of the GNU General Public License as published by the Free Software
 | Foundation, either version 3 of the License, or (at your option) any later
 | version.
 |
 | Bitsand is distributed in the hope that it will be useful, but WITHOUT ANY
 | WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 | FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 | details.
 |
 | You should have received a copy of the GNU General Public License along with
 | Bitsand.  If not, see <http://www.gnu.org/licenses/>.
 +---------------------------------------------------------------------------*/

include ('../inc/inc_head_db.php');
include ('../inc/inc_admin.php');
include ('../inc/inc_head_html.php');

?>
<script src="../inc/sorttable.js" type="text/javascript"></script>

<h1><?php echo TITLE?> - Update Core</h1>

<?php

include('../inc/version.php');
include('../inc/git.php');

$git = new GithubRepository('PeteAUK/bitsand');
$git_version = $git->getLatestTag();
$success = false;

if (BitsandVersion::under($git_version)) :
	if ($git->gitControlled()) : ?>
<p>Your installation of Bitsand is version controlled using Git.  Please execute the following command to update to the latest version:</p>
<code>git pull origin master --tag v<?php echo $git->getLatestTag(false); ?></code>
<?php
	else :
		if ($git->update($git_version)) : ?>
<p class="success">Success: Bitsand has been successfully updated</p>
<?php
		else : ?>
<p class="warn">Error: Unable to perform an inline update of Bitsand.</p>
<p><a href="<?php echo $git->getZipballUrl($git_version); ?>">Please update the core directly from the download</a></p>
<?php
		endif;
	endif;
else : ?>
<p>You are running on the latest version of Bitsand</p>
<?php
endif; ?>

<p><a href="admin_config_db_test.php">Click here to check configuration</a></p>

<?php

include ('../inc/inc_foot.php');