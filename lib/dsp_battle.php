<?php
//--------------------------------------------------------------------
//	戦闘画面
//--------------------------------------------------------------------
function battle($user,$w_user) {
	global $br;

	head('battle');
?>
<h1>戦闘発生</h1>
<?php echo LINKS?>
<div id="ATTACK">
<dl id="DATA">
	<dt><?php echo $br['PLACE'][$user['pls']]?>(<?php echo $br['AREA'][$user['pls']]?>)</dt>
	<dd><table border="0">
		<tbody>
		<tr>
			<td><img src="<?php echo IMGDIR.$user['icon']?>" width="70" height="70" alt="">
			<td>
			<td><img src="<?php echo IMGDIR.$w_user['icon']?>" width="70" height="70" alt="">
		</tr>
		<tr>
			<td><?php echo $user['cl'].' '.$user['sex'].$user['no']?>番
			<td>ＶＳ
			<td><?php echo $w_user['cl'].' '.$w_user['sex'].$w_user['no']?>番
		</tr>
		<tr>
			<td><?php echo $user['f_name'].' '.$user['l_name']?>
			<td>
			<td><?php echo $w_user['f_name'].' '.$w_user['l_name']?>
		</tr>
		</tbody>
	</table></dd>
</dl>
<dl id="COMMAND">
	<dt>コマンド</dt>
	<dd><form method="post" name="br">
		<input type="hidden" name="id" value="<?php echo $_POST['id']?>">
		<input type="hidden" name="pass" value="<?php echo $_POST['pass']?>">
<?php

	require LIBDIR.'command.php';
	command($user);

?>
	</form></dd>
</dl>
<dl id="LOG">
	<dt>ログ</dt>
	<dd><?php echo $user['log']?></dd>
</dl>
</div>

<?php
	foot();
}
