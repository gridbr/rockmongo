<div class="operation">
	<?php render_server_menu("replication"); ?>
</div>

<?php if(!empty($me)): ?>
<div class="gap"></div>

<table bgcolor="#cccccc" cellpadding="2" cellspacing="1" width="600">
	<tr>
		<th colspan="2"><?php hm("me"); ?> (<a href="<?php h(url("collection.index", array( "db" => "local", "collection" => "me" ))); ?>">local.me</a>)</th>
	</tr>
	<?php foreach ($me as $param => $value):?>
	<tr bgcolor="#fffeee">
		<td width="120" valign="top"><?php h($param);?></td>
		<td><?php h($value);?></td>
	</tr>
	<?php endforeach; ?>
</table>
<?php endif; ?>

<?php if(!empty($status)): ?>
<div class="gap"></div>

<table bgcolor="#cccccc" cellpadding="2" cellspacing="1" width="600">
	<tr>
		<th colspan="2"><?php hm("ReplSet"); ?> (rs.status())</th>
	</tr>
	<tr bgcolor="#fffeee">
		<td width="120" valign="top">set</td>
		<td><?php h($status['set']);?></td>
	</tr>
	<tr bgcolor="#fffeee">
		<td width="120" valign="top">myState</td>
		<td><?php h($status['myState']);?></td>
	</tr>
	<tr bgcolor="#fffeee">
		<td width="120" valign="top">date</td>
		<td><?php h($status['date']);?></td>
	</tr>
	<?php foreach ($status['members'] as $member):?>
	<tr bgcolor="#cfffff">
		<td colspan="2">member:<?php h($member["_id"]); ?></td>
	</tr>
		<?php foreach ($member as $param => $value):?>
		<tr bgcolor="#fffeee">
			<td width="120" valign="top"><?php h($param);?></td>
			<td><?php h($value);?></td>
		</tr>
		<?php endforeach; ?>
	<?php endforeach; ?>
</table>
<?php endif; ?>
