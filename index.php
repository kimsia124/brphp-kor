<?php
/*--------------------------------------------------------------------
 *	BRトップページ
 *------------------------------------------------------------------*/
require 'pref.php';
$c_id = (isset($_COOKIE['id']))? $_COOKIE['id']:"";
$c_pass = (isset($_COOKIE['pass']))? $_COOKIE['pass']:"";

$regist = '<a href="regist.php">新規登録</a>';

if(file_exists(MEMBERFILE)){
	list($m,$f,$mc,$fc) = explode(",",file_get_contents(MEMBERFILE));
	$listnum = ($mc+$fc) * MANMAX + ($m+$f);
}else{
	$listnum = 0;
}

//登録可能かどうかの判定
if($br['stopflg'] == '未初期化'){
	$regist = '登録停止中(準備未完了)';
}elseif($br['stopflg'] == '開始前'){
	$regist = '登録停止中(PG開始前)';
}elseif($listnum >= MAXMEM){
	$regist = '登録停止中（定員オーバー'.MAXMEM.'人）';
}elseif(mb_ereg('終了',$br['endflg']) or $br['ar'] >= ((REGIST_LIMIT * ADD_AREA) + 1)){
	$regist = '登録停止中（締め切り）';
}elseif(($br['stopflg'] == '開始前登録')){
	$regist = '<a href="regist.php">新規登録(事前登録)</a>';
}

//--------------------------------------------------------------------
//  トップページ画面
//--------------------------------------------------------------------
head('top');
echo '<h1>'.TITLE.'</h1>';
?>
<p>この国にはある『プログラム』があった。</p>
<p><em>『共和国戦闘実験第六十八番プログラム』</em></p>
<p>全国の中学３年の５０クラスを選抜。<br>
そしてクラスメイトが<em>『最後の一人』</em>になるまで戦う。<br>
最後に生き残った生徒だけが家に戻れるという<em>『殺人ゲーム』</em>であった・・・。</p>
<h2>ゲーム続行</h2>
<form method="post" action="br.php">
ＩＤ：<input size="12" type="text" name="id" maxlength="12" value="<?php echo $c_id?>">
　パスワード：<input size="12" type="password" name="pass" maxlength="12" value="<?php echo $c_pass?>">
　<button type="submit" name="Command" value="LOGIN">実行</button>
</form>
<ul id="NAVI">
	<li><a href="rule.html">説明書</a>
	<li><?php echo $regist?>
	<li><a href="rank.php">生存者一覧</a>
	<li><a href="news.php">進行状況</a>
	<li><a href="map.php">会場地図</a>
	<li><a href="messenger.php">メッセンジャー</a>
	<li><a href="win.php">歴代優勝者</a>
	<li><a href="admin.php">管理モード</a>
</ul>
<?php
foot();