<?php
/*--------------------------------------------------------------------
 *	管理モード
 *------------------------------------------------------------------*/
require 'pref.php';
//--------------------------------------------------------------------
//	入り口
//--------------------------------------------------------------------
function enter(){
	global $br;

	head('admin');
?>
<h1>管理モード</h1>
<p>管理用パスワード</p>
<form method="post">
<input type="password" name="adminpass" size="16" maxlength="16">
<button type="submit" name="Command" value="MAIN">決定</button>
</form>
<p><a href="<?php echo HOME?>">HOME</a></p>
<?php
	foot();
}
//--------------------------------------------------------------------
//	メニュー
//--------------------------------------------------------------------
function menu(){
	global $br;

	head('admin');
?>
<h1>管理モード</h1>
<form method="post">
<ul>
<li><label><input type="radio" name="Command" value="USERLIST">ユーザーデータ一覧</label>
<?php

	if(mb_ereg('停止',$br['stopflg'])){
		echo '<li><label><input type="radio" name="Command" value="START">再開</label>';
	}else{
		echo '<li><label><input type="radio" name="Command" value="STOP">緊急停止</label>';
	}

?>
<li><label><input type="radio" name="Command" value="ADMINMESSAGE">メッセージ管理</label>
<li><label><input type="radio" name="Command" value="DATARESET">データ初期化(すぐに開始する)</label>
<li><label><input type="radio" name="Command" value="RESETCONFIG">データ初期化(開始時間を設定する)</label>
</ul>
<p><button type="submit" name="adminpass" value="<?php echo $_POST['adminpass']?>">決定</button></p>
</form>
<p><a href="<?php echo HOME?>">HOME</a></p>
<?php
	foot();
}
//--------------------------------------------------------------------
//	ユーザー一覧
//--------------------------------------------------------------------
function userlist(){
	global $br;

	head('admin');
?>
<h1>ユーザー一覧</h1>
<form method="post">
削除メッセージ：<input type="text" name="COMMENT" value="" size="64">
<table border="1">
<thead>
<tr>
	<th>殺害
	<th>修正
	<th>名前
	<th>ID
	<th>PASS
	<th>状態
	<th>基本方針
	<th>場所
</tr>
</thead>
<tbody>
<?php

	$a = glob(U_DATADIR."*".U_DATAFILE);
	$filelist = array();
	if($a){
		foreach($a as $filename){
			list($no) = explode(",",file_get_contents($filename),2);
			$filelist[$no] = $filename;
		}
		ksort($filelist);
		foreach($filelist as $filename){
			$mem = getuserdata($filename);
			if($mem['hit'] <= 0){
				$sts = "死亡";
				echo '<tr class="dead">';
			}else{
				echo '<tr>';
			}

			echo '<td><input type="checkbox" name="DEL[]" value="'.$mem['number'].'">';
			echo '<td><input type="radio" name="Command" value="DISP_'.$mem['number'].'">';
			echo '<td>'.$mem['f_name'].' '.$mem['l_name'];
			echo '<td>'.$mem['id'];
			echo '<td>'.$mem['pass'];
			echo '<td>'.$mem['sts'];
			echo '<td>'.$mem['tactics'];
			echo '<td>'.$br['PLACE'][$mem['pls']];
			echo "</tr>\n";

		}
	}

?>
</tbody>
</table>
<p>
<label><input type="radio" name="Command" value="MAIN" checked>：戻る</label>
<label><input type="radio" name="Command" value="USERDEL1">：首輪を爆破</label>
<label><input type="radio" name="Command" value="USERDEL2">：データ削除</label>
</p>
<p>
<button type="submit" name="adminpass" value="<?php echo $_POST['adminpass']?>">　決定　</button>
<button type="reset">　リセット　</button>
</p>
</form>
<?php

	foot();
}
//--------------------------------------------------------------------
//	個別データ閲覧
//--------------------------------------------------------------------
function datadisp(){
	global $br;

	$file = search_userfile(str_replace('DISP_','',$_POST['Command']),'number');
	$user = getuserdata($file);

	head('admin');

	print <<<HTML
<h1>{$user['f_name']} {$user['l_name']}</h1>
<p>キャラ番号:{$user['number']} - ID:{$user['id']} - PASS:{$user['pass']}</p>
<form method="post">
<table border="0">
<tbody>
<tr><th>姓/名<td><input size="20" type="text" name="f_name" value="{$user['f_name']}"> / <input size="20" type="text" name="l_name" value="{$user['l_name']}"></tr>
<tr><th>性別/クラス/番号<td><input size="10" type="text" name="sex" value="{$user['sex']}"> / <input size="10" type="text" name="cl" value="{$user['cl']}"> / <input type="number" name="no" min="1" max="999" value="{$user['no']}"></tr>
<tr><th>クラブ<td><input size="40" type="text" name="club" value="{$user['club']}"></tr>
<tr><th>アイコン<td><select name="icon">
<option value="NOCHANGE" selected>変更しない</option>
HTML;

		foreach($br['M_ICON_NAME'] as $i => $name){
			echo '<option value="M_'.$i.'">'.$name;
		}
		foreach($br['F_ICON_NAME'] as $i => $name){
			echo '<option value="F_'.$i.'">'.$name;
		}
		if(USE_NPC){
			foreach($br['N_ICON_NAME'] as $i => $name){
				echo '<option value="N_'.$i.'">'.$name;
			}
		}
		if(USE_SP_ICON){
			foreach($br['S_ICON_NAME'] as $i => $name){
				echo '<option value="S_'.$i.'">'.$name;
			}
		}
		echo "</select></td></tr>\n";

		if(G_MODE){
			echo '<tr><th>グループ名/パス<td><input size="30" type="text" name="g_name" value="'.$user['g_name'].'"> / <input size="30" type="text" name="g_pass" value="'.$user['g_pass'].'">';
		}

	echo '<tr><th>場所(番号)<td><select name="pls">';

	for($i=0;$i<$br['arcnt'];$i++){
		if($i == $user['pls']){
			echo '<option value="'.$i.'" selected>'.$br['PLACE'][$i];
		}else{
			echo '<option value="'.$i.'">'.$br['PLACE'][$i];
		}
	}

	$maxsta = MAXSTA;//ヒアドキュメント内に定数入れられないから苦肉の策
	print <<<HTML
</select></tr>
<tr><th>レベル/経験値<td><input type="number" name="level" min="1" max="999" value="{$user['level']}"> / <input type="number" name="exp" min="0" max="9999" value="{$user['exp']}"></tr>
<tr><th>体力/最大体力<td><input type="number" name="hit" min="0" max="99999" value="{$user['hit']}"> / <input type="number" name="mhit" min="0" max="99999" value="{$user['mhit']}"></tr>
<tr><th>スタミナ<td><input type="number" name="sta" min="1" max="{$maxsta}" value="{$user['sta']}"> / {$maxsta}</tr>
<tr><th>攻撃力/防御力<td><input type="number" name="att" min="1" max="999" value="{$user['att']}"> / <input type="number" name="def" min="1" max="999" value="{$user['def']}"></tr>
<tr><th>殺害数<td><input type="number" name="kill" min="0" max="999" value="{$user['kill']}"></tr>
<tr><th>基本方針/応戦方針<td><input size="20" type="text" name="tactics" value="{$user['tactics']}"> / <input size="20" type="text" name="ousen" value="{$user['ousen']}"></tr>
HTML;

	$eq = array("武器","体防具","頭防具","腕防具","足防具","装飾品");
	for($i=0;$i<6;$i++){
		echo '<tr><th>'.$eq[$i].'<td><input type="text" size="30" name="equip['.$i.']" value="'.$user['equip'][$i].'"> / <input type="number" name="eqeff['.$i.']" min="0" max="999" value="'.$user['eqeff'][$i].'"> / <input type="text" size="5" name="eqtai['.$i.']" value="'.$user['eqtai'][$i].'">';
	}
	for($i=0;$i<ITEMMAX;$i++){
		echo '<tr><th>所持品'.($i+1).'<td><input type="text" size="30" name="item['.$i.']" value="'.$user['item'][$i].'"> / <input type="number" name="eff['.$i.']" min="0" max="999" value="'.$user['eff'][$i].'"> / <input type="text" size="5" name="itai['.$i.']" value="'.$user['itai'][$i].'">';
	}

	print <<<HTML
<tr><th>熟練度<td>
射:<input type="number" name="wa" min="0" max="9999" value="{$user['wa']}"> / 銃:<input type="number" name="wg" min="0" max="9999" value="{$user['wg']}"> / 投:<input type="number" name="wc" min="0" max="9999" value="{$user['wc']}"> / 爆:<input type="number" name="wd" min="0" max="9999" value="{$user['wd']}"><br>
斬:<input type="number" name="wn" min="0" max="9999" value="{$user['wn']}"> / 刺:<input type="number" name="ws" min="0" max="9999" value="{$user['ws']}"> / 棍:<input type="number" name="wb" min="0" max="9999" value="{$user['wb']}"> / 殴:<input type="number" name="wp" min="0" max="9999" value="{$user['wp']}">
</tr>
<tr><th>メッセージをログに残す<td><input size="60" type="text" name="mes" value=""></tr>
</tbody>
</table>
<p>
<input type="hidden" name="target" value="{$user['number']}">
<label><input type="radio" name="Command" value="DATACHANGE">：能力修正</label>
<label><input type="radio" name="Command" value="USERLIST" checked>：戻る</label>
<button type="submit" name="adminpass" value="{$_POST['adminpass']}">決定</button>
</p>
</form>
HTML;
foot();
}
//--------------------------------------------------------------------
//	メッセージ管理
//--------------------------------------------------------------------
function admin_message(){

	head('admin');
?>
<h1>メッセージ管理</h1>
<p>ニュースにメッセージを残す</p>
<form method="post">
<input type="text" name="message" value="" size="64" maxlength="64">
<p>
<label><input type="radio" name="Command" value="SENDMESSAGE">：メッセージ送信</label>
<label><input type="radio" name="Command" value="MAIN" checked>：戻る</label>
<button type="submit" name="adminpass" value="<?php echo $_POST['adminpass']?>">決定</button>
<button type="reset">　リセット　</button>
</p>
</form>
<?php
	$messages = "";

	if(file_exists(MES_ADMINFILE)){
		foreach(array_reverse(file(MES_ADMINFILE),true) as $key => $mesdata){
			list($logtime,$from_name,$from_icon,$message) = explode(",",$mesdata);
			$time = date('Y年m月d日 H時i分',$logtime);

			$messages .= '<tr><td><input type="checkbox" name="DEL[]" value="'.$key.'">';
			$messages .= '<td><img src="'.IMGDIR.$from_icon.'" width="70" height="70" alt="">';
			$messages .= '<td>'.$time.' '.$from_name.'より<br>『'.$message.'』</tr>';
		}
	}

	if($messages){

?>
<form method="post">
<input type="hidden" name="adminpass" value="<?php echo $_POST['adminpass']?>">
<table border="0">
<caption>管理人へのメッセージ</caption>
<tbody>
<?php echo $messages?>
</tbody>
</table>
<button type="submit" name="Command" value="MESDEL">チェックしたメッセージを削除</button>
</form>
<?php

	}else{
		echo "<p>管理人へのメッセージはありません</p>\n";
	}
	foot();
}
//--------------------------------------------------------------------
//	管理人へのメッセージ削除
//--------------------------------------------------------------------
function mesdel(){

	$logs = array_diff_key(file(MES_ADMINFILE),array_flip($_POST['DEL']));
	file_put_contents(MES_ADMINFILE,$logs,LOCK_EX);

}
//--------------------------------------------------------------------
//	開始時間設定
//--------------------------------------------------------------------
function config_resettime(){
	global $br;
	head('admin');

	$date = getdate($_SERVER['REQUEST_TIME'] + 86400);

?>
<h1>管理モード</h1>
<form method="post">
<ul>
<li><label><input type="radio" name="TIME" value="back" checked>戻る(初期化しない)</label>
<li><label><input type="radio" name="TIME" value="now">すぐに開始</label>
<li><label><input type="radio" name="TIME" value="next">翌日0時</label>
<li><label><input type="radio" name="TIME" value="config">次の時間に開始</label>
<input type="number" name="year" min="<?php echo $br['year']?>" max="<?php echo ($br['year'] + 1)?>" value="<?php echo $date['year']?>">年
<input type="number" name="month" min="1" max="12" value="<?php echo $date['mon']?>">月
<input type="number" name="day" min="1" max="31" value="<?php echo $date['mday']?>">日
<input type="number" name="hour" min="0" max="23" value="0">時開始
</ul>
<input type="hidden" name="Command" value="DATARESET">
<p><label><input type="checkbox" name="REGIST" value="1">開始時間まで登録を許可する(すぐに開始する場合は関係無し)</label></p>
<p>※過去の時間を指定した場合はすぐに開始となります。</p>
<button type="submit" name="adminpass" value="<?php echo $_POST['adminpass']?>">決定</button>
</form>
<p><a href="<?php echo HOME?>">HOME</a></p>
<?php
	foot();
}
//--------------------------------------------------------------------
//	個別データ修正
//--------------------------------------------------------------------
function datachange(){
	global $br;

	$file = search_userfile($_POST['target'],'number');
	$user = getuserdata($file);

	$user['f_name'] = $_POST['f_name'];
	$user['l_name'] = $_POST['l_name'];
	$user['sex'] = $_POST['sex'];
	$user['cl'] = $_POST['cl'];
	$user['no'] = (int)$_POST['no'];
	$user['club'] = $_POST['club'];
	if(preg_match("/^[MFNS]_\d+/",$_POST['icon'])){
		list($chk,$iconno) = explode("_",$_POST['icon']);
		switch($chk){
			case "M":$user['icon'] = $br['M_ICON_FILE'][$iconno];break;
			case "F":$user['icon'] = $br['F_ICON_FILE'][$iconno];break;
			case "N":$user['icon'] = $br['N_ICON_FILE'][$iconno];break;
			case "S":$user['icon'] = $br['S_ICON_FILE'][$iconno];break;
		}
	}
	if(G_MODE){
		$user['g_name'] = $_POST['g_name'];
		$user['g_pass'] = $_POST['g_pass'];
	}
	$user['pls'] = (int)$_POST['pls'];
	$user['level'] = (int)$_POST['level'];
	$user['exp'] = (int)$_POST['exp'];
	$user['hit'] = (int)$_POST['hit'];
	$user['mhit'] = (int)$_POST['mhit'];
	$user['sta'] = (int)$_POST['sta'];
	$user['att'] = (int)$_POST['att'];
	$user['def'] = (int)$_POST['def'];
	$user['kill'] = (int)$_POST['kill'];
	$user['tactics'] = $_POST['tactics'];
	$user['ousen'] = $_POST['ousen'];
	$user['equip'] = $_POST['equip'];
	$user['eqeff'] = $_POST['eqeff'];
	$user['eqtai'] = $_POST['eqtai'];
	$user['item'] = $_POST['item'];
	$user['eff'] = $_POST['eff'];
	$user['itai'] = $_POST['itai'];
	$user['wa'] = (int)$_POST['wa'];
	$user['wg'] = (int)$_POST['wg'];
	$user['wc'] = (int)$_POST['wc'];
	$user['wd'] = (int)$_POST['wd'];
	$user['wn'] = (int)$_POST['wn'];
	$user['ws'] = (int)$_POST['ws'];
	$user['wb'] = (int)$_POST['wb'];
	$user['wp'] = (int)$_POST['wp'];
	if($_POST['mes'] != ""){
		$user['log'] .= '<span class="msg">管理人『'.$_POST['mes'].'』</span><br>';
	}

	save($user,true);
	message("データの修正を行いました。");
}
//--------------------------------------------------------------------
//	データ削除
//--------------------------------------------------------------------
function userdel(){

foreach($_POST['DEL'] as $wk){
	$file = search_userfile($wk,'number');
	$mem = getuserdata($file);
	if($_POST['Command'] == "USERDEL1"){//処刑
		$mem['hit'] = 0;
		$mem['death'] = "政府による処刑";
		$mem['sts'] = "死亡";
		$mem['kmes'] = $_POST['COMMENT'];
		save($mem,true);
		logsave("DEATH7",$mem);
	}elseif($_POST['Command'] == "USERDEL2"){//削除
		unlink($file);
		if(file_exists(U_BACKDIR.$mem['id'].U_BACKFILE)){
			unlink(U_BACKDIR.$mem['id'].U_BACKFILE);
		}
	}
}

}
//--------------------------------------------------------------------
//	処理後メッセージ
//--------------------------------------------------------------------
function message($mes){

	head('admin');
?>
<h1>管理モード</h1>
<p><?php echo $mes?></p>
<form method="post">
<input type="hidden" name="Command" value="MAIN">
<p><button type="submit" name="adminpass" value="<?php echo $_POST['adminpass']?>">管理モードに戻る</button></p>
</form>
<p><a href="<?php echo HOME?>">HOME</a></p>
<?php
	foot();
}
//--------------------------------------------------------------------
//	メイン処理
//--------------------------------------------------------------------
string_operation($_POST,true);

if(isset($_POST['adminpass'])){
	if($_POST['adminpass'] == ADMINPASS){
		if($_POST['Command'] == "USERLIST"){
			//ユーザーデータ一覧
			userlist();
		}elseif(preg_match("/^DISP_/",$_POST['Command'])){
			//個別データ閲覧
			datadisp();
		}elseif(preg_match("/^USERDEL/",$_POST['Command']) and is_array($_POST['DEL'])){
			//削除
			userdel();
			userlist();
		}elseif($_POST['Command'] == "DATACHANGE"){
			//データ修正
			datachange();
		}elseif($_POST['Command'] == "ADMINMESSAGE"){
			//メッセージ管理
			admin_message();
		}elseif($_POST['Command'] == "SENDMESSAGE"){
			//管理者からのメッセージをニュースに残す
			logsave('ADMINMESSAGE');
			message("管理者からのメッセージを発信しました。");
		}elseif($_POST['Command'] == "MESDEL" and is_array($_POST['DEL'])){
			//管理者へのメッセージを削除
			mesdel();
			admin_message();
		}elseif($_POST['Command'] == "STOP"){
			//停止
			file_put_contents(FLAGFILE,"{$br['hackflg']},{$br['endflg']},停止,",LOCK_EX);
			message("プログラムを停止させました。");
		}elseif($_POST['Command'] == "START"){
			//再開
			file_put_contents(FLAGFILE,"{$br['hackflg']},{$br['endflg']},,",LOCK_EX);
			message("プログラムを再開させました。");
		}elseif($_POST['Command'] == "DATARESET"){
			//初期化
			if(isset($_POST['TIME']) and $_POST['TIME'] == 'back'){
				menu();
			}else{
				require_once LIBDIR.'reset.php';
				$date = datareset();
				$msg = ($date != '')? $date."よりプログラムを開始します。":"";
				message("初期化しました。".$msg);
			}
		}elseif($_POST['Command'] == "RESETCONFIG"){
			config_resettime();
		}else{
			//その他の場合はメニューへ
			menu();
		}
	}else{
		//パスワード照合失敗
		enter();
	}
}else{
	enter();
}

