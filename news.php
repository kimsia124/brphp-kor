<?php
//--------------------------------------------------------------------
//	進行状況
//--------------------------------------------------------------------
require 'pref.php';

$ars = implode(" ",array_slice($br['kin_ar'],0,$br['ar']));
$ars2 = implode(" ",array_slice($br['kin_ar'],$br['ar'],ADD_AREA));

head('news');
?>
<h1>進行状況</h1>
<p>『みんなー、元気にやってるかあ。<br>
  それじゃ、これまでの状況でーす。<br>
  今日も一日がんばろーなー。』</p>
<dl id="DEATHAREA">
<dt>現在の禁止エリア</dt>
<dd><?php echo $ars?></dd>
<dt>次回の禁止エリア</dt>
<dd><?php echo $ars2?></dd>
</dl>
<?php

$getmonth = $getday = 0;

if(file_exists(NEWSFILE)){
	foreach(file(NEWSFILE) as $loglist){
		list($logtime,$kind,$data1,$data2,$data3,$f_name,$l_name,$sex,$cl,$no,$f_name2,$l_name2,$sex2,$cl2,$no2,) = explode(",",$loglist);
		list($month,$mday,$wday,$time) = explode('-',date('m-d-w-H時i分',(int)$logtime));
		if($getmonth != $month or $getday != $mday){
			if($getmonth !== 0){echo "\n</ul>";}
			$getmonth = $month;
			$getday = $mday;
			echo '<p class="date">'.$month.'月 '.$mday.'日 ('.$br['WEEK'][$wday]."曜日)<ul>\n";
		}

		if(preg_match("/DEATH/",$kind) and $data3){
			$dat = '<span class="message">('.$data3.')</span>';
		}else{
			$dat = $data3;
		}

		switch($kind){
			case "ENTRY"://新規登録
				$dat = (HOST_VIEW)? "({$data1})":"";
				echo '<li class="entry">'.$time.'、<span>'.$f_name.' '.$l_name.'('.$cl.' '.$sex.$no.'番)</span> が 転校してきた。'.$dat;
				break;
			case "DEATH1"://殺害
				echo '<li class="dead">'.$time.'、<span>'.$f_name.' '.$l_name.'('.$cl.' '.$sex.$no.'番)</span> が 死亡した。'.$dat;
				break;
			case "DEATH2"://返り討ち
				echo '<li class="dead">'.$time.'、<span>'.$f_name.' '.$l_name.'('.$cl.' '.$sex.$no.'番)</span> が 死亡した。'.$dat;
				break;
			case "DEATH3"://衰弱死
				echo '<li class="dead">'.$time.'、<span>'.$f_name.' '.$l_name.'('.$cl.' '.$sex.$no.'番)</span> が 死亡した。'.$dat;
				break;
			case "DEATH4"://イベント死
				echo '<li class="dead">'.$time.'、<span>'.$f_name.' '.$l_name.'('.$cl.' '.$sex.$no.'番)</span> が 死亡した。'.$dat;
				break;
			case "DEATH5"://毒死
				echo '<li class="dead">'.$time.'、<span>'.$f_name.' '.$l_name.'('.$cl.' '.$sex.$no.'番)</span> が 死亡した。'.$dat;
				break;
			case "DEATH6"://罠死
				echo '<li class="dead">'.$time.'、<span>'.$f_name.' '.$l_name.'('.$cl.' '.$sex.$no.'番)</span> が 死亡した。'.$dat;
				break;
			case "DEATH7"://処刑(管理)
				echo '<li class="dead">'.$time.'、<span>'.$f_name.' '.$l_name.'('.$cl.' '.$sex.$no.'番)</span> が 政府により処刑された。'.$dat;
				break;
			case "DEATH8"://処刑(ハック失敗)
				echo '<li class="dead">'.$time.'、<span>'.$f_name.' '.$l_name.'('.$cl.' '.$sex.$no.'番)</span> が 政府により処刑された。'.$dat;
				break;
			case "DEATHAREA"://禁止エリア滞在
				echo '<li class="dead">00時00分、<span>'.$f_name.' '.$l_name.'('.$cl.' '.$sex.$no.'番)</span> が 禁止エリアの為、死亡した。'.$dat;
				break;
			case "GROUP1"://グループ結成
				echo '<li class="group">'.$time.'、<span>'.$f_name.' '.$l_name.'('.$cl.' '.$sex.$no.'番)</span> が <span>『'.$data1.'』</span>を結成した。';
				break;
			case "GROUP2"://グループ加入
				echo '<li class="group">'.$time.'、<span>'.$f_name.' '.$l_name.'('.$cl.' '.$sex.$no.'番)</span> が <span>『'.$data1.'』</span>に加入した。';
				break;
			case "GROUP3"://グループ脱退
				echo '<li class="group">'.$time.'、<span>'.$f_name.' '.$l_name.'('.$cl.' '.$sex.$no.'番)</span> が <span>『'.$data1.'』</span>を脱退した。';
				break;
			case "SPEAKER"://スピーカーなどで叫ぶ
				echo '<li class="speaker">'.$time.'、<span>'.$f_name.' '.$l_name.'('.$cl.' '.$sex.$no.'番)</span> が <span>『'.$data1.'』</span>と叫んだ。';
				break;
			case "HACK"://ハッキング成功
				echo '<li class="hack">'.$time.'、<span>'.$f_name.' '.$l_name.'('.$cl.' '.$sex.$no.'番)</span> が <span>ハッキングに成功した</span>。';
				break;
			case "AREAADD"://禁止エリア追加
				echo '<li class="addarea">00時00分、<span>'.implode("、",array_slice($br['kin_ar'],$data2,ADD_AREA)).'</span> が 禁止エリアに指定された。';
				if($data1 < $br['arcnt']){
					echo '次回禁止エリアは<span>'.implode("、",array_slice($br['kin_ar'],$data1,ADD_AREA)).'</span>';
				}
				break;
			case "ADMINMESSAGE":
				echo '<li class="admin">'.$time.'、<span>管理者『'.$data1.'』</span>';
				break;
			case "WINEND"://優勝者決定
				echo '<li class="end">'.$time.'、<span>ゲーム終了・以上本プログラム実施本部選手確認モニタより</span>';
				break;
			case "EXEND"://ハッキングによる停止
				echo '<li class="end">'.$time.'、<span>ゲーム終了・プログラム緊急停止</span>';
				break;
			case "TIMELIMIT"://時間切れ
				echo '<li class="end">'.$time.'、<span>ゲーム終了・時間切れにより優勝者無し</span>';
				break;
			case "NEWGAME"://初期化
				echo '<li>'.$time.'、新規プログラム開始。';
				break;
			case "PREPARATION"://準備
				$a = ($br['ar'] == 0 and $br['stopflg'] != '開始前')? "現在登録受付中です。":"";
				echo '<li>'.$time.'、新規プログラム開始準備が完了。'.$data1.'から開始となります。'.$a;
				break;
		}
	}
	echo '</ul>';
}else{
	echo "<p><em>ニュースファイルがありません。一度初期化を行ってください。</em></p>\n";
}

?>
<p class="back"><a href="<?php echo HOME?>">HOME</a></p>
<?php
foot();