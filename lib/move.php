<?php
//--------------------------------------------------------------------
//	移動
//--------------------------------------------------------------------
function move(&$user){
	global $br;

	$mv = (int)$_POST['Command1'];

	$usesta = mt_rand(8,12);
	if($user['club'] == "陸上部"){$usesta *= 3/4;}
	if(mb_ereg("足",$user['inf'])){$usesta *= 3/2;}
	$usesta = (int)$usesta;

	if($br['hackflg']){//ハッキング時は本来禁止エリアの場所への移動でも警告メッセージ
		$mes = (in_array($mv,array_slice($br['ara'],0,$br['ar']+ADD_AREA)))? "次にここは禁止エリアになってしまうな。":"";
	}else{
		if(in_array($mv,array_slice($br['ara'],0,$br['ar']))){
			$user['log'] .= $br['PLACE'][$mv]."は禁止エリアだ。移動することは出来ないな・・・。<br>";
			$_POST['Command'] = 'MAIN';
			return;
		}
		$mes = (in_array($mv,array_slice($br['ara'],$br['ar'],ADD_AREA)))? "次にここは禁止エリアになってしまうな。":"";
	}

	$user['pls'] = $mv;
	$user['log'] .= $br['PLACE'][$mv]."に移動した。".$mes."<br>".$br['ARINFO'][$mv]."<br>";

	$user['sta'] -= $usesta;
	if($user['sta'] <= 0){//スタミナ切れ？
		drain($user);
	}

	search2($user);
	save($user);
}
//--------------------------------------------------------------------
//	探索
//--------------------------------------------------------------------
function search(&$user){

	$usesta = mt_rand(12,18);
	if($user['club'] == "陸上部"){$usesta *= 3/4;}
	if(preg_match("/足/",$user['inf'])){$usesta *= 4/3;}
	$usesta = (int)$usesta;

	$user['log'] .= $user['l_name']."は辺りを探索した。<br>";

	$user['sta'] -= $usesta;
	if($user['sta'] <= 0){//スタミナ切れ？
		drain($user);
	}

	if(!search2($user)){
		$user['log'] .= "しかし、何も見つからなかった。<br>";
	}
	save($user);
}
//------------------------------------------------------------------------------
//	発見、イベント分岐
//------------------------------------------------------------------------------
function search2(&$user){

	$chksts = $chksts2 = false;
	list($search,$sen) = tactget1($user,"PC");

	if(mt_rand(1,10) <= 5){//敵発見判定
		$plist = glob(U_DATADIR."*".U_DATAFILE);
		shuffle($plist);

		foreach($plist as $filename){
			$w_user = getuserdata($filename);

			//遭遇条件を満たさない相手をふるい落とす
			//1.同じ場所にいない相手、自分自身には常に遭遇しない
			//2.連闘行動でない時
			//├最後に攻撃したのが自分である相手には遭遇しない
			//└グループ所属時は同じグループの人又は最後に攻撃したのが自分と同じグループの人である相手には遭遇しない
			if($w_user['pls'] != $user['pls'] or $w_user['id'] == $user['id']){
				continue;
			}elseif($user['tactics'] != "連闘行動"){
				if($w_user['bid'] == $user['id']){
					continue;
				}elseif($user['g_name'] != ""){
					if($w_user['bg_name'] == $user['g_name'] or $w_user['g_name'] == $user['g_name']){
						continue;
					}
				}
			}else{
				//連闘行動の時は同じグループの人にも遭遇するようになっている
				//最後に攻撃したのが自分、又はグループ所属時は自分と同じグループの人にも一定の確率で続けて遭遇することが出来る
				//if(mt_rand(1,10) >= 5){ の一番右の数字を大きくすれば同じ相手に遭遇する確率が上がる
				$chk = false;
				if($w_user['bid'] == $user['id']){
					$chk = true;
				}elseif($user['g_name'] != "" and $w_user['bg_name'] == $user['g_name']){
					$chk = true;
				}
				if($chk){
					if(mt_rand(1,10) >= 5){
						continue;
					}
				}
			}

			list($chkpnt) = tactget1($w_user,"NPC");

			if((mt_rand(1,100) * $chkpnt) <= $search){//敵発見
				if($w_user['hit'] > 0){//相手が生きている
					require LIBDIR.'attack.php';
					require LIBDIR.'dsp_battle.php';
					$user['bb'] = $w_user['id'];//ブラウザバック対策
					if(mt_rand(1,100) <= $sen){//先制
						attack($user,$w_user);
					}else{//後攻
						attack2($user,$w_user);
					}
					battle($user,$w_user);
				}else{//相手が死亡している
					if(mt_rand(1,100) <= 20){
						$chk = false;
						for($i=0;$i<6;$i++){
							if($w_user['equip'][$i] != ""){
								$chk = true;
								break;
							}
						}
						if(!$chk){
							for($i=0;$i<ITEMMAX;$i++){
								if($w_user['item'][$i] != ""){
									$chk = true;
									break;
								}
							}
						}
						if($chk){
							$user['bb'] = $w_user['id'];//ブラウザバック対策
							$user['sts'] = "DEAD_".$w_user['number'];
							deathget($user,$w_user);
							$chksts = true;
							$chksts2 = false;
							break;
						}
					}
				}
			}else{
				$chksts2 = true;
			}
		}//foreach
		if($chksts2){
			if(mt_rand(1,100) <= $search and $_POST['Command'] == "SEARCH"){
				require LIBDIR.'item1.php';
				itemget($user);
				$chksts = true;
			}else{
				$user['log'] .= "何者かが潜んでいる気配がする・・・。気のせいか？<br>";
			}
		}
	}else{
		if(mt_rand(1,100) <= $search and $_POST['Command'] == "SEARCH"){
			require LIBDIR.'item1.php';
			itemget($user);
			$chksts = true;
		}else{
			require LIBDIR.'event.php';
			event($user,$chksts);
		}
	}

	if(!preg_match("/^DEATHGET_/",$_POST['Command'])){
		$_POST['Command'] = "MAIN";
	}

	return $chksts;

}
//------------------------------------------------------------------------------
//	死体発見
//------------------------------------------------------------------------------
function deathget(&$user,$w_user){
	$user['log'] .= $w_user['f_name']." ".$w_user['l_name']."の死体を発見した。<br>";

	if(mb_ereg("斬殺",$w_user['death'])){
		switch($w_user['com']){
			case 1:$user['log'] .= "頭部が首の皮一枚でつながってる状態だ・・・。首を刎ねられたようだ。<br>";break;
			case 2:$user['log'] .= "腹部が鋭利な刃物のようなもので裂かれて、内臓がはみ出している・・・。<br>";break;
			case 3:$user['log'] .= "肩口から胸にかけての袈裟切りだ。見事に切り裂かれている・・・。<br>";break;
			case 4:$user['log'] .= "首・胴・両腕・両足が分断されている。こういう事が正気の人間に出来るのだろうか・・・。<br>";break;
			case 5:$user['log'] .= "顔を集中的に切り刻まれている。生前の面影など全く無い・・・。<br>";break;
			case 6:$user['log'] .= "腹部を切り裂かれているが、よく見ると手首にも切り傷が数多くある・・・。<br>相手に切られた後に自殺をしようと思ったのだろうか？<br>";break;
			default:$user['log'] .= "頭から胸にかけて無残に切り裂かれている・・・。<br>";
		}
	}elseif(mb_ereg("射殺",$w_user['death'])){
		switch($w_user['com']){
			case 1:$user['log'] .= "額に一本の矢が突き刺さっている・・・。<br>";break;
			case 2:$user['log'] .= "背中に何本も矢が刺さっている。逃げようとした所、背後から射られたようだ。<br>";break;
			case 3:$user['log'] .= "心臓の場所に一本正確に矢が刺さっている。相当な腕の持ち主だろう・・・。<br>";break;
			case 4:$user['log'] .= "足と頭に矢が立っている。足を射て、逃げられなくさせておいてから急所を射たようだ・・・。<br>";break;
			case 5:$user['log'] .= "壁に矢で縫い付けられたようになっている・・・ゴルゴダの丘で処刑された聖者のような体勢だ・・・。<br>";break;
			case 6:$user['log'] .= "何本もの矢が刺さり、ハリネズミのようになっている・・・。<br>";break;
			default:$user['log'] .= "首に数本の矢が刺さっている・・・。一本は顎の下に突き抜けている・・・。<br>";
		}
	}elseif(mb_ereg("銃殺",$w_user['death'])){
		switch($w_user['com']){
			case 1:$user['log'] .= "胸に・・・３発、額に１発の弾痕がある・・・。額の一発が致命傷になったみたいだ・・・。<br>";break;
			case 2:$user['log'] .= "腹部に数発の弾痕があり、血が流れ出している。しかし、その血ももう乾いている。<br>";break;
			case 3:$user['log'] .= "頭が原形をとどめていない位吹き飛んでいる・・・。名札から辛うじて名前が分かったくらいだ。<br>";break;
			case 4:$user['log'] .= "胸に数発。そして、脳髄が吹き飛んでいる。殺した後、口に銃を突っ込んで撃ったんだろう、ふざけたことをしている・・・。<br>";break;
			case 5:$user['log'] .= "腹部にぽっかり穴があり、向こう側が見える。これじゃ絶対生きていられないな・・・。<br>";break;
			case 6:$user['log'] .= "顔に何発もの弾痕がある・・・。恨みでもあったのであろうか。<br>";break;
			default:$user['log'] .= "右頭部が激しく損傷し、脳が流れ出している・・・。<br>";
		}
	}elseif(mb_ereg("爆殺",$w_user['death'])){
		switch($w_user['com']){
			case 1:$user['log'] .= "そこらじゅうに、体のパーツが分散している。派手にやられたみたいだ・・・。<br>";break;
			case 2:$user['log'] .= "両足が吹き飛ばされている。腕だけで這って逃げようとしたのか・・・。<br>";break;
			case 3:$user['log'] .= "爆弾にでも攻撃されたのであろうか、頭と右腕しか残っていない・・・。<br>";break;
			case 4:$user['log'] .= "爆弾に吹き飛ばされたのであろうか、頭が半分欠けて中身がのぞいている・・・。<br>";break;
			case 5:$user['log'] .= "爆風で吹き飛ばされた片腕が、５ｍほど先にころがっている・・・。<br>";break;
			case 6:$user['log'] .= "死体というより、肉の塊だな・・・。<br>";break;
			default:$user['log'] .= "首と手が見当たらないな・・・。爆風で吹き飛ばされたんだろうか・・・。<br>";
		}
	}elseif(mb_ereg("撲殺",$w_user['death'])){
		switch($w_user['com']){
			case 1:$user['log'] .= "腹を抑えた体制で、うずくまっているが・・・どうやら、そのまま息絶えたようだ・・・。<br>";break;
			case 2:$user['log'] .= "相当派手に殴られたみたいだ・・・。顔が紫に腫れ上がっている・・・。<br>";break;
			case 3:$user['log'] .= "首の骨が折られ、首から骨が突き出ている・・・。<br>";break;
			case 4:$user['log'] .= "地面に顔を埋め、大量の血を顔面から流している・・。倒れた所、後頭部を殴打されたようだ。<br>";break;
			case 5:$user['log'] .= "額が割れ、血と脳漿が流れている。真正面から激しく殴られたようだな・・。<br>";break;
			case 6:$user['log'] .= "後ろから鈍器のようなもので殴られたのだろうか？頭を抱えたまま倒れている・・・。<br>";break;
			default:$user['log'] .= "首が見事に横に向いている。どうみても、首の骨が折れているな・・・。<br>";
		}
	}elseif(mb_ereg("刺殺",$w_user['death'])){
		switch($w_user['com']){
			case 1:$user['log'] .= "全身に、何か鋭利な刃物で刺された傷が、大量にある・・・。死体の回りは、血の海だ・・・。<br>";break;
			case 2:$user['log'] .= "馬乗りになられて、何度も何度も刺されたような痕跡がある・・・。<br>";break;
			case 3:$user['log'] .= "心臓を一突き。未だに傷から血が湧き出ている・・・。殺されたのはつい先程のようだ。<br>";break;
			case 4:$user['log'] .= "喉を刺されている・・。目は白目をむいている・・・。<br>";break;
			case 5:$user['log'] .= "後ろから腹部を刺されて倒れている。不意打ちだったのだろうか・・？<br>";break;
			case 6:$user['log'] .= "左腹部が激しく損傷している。刺した後、えぐったような傷がある・・・。<br>";break;
			default:$user['log'] .= "なにかで刺されている・・・。血の涙を流しているようだ・・・。<br>";
		}
	}elseif(mb_ereg("毒",$w_user['death'])){
		switch($w_user['com']){
			case 1:$user['log'] .= "毒物を口にしたのかな・・？嘔吐した形跡もある・・・。<br>";break;
			case 2:$user['log'] .= "口から一筋の血が流れている。ぱっとみは、眠っているようにしかみえないな・・・。<br>";break;
			case 3:$user['log'] .= "死体に顔を近づけると特有のアーモンド臭がある。毒殺されたのか・・・。<br>";break;
			case 4:$user['log'] .= "毒殺されたのか。口から大量の血の混じった泡を吹いている・・・。<br>";break;
			case 5:$user['log'] .= "毒を飲んで苦しんだんだろうか。喉を自分で激しく爪でかきむしっている・・・。<br>";break;
			case 6:$user['log'] .= "何者かに毒薬でもかけられたのか？皮膚が激しく変色している・・・。<br>";break;
			default:$user['log'] .= "皮膚がどす黒い色に変色して、口からは大量の血を吐いている・・・。<br>";
		}
	}else{
		$user['log'] .= "無残にも仰向けに転がっている・・・。<br>";
	}
	$user['log'] .= "デイパックの中身を物色させてもらうか・・・。<br>";
	$_POST['Command'] = "DEATHGET_".$w_user['number'];
}
