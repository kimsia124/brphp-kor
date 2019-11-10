<?php
/*--------------------------------------------------------------------
 *  会場地図
 *------------------------------------------------------------------*/
require 'pref.php';

$tate = range('A', 'J');
$yoko = array('０１','０２','０３','０４','０５','０６','０７','０８','０９','１０');

if($br['hackflg'] or $br['stopflg'] == '未初期化'){
	$dead = $next = array();
}else{
	$dead = array_slice($br['ara'],0,$br['ar']);
	$next = array_slice($br['ara'],$br['ar'],ADD_AREA);
}

head('map');

print <<<HTML
<h1>会場簡易地図</h1>
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
		}elseif(in_array($i,$dead)){
			echo '<td class="dead">'.$br['PLACE'][$i];
		}elseif(in_array($i,$next)){
			echo '<td class="caution">'.$br['PLACE'][$i];
		}else{
			echo '<td>'.$br['PLACE'][$i];
		}
	}
	echo "</tr>\n";
}

echo '</tbody></table><p><a href="'.HOME.'">HOME</a></p>';
foot();