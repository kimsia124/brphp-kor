<?php
//--------------------------------------------------------------------
//	アイテム合成
//--------------------------------------------------------------------
function gousei(&$user){
	global $br;

	$wk = $wk2 = array();

	foreach($_POST['GOUSEI'] as $i){
		if(!empty($user['item'][$i])){
			$wk[] = $i;
			list($wk2[],) = explode("<>",$user['item'][$i]);
		}
	}

	if(count($wk) < 2){
		$user['log'] .= "アイテムが正しく選択されていません。<br>";
		$_POST['Command'] = "MAIN";
		return;
	}

	$wk2 = array_unique($wk2);
	$cnt = count($wk2);
	if(count($wk) != $cnt){
		$user['log'] .= "合成に同じアイテムは選択できません。<br>";
		$_POST['Command'] = "MAIN";
		return;
	}

	$chk = false;
	foreach($wk as $i){
		if($user['itai'][$i] == 1 or $user['itai'][$i] == "∞" or preg_match("/<>[WDA]/",$user['item'][$i])){
			$chk = $i;
			break;
		}
	}
	if($chk === false){
		for($i=0;$i<ITEMMAX;$i++){
			if($user['item'][$i] == ""){
				$chk = $i;
				break;
			}
		}
		if($chk === false){
			$user['log'] .= "それ以上デイパックに入りません。<br>";
			$_POST['Command'] = "MAIN";
			return;
		}
	}

	$chk2 = false;
	$g_table = gousei_tbl($cnt);
	$str = str_repeat('$table[],',$cnt);
	if($g_table){
		foreach($g_table as $g_list){
			$table = array();
			eval('list('.$str.'$name,$kind,$eff,$itai) = $g_list;');
			if(!array_diff($wk2,$table)){
				$chk2 = true;
				break;
			}
		}
	}

	if(!$chk2){
		$user['log'] .= implode("と",$wk2).'は組み合わせられないな。<br>';
		$_POST['Command'] = "MAIN";
		return;
	}

	$user['log'] .= implode("と",$wk2).'で'.$name.'が出来た！<br>';
	$user['item'][$chk] = $name."<>".$kind;
	$user['eff'][$chk] = $eff;
	$user['itai'][$chk] = $itai;

	foreach($wk as $i => $j){
		if($chk != $j){
			if(preg_match("/<>[WDA]/",$user['item'][$j]) or --$user['itai'][$j] <= 0){
				$user['item'][$j] = "";
				$user['eff'][$j] = $user['itai'][$j] = 0;
			}
		}
	}

	save($user);
	$_POST['Command'] = 'MAIN';
}
//------------------------------------------------------------------------------
//	合成テーブル
//------------------------------------------------------------------------------
function gousei_tbl($cnt){

	//効果や回数が∞等、数字でない場合は"∞"の様に「" "」で括る。

	if($cnt == 2){
		//２対合成用
		$g_table = array(
		//array("材料1",		,"材料2",		,"完成品名"			,"属性"	,"効果"	,"回数"),
			array("携帯電話"	,"パワーブック"	,"モバイルPC"		,"Y"	,1		,0 ),
			array("スプレー缶"	,"ライター"		,"簡易火炎放射器"	,"WD"	,15		,10),
			array("ガソリン"	,"空き瓶"		,"火炎瓶"			,"WD"	,25		,5 ),
			array("水"			,"粉末ジュース"	,"ジュース"			,"HH"	,50		,1 ),
		);
	}elseif($cnt == 3){
		//３対合成用
		$g_table = array(
		//array("材料1","材料2","材料3"	,"完成品名"		,"属性"	,"効果"	,"回数"),
			array("軽油","肥料","導火線","ダイナマイト"	,"WD"	,45		,5),
			array("軽油","肥料","信管"	,"爆弾"			,"WD"	,60		,3),
		);
	}else{
		$g_table = false;
	}

	return $g_table;
}