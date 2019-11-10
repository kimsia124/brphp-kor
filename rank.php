<?php
//--------------------------------------------------------------------
//	生存者一覧
//--------------------------------------------------------------------
require 'pref.php';

$grp = (G_MODE)? "<th>グループ":"";
$ok = 0;

head('rank');
?>
<h1>生存者一覧</h1>
<table border="1">
<thead>
<tr>
<th>アイコン
<th>名前
<th>番号
<?php echo $grp?>
<th>コメント
</tr>
</thead>
<tbody>
<?php
$a = glob(U_DATADIR."*".U_DATAFILE);
$filelist = array();
if($a){
	foreach($a as $filename){
		list($no) = explode(",",file_get_contents($filename),2);
		$filelist[$no] = $filename;
	}
	ksort($filelist);
	foreach($filelist as $filename){
		$mem = getuserdata($filename);
		if($mem['hit'] <= 0){
			continue;
		}
		if(!$br['hackflg']){//ハックされていないときは一部のNPCも表示しない
			if(preg_match("/^NPC(0|2)$/",$mem['type'])){
				continue;
			}
		}
		$grp = (G_MODE)? "<td>{$mem['g_name']}":"";
		echo '<tr><td><img src="'.IMGDIR.$mem['icon'].'" width="70" height="70" alt=""><td>'.$mem['f_name'].' '.$mem['l_name'].'<td>'.$mem['cl'].'<br>'.$mem['sex'].$mem['no'].'番'.$grp.'<td>'.$mem['com']."</tr>\n";
		$ok++;
	}
}
?>
</tbody>
</table>
<p>【残り<?php echo $ok?>人】</p>
<p><a href="<?php echo HOME?>">HOME</a></p>
<?php
foot();
