<?php
//--------------------------------------------------------------------
//	初期化処理
//--------------------------------------------------------------------
function datareset(){
	global $br;

	//個別データ、バックアップ削除
	if(glob(U_DATADIR."*".U_DATAFILE)){
		foreach(glob(U_DATADIR."*".U_DATAFILE) as $filename){
			unlink($filename);
		}
	}
	if(glob(U_BACKDIR."*".U_BACKFILE)){
		foreach(glob(U_BACKDIR."*".U_BACKFILE) as $filename){
			unlink($filename);
		}
	}

	//NPCの設置
	//NPCの強さは全く調整を行っていないのでそのまま使用することは非推奨
	//そもそも分校NPCしか用意してませんが。
	if(!USE_NPC){
		$count = 0;
	}else{
		$npc = file(DATDIR.'npc.csv');
		$npc = preg_grep('/^##/',$npc,PREG_GREP_INVERT);
		$npc = array_values($npc);
		$count = count($npc);
		for($i=0;$i<$count;$i++){
			$item = array_fill(0,ITEMMAX,'');
			$eff = $itai = array_fill(0,ITEMMAX,0);
			list($type,$f_name,$l_name,$cl,$sex,$no,$icon,$equip[0],$eqeff[0],$eqtai[0],$equip[1],$eqeff[1],$eqtai[1],$equip[2],$eqeff[2],$eqtai[2],$equip[3],$eqeff[3],$eqtai[3],$equip[4],$eqeff[4],$eqtai[4],$equip[5],$eqeff[5],$eqtai[5],$item[0],$eff[0],$itai[0],$item[1],$eff[1],$itai[1],$item[2],$eff[2],$itai[2],$item[3],$eff[3],$itai[3],$item[4],$eff[4],$itai[4],$com,$kmes,$dmes) = explode(",",$npc[$i]);
			//$typeについて
			//$type	|生存者数に入るか|時間切れ時に	|禁止エリアから	|備考
			//NPC	|入る			|死亡する		|移動する		|生き残るには倒す必要有り
			//NPC2	|入らない		|死亡しない		|移動する		|倒す必要は無い
			//NPC0	|入らない		|死亡しない		|移動しない		|担任等の分校にいるNPC
			if($type == 'NPC0'){
				$att = mt_rand(40,60);
				$def = mt_rand(40,60);
				$hit = $mhit = mt_rand(450,500);
				$level = 20;
				$pls = 0;
				$wa=$wg=$wc=$wd=$wn=$ws=$wb=$wp = BASE * 10;
			}elseif($type == 'NPC2'){
				$att = mt_rand(30,50);
				$def = mt_rand(30,50);
				$hit = $mhit = mt_rand(250,300);
				$level = 10;
				$pls = mt_rand(1,$br['arcnt']-1);
				$wa=$wg=$wc=$wd=$wn=$ws=$wb=$wp = BASE * 5;
			}elseif($type == 'NPC'){
				$att = mt_rand(20,30);
				$def = mt_rand(20,30);
				$hit = $mhit = mt_rand(100,150);
				$level = 5;
				$pls = mt_rand(1,$br['arcnt']-1);
				$wa=$wg=$wc=$wd=$wn=$ws=$wb=$wp = BASE * 1;
			}
			$id = NPCID.$i;
			$pass = NPCPASS.$i;
			$club = "";
			$exp = ($level >= 2)? (int)($level*2-3)*BASEEXP : 0;
			$sta = MAXSTA;
			$icon = $br['N_ICON_FILE'][$icon];
			$kill = 0;
			$endtime = 0;
			$tactics = $ousen = "通常";
			$sts = "正常";
			$g_name = $g_pass = "";
			$ip = $bb = $bid = $bg_name = "";
			$inf = $death = $log = "";

			$u_item = serialize(array('item' => $item , 'eff' => $eff , 'itai' => $itai));

			$file = U_DATADIR.$id."_{$i}_".U_DATAFILE;
			file_put_contents($file,"$i,$id,$pass,$type,$f_name,$l_name,$sex,$cl,$no,$icon,$club,$g_name,$g_pass,$pls,$level,$exp,$hit,$mhit,$sta,$att,$def,$kill,$tactics,$ousen,$equip[0],$eqeff[0],$eqtai[0],$equip[1],$eqeff[1],$eqtai[1],$equip[2],$eqeff[2],$eqtai[2],$equip[3],$eqeff[3],$eqtai[3],$equip[4],$eqeff[4],$eqtai[4],$equip[5],$eqeff[5],$eqtai[5],$u_item,$wa,$wg,$wc,$wd,$wn,$ws,$wb,$wp,$ip,$bb,$bid,$bg_name,$sts,$inf,$endtime,$death,$com,$kmes,$dmes,$log,");

		}//for
	}

	//時間ファイル更新
	file_put_contents(TIMEFILE,'0');

	//生徒番号ファイル更新
	file_put_contents(MEMBERFILE,"0,0,0,0,0,{$count},");

	//禁止エリアファイル更新
	$ar = 1;
	$start_date = '';
	if(isset($_POST['TIME']) and $_POST['TIME'] == "next"){//翌日開始
		$chk = mktime(0,0,0,$br['month'],$br['mday'],$br['year']);
		$chk += 86400;
		$start_date = '翌日0時';
		$ar = 0;
	}elseif(isset($_POST['TIME']) and $_POST['TIME'] == "config"){//開始時間指定
		if(isset($_POST['year'],$_POST['month'],$_POST['day'],$_POST['hour'])){
			$chk = mktime($_POST['hour'],0,0,$_POST['month'],$_POST['day'],$_POST['year']);
			if($chk <= NOW){//今より前の時間を指定した場合はすぐに開始
				$chk = mktime(0,0,0,$br['month'],$br['mday'],$br['year']);
				$chk += 86400;
			}else{
				$start_date = date("n月j日 G時",$chk);
				$ar = 0;
			}
		}else{//開始時間が正しく指定されていない場合もすぐ開始
			$chk = mktime(0,0,0,$br['month'],$br['mday'],$br['year']);
			$chk += 86400;
		}
	}else{
		$chk = mktime(0,0,0,$br['month'],$br['mday'],$br['year']);
		$chk += 86400;
	}

	//禁止エリア追加時間、禁止エリア数
	$areadata[0] = $chk.",".$ar.",";

	$areadata[1] = $br['PLACE'][0].",";
	$areadata[2] = "0,";

	$numbers = range(1,$br['arcnt']-1);
	shuffle($numbers);

	while(list(,$number) = each($numbers)){
		$areadata[1] .= $br['PLACE'][$number].",";
		$areadata[2] .= $number.",";
	}

	file_put_contents(AREAFILE,implode("\n",$areadata)."\n");

	//アイテムファイル更新
	$aritem = array_fill(0,$br['arcnt'],'');
	$area_list_all = range(1,$br['arcnt']-1);

	foreach(file(DATDIR.'item.csv') as $areaitem){
		if(preg_match('/^##/',$areaitem)){//コメント行(「##」で始まる行)は無視する
			continue;
		}
		list($idx,$cnt,$item,$eff,$itai) = explode(",",$areaitem);
		if($idx == 99 or preg_match('/\|/',$idx)){
			//設置するエリアを複数から一つ選ぶタイプ(99を指定するか"|"で区切っているもの)
			//99はエリア番号に"1|2|3|(中略)|全エリア数-1(デフォルトでは28)"を指定するのと等しい
			$area_list = ($idx == 99 or preg_match('/99/',$idx))? $area_list_all:array_map('intval',explode('|',$idx));
			$listc = count($area_list);
			for($i=0;$i<$cnt;$i++){
				list($eff2,$itai2) = get_areaitem_data($eff,$itai);
				$aritem[$area_list[mt_rand(0,$listc-1)]] .= $item.",".$eff2.",".$itai2.",\n";
			}
		}elseif(preg_match('/&/',$idx)){
			//複数のエリアに設置するタイプ("&"で区切っているもの)
			$area_list = array_map('intval',explode('&',$idx));
			foreach($area_list as $ipls){
				for($i=0;$i<$cnt;$i++){
					list($eff2,$itai2) = get_areaitem_data($eff,$itai);
					$aritem[$ipls] .= $item.",".$eff2.",".$itai2.",\n";
				}
			}
		}else{
			//設置エリアを一つだけ指定しているもの
			for($i=0;$i<$cnt;$i++){
				list($eff2,$itai2) = get_areaitem_data($eff,$itai);
				$aritem[$idx] .= $item.",".$eff2.",".$itai2.",\n";
			}
		}
	}

	for($i=0;$i<$br['arcnt'];$i++){
		file_put_contents(ITEMDIR.$i.ITEMFILE,$aritem[$i]);
	}

	//進行ログファイル、FLAGファイル更新
	if($ar == 0){
		$reg = (isset($_POST['REGIST']))? 1:0;
		file_put_contents(NEWSFILE,NOW.",PREPARATION,".$start_date.",$reg,,,,,,,,,,,,\n");
		if(isset($_POST['REGIST'])){
			file_put_contents(FLAGFILE,'0,,開始前登録,');
		}else{
			file_put_contents(FLAGFILE,'0,,開始前,');
		}
	}else{
		file_put_contents(NEWSFILE,NOW.",NEWGAME,,,,,,,,,,,,,,\n");
		file_put_contents(FLAGFILE,'0,,,');
	}

	//銃声ログファイル更新
	file_put_contents(SOUNDFILE,array_fill(0,4,"0,,,,\n"));

	//メッセンジャーのログ削除
	file_put_contents(MES_FILE,'');
	file_put_contents(MES_MEMFILE,'');

	return $start_date;
}
//--------------------------------------------------------------------
//	初期化時のアイテムデータ決定
//	2.2.0からitem.csvで使用できる記述法が増えたためそれに対応するためのもの
//	記述の仕方でアイテムの持つ効果、回数を初期化時にランダムにして配置することが出来ます
//	ハイフンで区切られる場合は範囲
//	例えば効果に10-20と指定した場合、10以上20以下の数字の中から選ばれる
//	「|」で区切られた場合、その中から一つ選ばれる
//	例えば10|20|30と指定した場合、10か20か30の中から選ばれる
//--------------------------------------------------------------------
function get_areaitem_data($eff,$tai){
	$eff2 = get_areaitem_sts($eff);
	$tai2 = get_areaitem_sts($tai);

	return array($eff2,$tai2);
}
function get_areaitem_sts($sts){
	if (preg_match('/\-/',$sts)) {
		$list = array_map('intval',explode('-',$sts));
		$sts2 = ($list[0] > $list[1])? mt_rand($list[1],$list[0]):mt_rand($list[0],$list[1]);
	}elseif(preg_match('/\|/',$sts)) {
		$list = array_map('intval',explode('|',$sts));
		$sts2 = $list[mt_rand(0,count($list) - 1)];
	}elseif($sts === '∞'){
		$sts2 = '∞';
	}else{
		$sts2 = (int)$sts;
	}

	return $sts2;
}