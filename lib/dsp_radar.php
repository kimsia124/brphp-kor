<?php
//--------------------------------------------------------------------
//	レーダー画面
//--------------------------------------------------------------------
function radar($user,$wk=false) {
	global $br;

	if($wk !== false){
		if(!preg_match("/<>R/",$user['item'][$wk])){error("不正アクセスです。");}
	}

	$tate = range('A','J');
	$yoko = array('01','02','03','04','05','06','07','08','09','10');

	$cnt = array_fill(0,$br['arcnt'],0);

	//typeがNPC0(ボスNPC)とNPC2(進行に影響を与えないNPC)はレーダーに写らない
	$a = glob(U_DATADIR."*".U_DATAFILE);
	foreach($a as $filename){
		$mem = getuserdata($filename);
		if($mem['hit'] > 0 and in_array($mem['type'] , array('PC','NPC'))){
			$cnt[$mem['pls']]++;
		}
	}

	//全エリア対応のレーダーでない場合、使用者のいるエリア以外は人数を表示しない
	if(!preg_match("/<>R2/",$user['item'][$wk])){
		$cnt2 = array_fill(0,$br['arcnt'],'');
		$cnt2[$user['pls']] = $cnt[$user['pls']];
		$cnt = $cnt2;
	}

	$dead = ($br['hackflg'])? array():array_slice($br['ara'],0,$br['ar']);
	$radar = array_fill_keys($dead,'×') + $cnt;

	head('battle');

	echo '<h1>'.$br['PLACE'][$user['pls']].'('.$br['AREA'][$user['pls']].')</h1>';
	echo LINKS;
	print <<<HTML
<div id="RADAR">
<table border="1">
<thead>
<tr><th>
HTML;

	while(list($key,) = each($br['MAP'][0])){
		echo '<th>'.$yoko[$key];
	}

	print <<<HTML
</tr>
</thead>
<tbody>
HTML;

	while (list($key,) = each($tate)) {
		echo '<tr><th>'.$tate[$key];
		foreach ($br['MAP'][$key] as $i) {
			if($i === -1){
				echo '<td class="sea">';
			}elseif($i === -2){
				echo '<td>';
			}elseif($i == $user['pls']){
				echo '<td class="self">'.$radar[$i];
			}elseif($radar[$i] === '×'){
				echo '<td class="dead">×';
			}else{
				echo '<td>'.$radar[$i];
			}
		}
		echo "</tr>\n";
	}

	print <<<HTML
</tbody>
</table>
<dl id="COMMAND">
	<dt>コマンド</dt>
	<dd><form method="post" name="br">
		<input type="hidden" name="id" value="{$_POST['id']}">
		<input type="hidden" name="pass" value="{$_POST['pass']}">
HTML;

	$_POST['Command'] = "MAIN";
	require LIBDIR.'command.php';
	command($user);

	print <<<HTML
	</form></dd>
</dl>
<dl id="LOG">
	<dt>ログ</dt>
	<dd>{$user['log']}</dd>
</dl>
</div>
HTML;

	foot();
}
