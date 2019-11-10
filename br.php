<?php
/*--------------------------------------------------------------------
 *	BRメイン
 *------------------------------------------------------------------*/
require 'pref.php';
require LIBDIR.'lib2.php';
if($br['stopflg'] == "開始前登録"){
	error("プログラム開始まで登録したキャラクタを操作することは出来ません。");
}
$host = hostcheck();
pgstop();
string_operation($_POST);

list($user,$mem) = idcheck($host);

//--------------------------------------------------------------------
//	コマンド分岐
//--------------------------------------------------------------------
if($_POST['Command'] == "MOVE" and ctype_digit($_POST['Command1'])){
	//移動
	require LIBDIR.'move.php';
	move($user);
}elseif($_POST['Command'] == "SEARCH"){
	//探索
	require LIBDIR.'move.php';
	search($user);
}elseif($_POST['Command'] == "HEALCMD" and preg_match("/^HEAL_/",$_POST['Command2'])){
	//回復
	require LIBDIR.'action.php';
	heal($user);
}elseif(preg_match("/^OUK_/",$_POST['Command'])){
	//応急処置
	require LIBDIR.'action.php';
	oukyu($user);
}elseif($_POST['Command'] == "ITEMUSE" and !empty($_POST['USE'])){
	//アイテム使用
	require LIBDIR.'item1.php';
	itemuse($user);
}elseif($_POST['Command'] == "ITEMDEL"){
	//アイテム投棄
	if(array_key_exists('DEL',$_POST)){
		require LIBDIR.'item1.php';
		itemdel($user);
	}else{//何も捨てない時
		$_POST['Command'] = "MAIN";
	}
}elseif($_POST['Command'] == 'ITEMSEIRI'){
	//アイテム整理
	if(!in_array("MAIN",$_POST['SEIRI'])){
		require LIBDIR.'item2.php';
		itemsei($user);
	}else{//アイテムを一つも選択しなかった時
		$_POST['Command'] = "MAIN";
	}
}elseif(isset($_POST['GOUSEI']) and is_array($_POST['GOUSEI'])){
	//アイテム合成
	if(preg_grep("/^[0-9]+$/",$_POST['GOUSEI'])){
		require LIBDIR.'item3.php';
		gousei($user);
	}else{//アイテムを一つも選択しなかった時
		$_POST['Command'] = "MAIN";
	}
}elseif(preg_match("/^SEND_/",$_POST['Command']) and $_POST['TARGET'] != "MAIN"){
	//アイテム譲渡
	require LIBDIR.'item2.php';
	senditem($user);
}elseif($_POST['Command'] == "ITEM" and $_POST['Command3'] == "DELSHOT"){
	//弾丸、矢を取り出す
	require LIBDIR.'item2.php';
	delshot($user);
}elseif($_POST['Command'] == "ITEM" and preg_match("/^EQDEL_/",$_POST['Command3'])){
	//装備を外す
	require LIBDIR.'item1.php';
	eqdel($user);
}elseif(preg_match("/^TAC_/",$_POST['Command'])){
	//基本方針
	require LIBDIR.'action.php';
	tactics($user);
}elseif(preg_match("/^OUS_/",$_POST['Command'])){
	//応戦方針
	require LIBDIR.'action.php';
	ousen($user);
}elseif($_POST['Command'] == "MESCHG"){
	//口癖変更
	require LIBDIR.'action.php';
	meschange($user);
}elseif($_POST['Command'] == "GROUP"){
	//グループ変更
	require LIBDIR.'action.php';
	group($user);
}elseif(preg_match("/^POI_/",$_POST['Command'])){
	//毒混入
	require LIBDIR.'action.php';
	poison($user);
}elseif(preg_match("/^PCHK_/",$_POST['Command'])){
	//毒見
	require LIBDIR.'action.php';
	pcheck($user);
}elseif(preg_match("/^PDEL_/",$_POST['Command'])){
	//毒中和
	require LIBDIR.'action.php';
	pdelete($user);
}elseif($_POST['Command'] == "SPEAKER"){
	//スピーカ使用
	require LIBDIR.'action.php';
	speaker($user);
}elseif($_POST['Command'] == "SPECIAL" and $_POST['Command4'] == "HACK"){
	//ハッキング
	require LIBDIR.'action.php';
	hack($user);
}elseif(preg_match("/^GET_/",$_POST['Command'])){
	//戦利品取得
	require LIBDIR.'item2.php';
	winget($user);
}elseif(preg_match("/^ATK_/",$_POST['Command'])){
	//戦闘
	require LIBDIR.'attack.php';
	require LIBDIR.'dsp_battle.php';
	$w_user = attack1($user);
	battle($user,$w_user);
}elseif($_POST['Command'] == "RUNAWAY"){
	//逃走
	$user['log'] .= $user['l_name']." は 全速力で逃げ出した・・・。<br>";
	$user['bb'] = '';
	$user['sts'] = "正常";
	save($user);
	$_POST['Command'] = "MAIN";
}

require LIBDIR.'dsp_main.php';
status($user);