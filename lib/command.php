<?php
//--------------------------------------------------------------------
//	各種コマンド表示
//--------------------------------------------------------------------
function command(&$user){
	global $br;

	if(!isset($_POST['Command']) or $_POST['Command'] == "MAIN"){
		//音ログ
		$soundlist = file(SOUNDFILE);
		//銃声
		list($gtime,$gpls,$wid,$wid2) = explode(",",$soundlist[0]);
		if($user['id'] != $wid and $user['id'] != $wid2 and NOW < $gtime + 15){
			$user['log'] .= '<span class="caution">'.$br['PLACE'][$gpls].'の方で、銃声が聞こえた･･･。</span><br>';
		}
		//爆音
		list($gtime,$gpls,$wid,$wid2) = explode(",",$soundlist[1]);
		if($user['id'] != $wid and $user['id'] != $wid2 and NOW < $gtime + 15){
			$user['log'] .= '<span class="caution">'.$br['PLACE'][$gpls].'の方で、爆音が聞こえた･･･。</span><br>';
		}
		//悲鳴
		list($gtime,$gpls,$wid,$wid2) = explode(",",$soundlist[2]);
		if($user['pls'] == $gpls and NOW < $gtime + 15){
			if($user['id'] != $wid and $user['id'] != $wid2){
				$user['log'] .= '<span class="caution">近くで悲鳴が？誰か、殺されたのか･･･？</span><br>';
			}
		}
		//スピーカー
		list($gtime,$gpls,$wid,$wid2) = explode(",",$soundlist[3]);
		if($wid != $user['f_name']." ".$user['l_name'] and NOW < $gtime + 30){
			$user['log'] .= '<span class="caution">'.$br['PLACE'][$gpls].'の方から'.$wid.'の声が聞こえる･･･</span><br><span class="msg">『'.$wid2.'』</span><br>';
		}

		$user['log'] .= "さて、どうしよう・・・。<br>";
		echo "何を行いますか？<br><br>\n";

		//禁止エリアは移動先の選択肢から外す処理
		$dead_area = ($br['hackflg'])? array():array_slice($br['kin_ar'],0,$br['ar']);

		$x = 0;
		echo '<label><input type="radio" name="Command" value="MOVE" checked>移動：</label><select name="Command1" onclick="sl('.$x.')">';
		$move = array_diff($br['PLACE'], $dead_area, array($br['PLACE'][$user['pls']]));
		foreach($move as $no => $place){
			echo '<option value="'.$no.'">'.$place;
		}
		echo "</select><br>\n";

		$x++;
		if($user['pls'] != 0 or $br['hackflg']){
			echo '<label><input type="radio" name="Command" value="SEARCH">探索</label><br>';;

			$x++;
			echo '<label><input type="radio" name="Command" value="HEALCMD">回復：</label><select name="Command2" onclick="sl('.$x.')">';
			echo '<option value="HEAL_0">睡眠';
			echo '<option value="HEAL_1">治療';
			echo '<option value="HEAL_2">休憩';
			echo "</select><br>\n";
			$x++;
		}

		echo '<label><input type="radio" name="Command" value="ITEM">道具：</label><select name="Command3" onclick="sl('.$x.')">';
		echo '<option value="USE">使用・装備';
		echo '<option value="DEL">投棄';
		echo '<option value="SEIRI">整理';
		if(G_MODE and $user['g_name'] != ''){
			echo '<option value="SEND">譲渡';
		}
		if(preg_match("/<>WG|<>WA/",$user['equip'][0]) and $user['eqtai'][0] > 0){
			echo '<option value="DELSHOT">取り出し';
		}
		echo '<option value="GOUSEI">合成';
		$eqkind = array("武器","体防具","頭防具","腕防具","足防具","装飾品");
		for($i=0;$i<6;$i++){
			if($user['equip'][$i] != ""){
				echo '<option value="EQDEL_'.$i.'">'.$eqkind[$i].'を外す';
			}
		}
		echo "</select><br>\n";

		$x++;
		echo '<label><input type="radio" name="Command" value="SPECIAL">特殊：</label><select name="Command4" onclick="sl('.$x.')">';
		echo '<option value="OUKYU">応急処置';
		echo '<option value="TACTICS">基本方針';
		echo '<option value="OUSEN">応戦方針';
		echo '<option value="COMMENT">口癖変更';
		if(G_MODE){
			echo '<option value="GROUP">グループ';
		}
		if($user['club'] == "料理研究部"){
			echo '<option value="PSCHECK">毒見';
		}
		if(in_array("毒薬<>Y",$user['item'])){
			echo '<option value="POISON">毒物混入';
		}
		if(in_array("中和剤<>Y",$user['item'])){
			echo '<option value="PCHUWA">毒中和';
		}
		if(in_array("携帯スピーカ<>Y",$user['item'])){
			echo '<option value="SPEAKER">スピーカ使用';
		}
		for($i=0;$i<ITEMMAX;$i++){
			if($user['item'][$i] == "モバイルPC<>Y" and $user['itai'][$i] >= 1){
				echo '<option value="HACK">ハッキング';
				break;
			}
		}
		echo "</select><br><br>\n";
		echo '　<input type="submit" name="Enter" value="決定">';
	}elseif(in_array($_POST['Command'],array('SLEEP','HEAL','REST'))){
		backsave($user);//バックアップ保存
		if($_POST['Command'] == "SLEEP"){
			$user['log'] .= "少し寝ておくか。<br>";
			$user['sts'] = "睡眠";
		}elseif($_POST['Command'] == "HEAL"){
			$user['log'] .= "怪我の治療をしよう。<br>";
			$user['sts'] = "治療";
		}else{
			$user['log'] .= "少し休んでおこう。<br>";
			$user['sts'] = "休憩";
		}
		echo $user['sts']."中・・・。<br><br>\n";
		echo '<label><input type="radio" name="Command" value="'.$_POST['Command'].'" checked>'.$user['sts'].'続行</label><br><br>';
		echo '<label><input type="radio" name="Command" value="MAIN">'.$user['sts'].'解除<br><br>';
		echo '　<input type="submit" name="Enter" value="決定">';
	}elseif($_POST['Command'] == "ITEM"){
		//アイテムコマンド関連--------------------------------------------------------
		if($_POST['Command3'] == "USE"){
			//アイテム使用
			$user['log'] .= "デイパックの中には、何が入っていたかな・・・。<br>";
			echo "何を使用しますか？<br><br>\n";
			for($i=0;$i<5;$i++){
				echo '<select name="USE[]">';
				echo '<option value="MAIN" selected>止める';
				for($j=0;$j<ITEMMAX;$j++){
					if($user['item'][$j] != ""){
						list($in,$ik) = explode("<>",$user['item'][$j]);
						echo '<option value="'.$j.'">'.$in.'/'.$user['eff'][$j].'/'.$user['itai'][$j];
					}
				}
				echo "</select><br><br>\n";
			}
			echo '　<button type="submit" name="Command" value="ITEMUSE">決定</button>';
		}elseif($_POST['Command3'] == "DEL"){
			//アイテム投棄
			$user['log'] .= "いらないものを捨ててデイパックの中を整理するか・・・。<br>";
			echo "何を捨てますか？<br><br>\n";
			for($i=0;$i<ITEMMAX;$i++){
				if($user['item'][$i] != ""){
					list($in,$ik) = explode("<>", $user['item'][$i]);
					echo '<label><input type="checkbox" name="DEL[]" value="'.$i.'">'.$in.'/'.$user['eff'][$i].'/'.$user['itai'][$i].'</label><br>';
				}
			}
			echo '<br>　<button type="submit" name="Command" value="ITEMDEL">決定</button>';
		}elseif($_POST['Command3'] == "SEIRI"){
			//アイテム整理
			$user['log'] .= "デイパックの中を整理するか・・・。<br>";
			echo "何と何を纏めますか？<br><br>\n";
			for($i=1;$i<=2;$i++){
				echo '<select name="SEIRI[]">';
				echo '<option value="MAIN" selected>止める';
				for($j=0;$j<ITEMMAX;$j++){
					if($user['item'][$j] != ""){
						list($in,$ik) = explode("<>",$user['item'][$j]);
						echo '<option value="'.$j.'">'.$in.'/'.$user['eff'][$j].'/'.$user['itai'][$j];
					}
				}
				echo "</select><br><br>\n";
			}
			echo '　<button type="submit" name="Command" value="ITEMSEIRI">決定</button>';
		}elseif($_POST['Command3'] == "GOUSEI"){
			//アイテム合成
			$user['log'] .= "今持っているものを組み合わせて、何かできないだろうか・・・。<br>";
			echo "合成に使うアイテムを選んでください。<br><br>\n";
			for($i=1;$i<=3;$i++){
				echo '<select name="GOUSEI[]">';
				echo '<option value="MAIN" selected>止める';
				for($j=0;$j<ITEMMAX;$j++){
					if($user['item'][$j] != ""){
						list($in,$ik) = explode("<>",$user['item'][$j]);
						echo '<option value="'.$j.'">'.$in.'/'.$user['eff'][$j].'/'.$user['itai'][$j];
					}
				}
				echo "</select><br><br>\n";
			}
			echo '　<button type="submit" name="Command" value="GOUSEI">決定</button>';
		}elseif($_POST['Command3'] == "SEND"){
			//アイテム譲渡
			$user['log'] .= "アイテムを仲間に渡そう。<br>\n";
			echo "誰に何を渡しますか？<br><br>\n";
			echo '<label><input type="radio" name="Command" value="MAIN" checked>戻る</label><br><br>';
			for($i=0;$i<ITEMMAX;$i++){
				if($user['item'][$i] != ""){
					list($in,$ik) = explode("<>",$user['item'][$i]);
					echo '<label><input type="radio" name="Command" value="SEND_'.$i.'">'.$in.'/'.$user['eff'][$i].'/'.$user['itai'][$i].'</label><br>';
				}
			}
			echo "<br><br>\n";
			echo '<select name="TARGET">';
			echo '<option value="MAIN" selected>渡す相手';
			$a = glob(U_DATADIR."*".U_DATAFILE);
			foreach($a as $filename){
				$w_user = getuserdata($filename);
				if($w_user['id'] != $user['id'] and $w_user['pls'] == $user['pls'] and $w_user['g_name'] == $user['g_name'] and $w_user['g_pass'] == $user['g_pass'] and !preg_match("/NPC/",$w_user['type']) and $w_user['hit'] > 0){
					echo '<option value="'.$w_user['number'].'">'.$w_user['f_name'].' '.$w_user['l_name'];
				}
			}
			echo "</select><br><br>\n";
		echo '　<input type="submit" name="Enter" value="決定">';
		}else{
			echo "何を行いますか？<br><br>\n";
			echo '　<button type="submit" name="Command" value="MAIN">戻る</button>';
		}
	}elseif($_POST['Command'] == "SPECIAL"){
		//特殊コマンド関連------------------------------------------------
		if($_POST['Command4'] == "OUKYU"){
			//応急処置
			$user['log'] .= "怪我の治療をするか・・・。<br>";

			echo "何処を治療しますか？<br><br>\n";
			echo '<label><input type="radio" name="Command" value="MAIN" checked>戻る</label><br><br>';

			if(mb_ereg("頭",$user['inf'])){
				echo '<label><input type="radio" name="Command" value="OUK_0">頭</label><br>';
			}
			if(mb_ereg("腕",$user['inf'])){
				echo '<label><input type="radio" name="Command" value="OUK_1">腕</label><br>';
			}
			if(mb_ereg("腹",$user['inf'])){
				echo '<label><input type="radio" name="Command" value="OUK_2">腹部</label><br>';
			}
			if(mb_ereg("足",$user['inf'])){
				echo '<label><input type="radio" name="Command" value="OUK_3">足</label><br>';
			}

			echo "<br>\n";
			echo '　<input type="submit" name="Enter" value="決定">';
		}elseif($_POST['Command4'] == "TACTICS"){
			//基本方針
			$user['log'] .= "基本方針を決めるか・・・。<br>";
			echo "基本方針を選んでください。<br><br>\n";
			echo '<label><input type="radio" name="Command" value="MAIN" checked>戻る</label><br><br>';
			echo '<label><input type="radio" name="Command" value="TAC_0">通常</label><br>';
			echo '<label><input type="radio" name="Command" value="TAC_1">攻撃重視</label><br>';
			echo '<label><input type="radio" name="Command" value="TAC_2">防御重視</label><br>';
			echo '<label><input type="radio" name="Command" value="TAC_3">先制行動</label><br>';
			echo '<label><input type="radio" name="Command" value="TAC_4">探索行動</label><br>';
			if($br['ar'] >= ($br['arcnt'] - ADD_AREA)){//最終日に出現。
				echo '<label><input type="radio" name="Command" value="TAC_5">連闘行動</label><br>';
			}
			echo '<br>　<input type="submit" name="Enter" value="決定">';
		}elseif($_POST['Command4'] == "OUSEN"){
			//応戦方針
			$user['log'] .= "応戦方針を決めるか・・・。<br>";
			echo "応戦方針を選んでください。<br><br>\n";
			echo '<label><input type="radio" name="Command" value="MAIN" checked>戻る</label><br><br>';
			echo '<label><input type="radio" name="Command" value="OUS_0">通常</label><br>';
			echo '<label><input type="radio" name="Command" value="OUS_1">攻撃重視</label><br>';
			echo '<label><input type="radio" name="Command" value="OUS_2">防御重視</label><br>';
			echo '<label><input type="radio" name="Command" value="OUS_3">隠密行動</label><br>';
			echo '<label><input type="radio" name="Command" value="OUS_4">回復行動</label><br>';
			echo '<br>　<input type="submit" name="Enter" value="決定">';
		}elseif($_POST['Command4'] == "COMMENT"){
			//口癖変更
			$user['log'] .= "殺害時、死亡時の口癖を変更します。<br>";
			echo "口癖を入力してください<br>（３２文字まで）<br><br>\n";
			echo '<label>一言コメント：<br>';
			echo '<textarea name="comment" cols="30" rows="4">'.str_replace("<br>","\n",$user['com']).'</textarea></label><br><br>';
			echo '<label>殺害時：<br>';
			echo '<input size="25" type="text" name="kmes" value="'.$user['kmes'].'"></label><br><br>';
			echo '<label>遺言：<br>';
			echo '<input size="25" type="text" name="dmes" value="'.$user['dmes'].'"></label><br><br>';
			echo '<label><input type="radio" name="Command" value="MAIN" checked>戻る</label><br>';
			echo '<label><input type="radio" name="Command" value="MESCHG">変更</label><br><br>';
			echo '　<input type="submit" name="Enter" value="決定">';
		}elseif($_POST['Command4'] == "POISON"){
			//毒混入
			$user['log'] .= "この毒薬を混ぜれば・・・ふふふ。<br>";
			echo "何に毒物混入しますか？<br><br>\n";
			echo '<label><input type="radio" name="Command" value="MAIN" checked>戻る</label><br><br>';
			$item = preg_grep('/<>[SH][HD]/',$user['item']);
			foreach($item as $no => $it){
				list($in,$ik) = explode("<>",$it);
				echo '<label><input type="radio" name="Command" value="POI_'.$no.'">'.$in.'/'.$user['eff'][$no].'/'.$user['itai'][$no].'</label><br>';
			}
			echo "<br>\n";
			echo '　<input type="submit" name="Enter" value="決定">';
		}elseif($_POST['Command4'] == "PSCHECK"){
			//毒見
			$user['log'] .= "毒が入っているかを調べよう。<br>";
			echo "何を毒見しますか？<br><br>\n";
			echo '<label><input type="radio" name="Command" value="MAIN" checked>戻る</label><br><br>';
			$item = preg_grep('/<>[SH][HD]/',$user['item']);
			foreach($item as $no => $it){
				list($in,$ik) = explode("<>",$it);
				echo '<label><input type="radio" name="Command" value="PCHK_'.$no.'">'.$in.'/'.$user['eff'][$no].'/'.$user['itai'][$no].'</label><br>';
			}
			echo "<br>\n";
			echo '　<input type="submit" name="Enter" value="決定">';
		}elseif($_POST['Command4'] == "PCHUWA"){
			//毒中和
			$user['log'] .= "これで毒を中和しよう。<br>";
			echo "何に中和剤を使いますか？<br><br>\n";
			echo '<label><input type="radio" name="Command" value="MAIN" checked>戻る</label><br><br>';
			$item = preg_grep('/<>[SH][HD]/',$user['item']);
			foreach($item as $no => $it){
				list($in,$ik) = explode("<>",$it);
				echo '<label><input type="radio" name="Command" value="PDEL_'.$no.'">'.$in.'/'.$user['eff'][$no].'/'.$user['itai'][$no].'</label><br>';
			}
			echo "<br>\n";
			echo '　<input type="submit" name="Enter" value="決定">';
		}elseif($_POST['Command4'] == "GROUP"){
			//グループ変更
			$user['log'] .= "グループ名、グループパスを変更します。<br>";
			echo '<label>グループ名：<br>';
			echo '<input size="16" type="text" name="g_name" value="'.$user['g_name'].'"></label><br><br>';
			echo '<label>グループパス：<br>';
			echo '<input size="16" type="text" name="g_pass" value="'.$user['g_pass'].'"></label><br><br>';
			echo '<label><input type="radio" name="Command" value="MAIN" checked>戻る</label><br>';
			echo '<label><input type="radio" name="Command" value="GROUP">変更</label><br><br>';
			echo '　<input type="submit" name="Enter" value="決定">';
		}elseif($_POST['Command4'] == "SPEAKER"){
			//スピーカ使用
			$user['log'] .= "これを使えば、みんなに聞こえる筈だな・・・<br>";
			echo "携帯スピーカを使って、全員に伝言を伝えます。<br><br>\n";
			echo "<input size=\"30\" type=\"text\" name=\"comment\" maxlength=\"30\" value=\"\"><br><br>\n";
			echo "<label><input type=\"radio\" name=\"Command\" value=\"MAIN\" checked>止める</label><br>\n";
			echo "<label><input type=\"radio\" name=\"Command\" value=\"SPEAKER\">伝える</label><br><br>\n";
			echo '　<input type="submit" name="Enter" value="決定">';
		}else{
			echo "何を行いますか？<br><br>\n";
			echo '　<button type="submit" name="Command" value="MAIN">戻る</button>';
		}
	}elseif(preg_match("/BATTLE0_/",$_POST['Command'])){
		//戦闘コマンド
		list($a,$wid) = explode("_",$_POST['Command']);
		$user['log'] .= "さて、どうしよう・・・。";
		echo '何をしますか？<br><br>';
		echo '<label>メッセージ<br>';
		echo '<input size="30" type="text" name="Comment" maxlength="64"></label><br><br>';
		$chk = " checked" ;
		if($user['equip'][0] === ''){
			$w_name = '素手';
			$w_kind = 'WP';
		}else{
			list($w_name,$w_kind) = explode("<>",$user['equip'][0]);
		}
		if(preg_match("/B/",$w_kind)){
			//棍武器
			echo '<label><input type="radio" name="Command" value="ATK_WB"'.$chk.'>殴る('.$user['wb'].')</label><br>';
			$chk = '';
		}
		if(preg_match("/P/",$w_kind) or $w_name == ""){
			//殴武器
			echo '<label><input type="radio" name="Command" value="ATK_WP"'.$chk.'>殴る('.$user['wp'].')</label><br>';
			$chk = '';
		}
		if(preg_match("/G/",$w_kind) and $user['eqtai'][0] > 0){
			//銃武器
			echo '<label><input type="radio" name="Command" value="ATK_WG"'.$chk.'>撃つ('.$user['wg'].')</label><br>';
			$chk = '';
		}
		if(preg_match("/A/",$w_kind) and $user['eqtai'][0] > 0){
			//射武器
			echo '<label><input type="radio" name="Command" value="ATK_WA"'.$chk.'>射る('.$user['wa'].')</label><br>';
			$chk = '';
		}
		if(preg_match("/N/",$w_kind)){
			//斬武器
			echo '<label><input type="radio" name="Command" value="ATK_WN"'.$chk.'>斬る('.$user['wn'].')</label><br>';
			$chk = '';
		}
		if(preg_match("/S/",$w_kind)){
			//刺武器
			echo '<label><input type="radio" name="Command" value="ATK_WS"'.$chk.'>刺す('.$user['ws'].')</label><br>';
			$chk = '';
		}
		if(preg_match("/C/",$w_kind)){
			//投武器
			echo '<label><input type="radio" name="Command" value="ATK_WC"'.$chk.'>投げる('.$user['wc'].')</label><br>';
			$chk = '';
		}
		if(preg_match("/D/",$w_kind)){
			//爆武器
			echo '<label><input type="radio" name="Command" value="ATK_WD"'.$chk.'>投げる('.$user['wd'].')</label><br>';
			$chk = '';
		}
		if(preg_match("/G|A/",$w_kind) and $user['eqtai'][0] == 0){
			//銃、射武器で殴る
			echo '<label><input type="radio" name="Command" value="ATK_WB"'.$chk.'>殴る('.$user['wb'].')</label><br>';
			$chk = '';
		}
		echo '<label><input type="radio" name="Command" value="RUNAWAY">逃亡</label><br><br>';
		echo '　<button type="submit" name="wid" value="'.$wid.'">決定</button>';
	}elseif(preg_match("/DEATHGET_/",$_POST['Command'])){
		//アイテム強奪
		list($a,$wid) = explode("_",$_POST['Command']);
		echo "何を奪いますか？<br><br>\n";
		echo '<label><input type="radio" name="Command" value="GET_99" checked>取らない</label><br><br>';
		$file = search_userfile($wid,'number');
		$w_user = getuserdata($file);
		for($i=0;$i<6;$i++){
			if($w_user['equip'][$i] != ""){
				list($in,$ik) = explode("<>",$w_user['equip'][$i]);
				echo '<label><input type="radio" name="Command" value="GET_'.$i.'">'.$in.'/'.$w_user['eqeff'][$i].'/'.$w_user['eqtai'][$i].'</label><br>';
			}
		}
		for($i=0;$i<ITEMMAX;$i++){
			$j = $i + 6;
			if($w_user['item'][$i] != ""){
				list($in,$ik) = explode("<>",$w_user['item'][$i]);
				echo '<label><input type="radio" name="Command" value="GET_'.$j.'">'.$in.'/'.$w_user['eff'][$i].'/'.$w_user['itai'][$i].'</label><br>';
			}
		}
		echo "<br><br>\n";
		echo '　<button type="submit" name="wid" value="'.$wid.'">決定</button>';
	}else{
		echo "何を行いますか？<br><br>\n";
		echo '　<button type="submit" name="Command" value="MAIN">戻る</button>';
	}
}