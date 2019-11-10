<?php
//--------------------------------------------------------------------
//	アイテム入手
//--------------------------------------------------------------------
function itemget(&$user){

	$itemlist = file(ITEMDIR.$user['pls'].ITEMFILE);

	if(count($itemlist) <= 0){
		$user['log'] .= "もう、このエリアには何も無いのかな・・・？<br>";
		return;
	}

	$work = array_rand($itemlist);
	list($getitem,$geteff,$gettai) = explode(",",$itemlist[$work]);
	list($itname,$itkind) = explode("<>",$getitem);
	array_splice($itemlist,$work,1);

	if($itkind == "TO"){//仕掛けられた罠
		$result = mt_rand((int)($geteff/2),$geteff);
		$user['hit'] -= $result;
		$user['log'] .= '罠だ！仕掛けられていた '.$itname.' で傷をおい、<span class="dmg">'.$result.'のダメージ</span>を受けた！<br>';
		if($user['hit'] <= 0){//死亡？
			$user['hit'] = 0;
			$user['log'] .= '<span class="dead">'.$user['f_name'].' '.$user['l_name'].'（'.$user['cl'].' '.$user['sex'].$user['no'].'番）は死亡した。</span><br>';
			logsave("DEATH6",$user);
		}
		file_put_contents(ITEMDIR.$user['pls'].ITEMFILE,$itemlist,LOCK_EX);
		return;
	}

	$chk = false;
	for($i=0;$i<ITEMMAX;$i++){
		if($user['item'][$i] == ""){
			$chk = true;
			break;
		}elseif($user['item'][$i] == $getitem and preg_match("/<>WC|<>TN|弾丸|矢/",$getitem)){
			$chk = true;
			break;
		}
	}

	if(!$chk){
		$user['log'] .= $itname.'を発見した。しかし、これ以上カバンに入らない。<br>'.$itname.'をあきらめた・・・。<br>';
		return;
	}

	if(preg_match("/<>HH|<>HD/",$getitem)){
		$mes = "口にすれば体力が回復出来そうだな。";
	}elseif(preg_match("/<>SH|<>SD/",$getitem)){
		$mes = "口にすればスタミナが回復出来そうだな。";
	}elseif(preg_match("/<>W/",$getitem)){
		$mes = "こいつは武器になりそうだな。";
	}elseif(preg_match("/<>D/",$getitem)){
		$mes = "こいつは防具に出来そうだな。";
	}elseif(preg_match("/<>A/",$getitem)){
		$mes = "こいつは身に付けることが出来そうだな。";
	}else{
		$mes = "きっと何かに使えるだろう。";
	}
	$user['log'] .= $itname.'を発見した。'.$mes.'<br>';

	if($user['item'][$i] == ""){
		$user['item'][$i] = $getitem;
		$user['eff'][$i] = $geteff;
		$user['itai'][$i] = $gettai;
	}else{
		$user['itai'][$i] += $gettai;
	}

	file_put_contents(ITEMDIR.$user['pls'].ITEMFILE,$itemlist,LOCK_EX);

}
//--------------------------------------------------------------------
//	アイテム使用
//--------------------------------------------------------------------
function itemuse(&$user) {
	global $br;

	if(!is_array($_POST['USE'])){
		error("不正アクセスです。");
	}

	foreach($_POST['USE'] as $wk){
		if($wk == 'MAIN' or $user['item'][$wk] == ""){
			continue;
		}

		list($in,$ik) = explode("<>",$user['item'][$wk]);
		list($w_name,) = explode("<>",$user['equip'][0]);
		list($b_name,) = explode("<>",$user['equip'][1]);

		if(preg_match("/<>SH/",$user['item'][$wk])){
			//スタミナ回復
			$result = MAXSTA - $user['sta'];
			if($user['eff'][$wk] < $result){
				$result = $user['eff'][$wk];
			}
			$user['sta'] += $result;
			$user['log'] .= $in.'を使用した。スタミナが'.$result.'回復した。<br>';
			if(--$user['itai'][$wk] <= 0){
				$user['item'][$wk] = "";
				$user['eff'][$wk] = $user['itai'][$wk] = 0;
			}
		}elseif(preg_match("/<>HH/",$user['item'][$wk])){
			//体力回復
			$result = $user['mhit'] - $user['hit'];
			if($user['eff'][$wk] < $result){
				$result = $user['eff'][$wk];
			}
			$user['hit'] += $result;
			$user['log'] .= $in.'を使用した。体力が'.$result.'回復した。<br>';
			if(--$user['itai'][$wk] <= 0){
				$user['item'][$wk] = "";
				$user['eff'][$wk] = $user['itai'][$wk] = 0;
			}
		}elseif(preg_match("/<>HD|<>SD/",$user['item'][$wk])){
			//毒入り回復アイテム
			if(preg_match("/<>HD2|<>SD2/",$user['item'][$wk])){//料理研究部特製？
				$result = (int)($user['eff'][$wk]*1.5);
			}else{
				$result = $user['eff'][$wk];
			}
			$user['hit'] -= $result;
			$user['log'] .= 'うっ・・・しまった！どうやら、毒物が混入されていたみたいだ！<span class="dmg">'.$result.'のダメージ</span>！<br>';
			if(--$user['itai'][$wk] <= 0){
				$user['item'][$wk] = "";
				$user['eff'][$wk] = $user['itai'][$wk] = 0;
			}
			if($user['hit'] <= 0){
				$user['log'] .= '<span class="dead">'.$user['f_name'].' '.$user['l_name'].'（'.$user['cl'].' '.$user['sex'].$user['no'].'番）は死亡した。</span><br>';
				$user['com'] = mt_rand(1,6);
				//死亡ログ
				logsave("DEATH5",$user);
			}
			break;
		}elseif(preg_match("/<>W|<>D|<>A/",$user['item'][$wk])){
			//装備品
			if(preg_match("/<>W/",$user['item'][$wk])){$wk2 = 0;}//武器
			elseif(preg_match("/<>DB/",$user['item'][$wk])){$wk2 = 1;}//体防具
			elseif(preg_match("/<>DH/",$user['item'][$wk])){$wk2 = 2;}//頭防具
			elseif(preg_match("/<>DA/",$user['item'][$wk])){$wk2 = 3;}//腕防具
			elseif(preg_match("/<>DF/",$user['item'][$wk])){$wk2 = 4;}//足防具
			elseif(preg_match("/<>A/",$user['item'][$wk])){$wk2 = 5;}//装飾品
			list($user['equip'][$wk2],$user['item'][$wk]) = array($user['item'][$wk],$user['equip'][$wk2]);
			list($user['eqeff'][$wk2],$user['eff'][$wk]) = array($user['eff'][$wk],$user['eqeff'][$wk2]);
			list($user['eqtai'][$wk2],$user['itai'][$wk]) = array($user['itai'][$wk],$user['eqtai'][$wk2]);
			$user['log'] .= $in.'を装備した。<br>';
		}elseif(preg_match("/<>R/",$user['item'][$wk])){
			//レーダー
			save($user);
			$user['log'] .= $in.'を使用した。<br><br>数字：エリアにいる人数<br>赤数字：自分がいるエリアの人数<br><br>';
			require LIBDIR.'dsp_radar.php';
			radar($user,$wk);
		}elseif(preg_match("/<>TN/",$user['item'][$wk])){
			//罠
			file_put_contents(ITEMDIR.$user['pls'].ITEMFILE,$in."<>TO,".$user['eff'][$wk].",1,\n",FILE_APPEND|LOCK_EX);
			if(--$user['itai'][$wk] <= 0){
				$user['item'][$wk] = "";
				$user['eff'][$wk] = $user['itai'][$wk] = 0;
			}
			$user['log'] .= $in.'を罠として仕掛けた。自分も注意しなきゃな・・・。<br>';
		}elseif($in == "弾丸" and preg_match("/<>WG/",$user['equip'][0])){
			//弾丸
			$up = $user['itai'][$wk] + $user['eqtai'][0];
			$up = ($up > 6)? 6 - $user['eqtai'][0]:$user['itai'][$wk];
			$user['eqtai'][0] += $up;
			$user['itai'][$wk] -= $up;
			if($user['itai'][$wk] <= 0){
				$user['item'][$wk] = "";
				$user['eff'][$wk] = $user['itai'][$wk] = 0;
			}
			$user['log'] .= $in.'を、'.$w_name.'に装填した。<br>'.$w_name.'の使用回数が '.$up.' 向上した。<br>';
		}elseif($in == "矢" and preg_match("/<>WA/",$user['equip'][0])){
			//矢
			$up = $user['itai'][$wk] + $user['eqtai'][0];
			$up = ($up > 6)? 6 - $user['eqtai'][0]:$user['itai'][$wk];
			$user['eqtai'][0] += $up;
			$user['itai'][$wk] -= $up;
			if($user['itai'][$wk] <= 0){
				$user['item'][$wk] = "";
				$user['eff'][$wk] = $user['itai'][$wk] = 0;
			}
			$user['log'] .= $in.'を、'.$w_name.'に補充した。<br>'.$w_name.'の使用回数が '.$up.' 向上した。<br>';
		}elseif($in == "砥石" and preg_match("/<>WN|<>WS/",$user['equip'][0])){
			//砥石
			$user['eqeff'][0] += $user['eff'][$wk];
			if($user['eqeff'][0] > 30){$user['eqeff'][0] = 30;}
			if(--$user['itai'][$wk] <= 0){
				$user['item'][$wk] = "";
				$user['eff'][$wk] = $user['itai'][$wk] = 0;
			}
			$user['log'] .= $in.'を使用した。'.$w_name.'の攻撃力が '.$user['eqeff'][0].' になった。<br>';
		}elseif($in == "裁縫道具" and preg_match("/<>DB/",$user['equip'][1])){
			//裁縫道具
			$user['eqtai'][1] += $user['eff'][$wk];
			if($user['eqtai'][1] > 50){$user['eqtai'][1] = 50;}
			if(--$user['itai'][$wk] <= 0){
				$user['item'][$wk] = "";
				$user['eff'][$wk] = $user['itai'][$wk] = 0;
			}
			$user['log'] .= $in.'を使用した。'.$b_name.'の耐久力が '.$user['eqtai'][1].' になった。<br>';
		}elseif($in == "御神籤"){
			//御神籤
			$kuji = mt_rand(1,100);
			$user['log'] .= "御神籤か…開いてみよう。<br>";
			if($kuji <= 15){
				$chk = "good"; $hp = mt_rand(1,6);$atk = mt_rand(1,3);$def = mt_rand(1,3);
				$user['log'] .= "大吉だ！なにかいいことがありそうだな！<br>";
			}elseif($kuji <= 35){
				$chk = "good"; $hp = mt_rand(0,4);$atk = mt_rand(0,2);$def = mt_rand(0,2);
				$user['log'] .= "中吉か。まあ悪くはないな。<br>";
			}elseif($kuji <= 65){
				$chk = "none";
				$user['log'] .= "小吉か。ちょっと微妙だ…<br>";
			}elseif($kuji <= 85){
				$chk = "bad"; $hp = mt_rand(0,4);$atk = mt_rand(0,2);$def = mt_rand(0,2);
				$user['log'] .= "凶か…。まったく、不吉だな…<br>";
			}else{
				$chk = "bad"; $hp = mt_rand(1,6);$atk = mt_rand(1,3);$def = mt_rand(1,3);
				$user['log'] .= "大凶…何でこんなのが出るんだ…<br>\n";
			}
			if($chk == "good"){
				$user['mhit'] += $hp;
				$user['hit'] += $hp;
				$user['att'] += $atk;
				$user['def'] += $def;
				$user['log'] .= '体力＋'.$hp.'　攻撃力＋'.$atk.'　防御力＋'.$def.'<br>';
			}elseif($chk == "bad"){
				$user['mhit'] -= $hp;
				$user['hit'] -= $hp;
				$user['att'] -= $atk;
				$user['def'] -= $def;
				if($user['mhit'] < 1){
					$user['mhit'] = 1;
				}
				if($user['hit'] < 1){
					$user['hit'] = 1;
				}
				$user['log'] .= '体力－'.$hp.'　攻撃力－'.$atk.'　防御力－'.$def.'<br>';
			}
			if(--$user['itai'][$wk] <= 0){
				$user['item'][$wk] = "";
				$user['eff'][$wk] = $user['itai'][$wk] = 0;
			}
		}elseif(mb_ereg("バッテリ",$in)){
			$chk = false;
			for($i=0;$i<ITEMMAX;$i++){
				if($user['item'][$i] == "モバイルPC<>Y" and $user['itai'][$i] < 5){
					$user['itai'][$i] += $user['eff'][$wk];
					if($user['itai'][$i] > 5){$user['itai'][$i] = 5;}
					if(--$user['itai'][$wk] <= 0){
						$user['item'][$wk] = "";
						$user['eff'][$wk] = $user['itai'][$wk] = 0;
					}
					$user['log'] .= $in.'でモバイルPC を充電した。モバイルPC の使用回数が '.$user['itai'][$i].' になった。<br>';
					$chk = true;
					break;
				}
			}
			if(!$chk){
				$user['log'] .= "こいつは何に使うんだろう・・・。<br>";
				continue;
			}
		}elseif($in == "プログラム解除キー"){
			if($user['pls'] == 0){
				$user['inf'] .= "解";
				file_put_contents(FLAGFILE,$br['hackflg'].",解除終了,".$br['stopflg'].",",LOCK_EX);
				winnersave("destruction",$user);
				logsave("EXEND",$user);
				$user['log'] .= "解除キーを使ってプログラムを停止した。<br>首輪が外れた！<br>";
				if((int)AUTORESET >= 1){
					$resettime = mktime(0,0,0,$br['month'],$br['mday'],$br['year']) + 86400 * (int)AUTORESET;
					file_put_contents(TIMEFILE,$resettime,LOCK_EX);
				}
			}else{
				$user['log'] .= "ここで使っても意味がない・・・。<br>";
				continue;
			}
		}else{
			$user['log'] .= "こいつは何に使うんだろう・・・。<br>";
			continue;
		}
	}

	save($user);
	$_POST['Command'] = "MAIN";
}
//------------------------------------------------------------------------------
//	アイテム投棄
//------------------------------------------------------------------------------
function itemdel(&$user) {

	if(!is_array($_POST['DEL'])){
		error("不正アクセスです。");
	}

	$file = ITEMDIR.$user['pls'].ITEMFILE;

	$delitem = "";
	foreach($_POST['DEL'] as $wk){
		if($user['item'][$wk] != ""){
			list($in,) = explode("<>",$user['item'][$wk]);
			$delitem .= "{$user['item'][$wk]},{$user['eff'][$wk]},{$user['itai'][$wk]},\n";
			$user['item'][$wk] = "";
			$user['eff'][$wk] = $user['itai'][$wk] = 0;
			$user['log'] .= $in.'を捨てた。<br>';
		}else{
			error("不正アクセスです。");
		}
	}

	file_put_contents($file,$delitem,FILE_APPEND | LOCK_EX);

	save($user);
	$_POST['Command'] = "MAIN";
}
//------------------------------------------------------------------------------
//	装備解除
//------------------------------------------------------------------------------
function eqdel(&$user) {

	$wk = str_replace('EQDEL_','',$_POST['Command3']);

	if($user['equip'][$wk] == ""){
		error("不正アクセスです。");
	}
	list($eqname,) = explode("<>",$user['equip'][$wk]);

	$chk = false;
	for($i=0;$i<ITEMMAX;$i++){
		if($user['item'][$i] == ""){$chk = true;break;}
	}

	if(!$chk){
		$user['log'] .= "それ以上デイパックに入りません。<br>";
	}else{
		$user['log'] .= $eqname.'をデイパックにしまいました。<br>';
		$user['item'][$i] = $user['equip'][$wk];
		$user['eff'][$i] = $user['eqeff'][$wk];
		$user['itai'][$i] = $user['eqtai'][$wk];
		$user['equip'][$wk] = "";
		$user['eqeff'][$wk] = $user['eqtai'][$wk] = 0;
		save($user);
	}
	$_POST['Command'] = "MAIN";
}
