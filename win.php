<?php
/*--------------------------------------------------------------------
 *	優勝者一覧
 *------------------------------------------------------------------*/
require 'pref.php';
//--------------------------------------------------------------------
//	優勝者一覧
//--------------------------------------------------------------------
function winnerlist(){

	head();
?>
<h1>優勝者一覧</h1>
<table border="1">
<thead>
<tr>
<th>閲覧</th>
<th>優勝者決定日</th>
<th>優勝者名</th>
<th>結果</th>
</tr>
</thead>
<tbody>
<?php

	if(file_exists(WINFILE)){
		$winlist = file(WINFILE);
		$chk = count($winlist);
		if($chk != 0){
			for($i=0;$i<$chk;$i++){
			list($date,$log,$f_name,$l_name,) = explode(",",$winlist[$i]);
			echo '<tr><td><a href="win.php?no='.$i.'">閲覧</a><td>'.$date.'<td>'.$f_name.' '.$l_name.'<td>'.$log."</tr>\n";
			}
		}
	}

?>
</tbody>
</table>
<p><a href="<?php echo HOME?>">HOME</a></p>
<?php
	foot();
}
//------------------------------------------------------------------------------
//	優勝者表示
//------------------------------------------------------------------------------
function winnerview(){

	head();

	$winners = "";
	$cnt = 0;

	$windata = file(WINFILE);

	if(!isset($windata[$_GET['no']])){//存在しないデータを呼び出した
		winnerlist();
	}

	list($date,$log,$f_name,$l_name,$sex,$cl,$no,$icon,$club,$g_name,$g_pass,$pls,$level,$exp,$hit,$mhit,$sta,$maxsta,$att,$def,$kill,$tactics,$ousen,$equip[0],$eqeff[0],$eqtai[0],$equip[1],$eqeff[1],$eqtai[1],$equip[2],$eqeff[2],$eqtai[2],$equip[3],$eqeff[3],$eqtai[3],$equip[4],$eqeff[4],$eqtai[4],$equip[5],$eqeff[5],$eqtai[5],$itemmax,$u_item,$wa,$wg,$wc,$wd,$wn,$ws,$wb,$wp,$sts,$inf,$com,$kmes,$dmes,$baseexp,$base) = explode(",",$windata[$_GET['no']]);

	$up = ($level * 2 - 1) * $baseexp;

	//装備品による補正
	if(preg_match("/<>WG|<>WA/",$equip[0]) and $eqtai[0] == 0){
		$watt = round($eqeff[0]/10);
	}else{
		$watt = $eqeff[0];
	}
	$bdef = $eqeff[1]+$eqeff[2]+$eqeff[3]+$eqeff[4];
	if(preg_match("/<>AD/",$equip[5])){
		$bdef += $eqeff[5];
	}

	if($equip[0] == ""){
		$wep = "素手";$eqtai[0] = "∞";
	}else{
		list($wep) = explode("<>",$equip[0]);
	}
	if($equip[1] == ""){
		$bou = "下着";$eqtai[1] = "∞";
	}else{
		list($bou) = explode("<>",$equip[1]);
	}

	//負傷表示
	$kega = "";
	if(mb_ereg("頭",$inf)){$kega .= "頭 ";}
	if(mb_ereg("腕",$inf)){$kega .= "腕 ";}
	if(mb_ereg("腹",$inf)){$kega .= "腹 ";}
	if(mb_ereg("足",$inf)){$kega .= "足 ";}

	//熟練度
	$p_wa = (int)($wa/$base);
	$p_wg = (int)($wg/$base);
	$p_wc = (int)($wc/$base);
	$p_wd = (int)($wd/$base);
	$p_wn = (int)($wn/$base);
	$p_ws = (int)($ws/$base);
	$p_wb = (int)($wb/$base);
	$p_wp = (int)($wp/$base);

	$icon_path = IMGDIR.$icon;
	print <<<HTML
<h1>{$log}：{$f_name} {$l_name}</h1>
<table border="1">
<tbody>
<tr>
	<th colspan="6">ステータス
</tr>
<tr>
	<td rowspan="4"><img src="{$icon_path}" width="70" height="70" alt="">
	<th>氏名
	<td>{$f_name} {$l_name}
	<th>レベル
	<td colspan="2">{$level}
</tr>
<tr>
	<th>出席番号
	<td><{$cl} {$sex}{$no}番
	<th>経験値
	<td colspan="2">{$exp}/{$up}
</tr>
<tr>
	<th>体力
	<td>{$hit}/{$mhit}
	<th>攻撃力
	<td colspan="2">{$att}+{$watt}
</tr>
<tr>
	<th>スタミナ</th>
	<td>{$sta}/{$maxsta}</td>
	<th>防御力</th>
	<td colspan="2">{$def}+{$bdef}
</tr>
<tr>
	<th>負傷個所
	<td colspan="5" class="wound">{$kega}
</tr>
<tr>
	<th>熟練度
	<td colspan="5">
		射：{$p_wa}({$wa}) 銃：{$p_wg}({$wg}) 投：{$p_wc}({$wc}) 爆：{$p_wd}({$wd})
		斬：{$p_wn}({$wn}) 刺：{$p_ws}({$ws}) 棍：{$p_wb}({$wb}) 殴：{$p_wp}({$wp})
</tr>
<tr>
	<th>装備
	<th colspan="3">装備品
	<th>効果
	<th>回数
</tr>
<tr>
	<th>武器
	<td colspan="3">{$wep}
	<td>{$watt}
	<td>{$eqtai[0]}
</tr>
<tr>
	<th>体防具
	<td colspan="3">{$bou}
	<td>{$eqeff[1]}
	<td>{$eqtai[1]}
</tr>
HTML;

	$eq = array(2=>"頭防具",3=>"腕防具",4=>"足防具",5=>"装飾品");
	for($i=2;$i<6;$i++){
		if($equip[$i] == ""){
			$e_name = "なし";
		}else{
			list($e_name) = explode("<>", $equip[$i]);
		}
		echo '<tr>';
		echo '<th>'.$eq[$i];
		echo '<td colspan="3">'.$e_name;
		echo '<td>'.$eqeff[$i];
		echo '<td>'.$eqtai[$i];
		echo "</tr>\n";
	}

?>
<tr>
	<th colspan="6">所持品
</tr>
<?php
	extract(unserialize($u_item));

	for($i=0;$i<$itemmax;$i++){
		if($item[$i] == ""){
			$i_name = "なし";
		}else{
			list($i_name) = explode("<>", $item[$i]);
		}
		echo '<tr>';
		echo '<td colspan="4">'.$i_name;
		echo '<td>'.$eff[$i];
		echo '<td>'.$itai[$i];
		echo "</tr>\n";
	}

?>
</tbody>
</table>
<p><a href="win.php">戻る</a><br>
<a href="<?php echo HOME?>">HOME</a></p>
<?php
	foot();
}
//--------------------------------------------------------------------
//	ＭＡＩＮ
//--------------------------------------------------------------------
if(isset($_GET['no']) and ctype_digit($_GET['no'])){
	winnerview();
}else{
	winnerlist();
}
