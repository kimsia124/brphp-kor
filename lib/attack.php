<?php
//--------------------------------------------------------------------
//	敵発見処理
//--------------------------------------------------------------------
function attack(&$user,$w_user){

	$user['log'] .= "{$w_user['f_name']} {$w_user['l_name']}（{$w_user['cl']} {$w_user['sex']}{$w_user['no']}番）を発見した！<br>";
	$user['log'] .= "{$w_user['f_name']} {$w_user['l_name']} はこちらに気付いてないな・・・。<br>";

	$user['sts'] = 'ENEMY_'.$w_user['number'];
	save($user);
	$_POST['Command'] = 'BATTLE0_'.$w_user['number'];
}
//--------------------------------------------------------------------
//	先制攻撃
//--------------------------------------------------------------------
function attack1(&$user){
	global $br;

	list(,$w_kind) = explode("_",$_POST['Command']);
	$file = search_userfile($_POST['wid'],'number');
	$w_user = getuserdata($file);

	if($user['tactics'] != "連闘行動"){
		$bid = $w_user['bid'];
		$bg_name = $w_user['bg_name'];
	}else{
		$bid = $bg_name = "";
	}

	if($bid == $user['id'] or $w_user['hit'] <= 0){
		error("不正アクセスです");
	}elseif($user['g_name'] != "" and $bg_name == $user['g_name']){
		error("不正アクセスです");
	}

	bb_ck($user,$w_user);//ブラウザバック対処

	$user['log'] .= "{$w_user['f_name']} {$w_user['l_name']}（{$w_user['cl']} {$w_user['sex']}{$w_user['no']}番)と戦闘開始！<br>";

	if($w_user['pls'] != $user['pls']){
		$user['log'] .= "しかし、相手に逃げられてしまった！<br>";
		return $w_user;
	}

	if($user['equip'][0] == ""){
		$w_name = "素手";$w_kind = "WP";
	}else{
		list($w_name) = explode("<>",$user['equip'][0]);
	}
	if($w_user['equip'][0] == ""){
		$w_name2 = "素手";$w_kind2 = "WP";
	}else{
		list($w_name2,$w_kind2) = explode("<>",$w_user['equip'][0]);
	}

	$time = $br['hour'].':'.$br['min'].':'.$br['sec'];//現在の時刻
	$player = $user['f_name'].' '.$user['l_name'];//攻撃した人の氏名

	log_ck($w_user,$time);
	heal_result($w_user);

	list($atk1,$def1,$range1) = tactget2($user,"PC",$w_kind);
	list($atk2,$def2,$range2) = tactget2($w_user,"NPC",$w_kind2);

	if($_POST['Comment'] != ""){
		$user['log'] .= '<span class="msg">'.$player.'「'.$_POST['Comment'].'」</span><br>';
		$w_user['log'] .= '<span class="msg">'.$player.'「'.$_POST['Comment'].'」</span><br>';
	}

	$wep = weptreat($user,$w_user,$w_name,$w_kind,"攻撃","PC");

	if(mt_rand(1,100) <= $wep['mei']){
		//命中--------------------------------
		list($damage1,$kegames1,$hakaiinf1) = damage($user,$w_user,$atk1,$def2,$wep);
		$user['log'] .= '<span class="dmg">'.$damage1.'ダメージ '.$kegames1.' '.$hakaiinf1.'</span>！<br>';
	}else{
		//命中せず----------------------------
		$user['log'] .= "しかし、避けられた！<br>";
		$damage1 = 0;
		$kegames1 = $hakaiinf1 = '';
	}

	if($w_user['hit'] <= 0){
		//敵死亡------------------------------------
		$w_user['log'] .= '<span class="atk">'.$time.' 戦闘：'.$player.' 被:'.$damage1.' '.$kegames1.'</span><br>';
		death2($user,$w_user,$wep);
	}elseif(mt_rand(1,10) <= 5){
		//反撃--------------------------------------
		if($range1 == $range2){
			//距離同じ------------------------------
			$w_wep = weptreat($w_user,$user,$w_name2,$w_kind2,"反撃","NPC");
			if(mt_rand(1,100) <= $w_wep['mei']){
				//命中------------------------------
				list($damage2,$kegames2,$hakaiinf2) = damage($w_user,$user,$atk2,$def1,$w_wep);
				$user['log'] .= '<span class="dmg">'.$damage2.'ダメージ '.$kegames2.'</span>！<br>';
				if($user['hit'] <= 0){
					//自分死亡----------------------
					death1($user,$w_user,$w_wep,$time);
				}else{
					$user['log'] .= $w_user['l_name'].' は逃げ切った・・・。<br>';
				}
				$w_user['log'] .= '<span class="atk">'.$time.' 戦闘：'.$player.' 攻:'.$damage2.' 被:'.$damage1.' '.$hakaiinf2.$kegames1.'</span><br>';
			}else{
				//回避------------------------------
				$user['log'] .= "しかし、間一髪避けた！<br>";
				$w_user['log'] .= '<span class="atk">'.$time.' 戦闘：'.$player.' 攻:0 被:'.$damage1.' '.$kegames1.'</span><br>';
			}
		}else{
			//距離が違う----------------------------
			$user['log'] .= $w_user['l_name'].' は 反撃できない！<br>'.$w_user['l_name'].' は 逃げ切った・・・。<br>';
			$w_user['log'] .= '<span class="atk">'.$time.' 戦闘：'.$player.' 被:'.$damage1.' '.$kegames1.'</span><br>';
		}
	}else{
		//反撃せず----------------------------------
		$user['log'] .= $w_user['l_name'].' は 逃げ切った・・・。<br>';
		$w_user['log'] .= '<span class="atk">'.$time.' 戦闘：'.$player.' 被:'.$damage1.' '.$kegames1.'</span><br>';
	}

	$w_user['bid'] = $user['id'];
	$w_user['bg_name'] = $user['g_name'];
	if(!preg_match('/^KILL_/',$user['sts'])){
		$user['sts'] = "正常";
	}

	levelup($user);
	levelup($w_user);

	save($user);
	save($w_user,true);

	return $w_user;
}
//--------------------------------------------------------------------
//	後攻攻撃
//--------------------------------------------------------------------
function attack2(&$user,&$w_user){
	global $br;

	if($w_user['hit'] <= 0){
		error("不正アクセスです。");
	}

	$user['log'] .= "{$w_user['f_name']} {$w_user['l_name']}（{$w_user['cl']} {$w_user['sex']}{$w_user['no']}番)が突如襲い掛かってきた！<br>";

	if($user['equip'][0] == ""){
		$w_name = "素手";$w_kind = "WP";
	}else{
		list($w_name,$w_kind) = explode("<>",$user['equip'][0]);
	}
	if($w_user['equip'][0] == ""){
		$w_name2 = "素手";$w_kind2 = "WP";
	}else{
		list($w_name2,$w_kind2) = explode("<>",$w_user['equip'][0]);
	}

	$time = $br['hour'].':'.$br['min'].':'.$br['sec'];//現在の時刻
	$player = $user['f_name'].' '.$user['l_name'];//行動している人の氏名

	list($atk1,$def1,$range1) = tactget2($user,"PC",$w_kind);
	list($atk2,$def2,$range2) = tactget2($w_user,"NPC",$w_kind2);

	log_ck($w_user,$time);
	heal_result($w_user);

	$w_wep = weptreat($w_user,$user,$w_name2,$w_kind2,"攻撃","NPC");

	if(mt_rand(1,100) <= $w_wep['mei']){
		//命中--------------------------------
		list($damage1,$kegames1,$hakaiinf1) = damage($w_user,$user,$atk2,$def1,$w_wep);
		$user['log'] .= '<span class="dmg">'.$damage1.'ダメージ '.$kegames1.'</span>！<br>';
	}else{
		//回避--------------------------------
		$damage1 = 0;
		$kegames1 = $hakaiinf1 = '';
		$user['log'] .= "しかし、間一髪避けた！<br>";
	}

	if($user['hit'] <= 0){
		//自分死亡----------------------------------
		death1($user,$w_user,$w_wep,$time);
		$w_user['log'] .= '<span class="atk">'.$time.' 奇襲：'.$player.' 攻:'.$damage1.' '.$hakaiinf1.'</span><br>';
	}elseif(mt_rand(1,10) <= 5){
		//反撃--------------------------------------
		if($range1 == $range2){
			//距離同じ------------------------------
			$wep = weptreat($user,$w_user,$w_name,$w_kind,"反撃","PC");
			if(mt_rand(1,100) <= $wep['mei']){
				//命中------------------------------
				list($damage2,$kegames2,$hakaiinf2) = damage($user,$w_user,$atk1,$def2,$wep);
				$user['log'] .= '<span class="dmg">'.$damage2.'ダメージ '.$kegames2.' '.$hakaiinf2.'</span>！<br>';
				if($w_user['hit'] <= 0){
					//敵死亡------------------------
					death2($user,$w_user,$wep);
				}else{
					$user['log'] .= $user['l_name'].' は逃げ切った・・・。<br>';
				}
				$w_user['log'] .= '<span class="atk">'.$time.' 奇襲：'.$player.' 攻:'.$damage1.' 被:'.$damage2.' '.$hakaiinf1.$kegames2.'</span><br>';
			}else{
				//命中せず--------------------------
				$user['log'] .= "しかし、避けられた！<br>";
				$w_user['log'] .= '<span class="atk">'.$time.' 奇襲：'.$player.' 攻:'.$damage1.' 被:0 '.$hakaiinf1.'</span><br>';
			}
		}else{
			//距離が違う----------------------------
			$user['log'] .= $user['l_name'].' は反撃できない！<br>'.$user['l_name'].' は逃げ切った・・・。<br>';
			$w_user['log'] .= '<span class="atk">'.$time.' 奇襲：'.$player.' 攻:'.$damage1.' '.$hakaiinf1.'</span><br>';
		}
	}else{
		//反撃できず--------------------------------
		$user['log'] .= $user['l_name'].' は 逃げ切った・・・。<br>';
		$w_user['log'] .= '<span class="atk">'.$time.' 奇襲：'.$player.' 攻:'.$damage1.' '.$hakaiinf1.'</span><br>';
	}

	$w_user['bid'] = $user['id'];
	$w_user['bg_name'] = $user['g_name'];

	levelup($user);
	levelup($w_user);

	save($user);
	save($w_user,true);

}

//--------------------------------------------------------------------
//	武器種別処理
//--------------------------------------------------------------------
function weptreat(&$attman,&$defman,$w_name,$w_kind,$ind,$pc){
/*	$attman：攻撃者データ
	$defman：防御者データ
	$w_name：武器名
	$w_kind：攻撃種別
	$ind：攻撃種別(攻撃or反撃)
	$pc：攻撃者（PCorNPC)*/

	$log1 = $attman['l_name'].'の'.$ind.'！';

	if(preg_match("/G/",$w_kind) and $attman['eqtai'][0] > 0){//銃系
		$log2 = $w_name.' を '.$defman['l_name'].'目掛けて発砲した！';
		$mei = 55;	 $atkind = "G";			//命中率、攻撃種別
		$kega = 25;	$kegainf = "頭腕腹足";	//負傷率、負傷箇所
		$hakai = 3;	$killinf = "銃殺";		//破壊率、殺害種別
		$wk = ++$attman['wg'];				//熟練度上昇
		if(--$attman['eqtai'][0] <= 0){		//回数減少
			$attman['eqtai'][0] = 0;
		}
		soundsave("GUN",$attman,$defman);	//銃声保存
	}elseif(preg_match("/A/",$w_kind) and $attman['eqtai'][0] > 0){//射系
		$log2 = $w_name.' を '.$defman['l_name'].'目掛けて射た！';
		$mei = 65;	 $atkind = "A";
		$kega = 20;	$kegainf = "頭腕腹足";
		$hakai = 3;	$killinf = "射殺";
		$wk = ++$attman['wa'];
		if(--$attman['eqtai'][0] <= 0){		//回数減少
			$attman['eqtai'][0] = 0;
		}
	}elseif(preg_match("/B/",$w_kind) or preg_match("/A|G/",$w_kind)){//棍系 or 弾無し銃 or 矢無し弓
		$log2 = $w_name.' で '.$defman['l_name'].'に殴りかかった！';
		$mei = 75;	 $atkind = "B";
		$kega = 15;	$kegainf = "頭腕";
		$hakai = 3;	$killinf = "撲殺";
		$wk = ++$attman['wb'];
	}elseif(preg_match("/C/",$w_kind)){//投系
		$log2 = $w_name.' を '.$defman['l_name'].'に投げつけた！';
		$mei = 70;	 $atkind = "C";
		$kega = 15;	$kegainf = "頭腕";
		$hakai = 0;	$killinf = "殺害";
		$wk = ++$attman['wc'];
		if(--$attman['eqtai'][0] <= 0){
			$attman['equip'][0] = "";
			$attman['eqeff'][0] = $attman['eqtai'][0] = 0;
		}
	}elseif(preg_match("/D/",$w_kind)){//爆系
		$log2 = $w_name.' を '.$defman['l_name'].'に投げつけた！';
		$mei = 85;	 $atkind = "D";
		$kega = 25;	$kegainf = "頭腕腹足";
		$hakai = 0;	$killinf = "爆殺";
		$wk = ++$attman['wd'];
		if(--$attman['eqtai'][0] <= 0){
			$attman['equip'][0] = "";
			$attman['eqeff'][0] = $attman['eqtai'][0] = 0;
		}
		soundsave("BOMB",$attman,$defman); //爆音保存
	}elseif(preg_match("/N/",$w_kind)){//斬系
		$log2 = $w_name.' で '.$defman['l_name'].'に斬りつけた！';
		$mei = 75;	 $atkind = "N";
		$kega = 20;	$kegainf = "頭腕腹足";
		$hakai = 3;	$killinf = "斬殺";
		$wk = ++$attman['wn'];
	}elseif(preg_match("/S/",$w_kind)){//刺系
		$log2 = $w_name.' で '.$defman['l_name'].'を刺した！';
		$mei = 75;	 $atkind = "S";
		$kega = 20;	$kegainf = "頭腕腹足";
		$hakai = 3;	$killinf = "刺殺";
		$wk = ++$attman['ws'];
	}elseif(preg_match("/P/",$w_kind) and $w_name != "素手"){//殴系(素手以外)
		$log2 = $w_name.' で '.$defman['l_name'].'を殴った！';
		$mei = 70;	 $atkind = "P";
		$kega = 15;	$kegainf = "頭腹";
		$hakai = 3;	$killinf = "殺害";
		$wk = ++$attman['wp'];
	}else{//素手、又はいずれにも該当しないもの
		$log2 = $w_name.' で '.$defman['l_name'].'を殴った！';
		$mei = 70;	 $atkind = "P";
		$kega = 5;	 $kegainf = "頭腹";
		$hakai = 0;	$killinf = "殺害";
		$wk = ++$attman['wp'];
	}

	if($pc == 'PC'){
		$attman['log'] .= $log1.$log2;
	}else{
		$defman['log'] .= $log1.$log2;
	}

	$wk = (int)($wk/BASE);
	$mei += $wk;
	if(preg_match("/頭/",$attman['inf'])){$mei -= 20;}

	$wk = 0.9 + 0.05 * $wk;
	if($wk > 1.15){$wk = 1.15;}

	return compact("w_name","atkind","mei","kega","kegainf","hakai","killinf","wk");

}
//--------------------------------------------------------------------
//	ダメージ計算等
//--------------------------------------------------------------------
function damage(&$attman,&$defman,$atk,$def,$wep){
//攻撃者、防御者、攻撃側補正、防御側補正、武器データ

	for($i=1;$i<6;$i++){
		if(preg_match('/<>/',$defman['equip'][$i])){
			list($b_name[$i],$b_kind[$i]) = explode("<>",$defman['equip'][$i]);
		}else{
			$b_name[$i] = $b_kind[$i] = '';
		}
	}

	$pnt = 1;

	switch($wep['atkind']){
		case "G"://銃による攻撃
			if($b_kind[2] == "DH"){		//頭
				$pnt *= 1.2;
			}elseif($b_kind[5] == "ADB"){//防弾
				$pnt *= 0.8;
			}
			break;
		case "N"://斬による攻撃
			if($b_kind[5] == "ADB"){	//防弾
				$pnt *= 1.2;
			}elseif($b_kind[1] == "DBK"){//鎖
				$pnt *= 0.8;
			}
			break;
		case "B"://棍による攻撃
			if(preg_match("/DBA/",$b_kind[1])){	//鎧
				$pnt *= 1.2;
			}elseif($b_kind[2] == "DH"){		//頭
				$pnt *= 0.8;
			}
			break;
		case "S"://刺による攻撃
			if($b_kind[5] == "DBK"){				//鎖
				$pnt *= 1.2;
			}elseif(preg_match("/DBA/",$b_kind[1])){//鎧
				$pnt *= 0.8;
			}
			break;
	}

	//攻撃側の攻撃力
	if(preg_match("/<>WG|<>WA/",$attman['equip'][0]) and $wep['atkind'] == "B"){//銃、射武器で殴る
		$watt = round($attman['eqeff'][0]/10);
	}else{
		$watt = $attman['eqeff'][0];
	}
	$att = $attman['att'] + $watt;

	//防御側の防御力
	$ball = $defman['def'] + $defman['eqeff'][1] + $defman['eqeff'][2] + $defman['eqeff'][3] + $defman['eqeff'][4];
	if(preg_match("/^AD/",$b_kind[5])){$ball += $defman['eqeff'][5];}//装飾品が防具？

	//ダメージ計算
	$result = ($att * $atk * $wep['wk']) - ($ball * $def);
	$result /= 2;
	if($result >= 1){
		$result += mt_rand(0,round($result));
	}
	$result = round($result * $pnt);

	if($result < 1){$result = 1;}

	//経験値獲得
	$attman['exp']++;

	$defman['hit'] -= $result;

	if(mt_rand(1,100) <= $wep['hakai']){//武器損傷？
		$attman['equip'][0] = "";$attman['eqeff'][0] = $attman['eqtai'][0] = 0;
		$hakaiinf = "武器損傷！";
	}else{
		$hakaiinf = "";
	}

	if(preg_match("/N/",$attman['equip'][0]) and mt_rand(1,5) == 1){//刃こぼれ
		if(--$attman['eqeff'][0] < 1){$attman['eqeff'][0] = 1;}//攻撃力は1より下がらない
	}

	$defman['eqtai'][1]--;

	//怪我処理
	$kegames = "";
	if(mt_rand(1,100) <= $wep['kega']){
		$chk = false;
		$dice = mt_rand(1,4);
		if($dice == 1 and preg_match("/頭/",$wep['kegainf'])){//頭
			$k_work = "頭";
		}elseif($dice == 2 and preg_match("/腕/",$wep['kegainf'])){//腕
			$k_work = "腕";
		}elseif($dice == 3 and preg_match("/足/",$wep['kegainf'])){//足
			$k_work = "足";
		}elseif($dice == 4 and preg_match("/腹/",$wep['kegainf'])){//腹
			$k_work = "腹";
		}else{
			$chk = true;
		}

		//負傷防御による防具耐久力減少
		if(!$chk){
			if($k_work == "腹"){
				if(preg_match("/AD/",$b_kind[5])){
					$defman['eqtai'][5]--;
				}elseif(preg_match("/DB/",$b_kind[1])){
					$defman['eqtai'][1]--;
				}
			}elseif(preg_match("/DH/",$b_kind[2]) and $k_work == "頭"){
				$defman['eqtai'][2]--;
			}elseif(preg_match("/DA/",$b_kind[3]) and $k_work == "腕"){
				$defman['eqtai'][3]--;
			}elseif(preg_match("/DF/",$b_kind[4]) and $k_work == "足"){
				$defman['eqtai'][4]--;
			}else{
				$kegames = $k_work."部負傷";
				str_replace($k_work,"",$defman['inf']);
				$defman['inf'] .= $k_work;
			}
		}
	}

	for($i=1;$i<=5;$i++){
		if($defman['eqtai'][$i] <= 0){
			$defman['equip'][$i] = "";$defman['eqeff'][$i] = $defman['eqtai'][$i] = 0;
		}
	}

	return array($result,$kegames,$hakaiinf);
}
//--------------------------------------------------------------------
//	自分死亡処理
//--------------------------------------------------------------------
function death1(&$user,&$w_user,$w_wep,$time){
	global $br,$mem;

	$mem--;

	$user['hit'] = 0;
	$user['com'] = mt_rand(1,7);

	$user['log'] .= '<span class="dead">'.$user['f_name'].' '.$user['l_name'].'（'.$user['cl'].' '.$user['sex'].$user['no'].'番）は死亡した。</span><br>';

	if($w_user['kmes'] != ""){
		$user['log'] .= '<span class="msg">'.$w_user['f_name'].' '.$w_user['l_name'].'『'.$w_user['kmes'].'』</span><br>';
		$user['kmes'] = '<span class="msg">'.$w_user['f_name'].' '.$w_user['l_name'].'『'.$w_user['kmes'].'』</span><br>';
	}

	$user['death'] = "{$w_user['f_name']} {$w_user['l_name']}（{$w_user['cl']} {$w_user['sex']}{$w_user['no']}番）により{$w_wep['killinf']}";

	$w_user['kill']++;
	$w_user['log'] .= '<span class="atk">'.$time.' '.$user['f_name'].' '.$user['l_name'].'（'.$user['cl'].' '.$user['sex'].$user['no'].'番）と戦闘を行い、殺害した。【残り'.$mem.'人】</span><br>';

	$b_limit = BATTLE_LIMIT * ADD_AREA + 1;

	if($mem == 1 and $w_user['tactics'] != "NPC" and $br['ar'] >= $b_limit){$w_user['inf'] .= "勝";}
	soundsave("DEATH",$user,$w_user);
	logsave("DEATH2",$user,$w_user,$w_wep);

}
//--------------------------------------------------------------------
//	敵死亡処理
//--------------------------------------------------------------------
function death2(&$user,&$w_user,$wep){
	global $br,$mem;

	$user['bb'] = $w_user['id'];//ブラウザバック対処

	if(!preg_match("/NPC(0|2)/",$w_user['type'])){
		$mem--;
	}

	$w_user['hit'] = 0;
	$w_user['com'] = mt_rand(1,7);

	$user['kill']++;
	$user['log'] .= '<span class="dead">'.$w_user['f_name'].' '.$w_user['l_name'].'（'.$w_user['cl'].' '.$w_user['sex'].$w_user['no'].'番）を殺害した。【残り'.$mem.'人】</span><br>';

	if($w_user['dmes'] != ""){
		$user['log'] .= '<span class="atk">'.$w_user['f_name'].' '.$w_user['l_name'].'『'.$w_user['dmes'].'』</span><br>';
	}

	$w_user['death'] = "{$user['f_name']} {$user['l_name']}（{$user['cl']} {$user['sex']}{$user['no']}番）により{$wep['killinf']}";

	if($user['kmes'] != ""){
		$user['log'] .= '<span class="msg">'.$user['f_name'].' '.$user['l_name'].'『'.$user['kmes'].'』</span><br>';
		$w_user['kmes'] = '<span class="msg">'.$user['f_name'].' '.$user['l_name'].'『'.$user['kmes'].'』</span>';
	}

	$b_limit = BATTLE_LIMIT * ADD_AREA + 1;

	if($mem == 1 and $br['ar'] >= $b_limit){$user['inf'] .= "勝";}

	soundsave("DEATH",$user,$w_user);
	logsave("DEATH1",$user,$w_user,$wep);

	$_POST['Command'] = "DEATHGET_".$w_user['number'];
	$user['sts'] = "KILL_".$w_user['number'];

}
//--------------------------------------------------------------------
//	レベルアップ処理
//--------------------------------------------------------------------
function levelup(&$user){

	while($user['exp'] >= floor(($user['level']*2-1)*BASEEXP) and $user['hit'] > 0){
		$uphit = mt_rand(3,5);
		$upatt = mt_rand(2,4);
		$updef = mt_rand(2,4);
		$user['level']++;
		$user['mhit'] += $uphit;
		$user['att'] += $upatt;
		$user['def'] += $updef;
		$user['log'] .= "レベルが上がった。HP+".$uphit."　攻撃力+".$upatt."　防御力+".$updef."<br>";
	}

}
//--------------------------------------------------------------------
//	敵戦闘ログ自動削除
//--------------------------------------------------------------------
function log_ck(&$w_user,$time) {

if(strlen($w_user['log']) > 3000){
	$w_user['log'] = '<span class="caution">'.$time.' 戦闘ログは自動削除されました。</span><br>';
}

}
