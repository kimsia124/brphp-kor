<?php
//--------------------------------------------------------------------
//	回復行動
//--------------------------------------------------------------------
function heal(&$user){

	switch(str_replace('HEAL_','',$_POST['Command2'])){
		case 0:
			$user['sts'] = "睡眠";
			$_POST['Command'] = "SLEEP";
			break;
		case 1:
			$user['sts'] = "治療";
			$_POST['Command'] = "HEAL";
			break;
		case 2:
			$user['sts'] = "休憩";
			$_POST['Command'] = "REST";
			break;
		default:
			$_POST['Command'] = "MAIN";
			return;
	}

	$user['endtime'] = NOW;
	save($user);
}
//--------------------------------------------------------------------
//	応急処置
//--------------------------------------------------------------------
function oukyu(&$user){

	switch(str_replace('OUK_','',$_POST['Command'])){
		case 0://頭
			$user['inf'] = str_replace('頭','',$user['inf']);
			break;
		case 1://腕
			$user['inf'] = str_replace('腕','',$user['inf']);
			break;
		case 2://腹
			$user['inf'] = str_replace('腹','',$user['inf']);
			break;
		case 3://足
			$user['inf'] = str_replace('足','',$user['inf']);
			break;
		default:
			error("不正なアクセスです。");
	}

	$user['log'] .= "応急処置をした。<br>";
	$user['sta'] -= OKYU_STA;
	if($user['sta'] <= 0){//スタミナ切れ？
		drain($user);
	}

	save($user);
	$_POST['Command'] = "MAIN";
}
//--------------------------------------------------------------------
//	基本方針
//--------------------------------------------------------------------
function tactics(&$user){

	switch(str_replace('TAC_','',$_POST['Command'])){
		case 1:
			$user['tactics'] = "攻撃重視";
			break;
		case 2:
			$user['tactics'] = "防御重視";
			break;
		case 3:
			$user['tactics'] = "先制行動";
			break;
		case 4:
			$user['tactics'] = "探索行動";
			break;
		case 5:
			$user['tactics'] = "連闘行動";
			break;
		default:
			$user['tactics'] = "通常";
	}

	$user['log'] .= "基本方針を決定しました。<br>";

	save($user);
	$_POST['Command'] = "MAIN";
}
//--------------------------------------------------------------------
//	応戦方針
//--------------------------------------------------------------------
function ousen(&$user){

	switch(str_replace('OUS_','',$_POST['Command'])){
		case 1:
			$user['ousen'] = "攻撃重視";
			break;
		case 2:
			$user['ousen'] = "防御重視";
			break;
		case 3:
			$user['ousen'] = "隠密行動";
			break;
		case 4:
			$user['ousen'] = "回復行動";
			break;
		default:
			$user['ousen'] = "通常";
	}

	$user['log'] .= "応戦方針を決定しました。<br>";

	save($user);
	$_POST['Command'] = "MAIN";
}
//--------------------------------------------------------------------
//	口癖変更
//--------------------------------------------------------------------
function meschange(&$user){

	$errmsg = array();
	$flg =  false;

	if(mb_strlen($_POST['kmes']) > 32){
		$user['log'] .= "殺害時メッセージが長すぎます。<br>";
	}else{
		$user['kmes'] = $_POST['kmes'];
		$flg = true;
	}

	if(mb_strlen($_POST['dmes']) > 32){
		$user['log'] .= "遺言が長すぎます。<br>";
	}else{
		$user['dmes'] = $_POST['dmes'];
		$flg = true;
	}

	$chk = true;
	$line = explode('<br>',$_POST['comment']);
	if(count($line) > 4){
		$user['log'] .= "一言コメントは4行までです。";
		$chk = false;
	}
	if(mb_strlen($_POST['comment']) > 80){
		$user['log'] .= "コメントの文字数がオーバーしています。（70文字程度まで）";
		$chk = false;
	}
	if($chk){
		$user['com'] = $_POST['comment'];
		$flg = true;
	}

	if($flg){
		$user['log'] .= "口癖を変更しました。<br>";
		save($user);
	}

	$_POST['Command'] = "MAIN";
}
//--------------------------------------------------------------------
//	グループ
//--------------------------------------------------------------------
function group(&$user){

	if(!G_MODE){
		error("不正なアクセスです。");
	}

	if($user['g_name'] == $_POST['g_name']){
		//グループ名が変更前と同じ時
		$user['log'] .= "グループ移動を諦めました。<br>";
		$_POST['Command'] = "MAIN";return;
	}elseif($_POST['g_name'] == "" and $_POST['g_pass'] == ""){
		//グループ名、グループパスを空欄にした時(グループ脱退)
		$user['log'] .= "グループ".$user['g_name']."を脱退しました。<br>";
		$kind = "GROUP3";
		$gname = $user['g_name'];
		$user['g_name'] = $user['g_pass'] = "";
	}elseif($_POST['g_name'] == "" or $_POST['g_pass'] == ""){
		//グループ名又はグループパスが未記入
		$user['log'] .= "グループ名又はグループパスが未記入です。<br>";
		$_POST['Command'] = "MAIN";return;
	}else{
		$count = 0;//人数チェック
		$a = glob(U_DATADIR."*".U_DATAFILE);
		foreach($a as $filename){
			$w_user = getuserdata($filename);
			if($_POST['g_name'] == $w_user['g_name']){
				if($_POST['g_pass'] == $w_user['g_pass']){
					$count++;
				}else{
					$user['log'] .= "グループパスが一致しません。<br>";
					$_POST['Command'] = "MAIN";return;
				}
			}
		}
		if($count >= G_MAX){
			$user['log'] .= "グループの最大人数(".G_MAX."人)を超えています。<br>";
			$_POST['Command'] = "MAIN";return;
		}

		$gname = $user['g_name'] = $_POST['g_name'];
		$user['g_pass'] = $_POST['g_pass'];
		if($count >= 1){
			//グループ移籍
			$kind = "GROUP2";
			$user['log'] .= "グループ「".$_POST['g_name']."」に加入しました。<br>";
		}else{
			//新グループ結成
			$kind = "GROUP1";
			$user['log'] .= "新グループ「".$_POST['g_name']."」を結成しました。<br>";
		}
	}

	logsave($kind,$user,$gname);
	save($user);
	$_POST['Command'] = "MAIN";
}
//--------------------------------------------------------------------
//	毒見
//--------------------------------------------------------------------
function pcheck(&$user) {

	$wk = str_replace('PCHK_','',$_POST['Command']);

	if(!preg_match("/<>S|<>H/",$user['item'][$wk]) or $user['club'] != "料理研究部"){
		error("不正なアクセスです。");
	}
	list($in,$ik) = explode("<>",$user['item'][$wk]);
	if(preg_match("/<>SH|<>HH/",$user['item'][$wk])){
		$user['log'] .= "ん？ ".$in." は口にしても安全そうだ・・・。<br>";
	}else{
		$user['log'] .= "ん？ ".$in." には 毒物が混入してありそうだ・・・。<br>";
	}

	$user['sta'] -= PCHK_STA;

	save($user);
	$_POST['Command'] = "MAIN";
}
//--------------------------------------------------------------------
//	毒混入
//--------------------------------------------------------------------
function poison(&$user) {

	$chk = false;
	for($i=0;$i<ITEMMAX;$i++){
		if(mb_ereg("毒薬",$user['item'][$i])){$chk = true;break;}
	}

	$wk = str_replace('POI_','',$_POST['Command']);

	if(!preg_match("/<>SH|<>HH|<>SD|<>HD/",$user['item'][$wk]) or !$chk){
		error("不正なアクセスです。");
	}

	if(--$user['itai'][$i] <= 0){
		$user['item'][$i] = "";
		$user['eff'][$i] = $user['itai'][$i] = 0;
	}

	list($in,$ik) = explode("<>",$user['item'][$wk]);
	$user['log'] .= $in."に毒物を混入した。自分で口にしないよう気をつけよう・・・。<br>";

	if($user['club'] == "料理研究部"){
		if(preg_match("/<>H/",$user['item'][$wk])){
			$user['item'][$wk] = "{$in}<>HD2";
		}else{
			$user['item'][$wk] = "{$in}<>SD2";
		}
	}else{
		if(preg_match("/<>H/",$user['item'][$wk])){
			$user['item'][$wk] = "{$in}<>HD";
		}else{
			$user['item'][$wk] = "{$in}<>SD";
		}
	}
	save($user);
	$_POST['Command'] = "MAIN";
}
//--------------------------------------------------------------------
//	毒中和
//--------------------------------------------------------------------
function pdelete(&$user) {

	$chk = false;
	for($i=0;$i<ITEMMAX;$i++){
		if(mb_ereg("中和剤",$user['item'][$i])){$chk = true;break;}
	}

	$wk = str_replace('PDEL_','',$_POST['Command']);

	if(!preg_match("/<>SH|<>HH|<>SD|<>HD/",$user['item'][$wk]) or !$chk){
		error("不正なアクセスです。");
	}

	if(--$user['itai'][$i] <= 0){
		$user['item'][$i] = "";
		$user['eff'][$i] = $user['itai'][$i] = 0;
	}

	list($in,$ik) = explode("<>",$user['item'][$wk]);
	$user['log'] .= $in."の毒を中和した。これで口にしても大丈夫だな・・・。<br>";

	if(preg_match("/<>H/",$user['item'][$wk])){
		$user['item'][$wk] = $in."<>HH";
	}else{
		$user['item'][$wk] = $in."<>SH";
	}

	save($user);
	$_POST['Command'] = "MAIN";
}
//--------------------------------------------------------------------
//	スピーカ使用
//--------------------------------------------------------------------
function speaker(&$user) {

	$chk = false;
	for($i=0;$i<ITEMMAX;$i++){
		if(mb_ereg("携帯スピーカ",$user['item'][$i])){$chk = true;break;}
	}

	if(!$chk){
		error("不正なアクセスです。");
	}

	$user['log'] .= '<span class="msg">『'.$_POST['comment'].'』</span><br>ちゃんと伝わったかな？<br>';

	logsave("SPEAKER",$user,"",$_POST['comment']);
	soundsave("SPEAKER",$user,$_POST['comment']);

	$_POST['Command'] = "MAIN";
}
//--------------------------------------------------------------------
//	ハッキング
//--------------------------------------------------------------------
function hack(&$user) {
	global $br;

	$chk = false;
	for($i=0;$i<ITEMMAX;$i++){
		if($user['item'][$i] == "モバイルPC<>Y" and $user['itai'][$i] >= 1){$chk = true;break;}
	}

	if(!$chk){
		error("不正なアクセスです。");
	}

	$chk2 = 10;//基本成功率(%)

	if(mb_ereg("パソコン",$user['club'])){//パソコン部の基本成功率上昇
		$chk2 += 40;
	}
	$rand = mt_rand(1,100);
	$user['log'] .= $rand;
	$success = false;
	if($rand <= $chk2){//ハッキング成否判定
		logsave("HACK",$user);
		$user['log'] .= "ハッキング成功！全ての禁止エリアが解除された！！<br>";
		file_put_contents(FLAGFILE,"1,{$br['endflg']},{$br['stopflg']},",LOCK_EX);
		$success = true;
	}else{
		$user['log'] .= "ハッキングは失敗した・・・<br>";
	}

	if(mt_rand(1,100) >= 90 and !$success){//失敗した場合一定確率で機材破壊
		$user['item'][$i] = "";
		$user['eff'][$i] = $user['itai'][$i] = 0;
		$user['log'] .= "何てこった！機材が壊れてしまった。<br>";
		if(mt_rand(1,100) >= 90){//更に一定の確率で首輪爆破
			$user['hit'] = 0;
			$user['sts'] = "死亡";
			$user['death'] = "政府による処刑";
			logsave("DEATH8",$user);
			soundsave("DEATH",$user);
			$user['log'] .= '<br>・・・何だ？・・・首輪から警告音が・・・！？<br><br>・・・！！・・・<br><br>';
			$user['log'] .= '<span class="dead">'.$user['f_name'].' '.$user['l_name'].'（'.$user['cl'].' '.$user['sex'].$user['no'].'番）は死亡した。</span><br>';
		}
	}else{
		if(--$user['itai'][$i] <= 0){
			$user['log'] .= "モバイルPC のバッテリの電力を使い果たした。<br>";
		}
	}

	save($user);
	$_POST['Command'] = "MAIN";
}
