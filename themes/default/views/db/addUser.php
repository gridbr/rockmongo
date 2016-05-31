<h3><?php render_navigation($db); ?> &raquo; <?php hm("authentication"); ?></h3>

<div class="operation">
	<a href="<?php h(url("db.auth", array("db"=>$db))); ?>"><?php hm("users"); ?></a> |
	<a href="<?php h(url("db.addUser", array("db"=>$db))); ?>" class="current"><?php hm("adduser"); ?></a>
</div>

<div>
	<?php if(isset($error)):?><p class="error"><?php h($error);?></p><?php endif;?>
	<form method="post">
	<?php hm("username"); ?>:<br/>
	<input type="text" name="username" value="<?php h_escape(x("username"));?>"/><br/>
	<?php hm("password"); ?>:<br/>
	<input type="password" name="password"/><br/>
	<?php hm("confirm_pass"); ?>:<br/>
	<input type="password" name="password2"/><br/>
	<?php hm("Roles"); ?>:<br/>
	<input type="checkbox" name="read" value="<?php h("read");?>"/><?php hm("read"); ?>
	<input type="checkbox" name="readWrite" value="<?php h("readWrite");?>"/><?php hm("readWrite"); ?>
	<input type="checkbox" name="dbAdmin" value="<?php h("dbAdmin");?>"/><?php hm("dbAdmin"); ?>
	<input type="checkbox" name="userAdmin" value="<?php h("userAdmin");?>"/><?php hm("userAdmin"); ?>
	<?php if ($db === 'admin'):?>
	<input type="checkbox" name="clusterAdmin" value="<?php h("clusterAdmin");?>"/><?php hm("clusterAdmin"); ?>
	<br/>
	<input type="checkbox" name="readAnyDatabase" value="<?php h("readAnyDatabase");?>"/><?php hm("readAnyDatabase"); ?>
	<input type="checkbox" name="readWriteAnyDatabase" value="<?php h("readWriteAnyDatabase");?>"/><?php hm("readWriteAnyDatabase"); ?>
	<input type="checkbox" name="userAdminAnyDatabase" value="<?php h("userAdminAnyDatabase");?>"/><?php hm("userAdminAnyDatabase"); ?>
	<input type="checkbox" name="dbAdminAnyDatabase" value="<?php h("dbAdminAnyDatabase");?>"/><?php hm("dbAdminAnyDatabase"); ?>
	<?php endif;?>
	<br/>
	<br/>
	<input type="submit" value="<?php hm("addreplace"); ?>"/>
	</form>
</div>
