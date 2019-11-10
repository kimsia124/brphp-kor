<?php
/*--------------------------------------------------------------------
 *	新規登録
 *------------------------------------------------------------------*/
require 'pref.php';
//--------------------------------------------------------------------
//	入り口
//--------------------------------------------------------------------
function enter(){
	global $br;

	head('regist');

?>
<h1>転校手続き</h1>
<p>『君が転校生だね？僕が担任です。<br>
生徒からは、「とんぼ」とかいわれてるけどね。<br>
あ、そんな事はどうでもいいね。</p>
<p>とりあえず、ここに氏名と、性別を記入して、<br>
提出してもらえるかな？</p>
<form method="post" name="regist" onsubmit="return registcheck()">
姓：<input type="text" name="f_name" value="" size="16" maxlength="8" required><br>
名：<input type="text" name="l_name" value="" size="16" maxlength="8" required><br>
<br>
性別：<select name="sex">
<option value="NOSEX" selected>- 性別 -
<option value="M">男子
<option value="F">女子
</select>
アイコン：<select name="icon">
<option value="NOICON" selected>- アイコン -
<?php

	foreach($br['M_ICON_NAME'] as $i => $name){
		echo '<option value="M_'.$i.'">'.$name;
	}
	foreach($br['F_ICON_NAME'] as $i => $name){
		echo '<option value="F_'.$i.'">'.$name;
	}
	if(USE_SP_ICON){
		foreach($br['S_ICON_NAME'] as $i => $name){
			echo '<option value="S_'.$i.'">'.$name;
		}
	}

?>
</select><br>
<a href="?mode=icon" target="_blank">-アイコン一覧-</a>
<br><br>
ID：<input type="text" name="id" value="" size="12" maxlength="12" required> 
パスワード：<input type="text" name="pass" value="" size="12" maxlength="12" required><br>
（ID,パスワードは半角英数字8文字以内）<br><br>
<?php

	if(G_MODE){

?>
グループ名：<input type="text" name="g_name" value="" size="16" maxlength="16">
グループパス：<input type="text" name="g_pass" value="" size="16" maxlength="16"><br><br>
<?php

	}

?>
口癖：<input type="text" name="kmes" value="" size="32" maxlength="64"><br>
（相手殺害時の台詞。全角３２文字まで）<br>
遺言：<input type="text" name="dmes" value="" size="32" maxlength="64"><br>
（自分死亡時の台詞）<br>
自己アピール：<textarea name="comment" cols="30" rows="4"></textarea><br>
（一言コメント。生存者一覧に記載される。4行以内）<br>
<p class="caution">同一プレイヤーの複数登録、ゲームの世界観を<br>
損なう名前の登録はご遠慮ください。<br>
（例：外人名、姓名と判断出来ない名前、性別と違う名前、原作の名前）<br>
管理人の一存でデータを強制削除します。</p>
<button type="submit" name="mode" value="regist">実 行</button>
<button type="reset">リセット</button>
</form>
<?php

	foot();

}
//--------------------------------------------------------------------
//	登録処理
//--------------------------------------------------------------------
function regist($host){
	global $br;

	//姓名のところのmb_eregを使ったチェックで漢字によっては引っかかるかも知れません

	$errmes = array();
	//姓----------------------------------------------
	if($_POST['f_name'] == "")								{$errmes[] = "姓が入力されていません。";}
	if(mb_strlen($_POST['f_name']) > 4)						{$errmes[] = "姓の文字数がオーバーしています。（全角4文字まで）";}
	if(mb_ereg("[^亜-熙ぁ-んァ-ヶー々]+",$_POST['f_name']))	{$errmes[] = "姓に漢字、ひらがな、カタカナ以外は利用できません。";}
	//名----------------------------------------------
	if($_POST['l_name'] == "")								{$errmes[] = "名が入力されていません。";}
	if(mb_strlen($_POST['l_name']) > 4)						{$errmes[] = "名の文字数がオーバーしています。（全角4文字まで）";}
	if(mb_ereg("[^亜-熙ぁ-んァ-ヶー々]+",$_POST['l_name']))	{$errmes[] = "名に漢字、ひらがな、カタカナ以外は利用できません。";}
	//ID----------------------------------------------
	if($_POST['id'] == "")									{$errmes[] = "IDが入力されていません。";}
	if(strlen($_POST['id']) > 12)							{$errmes[] = "IDの文字数がオーバーしています。（半角12文字まで）";}
	if(!ctype_alnum($_POST['id']))							{$errmes[] = "IDは半角英数で入力してください。";}
	//PASS--------------------------------------------
	if($_POST['pass'] == "")								{$errmes[] = "パスワードが入力されていません。";}
	if(strlen($_POST['pass']) > 12)							{$errmes[] = "パスワードの文字数がオーバーしています。（半角12文字まで）";}
	if(!ctype_alnum($_POST['pass']))						{$errmes[] = "パスワードは半角英数で入力してください。";}
	if($_POST['id'] == $_POST['pass'])						{$errmes[] = "IDと同じ文字列はパスワードに使えません。";}
	//性別、アイコン
	if(!in_array($_POST['sex'],array('M','F')))				{$errmes[] = "性別が選択されていません。";}
	if(!in_array($_POST['icon'][0],array('M','F','S')))		{$errmes[] = "アイコンが選択されていません。";}
	if($_POST['sex'] == "M" and $_POST['icon'][0] == 'F')	{$errmes[] = "選択した性別とアイコンの性別が一致しません。";}
	elseif($_POST['sex'] == "F" and $_POST['icon'][0] == 'M'){$errmes[] = "選択した性別とアイコンの性別が一致しません。";}
	//その他------------------------------------------
	if(mb_strlen($_POST['kmes']) > 32)						{$errmes[] = "口癖の文字数がオーバーしています。（32文字まで）";}
	if(mb_strlen($_POST['dmes']) > 32)						{$errmes[] = "遺言の文字数がオーバーしています。（32文字まで）";}
	$line = explode('<br>',$_POST['comment']);
	if(count($line) > 4)									{$errmes[] = "一言コメントは4行までです。";}
	if(mb_strlen($_POST['comment']) > 80)					{$errmes[] = "コメントの文字数がオーバーしています。（70文字程度まで）";}

	if($errmes){
		error(implode("<br>",$errmes));
	}

	if(glob(U_DATADIR.$_POST['id']."_[0-9]*_".U_DATAFILE)){
		error("同一IDのキャラクタが既に存在します。");
	}

	$cnt = 0;
	$a = glob(U_DATADIR."*".U_DATAFILE);
	if($a){
		foreach($a as $filename){
			$mem = getuserdata($filename);
			if($mem['f_name'] == $_POST['f_name'] and $mem['l_name'] == $_POST['l_name']){
				error("同姓同名のキャラクタが既に存在します。");
			}
			if(G_MODE){
				if($_POST['g_name'] != "" and $_POST['g_name'] == $mem['g_name']){
					if($_POST['g_pass'] == $mem['g_pass']){
						$cnt++;
					}else{
						error("グループパスが一致していません。");
					}
				}
			}
		}
	}

	if(G_MODE){
		if($cnt >= G_MAX){
			error("グループの人数が上限(".G_MAX."人)を超えています。");
		}
		$g_name = $_POST['g_name'];
		$g_pass = $_POST['g_pass'];
	}else{
		$g_name = $g_pass = "";
	}

	$icon_file = explode('_',$_POST['icon']);
	if($icon_file[0] == 'M'){
		$icon = $br['M_ICON_FILE'][$icon_file[1]];
	}elseif($icon_file[0] == 'F'){
		$icon = $br['F_ICON_FILE'][$icon_file[1]];
	}else{
		$icon = $br['S_ICON_FILE'][$icon_file[1]];
	}

	list($male,$female,$mcl,$fcl,$allmem,$npc) = explode(",",file_get_contents(MEMBERFILE));
	//性別人数チェック
	if($_POST['sex'] == "M"){
		if($mcl >= CLMAX){//登録不可能？
			error("男子生徒はこれ以上登録できません。");
		}
		$no = ++$male;
		$cl = $br['CLASS'][$mcl];
		if($male >= MANMAX){//クラス更新？
			$male=0;$mcl++;
		}
		$sex = '男子';
	}else{
		if($fcl >= CLMAX){//登録不可能？
			error("女子生徒はこれ以上登録できません。");
		}
		$no = ++$female;
		$cl = $br['CLASS'][$fcl];
		if($female >= MANMAX){//クラス更新？
			$female=0;$fcl++;
		}
		$sex = '女子';
	}
	$number = $allmem++;
	$number += $npc;

	//生徒番号ファイル更新
	file_put_contents(MEMBERFILE,$male.",".$female.",".$mcl.",".$fcl.",".$allmem.",".$npc.",",LOCK_EX);

	//ステータス生成
	$id = $_POST['id'];
	$pass = $_POST['pass'];
	$f_name = $_POST['f_name'];
	$l_name = $_POST['l_name'];
	$pls = 0;
	$level = 1;
	$exp = 0;
	$hit = $mhit = mt_rand(75,90);
	$sta = MAXSTA;
	$att = mt_rand(15,20);
	$def = mt_rand(15,20);
	$kill = 0;
	$tactics = $ousen = "通常";
	$ip = $host;
	$bb = "";
	$bid = $bg_name = "";
	$sts = "正常";
	$inf = "";
	$endtime = 0;
	$death = "";
	$com = $_POST['comment'];
	$kmes = $_POST['kmes'];
	$dmes = $_POST['dmes'];
	$log = "";

	//初期アイテム
	$equip = array_fill(0,6,'');
	$eqeff = $eqtai = array_fill(0,6,0);
	$equip[1] = ($sex == "男子")? "学ラン<>DBN":"セーラー服<>DBN";
	$eqeff[1] = 5;
	$eqtai[1] = 30;

	$item = array_fill(0,ITEMMAX,'');
	$eff = $itai = array_fill(0,ITEMMAX,0);
	$item[0] = "水<>HH";
	$eff[0] = 20;
	$itai[0] = 3;
	$item[1] = "パン<>SH";
	$eff[1] = 30;
	$itai[1] = 3;

	//初期配布武器リスト取得
	$weapon = preg_grep('/^##/',file(DATDIR.'weapon.csv'),PREG_GREP_INVERT);
	list($item[2],$eff[2],$itai[2]) = explode(",",$weapon[array_rand($weapon)]);

	//私物アイテムリスト取得
	$stitem = preg_grep('/^##/',file(DATDIR.'stitem.csv'),PREG_GREP_INVERT);
	list($item[4],$eff[4],$itai[4]) = explode(",",$stitem[array_rand($stitem)]);

	list($w_name,$w_kind) = explode("<>",$item[2]);
	list($i_name,$i_kind) = explode("<>",$item[4]);

	//弾又は矢支給
	if(preg_match("/WG/",$w_kind)){
		$item[3] = "弾丸<>Y";
		$eff[3] = 1;
		$itai[3] = 24;
	}elseif(preg_match("/WA/",$w_kind)){
		$item[3] = "矢<>Y";
		$eff[3] = 1;
		$itai[3] = 24;
	}else{
		$item[3] = "";
		$eff[3] = $itai[3] = 0;
	}
	$u_item = serialize(array('item' => $item , 'eff' => $eff , 'itai' => $itai));

	//クラブ
	$wa = $wg = $wc = $wd = $wn = $ws = $wb = $wp = 0;
	$dice = mt_rand(1,11);

	if($dice == 1){
		$club = "弓道部";
		$wa = 1 * BASE;
	}elseif($dice == 2){
		$club = "射撃部";
		$wg = 1 * BASE;
	}elseif($dice == 3){
		$club = "空手部";
		$wb = 1 * BASE;
	}elseif($dice == 4){
		$club = "バスケ部";
		$wc = 1 * BASE;
	}elseif($dice == 5){
		$club = "科学部";
		$wd = 1 * BASE;
	}elseif($dice == 6){
		$club = "フェンシング部";
		$ws = 1 * BASE;
	}elseif($dice == 7){
		$club = "剣道部";
		$wn = 1 * BASE;
	}elseif($dice == 8){
		$club = "ボクシング部";
		$wp = 1 * BASE;
	}elseif($dice == 9){
		$club = "陸上部";
	}elseif($dice == 10){
		$club = "料理研究部";
	}elseif($dice == 11){
		$club = "パソコン部";
	}else{
		$club = "";
	}

	file_put_contents(U_DATADIR.$id.'_'.$number.'_'.U_DATAFILE,"$number,$id,$pass,PC,$f_name,$l_name,$sex,$cl,$no,$icon,$club,$g_name,$g_pass,$pls,$level,$exp,$hit,$mhit,$sta,$att,$def,$kill,$tactics,$ousen,$equip[0],$eqeff[0],$eqtai[0],$equip[1],$eqeff[1],$eqtai[1],$equip[2],$eqeff[2],$eqtai[2],$equip[3],$eqeff[3],$eqtai[3],$equip[4],$eqeff[4],$eqtai[4],$equip[5],$eqeff[5],$eqtai[5],$u_item,$wa,$wg,$wc,$wd,$wn,$ws,$wb,$wp,$ip,$bb,$bid,$bg_name,$sts,$inf,$endtime,$death,$com,$kmes,$dmes,$log,",LOCK_EX);

	$news = array('sex' => $sex,'cl' => $cl,'no' => $no);
	logsave("ENTRY",$news,null,$host);

	head('regist');

?>
<h1>転校手続き完了</h1>
<table border="1">
<tr>
	<th>クラス・番号</th>
	<td colspan="3"><?php echo $cl.' '.$sex.$no?>番
</tr>
<tr>
	<th>氏名
	<td colspan="3"><?php echo $f_name.' '.$l_name?>
</tr>
<tr>
	<th>クラブ
	<td colspan="3"><?php echo $club?>
</tr>
<tr>
	<th>体力
	<td><?php echo $hit.'/'.$mhit?>
	<th>スタミナ
	<td><?php echo $sta?>
</tr>
<tr>
	<th>攻撃力
	<td><?php echo $att?>
	<th>防御力
	<td><?php echo $def?>
</tr>
<tr>
	<th>支給武器
	<td colspan="3"><?php echo $w_name?>
</tr>
<tr>
	<th>私物アイテム
	<td colspan="3"><?php echo $i_name?>
</tr>
</table>
<p><?php

	if($sex == "男子"){
		echo $f_name." ".$l_name." くんだね？<br>";
	}else{
		echo $f_name." ".$l_name." さんだね？<br>";
	}

?>
転校早々だけど、明日は修学旅行だ。<br>
<br>
きちんと遅れずにくるんだぞ！</p>
<form method="post">
<input type="hidden" name="id" value="<?php echo $_POST['id']?>">
<input type="hidden" name="pass" value="<?php echo $_POST['pass']?>">
<button type="submit" name="mode" value="opening">修学旅行に出発</button>
</form>
<?php
	foot();
}
//-------------------------------------------------------------------
//	説明画面
//-------------------------------------------------------------------
function opening(){
	head('regist');

?>
<h1>登録完了</h1>
<p>目がさめると、教室のような所にいた。修学旅行に行ったはずなのに・・・。<br>
「そうだ、修学旅行に行くバスの中で急に眠気が襲ってきて・・・」<br>
周りを見渡すと、他の生徒もいるようだ。よく見ると、皆、銀色の首輪がはめられている事に気づいた<br>
自分の首に触れると、冷たい金属の感触が伝わってきた。<br>
皆と同様、あの銀色の首輪がはめられていた。</p>
<p>突然、前の扉から、一人の男が入ってきた・・・。</p>
<p>『じゃ、説明しまーす。みんなにここに来てもらったのは他でもありませーん。<br>
今日は、皆さんにちょっと、殺し合いをしてもらいまーす。<br>
<br>
逆らったり、その首輪をはずしたり、脱走しようと試みた場合は即座に殺されると思ってください。</p>
<p>皆さんは、今年の“プログラム”対象クラスに選ばれました。</p>
<p>ルールは簡単です。お互い、殺しあってくれればいいだけです。<br>
反則はありませーん。<br>
<br>
ああ、先生言い忘れてたけど、ここは島でーす。</p>
<p>いいかー、ここはこの島の分校です。<br>
先生、ここにずっといるからなー。みんながんばるの、見守ってるからなー。</p>
<p>さて、いいですかぁ。ここを出たらどこへ行っても構いません。<br>
けど、毎日零時に、全島放送をします。一日一回なー。</p>
<p>そこで、みんながもってる地図に従って、何時からこのエリアは危ないぞー、<br>
って先生知らせます。<br>
地図を良く見て、磁石と地形を照らし合わせて、<br>
速やかにそのエリアを出てください。</p>
<p>なんでかというとー、その首輪はやっぱり爆発します。</p>
<p>いいか、だからぁ、建物の中にいてもだめだぞぉ。<br>
穴掘って隠れても電波は届きまーす。<br>
あーそうそう、ついでですがー、建物の中に隠れるのは勝手でーす。</p>
<p>あー、それともう一つ。タイムリミットがあります。<br>
いいですか、タイムリミットでーす。</p>
<p>プログラムでは、どんどん人が死にますがぁ、24時間に渡って死んだ人が誰もでなかったらぁ、<br>
それが時間切れでーす。あと何人残っていようと、コンピュータが作動して、<br>
残ってる人全員の首輪が爆発しまーす。優勝者はありませーん。</p>
<p>さーて、それじゃ一人づつ、このデイパックを持って、教室をでてもらいまーす。</p>
<form method="post" action="br.php">
<input type="hidden" name="id" value="<?php echo $_POST['id']?>">
<input type="hidden" name="pass" value="<?php echo $_POST['pass']?>">
<button type="submit" name="Command" value="MAIN">教室を出る</button>
</form>
<?php
	foot();
}
//-------------------------------------------------------------------
//	アイコン一覧
//-------------------------------------------------------------------
function iconlist(){
	global $br;

	if(USE_SP_ICON){
		$icon_file = array_merge($br['M_ICON_FILE'],$br['F_ICON_FILE'],$br['S_ICON_FILE']);
		$icon_name = array_merge($br['M_ICON_NAME'],$br['F_ICON_NAME'],$br['S_ICON_NAME']);
	}else{
		$icon_file = array_merge($br['M_ICON_FILE'],$br['F_ICON_FILE']);
		$icon_name = array_merge($br['M_ICON_NAME'],$br['F_ICON_NAME']);
	}
	$cnt = count($icon_file);
	$i=$j=0;

	head('iconlist');

?>
<h1>アイコン一覧</h1>
<table border="1">
<tr>
<?php

	for($k=0;$k<$cnt;$k++){
		$i++;$j++;
		echo '<td><img src="'.IMGDIR.$icon_file[$k].'" width="70" height="70" alt="">'.$icon_name[$k];
		if($j != $cnt and $i >= 7){
			echo "</tr>\n<tr>\n";
			$i=0;
		}elseif($j == $cnt){
			if($i == 0){break;}
			while($i < 7){
				echo '<td>';
				$i++;
			}
		}
	}

?>
</tr>
</table>
<p><a href="<?php echo HOME?>">HOME</a> <a href="#" onClick="window.close(); return false;">CLOSE</a></p>
<?php

	foot();
}
//-------------------------------------------------------------------
//	チェック
//-------------------------------------------------------------------
function registcheck($host){
	global $br;

	if($br['stopflg'] == '開始前'){
		error("プログラム開始前です。<br>始まるまでしばらくお待ち下さい。");
	}

	$limit = (REGIST_LIMIT * ADD_AREA) + 1;

	if(mb_ereg('終了',$br['endflg']) or $br['ar'] >= $limit){
		error("プログラムの受付は終了いたしました。<br>次回プログラム開始をお待ち下さい。");
	}

	list($male,$female,$mcl,$fcl,) = explode(",",file_get_contents(MEMBERFILE));

	$listnum = ($mcl+$fcl) * MANMAX + ($male+$female);
	if($listnum >= MAXMEM){//最大人数超過？
		error("申し訳ございませんが、定員(".MAXMEM."人)オーバーです。");
	}

	//同じIPからの登録をチェックしない場合以降の処理をしない
	//死亡後の再登録時間のチェックも行われません
	if(!IP_DENY){
		return;
	}

	//同じIPからの登録をチェック
	//同一ホストからの登録を許可するホスト、IPアドレスかチェック
	$IP_chk = false;
	foreach($br['IP_OK'] as $oklist){
		if(preg_match("/^".preg_quote($oklist)."/",$host)){
			$IP_chk = true;
			break;
		}elseif(preg_match("/".preg_quote($oklist)."$/",$host)){
			$IP_chk = true;
			break;
		}
	}

	//重複、再登録チェック
	//クッキーがある場合、クッキーから名前を取得
	//無い場合は進行ログファイルを調べて同じホストから登録した人がいれば名前を取得
	$entrylist = array();
	$newslist = file(NEWSFILE);
	if(isset($_COOKIE['id'])){
		$cfile = search_userfile($_COOKIE['id'],'id');
		if(!$cfile){
			return;
		}else{
			$c_user = getuserdata($cfile);
			$entrylist[] = array($c_user['f_name'],$c_user['l_name']);
		}
	}else{
		$newslist2 = preg_grep('/,ENTRY,'.preg_quote($host).',/',$newslist);
		if(!$newslist2){
			return;
		}
		$entrylist = array();
		foreach($newslist2 as $news){
			list(,,,,,$f_name,$l_name,) = explode(",",$news);
			$entrylist[] = array($f_name,$l_name);
		}
	}

	$cnt = count($entrylist);

	//死亡のログをチェックし、死亡者の名前が上で作ったリストの中にあれば
	//再登録可能時間をチェック
	$newslist3 = array_reverse(preg_grep('/,DEATH/',$newslist));
	foreach($newslist3 as $news){
		list($logtime,$kind,,,,$f_name,$l_name,) = explode(",",$news);
		if(in_array(array($f_name,$l_name),$entrylist)){
			$chktime = $logtime;
			$cnt--;
		}
	}

	if($cnt >= 1 and !$IP_chk){
		error("キャラクタの複数登録は禁止しています。");
	}

	$registtime = $chktime + (3600 * REGIST_TIME);
	if(NOW <= $registtime){
		$time = date('Y/m/d H:i:s',$registtime);
		error("キャラ死亡後、".REGIST_TIME."時間は再登録出来ません。<br><br>次回登録可能時間：".$time);
	}

}
//-------------------------------------------------------------------
//	メイン処理
//-------------------------------------------------------------------
pgstop();

string_operation($_POST);
$host = hostcheck();
if(!isset($_POST['mode'])){
	if(isset($_GET['mode']) and $_GET['mode'] == "icon"){
		iconlist();
	}else{
		registcheck($host);
		enter();
	}
}elseif($_POST['mode'] == "regist"){
	registcheck($host);
	regist($host);
}elseif($_POST['mode'] == "opening"){
	opening();
}else{
	registcheck($host);
	enter();
}