<?php
//--------------------------------------------------------------------
//	IDチェック
//--------------------------------------------------------------------
function idcheck($host){
	global $br;

	$file = search_userfile($_POST['id'],'id');
	if($file){
		$user = getuserdata($file);
		if($_POST['id'] != $user['id']){
			//通常はまず起こらないはずですがこの時点でIDが一致しない場合バックアップの読み込みを試みる
			//起こるケースとしては直接ファイルを開いてidを書き換えたりした場合や
			//何らかの理由でユーザーデータファイルの中身が空になった場合に発生するものと思われます
			$user = userbackread();
		}
		if($_POST['pass'] == $user['pass']){
			if($user['hit'] > 0){
				setcookie("id",$_POST['id'],NOW+7*86400);
				setcookie("pass",$_POST['pass'],NOW+7*86400);
			}else{
				setcookie("id",$_POST['id'],NOW+REGIST_TIME*3600);
				setcookie("pass",$_POST['pass'],NOW+REGIST_TIME*3600);
				error("既に死亡しています。<br><br>死因".$user['death']."<br><br>".$user['log']);
			}
		}else{
			error("パスワードが一致しません");
		}
	}else{
		$user = userbackread();
	}

	//ログの消去
	if($user['log'] != ""){$log = $user['log'];save($user);$user['log'] = $log;}

	$user['bid'] = "";
	$user['bg_name'] = "";
	$user['ip'] = $host;

	$mem = membercount();
	$b_limit = BATTLE_LIMIT * ADD_AREA + 1;

	//エンディング判定
	if($mem == 1 and $br['ar'] >= $b_limit){
		//最後の一人になった
		if(!mb_ereg("終了",$br['endflg'])){
			file_put_contents(FLAGFILE,"{$br['hackflg']},終了,{$br['stopflg']},",LOCK_EX);
			logsave("WINEND",$user);
			winnersave("winner",$user);
			if((int)AUTORESET >= 1){
				$resettime = mktime(0,0,0,$br['month'],$br['mday'],$br['year']) + 86400 * (int)AUTORESET;
				file_put_contents(TIMEFILE,$resettime,LOCK_EX);
			}
		}
		require LIBDIR."ending.php";
		ending($user);
	}elseif(mb_ereg("解",$user['inf'])){
		//解除キーを使用した
		require LIBDIR."ending.php";
		ex_ending($user);
	}elseif(mb_ereg("解除",$br['endflg'])){
		//他の人が解除キーを使用した時に生きていた
		require LIBDIR."ending.php";
		ex_ending2($user);
	}

	//ログイン時、特定の条件下でメインのコマンド選択画面に戻らないようにする
	if($_POST['Command'] == 'LOGIN'){
		//睡眠、治療、休憩時は解除せずにそのまま続行する
		if($user['sts'] == '睡眠'){
			$_POST['Command'] = 'SLEEP';
		}elseif($user['sts'] == '治療'){
			$_POST['Command'] = 'HEAL';
		}elseif($user['sts'] == '休憩'){
			$_POST['Command'] = 'REST';
		}elseif(preg_match('/_/',$user['sts'])){
			//死体を含めた他人との遭遇時にうっかりブラウザのウインドウ又はタブを
			//閉じてしまった場合などに再びログインすることで
			//遭遇時の画面からやり直す。
			list($a,$b) = explode('_',$user['sts']);
			$file = search_userfile($b,'number');
			$w_user = getuserdata($file);
			switch($a){
				case 'ENEMY':
					$_POST['Command'] = 'BATTLE0_'.$b;
					require LIBDIR.'attack.php';
					require LIBDIR.'dsp_battle.php';
					attack($user,$w_user);
					battle($user,$w_user);
					break;
				case 'KILL':
					$_POST['Command'] = 'DEATHGET_'.$b;
					require LIBDIR.'dsp_battle.php';
					$user['log'] .= '<span class="dead">'.$w_user['f_name'].' '.$w_user['l_name'].'（'.$w_user['cl'].' '.$w_user['sex'].$w_user['no'].'番）を殺害した。【残り'.$mem.'人】</span><br>';
					battle($user,$w_user);
					break;
				case 'DEAD':
					$_POST['Command'] = 'DEATHGET_'.$b;
					require LIBDIR.'move.php';
					deathget($user,$w_user);
					break;
				default:
					$_POST['Command'] = 'MAIN';
			}
		}else{
			$_POST['Command'] = 'MAIN';
		}
	}else{
		//敵遭遇時、殺害時、死体遭遇時に指定されたコマンド以外のコマンドを入力すると
		//エラーを出す(多重窓対策)
		if(preg_match('/^ENEMY_/',$user['sts'])){
			if($_POST['Command'] !== 'RUNAWAY' and !preg_match('/^ATK_/',$_POST['Command'])){
				error('不正アクセスです。');
			}
		}elseif(preg_match('/^(KILL|DEAD)_/',$user['sts'])){
			if(!preg_match('/^GET_/',$_POST['Command'])){
				error('不正アクセスです。');
			}
		}
	}

	return array($user,$mem);

}
//--------------------------------------------------------------------
//	バックアップ読み込み
//--------------------------------------------------------------------
function userbackread(){

	$backfile = U_BACKDIR.$_POST['id'].U_BACKFILE;

	if(file_exists($backfile)){
		$user = getuserdata($backfile);
		if($_POST['id'] == $user['id']){
			if($_POST['pass'] == $user['pass']){
				save($user);
				$user['log'] .= "バックアップを読み込みました。";
			}else{
				error("パスワードが一致しません。");
			}
		}else{
			error("IDが見つかりません。");
		}
	}else{
		error("IDが見つかりません。");
	}
	return $user;
}
//--------------------------------------------------------------------
//	優勝者保存
//--------------------------------------------------------------------
function winnersave($kind,$user){
	global $br;

	$date = "{$br['year']}年{$br['month']}月{$br['mday']}日 {$br['hour']}時{$br['min']}分";

	extract($user);

	switch($kind){
		case "winner":
			$work = "プログラム優勝者";
			break;
		case "destruction":
			$work = "プログラム破壊容疑者";
			break;
		default:
			return;
	}

	$u_item = serialize(array('item' => $item , 'eff' => $eff , 'itai' => $itai));

	$adddata = "$date,$work,$f_name,$l_name,$sex,$cl,$no,$icon,$club,$g_name,$g_pass,$pls,$level,$exp,$hit,$mhit,$sta,".MAXSTA.",$att,$def,$kill,$tactics,$ousen,$equip[0],$eqeff[0],$eqtai[0],$equip[1],$eqeff[1],$eqtai[1],$equip[2],$eqeff[2],$eqtai[2],$equip[3],$eqeff[3],$eqtai[3],$equip[4],$eqeff[4],$eqtai[4],$equip[5],$eqeff[5],$eqtai[5],".ITEMMAX.",$u_item,$wa,$wg,$wc,$wd,$wn,$ws,$wb,$wp,$sts,$inf,$com,$kmes,$dmes,".BASEEXP.",".BASE.",\n";
	//優勝者保存では恐らく必要ないとの判断から以下の変数を保存していない。
	//$number,$id,$pass,$type,$number,$ip,$bb,$bid,$bg_name,$endtime,$death
	//その代わり今後の設定変更に対応出来るようにするため、以下の定数を保存しておく
	//MAXSTA,ITEMMAX,BASEEXP,BASE

	if(file_exists(WINFILE)){
		$logs = file(WINFILE);
		array_unshift($logs,$adddata);
	}else{
		$logs = array($adddata);
	}

	file_put_contents(WINFILE,$logs,LOCK_EX);

}
//--------------------------------------------------------------------
//	音記録
//--------------------------------------------------------------------
function soundsave($kind,$user="",$w_user=""){

	$soundlist = file(SOUNDFILE);

	switch($kind){
		case "GUN":
			$soundlist[0] = NOW.",{$user['pls']},{$user['id']},{$w_user['id']},\n";
			break;
		case "BOMB":
			$soundlist[1] = NOW.",{$user['pls']},{$user['id']},{$w_user['id']},\n";
			break;
		case "DEATH":
			$soundlist[2] = NOW.",{$user['pls']},{$user['id']},{$w_user['id']},\n";
			break;
		case "SPEAKER":
			$soundlist[3] = NOW.",{$user['pls']},{$user['f_name']} {$user['l_name']},{$w_user},\n";
			break;
		default:
			return;
	}

	file_put_contents(SOUNDFILE,$soundlist,LOCK_EX);

}
//--------------------------------------------------------------------
//	戦略計算1(発見関連)
//--------------------------------------------------------------------
function tactget1($mem,$attman){
	global $br;

	if($attman == "PC"){
	//行動側
		$search = 60; //発見率
		$sen = 60;		//先制率

		switch($br['ARSTS'][$mem['pls']]){
			case "SU":$search += 10;break;//発見増
			case "SD":$search -= 10;break;//発見減
		}

		switch($mem['tactics']){
			case "攻撃重視":							break;
			case "防御重視":							break;
			case "先制行動":				$sen += 30;	break;
			case "探索行動":$search += 30;	$sen += 10;	break;
			case "連闘行動":$search += 30;	$sen += 10;	break;
		}

		return array($search,$sen);

	}else{
	//待機側
		$chkpnt = 1;//被発見率補正(数値が減るほど敵に見つかりにくい)

		switch($mem['ousen']){
			case "攻撃重視":$chkpnt += 0.1;	break;
			case "防御重視":				break;
			case "隠密行動":$chkpnt -= 0.3;	break;
			case "回復行動":				break;
		}

		return array($chkpnt);
	}
}
//--------------------------------------------------------------------
//	戦略計算2(戦闘関連)
//--------------------------------------------------------------------
function tactget2($mem,$attman,$w_kind){
	global $br;

	$atk = 1;//攻撃力
	$def = 1;//防御力

	switch($br['ARSTS'][$mem['pls']]){
		case "AU":$atk += 0.1;break;//攻撃増
		case "AD":$atk -= 0.1;break;//攻撃減
		case "DU":$def += 0.1;break;//防御増
		case "DD":$def -= 0.1;break;//防御減
	}

	if($attman == "PC"){
	//行動側
		switch($mem['tactics']){
			case "攻撃重視":$atk += 0.2;$def -= 0.2;break;
			case "防御重視":$atk -= 0.2;$def += 0.2;break;
			case "先制行動":$atk -= 0.1;$def -= 0.1;break;
			case "探索行動":$atk -= 0.1;$def -= 0.1;break;
			case "連闘行動":$atk += 0.1;$def -= 0.2;break;
		}
	}else{
	//待機側
		switch($mem['ousen']){
			case "攻撃重視":$atk += 0.2;$def -= 0.2;break;
			case "防御重視":$atk -= 0.2;$def += 0.2;break;
			case "隠密行動":$atk -= 0.1;$def -= 0.1;break;
			case "回復行動":$atk -= 0.3;$def -= 0.3;break;
		}
	}

	if(mb_ereg("腕",$mem['inf'])){$atk -= 0.2;}

	if(preg_match("/C|D/",$w_kind)){
		$range = "L";
	}elseif(preg_match("/G|A/",$w_kind) and $mem['eqtai'][0] > 0){
		$range = "L";
	}else{
		$range = "S";
	}

	return array($atk,$def,$range);
}
//--------------------------------------------------------------------
//	スタミナ切れ
//--------------------------------------------------------------------
function drain(&$user){

	$user['log'] .= $user['l_name']."は、スタミナが尽きた・・・。最大HPが減少した。<br>";
	$user['sta'] = MAXSTA;

	$down_rate = mt_rand(10,20);//最大体力の低下率(デフォルトでは10%～20%低下する)
	$dhit = round($user['mhit'] * ($down_rate/100));

	if($dhit <= 5){$dhit = 5;}//最低でも5減少する
	$user['mhit'] -= $dhit;

	if($user['mhit'] <= 0){
		$user['hit'] = $user['mhit'] = 0;
		$user['log'] .= "<span class=\"dead\">{$user['f_name']} {$user['l_name']}（{$user['cl']} {$user['sex']}{$user['no']}番）は死亡した。</span><br>";
		logsave("DEATH3",$user);//死亡ログ
	}elseif($user['hit'] > $user['mhit']){
		$user['hit'] = $user['mhit'];
	}

}
//--------------------------------------------------------------------
//	回復コマンドによる回復処理
//--------------------------------------------------------------------
function heal_result(&$user,$player = false){

	$up = (NOW - $user['endtime'])/HEAL_TIME;
	if(mb_ereg("腹",$user['inf'])){$up /= 2;}
	if($user['ousen'] == "回復行動"){$up *= 1.5;}

	if($user['sts'] == "睡眠"){
		$up = (int)$up;
		$maxs = MAXSTA - $user['sta'];//最大までいくつか
		if($up > $maxs){$up = $maxs;}
		$user['sta'] += $up;
		if($player){
			$user['log'] .= "睡眠の結果、スタミナが {$up} 回復した。<br>";
		}
	}elseif($user['sts'] == "治療"){
		$up = (int)($up/HEAL_RATE);
		$maxh = $user['mhit'] - $user['hit'];//最大までいくつか
		if($up > $maxh){$up = $maxh;}
		$user['hit'] += $up;
		if($player){
			$user['log'] .= "治療の結果、体力が {$up} 回復した。<br>";
		}
	}elseif($user['sts'] == "休憩"){
		$ups = (int)($up/2);
		$uph = (int)($up/(HEAL_RATE*2));
		$maxs = MAXSTA - $user['sta'];//スタミナ最大までいくつか
		$maxh = $user['mhit'] - $user['hit'];//体力最大までいくつか
		if($ups > $maxs){$ups = $maxs;}
		if($uph > $maxh){$uph = $maxh;}
		$user['sta'] += $ups;
		$user['hit'] += $uph;
		if($player){
			$user['log'] .= "休憩した結果、体力が{$uph}、スタミナが {$ups} 回復した。<br>";
		}
	}

	if($player){//プレイヤーの操作による回復行動解除
		$user['sts'] = "正常";
		$user['endtime'] = 0;
		save($user);
	}else{//戦闘時の敵回復処理
		$w_user['endtime'] = NOW;
	}
}
//--------------------------------------------------------------------
//	ブラウザバック不正防止
//--------------------------------------------------------------------
function bb_ck(&$user,$w_user){

	if($user['bb'] == $w_user['id']){
		$user['bb'] = "";
	}else{
		error("ブラウザバックチェックにかかりました。");
	}

}