<?php
//--------------------------------------------------------------------
//	ステータス画面
//--------------------------------------------------------------------
function status($user){
	global $br,$mem;

	//回復コマンド解除
	if(!preg_match("/^(SLEEP|HEAL|REST)$/",$_POST['Command'])){
		heal_result($user,true);
	}

	$up = ($user['level'] * 2 - 1) * BASEEXP;

	//装備品による補正
	if(preg_match("/<>WG|<>WA/",$user['equip'][0]) and $user['eqtai'][0] == 0){
		$watt = round($user['eqeff'][0]/10);
	}else{
		$watt = $user['eqeff'][0];
	}
	$bdef = $user['eqeff'][1]+$user['eqeff'][2]+$user['eqeff'][3]+$user['eqeff'][4];
	if(preg_match("/<>AD/",$user['equip'][5])){
		$bdef += $user['eqeff'][5];
	}

	if($user['equip'][0] == ""){
		$wep = "素手";$user['eqtai'][0] = "∞";
	}else{
		list($wep) = explode("<>",$user['equip'][0]);
	}
	if($user['equip'][1] == ""){
		$bou = "下着";$user['eqtai'][1] = "∞";
	}else{
		list($bou) = explode("<>",$user['equip'][1]);
	}

	//負傷表示
	$kega = "";
	if(mb_ereg("頭",$user['inf'])){$kega .= "頭 ";}
	if(mb_ereg("腕",$user['inf'])){$kega .= "腕 ";}
	if(mb_ereg("腹",$user['inf'])){$kega .= "腹 ";}
	if(mb_ereg("足",$user['inf'])){$kega .= "足 ";}

	//熟練度
	$p_wa = (int)($user['wa']/BASE);
	$p_wg = (int)($user['wg']/BASE);
	$p_wc = (int)($user['wc']/BASE);
	$p_wd = (int)($user['wd']/BASE);
	$p_wn = (int)($user['wn']/BASE);
	$p_ws = (int)($user['ws']/BASE);
	$p_wb = (int)($user['wb']/BASE);
	$p_wp = (int)($user['wp']/BASE);

	//禁止エリア表示
	$kin = implode(" ",array_slice($br['kin_ar'],$br['ar'],4));

	head('battle');
	$icon_path = IMGDIR.$user['icon'];
	$maxsta = MAXSTA;//ヒアドキュメント内に定数入れられないから苦肉の策
	
	echo '<h1>'.$br['PLACE'][$user['pls']].'('.$br['AREA'][$user['pls']].')</h1>';
	echo LINKS;
	print <<<HTML
<div id="STATUS">
<table border="1" id="PC">
<tbody>
	<tr>
		<th colspan="6">ステータス
	</tr>
	<tr>
		<td rowspan="4"><img src="{$icon_path}" width="70" height="70" alt="">
		<th>氏名
		<td>{$user['f_name']} {$user['l_name']}
		<th>レベル
		<td colspan="2">{$user['level']}
	</tr>
	<tr>
		<th>出席番号
		<td>{$user['cl']} {$user['sex']}{$user['no']}番
		<th>経験値
		<td colspan="2">{$user['exp']}/{$up}
	</tr>
	<tr>
		<th>体力
		<td>{$user['hit']}/{$user['mhit']}
		<th>攻撃力
		<td colspan="2">{$user['att']}+{$watt}
	</tr>
	<tr>
		<th>スタミナ
		<td>{$user['sta']}/{$maxsta}
		<th>防御力
		<td colspan="2">{$user['def']}+{$bdef}
	</tr>
	<tr>
		<th>負傷個所
		<td colspan="5" class="wound">{$kega}
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
		<td>{$user['eqtai'][0]}
	</tr>
	<tr>
		<th>体防具
		<td colspan="3">{$bou}
		<td>{$user['eqeff'][1]}
		<td>{$user['eqtai'][1]}
	</tr>
HTML;

	$eq = array(2=>"頭防具",3=>"腕防具",4=>"足防具",5=>"装飾品");
	for($i=2;$i<6;$i++){
		if($user['equip'][$i] == ""){
			$e_name = "なし";
		}else{
			list($e_name) = explode("<>", $user['equip'][$i]);
		}
		echo '<tr><th>'.$eq[$i];
		echo '<td colspan="3">'.$e_name;
		echo '<td>'.$user['eqeff'][$i];
		echo '<td>'.$user['eqtai'][$i];
		echo "</tr>\n";
	}

?>
	<tr>
		<th colspan="6">所持品</th>
	</tr>
<?php

	for($i=0;$i<ITEMMAX;$i++){
		if($user['item'][$i] == ""){
			$i_name = "なし";
		}else{
			list($i_name) = explode("<>", $user['item'][$i]);
		}
		echo '<tr><td colspan="4">'.$i_name;
		echo '<td>'.$user['eff'][$i];
		echo '<td>'.$user['itai'][$i];
		echo "</tr>\n";
	}

?>
</tbody>
</table>
<dl id="COMMAND">
	<dt>コマンド</dt>
	<dd><form method="post" name="br">
		<input type="hidden" name="id" value="<?php echo $_POST['id']?>">
		<input type="hidden" name="pass" value="<?php echo $_POST['pass']?>">

<?php

	require LIBDIR.'command.php';
	command($user);

	print <<<HTML
	</form></dd>
</dl>
<dl id="LOG">
	<dt>ログ</dt>
	<dd>{$user['log']}</dd>
</dl>
<dl id="PGINFO">
	<dt>情報</dt>
	<dd>日時：{$br['month']}月{$br['mday']}日({$br['wday']}){$br['hour']}：{$br['min']}：{$br['sec']}<br>
	熟練度[所属クラブ：{$user['club']}]<br>
	射：{$p_wa}({$user['wa']}) 銃：{$p_wg}({$user['wg']}) 投：{$p_wc}({$user['wc']}) 爆：{$p_wd}({$user['wd']})<br>
	斬：{$p_wn}({$user['wn']}) 刺：{$p_ws}({$user['ws']}) 棍：{$p_wb}({$user['wb']}) 殴：{$p_wp}({$user['wp']})<br>
	基本方針：{$user['tactics']}　応戦方針：{$user['ousen']}<br>
	生存者：{$mem}人　殺害人数：{$user['kill']}人<br>
	次回禁止エリア：<br><span class="caution">{$kin}</span></dd>
</dl>
</div>
HTML;

	foot();
}
