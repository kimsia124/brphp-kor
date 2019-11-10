<?php
/*--------------------------------------------------------------------
 *	メッセンジャー
 *------------------------------------------------------------------*/
require 'pref.php';
//--------------------------------------------------------------------
//	入り口
//--------------------------------------------------------------------
function enter(){

	$c_id = (isset($_COOKIE['id']))? $_COOKIE['id']:"";
	$c_pass = (isset($_COOKIE['pass']))? $_COOKIE['pass']:"";

	head('messenger');

?>
<h1>BRメッセンジャー</h1>
<form method="post">
　ID　：<input size="16" type="text" name="id" maxlength="12" value="<?php echo $c_id?>"><br><br>
PASS：<input size="16" type="password" name="pass" maxlength="12" value="<?php echo $c_pass?>"><br><br>
<input type="hidden" name="mode" value="messenger">
<input type="submit" value="決定">
</form>
<p><a href="<?php echo HOME?>">HOME</a></p>
<?php
	foot();
}
//--------------------------------------------------------------------
//	メッセンジャー画面
//--------------------------------------------------------------------
function messenger($user) {

	$warning = "";
	if(isset($_POST['MESSAGE'],$_POST['Command']) and $_POST['Command'] == "SEND"){
		if($_POST['MESSAGE'] == ""){
			$warning = "メッセージが入力されていません。";
		}elseif($_POST['TO_ID'] == "MAIN"){
			$warning = "伝言先が選択されていません。";
		}elseif($_POST['TO_ID'] == "GROUP" and $user['g_name'] == ""){
			$warning = "無所属の人は同じグループの人には送信できません。";
		}elseif(substr_count($_POST['MESSAGE'],"<br>") >= MAXLINE){
			$warning = "行数が多すぎます。".MAXLINE."行までなら投稿可能です。";
		}elseif(mb_strlen($_POST['MESSAGE']) > MAXSTRLEN){
			$warning = "投稿文字数オーバーです。".MAXSTRLEN."文字までなら投稿可能です。";
		}else{
			messave($user);
		}
	}

	head('messenger');

?>
<h1><?php echo $user['f_name'].' '.$user['l_name']?>宛メッセージ</h1>
<form method="post">
<?php

	$chk = false;
	if(count(file(MES_FILE)) > 0){
		$cnt = 0;
		$messages = "";
		foreach(file(MES_FILE) as $mesdata){
			list($logtime,$from_id,$from_name,$from_g_name,$from_icon,$to_id,$to_name,$message) = explode(",",$mesdata);
			list($sec,$min,$hour,$mday,$month,$year,$wday) = localtime($logtime);
			$year += 1900; $month++;
			$time = "{$month}月{$mday}日 {$hour}時{$min}分";
			if($from_id == $user['id']){
				//送信メッセージ
				$target = '';
				if($to_id == 'ALL'){
					$target = '参加者全員';
				}elseif($to_id == 'GROUP'){
					$target = $to_name.'のメンバー';
				}elseif($to_id == 'ADMIN'){
					$target = '管理人';
				}else{
					$target = $to_name;
				}
				$messages .= '<tr><td><img src="'.IMGDIR.$from_icon.'" width="70" height="70" alt="">';
				$messages .= '<td class="from">'.$time.'（'.$target.'へ送信）<br>『'.$message.'』</tr>';
				$chk = true;
				$cnt++;
			}elseif($to_id == $user['id']){
				//自分宛メッセージ
				$messages .= '<tr><td><img src="'.IMGDIR.$from_icon.'" width="70" height="70" alt="">';
				$messages .= '<td class="me">'.$time.'（'.$from_name.'より自分宛）<br>『'.$message.'』</tr>';
				$chk = true;
				$cnt++;
			}elseif($to_id == "ALL"){
				//全員宛メッセージ
				$messages .= '<tr><td><img src="'.IMGDIR.$from_icon.'" width="70" height="70" alt="">';
				$messages .= '<td class="all">'.$time.'（'.$from_name.'より全員宛）<br>『'.$message.'』</tr>';
				$chk = true;
				$cnt++;
			}elseif($to_id == "GROUP" and $from_g_name == $user['g_name']){
				//グループ宛メッセージ
				$messages .= '<tr><td><img src="'.IMGDIR.$from_icon.'" width="70" height="70" alt="">';
				$messages .= '<td class="all">'.$time.'（'.$from_name.'より'.$from_g_name.'全員宛）<br>『'.$message.'』</tr>';
				$chk = true;
				$cnt++;
			}
			if($cnt >= MESMAX){
				break;
			}
		}
	}
	if($chk){
		echo '<table border="0"><tbody>'.$messages.'</tbody></table>';
	}else{
		echo '<p>メッセージはありません</p>';
	}
	if($warning != ""){echo '<p>'.$warning.'</p>';}

?>
<p>伝言先　
<select name="TO_ID">
<option value="MAIN">送信先選択
<option value="ALL">ALL
<?php

	if(G_MODE and $user['g_name'] != ""){
		echo '<option value="GROUP">'.$user['g_name'].'所属の人全員';
	}

	echo '<option value="ADMIN">管理人';

	$a = glob(U_DATADIR."*".U_DATAFILE);
	$filelist = array();
	foreach($a as $filename){
		list($no) = explode(",",file_get_contents($filename),2);
		$filelist[$no] = $filename;
	}
	ksort($filelist);
	foreach($filelist as $filename){
		$w_user = getuserdata($filename);
		if(preg_match("/NPC/",$w_user['type']) or $w_user['id'] == $user['id']){
			continue;
		}
		echo '<option value="'.$w_user['number'].'">'.$w_user['f_name'].' '.$w_user['l_name'];
	}

?>
</select></p>
<p>伝言メッセージ
<textarea name="MESSAGE" cols="40" rows="4"></textarea></p>
<?php

	if(MEMDISP){//在籍者表示
		$member = mes_memcount($user);
		echo '<p>在籍者：'.$member.'</p>';
	}

?>
<input type="hidden" name="pass" value="<?php echo $_POST['pass']?>">
<input type="hidden" name="id" value="<?php echo $_POST['id']?>">
<button type="submit" name="Command" value="SEND">伝言発信</button>
<button type="submit" name="Command" value="RELOAD">ページ更新</button>
<button type="reset">リセット</button>
</form>
<p><a href="<?php echo HOME?>">HOME</a></p>
<?php
	foot();
}
//--------------------------------------------------------------------
//	発信メッセージ登録
//--------------------------------------------------------------------
function messave($user){

	$to_id = $to_name = '';

	if($_POST['TO_ID'] == "ALL"){
		$to_id = "ALL";
	}elseif($_POST['TO_ID'] == "GROUP"){
		$to_id = "GROUP";
		if($user['g_name']){
			$to_name = $user['g_name'];
		}else{
			return;
		}
	}elseif($_POST['TO_ID'] == "ADMIN"){
		$to_id = "ADMIN";
		//管理人にメッセージを送る場合、専用のログファイルにも記録し、初期化で消えないようにする
		$adminlog = (file_exists(MES_ADMINFILE))? file(MES_ADMINFILE):array();
		$adminlog[] = NOW.",{$user['f_name']} {$user['l_name']},{$user['icon']},{$_POST['MESSAGE']},\n";
		file_put_contents(MES_ADMINFILE,$adminlog,LOCK_EX);
	}else{
		$file = search_userfile($_POST['TO_ID'],'number');
		$mem = getuserdata($file);
		$to_id = $mem['id'];
		$to_name = "{$mem['f_name']} {$mem['l_name']}";
	}

	$logs = file(MES_FILE);
	array_unshift($logs,NOW.",{$user['id']},{$user['f_name']} {$user['l_name']},{$user['g_name']},{$user['icon']},{$to_id},{$to_name},{$_POST['MESSAGE']},\n");

	if(count($logs) > LISTMAX){array_pop($logs);}

	file_put_contents(MES_FILE,$logs,LOCK_EX);

}
//--------------------------------------------------------------------
//	在籍者表示処理
//--------------------------------------------------------------------
function mes_memcount($user){

	$memberlist = file(MES_MEMFILE);
	$mem = "";
	$new_memlist = array();
	$chk = false;

	foreach($memberlist as $member){
		list($get_time,$mem_id,$mem_name) = explode(",",$member);
		if($mem_id == $user['id']){
			$new_memlist[] = NOW.",{$mem_id},{$mem_name},\n";
			$mem .= " {$mem_name}";
			$chk = true;
		}elseif((NOW - MEMTIME) <= $get_time){
			$new_memlist[] = "{$get_time},{$mem_id},{$mem_name},\n";
			$mem .= " {$mem_name}";
		}
	}

	if(!$chk){
		$new_memlist[] = NOW.",{$user['id']},{$user['f_name']} {$user['l_name']},\n";
		$mem .= " {$user['f_name']} {$user['l_name']}";
	}

	file_put_contents(MES_MEMFILE,$new_memlist,LOCK_EX);

	return $mem;

}
//--------------------------------------------------------------------
//	ＭＡＩＮ
//--------------------------------------------------------------------
string_operation($_POST);

if(isset($_POST['id'],$_POST['pass'])){
	$file = search_userfile($_POST['id'],'id');
	if(!$file){
		error("IDが見つかりません。");
	}

	$user = getuserdata($file);

	if($_POST['pass'] !== $user['pass']){
		error("パスワードが一致しません");
	}

	messenger($user);

}else{
	enter();
}
