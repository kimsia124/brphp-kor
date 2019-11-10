<?php
//--------------------------------------------------------------------
//	戦利品取得
//--------------------------------------------------------------------
function winget(&$user) {

	$wk = str_replace('GET_','',$_POST['Command']);

	if($wk == 99){
		$user['log'] .= "持ち物を拾うのを諦めた。<br>";
		$user['bb'] = '';
		$user['sts'] = "正常";
		save($user);
		$_POST['Command'] = "MAIN";
		return;
	}

	$chk = false;
	for($i=0;$i<ITEMMAX;$i++){
		if($user['item'][$i] == ""){
			$chk = true;break;
		}
	}

	if(!$chk){
		$user['log'] .= "それ以上所持品を持てない。<br>";
		$_POST['Command'] = "MAIN";return;
	}

	$file = search_userfile($_POST['wid'],'number');
	if(!$file){
		error("不正アクセスです");
	}
	$w_user = getuserdata($file);

	bb_ck($user,$w_user);//ブラウザバック対処

	if($w_user['hit'] > 0){
		$user['log'] .= "{$w_user['f_name']}のあの持ち物が欲しいと強く念じてみた。<br>空しい・・・。<br>";
		$_POST['Command'] = "MAIN";return;
	}

	if($wk >= 0 and $wk <= 5){
		$user['item'][$i] = $w_user['equip'][$wk];
		$user['eff'][$i] = $w_user['eqeff'][$wk];
		$user['itai'][$i] = $w_user['eqtai'][$wk];
		$w_user['equip'][$wk] = "";
		$w_user['eqeff'][$wk] = $w_user['eqtai'][$wk] = 0;
	}elseif($wk >= 6 and $wk <= (5 + ITEMMAX)){
		$wk -= 6;
		$user['item'][$i] = $w_user['item'][$wk];
		$user['eff'][$i] = $w_user['eff'][$wk];
		$user['itai'][$i] = $w_user['itai'][$wk];
		$w_user['item'][$wk] = "";
		$w_user['eff'][$wk] = $w_user['itai'][$wk] = 0;
	}else{
		$user['log'] .= "持ち物を拾うのを諦めた。<br>";
		$_POST['Command'] = "MAIN";return;
	}

	list($in,) = explode("<>",$user['item'][$i]);

	$user['log'] .= $user['l_name'].' は'.$in.'を手に入れた。<br>';
	$user['sts'] = "正常";

	save($user);
	save($w_user,true);
	$_POST['Command'] = "MAIN";
}
//------------------------------------------------------------------------------
//	取り出し
//------------------------------------------------------------------------------
function delshot(&$user){

	if(!preg_match("/<>WG|<>WA/",$user['equip'][0]) or $user['eqtai'][0] == 0){
		error("不正アクセスです。");
	}

	list($w_name,$w_kind) = explode("<>",$user['equip'][0]);

	if(preg_match("/WG/",$w_kind)){$in = "弾丸";}
	elseif(preg_match("/WA/",$w_kind)){$in = "矢";}

	$chk = 0;
	for($i=0;$i<ITEMMAX;$i++){
		if(preg_match("/^".$in."/",$user['item'][$i])){
			$chk = 1;
			break;
		}
	}
	if(!$chk){
		for($i=0;$i<ITEMMAX;$i++){
			if($user['item'][$i] == ""){$chk = 2;break;}
		}
	}

	if(!$chk){//空きが無い
		$user['log'] .= "それ以上デイパックに入りません。<br>";
		$_POST['Command'] = "MAIN";
		return;
	}
	if($chk == 2){//道具欄に弾丸/矢がない
		$user['item'][$i] = $in."<>Y";
		$user['eff'][$i] = 1;
		$user['itai'][$i] = $user['eqtai'][0];
	}elseif($chk == 1){//ある
		$user['itai'][$i] += $user['eqtai'][0];
	}
	$user['eqtai'][0] = 0;
	$user['log'] .= "{$w_name}から{$in}を取り出しました。<br>";

	save($user);
	$_POST['Command'] = "MAIN";
}
//--------------------------------------------------------------------
//	アイテム整理
//--------------------------------------------------------------------
function itemsei(&$user){

	$wk1 = $_POST['SEIRI'][0];
	$wk2 = $_POST['SEIRI'][1];

	if($user['item'][$wk1] == "" or $user['item'][$wk2] == ""){
		error("不正なアクセスです。");
	}

	list($in1,$ik1) = explode("<>",$user['item'][$wk1]);
	list($in2,$ik2) = explode("<>",$user['item'][$wk2]);

	if($wk1 == $wk2){
		//同アイテム選択？
		$user['log'] .= $in1.'を入れ直しました。<br>';
		$_POST['Command'] = "MAIN";return;
	}elseif($in1 == $in2 and $user['eff'][$wk1] == $user['eff'][$wk2] and preg_match("/HH|HD/",$ik1) and preg_match("/HH|HD/",$ik2)){
		//体力回復アイテム
		$user['itai'][$wk1] += $user['itai'][$wk2];
		if($ik1 == "HD" or $ik2 == "HD"){
			$user['item'][$wk1] = $in1.'<>HD';
		}
		if($ik1 == "HD2" or $ik2 == "HD2"){
			$user['item'][$wk1] = $in1.'<>HD2';
		}
		$user['item'][$wk2] = "";
		$user['eff'][$wk2] = $user['itai'][$wk2] = 0;
		$user['log'] .= $in1.'を纏めました。<br>';
	}elseif($in1 == $in2 and $user['eff'][$wk1] == $user['eff'][$wk2] and preg_match("/SH|SD/",$ik1) and preg_match("/SH|SD/",$ik2)){
		//スタミナ回復アイテム
		$user['itai'][$wk1] += $user['itai'][$wk2];
		if($ik1 == "SD" or $ik2 == "SD"){
			$user['item'][$wk1] = "{$in1}<>SD";
		}
		if($ik1 == "SD2" or $ik2 == "SD2"){
			$user['item'][$wk1] = "{$in1}<>SD2";
		}
		$user['item'][$wk2] = "";
		$user['eff'][$wk2] = $user['itai'][$wk2] = 0;
		$user['log'] .= $in1.'を纏めました。<br>';
	}elseif($in1 == $in2 and $ik1 == $ik2 and preg_match("/WC|WD|TN/",$ik1)){
		//投・爆武器、罠
		//攻撃力の平均化
		$user['eff'][$wk1] = round(($user['eff'][$wk1] * $user['itai'][$wk1] + $user['eff'][$wk2] * $user['itai'][$wk2])/($user['itai'][$wk1] + $user['itai'][$wk2]));
		$user['itai'][$wk1] += $user['itai'][$wk2];
		$user['item'][$wk2] = "";
		$user['eff'][$wk2] = $user['itai'][$wk2] = 0;
		$user['log'] .= $in1.'を纏めました。<br>';
	}elseif($in1 == $in2 and $ik1 == $ik2 and $user['eff'][$wk1] == $user['eff'][$wk2] and $ik1 == "Y" and preg_match("/弾丸|矢|毒薬|中和剤|砥石/",$in1)){
		//弾丸、矢、毒薬、中和剤、砥石
		$user['itai'][$wk1] += $user['itai'][$wk2];
		$user['item'][$wk2] = "";$user['eff'][$wk2] = $user['itai'][$wk2] = 0;
		$user['log'] .= $in1.'を纏めました。<br>';
	}else{//違うアイテム、纏められない物
		$user['log'] .= $in1.'と'.$in2.'は纏められないな。<br>';
		$_POST['Command'] = "MAIN";return;
	}
	save($user);
	$_POST['Command'] = "MAIN";
}
//--------------------------------------------------------------------
//	アイテム譲渡
//--------------------------------------------------------------------
function senditem(&$user) {
	global $br;

	if($user['g_name'] == ''){
		error("不正アクセスです。");
	}

	$wk = str_replace('SEND_','',$_POST['Command']);
	if($user['item'][$wk] == ""){
		error("不正アクセスです。");
	}
	list($in,) = explode("<>",$user['item'][$wk]);

	$file = search_userfile($_POST['TARGET'],'number');
	$w_user = getuserdata($file);

	if($w_user['hit'] <= 0 or preg_match("/NPC/",$w_user['type'])){
		error("不正アクセスです。");
	}

	$chk = false;
	for($i=0;$i<ITEMMAX;$i++){
		if($w_user['item'][$i] == ""){
			$chk = true;break;
		}
	}

	if(!$chk){
		$user['log'] .= "相手のデイパックがいっぱいです。<br>";
		$_POST['Command'] = "MAIN";return;
	}

	$w_user['item'][$i] = $user['item'][$wk];
	$w_user['eff'][$i] = $user['eff'][$wk];
	$w_user['itai'][$i] = $user['itai'][$wk];
	$w_user['log'] .= '<span class="msg">'.$br['hour'].'：'.$br['min'].'：'.$br['sec'].' '.$user['f_name'].' '.$user['l_name'].'（'.$user['cl'].' '.$user['sex'].$user['no'].'番）から'.$in.'をもらいました。</span><br>';

	$user['item'][$wk] = "";
	$user['eff'][$wk] = $user['itai'][$wk] = 0;
	$user['log'] .= $w_user['f_name'].' '.$w_user['l_name'].'に'.$in.'を渡しました。<br>';

	save($user);
	save($w_user,true);
	$_POST['Command'] = "MAIN";

}