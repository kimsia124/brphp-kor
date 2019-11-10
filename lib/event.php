<?php
//--------------------------------------------------------------------
//	イベント
//--------------------------------------------------------------------
function event(&$user,&$chksts) {

	if(mt_rand(1,5) <= 2){
		return;
	}

	switch($user['pls']){
		case 0:	//分校
			break;
		case 1:	//北の岬
			break;
		case 2:	//北村住宅街
			bird($user,$chksts);
			break;
		case 3:	//北村役場
			break;
		case 4:	//郵便局
			break;
		case 5:	//消防署
			break;
		case 6:	//観音堂
			break;
		case 7:	//丘陵地帯
			break;
		case 8:	//清水池
			break;
		case 9:	//西村神社
			break;
		case 10: //ホテル跡
			break;
		case 11: //山岳地帯・西北
			rockfall($user,$chksts);
			break;
		case 12: //山岳地帯・北
			rockfall($user,$chksts);
			break;
		case 13: //トンネル
			break;
		case 14: //西村住宅街
			bird($user,$chksts);
			break;
		case 15: //山岳地帯・西
			rockfall($user,$chksts);
			break;
		case 16: //山岳地帯・南
			rockfall($user,$chksts);
			break;
		case 17: //山岳地帯・南東
			rockfall($user,$chksts);
			break;
		case 18: //寺
			break;
		case 19: //廃校
			break;
		case 20: //南村神社
			break;
		case 21: //森林地帯・西
			straydog($user,$chksts);
			break;
		case 22: //森林地帯・東
			straydog($user,$chksts);
			break;
		case 23: //源次郎池
			pond($user,$chksts);
			break;
		case 24: //焼所
			break;
		case 25: //南村住宅街
			bird($user,$chksts);
			break;
		case 26: //診療所
			break;
		case 27: //灯台
			break;
		case 28: //南の岬
			break;
	}

}
//--------------------------------------------------------------------
//	鳥に襲われる
//--------------------------------------------------------------------
function bird(&$user,&$chksts){
	$dice = mt_rand(1,3);
	$dice2 = mt_rand(10,15);

	$user['log'] .= "ふと、空を見上げると、烏の群れだ！<br>";
	if($dice == 1){
		$user['log'] .= '烏に襲われ、<span class="dmg">頭を負傷した</span>！<br>';
		$user['inf'] = str_replace('頭','',$user['inf']);
		$user['inf'] .= "頭";
	}elseif($dice == 2) {
		$user['log'] .= '烏に襲われ、<span class="dmg">'.$dice2.'ダメージ</span> を受けた！<br>';
		$user['hit'] -= $dice2;
		if($user['hit'] <= 0){
			$user['hit'] = 0;
			$user['log'] .= '<span class="dead">'.$user['f_name'].' '.$user['l_name'].'（'.$user['cl'].' '.$user['sex'].$user['no'].'番）は死亡した。</span><br>';
			//死亡ログ
			logsave("DEATH3",$user);
		}
	}else{
		$user['log'] .= "ふぅ、なんとか撃退した・・・。<br>";
	}
	$chksts = true;
}
//--------------------------------------------------------------------
//	落石
//--------------------------------------------------------------------
function rockfall(&$user,&$chksts){
	$dice = mt_rand(1,3);
	$dice2 = mt_rand(10,15);

	$user['log'] .= "しまった、土砂崩れだ！<br>";
	if($dice == 1){
		$user['log'] .= '何とかかわしたが、落石で<span class="dmg">足を負傷した</span>！<br>';
		$user['inf'] = str_replace('足','',$user['inf']);
		$user['inf'] .= "足";
	}elseif($dice == 2){
		$user['log'] .= '落石により、<span class="dmg">'.$dice2.'ダメージ</span> を受けた！<br>';
		$user['hit'] -= $dice2;
		if($user['hit'] <= 0){
			$user['hit'] = $user['mhit'] = 0;
			$user['log'] .= '<span class="dead">'.$user['f_name'].' '.$user['l_name'].'（'.$user['cl'].' '.$user['sex'].$user['no'].'番）は死亡した。</span><br>';
			//死亡ログ
			logsave("DEATH3",$user);
		}
	}else{
		$user['log'] .= "ふぅ、なんとかかわした・・・。<br>";
	}
	$chksts = true;
}
//--------------------------------------------------------------------
//	野犬
//--------------------------------------------------------------------
function straydog(&$user,&$chksts){
	$dice = mt_rand(1,3);
	$dice2 = mt_rand(10,15);

	$user['log'] .= "突如、野犬が襲い掛かってきた！<br>";
	if($dice == 1){
		$user['log'] .= '腕をかまれ、<span class="dmg">腕を負傷した</span>！<br>';
		$user['inf'] = str_replace('腕','',$user['inf']);
		$user['inf'] .= "腕";
	}elseif($dice == 2){
		$user['log'] .= '野犬に襲われ、<span class="dmg">'.$dice2.'ダメージ</span> を受けた！<br>';
		$user['hit'] -= $dice2;
		if($user['hit'] <= 0){
			$user['hit'] = $user['mhit'] = 0;
			$user['log'] .= '<span class="dead">'.$user['f_name'].' '.$user['l_name'].'（'.$user['cl'].' '.$user['sex'].$user['no'].'番）は死亡した。</span><br>';
			//死亡ログ
			logsave("DEATH3",$user);
			$_POST['Command'] = "EVENT";
		}
	}else{
		$user['log'] .= "ふぅ、なんとか撃退した・・・。<br>";
	}
	$chksts = true;
}
//--------------------------------------------------------------------
//	池で滑る
//--------------------------------------------------------------------
function pond(&$user,&$chksts){
	$dice = mt_rand(1,5);
	$dice2 = mt_rand(15,25);

	$user['log'] .= "しまった、足を滑らせた！<br>";
	if($dice <= 3){
		$user['log'] .= '池の中に落下したが、なんとか這い上がった！<br>スタミナを <span class="dmg">'.$dice2.'ポイント消費した</span>！<br>';
		$user['sta'] -= $dice2;
		if($user['sta'] <= 0){//スタミナ切れ？
			drain($user);
		}
	}else{
		$user['log'] .= "ふぅ、なんとか落ちずに済んだ・・・。<br>";
	}
	$chksts = true;
}
