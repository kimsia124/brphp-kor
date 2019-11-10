<?php
//--------------------------------------------------------------------
//	ヘッダー部
//--------------------------------------------------------------------
function head($pageid = false){

	if($pageid and is_string($pageid)){
		$id = strtoupper($pageid);
	}else{
		$id = 'BR';
	}

?>
<!DOCTYPE html>
<html lang="ja" id="<?php echo $id?>">
<head>
<meta charset="EUC-JP">
<link rel="stylesheet" href="br.css">
<script src="br.js"></script>
<title><?php echo TITLE?></title>
</head>
<body>
<?php

}
//--------------------------------------------------------------------
//	フッター部
//--------------------------------------------------------------------
function foot(){

?>
<hr>
<footer>
<ul>
<li><a href="http://www.happy-ice.com/battle/">■BATTLE ROYALE■</a>
<li><a href="https://hexa.bz/">PHP Edition <?php echo VERSION?></a>
</ul>
</footer>
</body>
</html>
<?php
exit;
}
//--------------------------------------------------------------------
//	エラー
//--------------------------------------------------------------------
function error($errmes){

	head();

?>
<h1>エラー発生</h1>
<p id="LOG"><?php echo $errmes?></p>
<p><a href="<?php echo HOME?>">HOME</a></p>
<?php

	foot();
}
//--------------------------------------------------------------------
//	文字列加工
//--------------------------------------------------------------------
function string_operation(&$array,$admin = false){

	if(isset($_SERVER['CONTENT_LENGTH']) and $_SERVER['CONTENT_LENGTH'] >= 10240){
		error("投稿量が多すぎます。");
	}

	foreach($array as $key => $value){

		if(is_array($value)){
			string_operation($value,$admin);
		}else{
			$value = htmlspecialchars($value,ENT_QUOTES,'EUC-JP',false);
			$value = str_replace(array(',',' ',"\t"),array('&#44;','&nbsp;',''),$value);

			$value = preg_replace("/(\r\n|\r|\n)/","<br>",trim($value));

			//管理モードから能力変更時の対策
			//これがないとアイテム修正を正しく処理出来なくなる
			if($admin){
				$value = str_replace('&lt;&gt;','<>',$value);
			}

			//半角仮名を全角に置換
			$value = mb_convert_kana($value,"KV");
		}

		$array[$key] = $value;

	}

}
//--------------------------------------------------------------------
//	ログ保存
//--------------------------------------------------------------------
function logsave($kind,$user="",$w_user="",$data1="",$data2="",$data3=""){

	switch($kind){
		case "ENTRY"://新規登録
			$newlog = NOW.",ENTRY,{$data1},,,{$_POST['f_name']},{$_POST['l_name']},{$user['sex']},{$user['cl']},{$user['no']},,,,,,";
			break;
		case "DEATH1"://殺害
			$newlog = NOW.",DEATH1,{$data1['w_name']},{$data1['killinf']},{$w_user['dmes']},{$w_user['f_name']},{$w_user['l_name']},{$w_user['sex']},{$w_user['cl']},{$w_user['no']},{$user['f_name']},{$user['l_name']},{$user['sex']},{$user['cl']},{$user['no']},";
			backsave($w_user);
			break;
		case "DEATH2"://返り討ち
			$newlog = NOW.",DEATH2,{$data1['w_name']},{$data1['killinf']},{$user['dmes']},{$user['f_name']},{$user['l_name']},{$user['sex']},{$user['cl']},{$user['no']},{$w_user['f_name']},{$w_user['l_name']},{$w_user['sex']},{$w_user['cl']},{$w_user['no']},";
			backsave($user);
			break;
		case "DEATH3"://衰弱死
			$newlog = NOW.",DEATH3,,,{$user['dmes']},{$user['f_name']},{$user['l_name']},{$user['sex']},{$user['cl']},{$user['no']},,,,,,";
			backsave($user);
			break;
		case "DEATH4"://イベント死
			$newlog = NOW.",DEATH4,,,{$user['dmes']},{$user['f_name']},{$user['l_name']},{$user['sex']},{$user['cl']},{$user['no']},,,,,,";
			backsave($user);
			break;
		case "DEATH5"://毒死
			$newlog = NOW.",DEATH5,,,{$user['dmes']},{$user['f_name']},{$user['l_name']},{$user['sex']},{$user['cl']},{$user['no']},,,,,,";
			backsave($user);
			break;
		case "DEATH6"://罠死
			$newlog = NOW.",DEATH6,,,{$user['dmes']},{$user['f_name']},{$user['l_name']},{$user['sex']},{$user['cl']},{$user['no']},,,,,,";
			backsave($user);
			break;
		case "DEATH7"://処刑(管理)
			$newlog = NOW.",DEATH7,,,{$user['dmes']},{$user['f_name']},{$user['l_name']},{$user['sex']},{$user['cl']},{$user['no']},,,,,,";
			backsave($user);
			break;
		case "DEATH8"://処刑(ハック失敗)
			$newlog = NOW.",DEATH8,,,{$user['dmes']},{$user['f_name']},{$user['l_name']},{$user['sex']},{$user['cl']},{$user['no']},,,,,,";
			backsave($user);
			break;
		case "DEATHAREA"://禁止エリア滞在
			$newlog = NOW.",DEATHAREA,,,{$user['dmes']},{$user['f_name']},{$user['l_name']},{$user['sex']},{$user['cl']},{$user['no']},,,,,,";
			backsave($user);
			break;
		case "GROUP1"://グループ結成
			$newlog = NOW.",GROUP1,{$w_user},,,{$user['f_name']},{$user['l_name']},{$user['sex']},{$user['cl']},{$user['no']},,,,,,";
			break;
		case "GROUP2"://グループ加入
			$newlog = NOW.",GROUP2,{$w_user},,,{$user['f_name']},{$user['l_name']},{$user['sex']},{$user['cl']},{$user['no']},,,,,,";
			break;
		case "GROUP3"://グループ脱退
			$newlog = NOW.",GROUP3,{$w_user},,,{$user['f_name']},{$user['l_name']},{$user['sex']},{$user['cl']},{$user['no']},,,,,,";
			break;
		case "SPEAKER"://スピーカー
			$newlog = NOW.",SPEAKER,{$data1},,,{$user['f_name']},{$user['l_name']},{$user['sex']},{$user['cl']},{$user['no']},,,,,,";
			break;
		case "HACK"://ハッキング
			$newlog = NOW.",HACK,,,,{$user['f_name']},{$user['l_name']},{$user['sex']},{$user['cl']},{$user['no']},,,,,,";
			break;
		case "AREAADD"://禁止エリア追加
			$newlog = $data1.",AREAADD,{$user},{$w_user},,,,,,,,,,,,";
			break;
		case "ADMINMESSAGE":
			$newlog = NOW.",ADMINMESSAGE,{$_POST['message']},,,,,,,,,,,,,";
			break;
		case "WINEND"://優勝者決定
			$newlog = NOW.",WINEND,,,,{$user['f_name']},{$user['l_name']},{$user['sex']},{$user['cl']},{$user['no']},,,,,,";
			break;
		case "EXEND"://ハッキングによる停止
			$newlog = NOW.",EXEND,,,,{$user['f_name']},{$user['l_name']},{$user['sex']},{$user['cl']},{$user['no']},,,,,,";
			break;
		case "TIMELIMIT"://時間切れ
			$newlog = NOW.",TIMELIMIT,,,,,,,,,,,,,,";
			break;
		case "NEWGAME"://初期化時に開始しなかったときのプログラム開始
			$newlog = $user.",NEWGAME,,,,,,,,,,,,,,";
			break;
		default://それ以外は何もしない。
			return;
	}

	$logs = file(NEWSFILE);
	array_unshift($logs,$newlog."\n");

	file_put_contents(NEWSFILE,$logs,LOCK_EX);

}
//--------------------------------------------------------------------
//	ユーザーファイル検索
//--------------------------------------------------------------------
function search_userfile($search,$type=false){

	//適切な引数が与えられているかチェック
	if(!$type){
		error('関数&quot;search_userfile&quot;は2つめの引数に&quot;id&quot;か&quot;number&quot;を指定しなければなりません。');
	}

	//ユーザーIDで検索(プレイヤーのログイン時等に使う)
	if($type == 'id' and is_string($search)){
		$filelist = glob(U_DATADIR.$search.'_[0-9]*_'.U_DATAFILE);
		if(!$filelist){//探すファイルが見つからない
			return false;
		}
		//通常は起こらないはずですが万一該当するファイルが2つ以上あった場合、
		//ファイルの中身を調べて一致するIDがあるか調べる
		if(count($filelist) >= 2){
			foreach($filelist as $file){
				list(,$id) = explode(",",file_get_contents($file));
				if($id == $search){
					return $file;
				}
			}
			return false;
		}else{
			list(,$id) = explode(',',file_get_contents($filelist[0]));
			if($id == $search){
				return $filelist[0];
			}else{
				return false;
			}
		}
	}elseif($type == 'number' and is_string($search) and ctype_digit($search)){
		//番号で検索(敵との遭遇時とか色々な場面で使用)
		$filelist = glob(U_DATADIR.'[a-zA-Z0-9]*_'.$search.'_'.U_DATAFILE);
		if(!$filelist){//探すファイルが見つからない
			return false;
		}
		//通常は起こらないはずですが(中略)一致する番号があるか調べる
		if(count($filelist) >= 2){
			foreach($filelist as $file){
				$number = '';
				list($number,) = explode(',',file_get_contents($file));
				if($number == $search){
					return $file;
				}
			}
			return false;
		}else{
			list($number,) = explode(',',file_get_contents($filelist[0]));
			if($number == $search){
				return $filelist[0];
			}else{
				return false;
			}
			return $filelist[0];
		}
	}

	error('関数&quot;search_userfile&quot;の引数が正しくありません。');
}
//--------------------------------------------------------------------
//	ユーザーデータ取得
//--------------------------------------------------------------------
function getuserdata($filename){

	$userdata = file_get_contents($filename);

	list($a['number'],$a['id'],$a['pass'],$a['type'],$a['f_name'],$a['l_name'],$a['sex'],$a['cl'],$a['no'],$a['icon'],$a['club'],$a['g_name'],$a['g_pass'],$a['pls'],$a['level'],$a['exp'],$a['hit'],$a['mhit'],$a['sta'],$a['att'],$a['def'],$a['kill'],$a['tactics'],$a['ousen'],$a['equip'][0],$a['eqeff'][0],$a['eqtai'][0],$a['equip'][1],$a['eqeff'][1],$a['eqtai'][1],$a['equip'][2],$a['eqeff'][2],$a['eqtai'][2],$a['equip'][3],$a['eqeff'][3],$a['eqtai'][3],$a['equip'][4],$a['eqeff'][4],$a['eqtai'][4],$a['equip'][5],$a['eqeff'][5],$a['eqtai'][5],$item,$a['wa'],$a['wg'],$a['wc'],$a['wd'],$a['wn'],$a['ws'],$a['wb'],$a['wp'],$a['ip'],$a['bb'],$a['bid'],$a['bg_name'],$a['sts'],$a['inf'],$a['endtime'],$a['death'],$a['com'],$a['kmes'],$a['dmes'],$a['log']) = explode(',',$userdata);

	$b = unserialize($item);
	$a['item'] = $b['item'];
	$a['eff'] = $b['eff'];
	$a['itai'] = $b['itai'];
	return $a;

}
//--------------------------------------------------------------------
//	ユーザーデータ保存
//--------------------------------------------------------------------
function save($mem,$logflg=false){

	extract($mem);

	if(!$logflg){$log = "";}
	if($hit <= 0){$sts = "死亡";}

	$u_item = serialize(array('item' => $item , 'eff' => $eff , 'itai' => $itai));

	$file = U_DATADIR.$id.'_'.$number.'_'.U_DATAFILE;
	file_put_contents($file,"$number,$id,$pass,$type,$f_name,$l_name,$sex,$cl,$no,$icon,$club,$g_name,$g_pass,$pls,$level,$exp,$hit,$mhit,$sta,$att,$def,$kill,$tactics,$ousen,$equip[0],$eqeff[0],$eqtai[0],$equip[1],$eqeff[1],$eqtai[1],$equip[2],$eqeff[2],$eqtai[2],$equip[3],$eqeff[3],$eqtai[3],$equip[4],$eqeff[4],$eqtai[4],$equip[5],$eqeff[5],$eqtai[5],$u_item,$wa,$wg,$wc,$wd,$wn,$ws,$wb,$wp,$ip,$bb,$bid,$bg_name,$sts,$inf,$endtime,$death,$com,$kmes,$dmes,$log,",LOCK_EX);

}
//--------------------------------------------------------------------
//	個別バックアップ保存
//--------------------------------------------------------------------
function backsave($mem){

	extract($mem);

	$u_item = serialize(array('item' => $item , 'eff' => $eff , 'itai' => $itai));

	$file = U_BACKDIR.$id.U_BACKFILE;
	file_put_contents($file,"$number,$id,$pass,$type,$f_name,$l_name,$sex,$cl,$no,$icon,$club,$g_name,$g_pass,$pls,$level,$exp,$hit,$mhit,$sta,$att,$def,$kill,$tactics,$ousen,$equip[0],$eqeff[0],$eqtai[0],$equip[1],$eqeff[1],$eqtai[1],$equip[2],$eqeff[2],$eqtai[2],$equip[3],$eqeff[3],$eqtai[3],$equip[4],$eqeff[4],$eqtai[4],$equip[5],$eqeff[5],$eqtai[5],$u_item,$wa,$wg,$wc,$wd,$wn,$ws,$wb,$wp,$ip,$bb,$bid,$bg_name,$sts,$inf,$endtime,$death,$com,$kmes,$dmes,,",LOCK_EX);

}//--------------------------------------------------------------------
//	人数取得
//--------------------------------------------------------------------
function membercount(){

	//デフォルトでは一部NPCを除いた生存者の人数を数えるが、
	//使い方次第では他のことの人数を数えることにも使える
	$cnt = 0;
	$filelist = glob(U_DATADIR."*".U_DATAFILE);
	foreach($filelist as $filename){
		$mem = getuserdata($filename);
		//死んでいる人は数えない
		if($mem['hit'] <= 0){
			continue;
		}
		if(!preg_match("/^NPC(0|2)$/",$mem['type'])){
			$cnt++;
		}
	}

	return $cnt;

}
//------------------------------------------------------------------------------
//	ホストチェック
//------------------------------------------------------------------------------
function hostcheck(){
	global $br;

	$host1 = $_SERVER['REMOTE_ADDR'];
	$host2 = gethostbyaddr($host1);

	if(ACCESS_DENY){
		$okflg = false;
		foreach($br['OKLIST'] as $oklist){
			if(preg_match("/^".preg_quote($oklist)."/",$host1)){
				$okflg = true;
				break;
			}elseif($host2){
				if(preg_match("/".preg_quote($oklist)."$/i",$host2)){
					$okflg = true;
					break;
				}
			}
		}
		if(!$okflg){
			foreach($br['KICK'] as $kick){
				if(preg_match("/^".preg_quote($kick)."/",$host1)){
					error("あなたのホストはアクセスが許可されていません。");
				}elseif($host2){
					if(preg_match("/".preg_quote($kick)."$/i",$host2)){
						error("あなたのホストはアクセスが許可されていません。");
					}
				}
			}
		}
	}

	return (IP_HOST and $host2)? $host2:$host1;

}
//--------------------------------------------------------------------
//	緊急停止
//--------------------------------------------------------------------
function pgstop(){
	global $br;

	if(mb_ereg('停止',$br['stopflg'])){
		error('ただ今プログラム緊急停止中です、しばらくお待ちください。');
	}

}