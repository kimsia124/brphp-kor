<?php
/*---------------------------------------------------------------------------*
 *	事前処理
 *---------------------------------------------------------------------------*/
require 'config.php';
require LIBDIR.'lib1.php';

mb_internal_encoding('EUC-JP');
mb_regex_encoding('EUC-JP');
header("content-type: text/html;charset=EUC-JP");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

//時間取得
list($br['sec'],$br['min'],$br['hour'],$br['mday'],$br['month'],$br['year'],$wday) = localtime(NOW);
$br['year'] += 1900;
$br['month']++;
$br['wday'] = $br['WEEK'][$wday];

//エリア数
$br['arcnt'] = count($br['PLACE']);

//エリアデータ関連
$br['ar'] = 0;	//禁止エリア数
$br['kin_ar'] = array();//禁止エリアの順番(エリア名)
$br['ara'] = array();	//禁止エリアの順番(エリア番号)

//フラグ
$br['hackflg'] = false;	//ハックフラグ
$br['endflg'] = '終了';	//終了フラグ
$br['stopflg'] = '未初期化';	//停止フラグ

//初めてアクセスした時は各種ログファイルがないはずなのでその場合禁止エリア追加関連の処理をしないようにする
if(file_exists(AREAFILE)){
	$arealist = file(AREAFILE);

	list($artime,$br['ar']) = explode(",",$arealist[0]);
	$br['kin_ar'] = explode(",",$arealist[1]);
	$br['ara'] = explode(",",$arealist[2]);
	array_pop($br['kin_ar']);
	array_pop($br['ara']);

	//各種フラグ
	list($br['hackflg'],$br['endflg'],$br['stopflg']) = explode(',',file_get_contents(FLAGFILE));

	//PGが終了していない場合この後の禁止エリア追加処理をする
	if(!mb_ereg('終了',$br['endflg'])){
		//禁止エリア追加処理
		while($artime <= NOW){
			//初期化時にすぐに開始していなかった場合、プログラム開始。
			if($br['ar'] == 0){

				logsave('NEWGAME',$artime);

				$br['ar'] = 1;
				list(,,,$mday,$month,$year) = localtime($artime);
				$month++;
				$year += 1900;
				$chk = mktime(0,0,0,$month,$mday,$year);

				$chk += 86400;
				$arealist[0] = $chk.",1,\n";
				file_put_contents(AREAFILE,$arealist,LOCK_EX);

				$br['endflg'] = $br['stopflg'] ='';
				file_put_contents(FLAGFILE,'0,,,',LOCK_EX);

				$artime = $chk;
				break;
			}

			$br['ar'] += ADD_AREA;
			$br['hackflg'] = 0;

			logsave('AREAADD',$br['ar'],$br['ar']-ADD_AREA,$artime);

			$artime += 86400;

			//長期間アクセスがなくまだ禁止エリアが追加可能な場合
			//禁止エリアによる死亡や移動処理は後回しにして禁止エリアを追加する
			if($br['ar'] < $br['arcnt'] and $artime <= NOW){
				continue;
			}

			$userlist = glob(U_DATADIR."*".U_DATAFILE);
			if($userlist){
				$dead_area = array_slice($br['ara'],0,$br['ar']);
				foreach($userlist as $filename){
					$mem = getuserdata($filename);
					//禁止エリア滞在関連の処理
					//次のいずれかの条件を満たす場合は処理をしない
					//1.禁止エリアにいない
					//2.既に死亡している
					//3.ボスNPC
					//4.全てのエリアが禁止エリアになった場合の一部NPC
					if(!in_array($mem['pls'],$dead_area) or $mem['hit'] <= 0 or $mem['type'] == "NPC0"){
						continue;
					}
					if($mem['type'] == 'NPC2' and $br['ar'] >= $br['arcnt']){
						continue;
					}

					//ボス以外のNPCは時間切れにならない限り禁止エリアから移動する
					if(preg_match("/^NPC/",$mem['type']) and $br['ar'] < $br['arcnt']){
						$mem['pls'] = $br['ara'][mt_rand($br['ar'],$br['arcnt']-1)];
					}else{
						$mem['hit'] = 0;
						$mem['sts'] = '死亡';
						$mem['death'] = '禁止エリア滞在';
						logsave('DEATHAREA',$mem);
					}
					save($mem,true);
				}//foreach
			}

			$arealist[0] = $artime.",".$br['ar'].",\n";
			file_put_contents(AREAFILE,$arealist,LOCK_EX);

			//禁止エリア数がエリア数以上になった
			//つまり全エリアが禁止エリア(時間切れ)
			if($br['ar'] >= $br['arcnt']){
				logsave('TIMELIMIT');
				$br['endflg'] = '終了';
				if(is_int(AUTORESET) and AUTORESET >= 1){
					file_put_contents(TIMEFILE,(mktime(0,0,0,$br['month'],$br['mday'],$br['year']) + 86400 * AUTORESET));
				}elseif(in_array(AUTORESET,array('sun','mon','tue','wed','thu','fri','sat'),true)){
					file_put_contents(TIMEFILE,strtotime('next '.AUTORESET));
				}
			}

			file_put_contents(FLAGFILE,"{$br['hackflg']},{$br['endflg']},{$br['stopflg']},",LOCK_EX);
			break;

		}//while
	}
}


//自動初期化の条件を満たしていた場合、自動初期化を行う。
if(mb_ereg('終了',$br['endflg'])){
	if((is_int(AUTORESET) and AUTORESET >= 1) or in_array(AUTORESET,array('sun','mon','tue','wed','thu','fri','sat'),true)){
		if(NOW >= (int)file_get_contents(TIMEFILE)){
			require LIBDIR.'reset.php';
			datareset();
		}
	}
}