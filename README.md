PHP판 BATTLE ROYALE Ver 2.2.0

Copyright (C) 2001,2002 bacchus.
All rights reserved.
원작자(Perl판): bacchus
E-mail:         battle@happy-ice.com
WebSite:        http://www.happy-ice.com/battle/
※ 현재 위의 주소는 존재하지 않습니다. 메일 전송 또한 불가합니다.

PHP판 제작자: Minon
E-mail:    mail@hexa.bz
WebSite:   https://hexa.bz/


◆PHP 버전 이용 규정
  1 : 본 PHP 스크립트는 프리웨이입니다.
      개인 사용에 한해 자유롭게 이용 가능하지만, 저작권은 제작자에게 있습니다.
  2 : 이용자는 스크립트의 저작권 표시를 삭제할 수 없습니다.
  3 : 스크립트 개조는 자유입니다.
  4 : 재배포는 불가능합니다.
  5 : 모든 환경에서의 동작을 보장하지 않습니다.
  6 : 이 스크립트를 이용하며 발생하는 문제에 대해서
      제작자는 일체의 책임을 지지 않습니다.
  7 : 이용에 따라 제작자에게 불이익이 되는 경우 스크립트의 사용을
      강제로 중지시킬 수 있습니다.
  8 : 본 이용 규정은 예고 없이 개편 및 가필될 수 있습니다.


◆ 설치 가능 서버
  1 : PHP 5.3.0 버전 이상
      2014년 5월 현재 주로 사용되는 5.3 이상 버전을 권장합니다.
      다만 PHP 5.3은 2014년 8월 14일 공식 지원이 종료되었습니다.
  2 : 멀티 바이트 문자열 함수(mb_로 시작되는 함수)를 이용할 수 있어야 함.
      국내 (일본) 서버라면 문제가 없지만, 해외서버라면 이용하지 못할 수도 있습니다.

※ 위의 조건을 충족시키면 실행 가능하지만 설치 후 서버 부하로 인해
  계정이 정지, 삭제되어도 책임지지 않습니다.


◆ 파일 구성
brphp\
├ admin.php            관리 모드
├ br.php               BATTLEROYALE 본체
├ br.css               BR CSS 파일
├ br.js                javascript 파일
├ config.php           설정 파일
├ index.php            톱페이지
├ map.php              회장 지도
├ messenger.php        BR 메신저
├ news.php             진행 상황
├ pref.php             공통 처리 파일
├ rank.php             생존자 목록
├ regist.php           등록 처리
├ win.php              우승자 목록
├ readme.txt           Read me (업로드 불필요, * 한국어 버전은 readme.md)
├ rule.html            설명서
├ rule.css             설명서용 CSS 파일
├ dat\                 고정정보 저장 폴더 (폴더명 변경 필요)
│ ├ customiza.txt     각종 csv 파일 커스터마이징 방법.
│ ├ item.csv          각 지역에 배치되는 아이템 데이터 파일
│ ├ npc.csv           NPC 데이터 파일
│ ├ stitem.csv        아이템 등록 데이터 파일
│ └ weapon.csv        배포 무기 데이터 파일
├ img\                 이미지 저장 폴더
├ lib\                 각종 함수
│ ├ action.php        각종 액션 커맨드
│ ├ attack.php        전투 처리
│ ├ command.php       명령 표시
│ ├ dsp_battle.php    전투 화면
│ ├ dsp_main.php      메인 스테이터스 화면
│ ├ dsp_radar.php     레이더 화면
│ ├ ending.php        엔딩
│ ├ event.php         에리어 이벤트
│ ├ item1.php         아이템 처리 1 (획득, 사용, 투기, 장비 해제)
│ ├ item2.php         아이템 처리 2 (전리품 획득, 총알·화살 꺼내기, 정리, 양도)
│ ├ item3.php         아이템 처리 3 (합성)
│ ├ lib1.php          다양한 스크립트에서 이용되는 함수
│ ├ lib2.php          br.php에서 이용되는 함수
│ ├ move.php          이동, 탐색 처리
│ └ reset.php         초기화
└ log\                 각종 로그 데이터 저장 폴더 (폴더명 변경 필요)
   ├ item\             아이템 데이터 저장 폴더
   │ └ \**_item.log    각 지역의 아이템(** = 지역 번호)
   ├ back\             사용자 별 백업 데이터 저장 폴더
   │ └ *\**_back.log   백업 데이터 (*** = 유저 ID)
   ├ user\             사용자 별 데이터 저장 폴더
   │ └ *\**_data.log   사용자 데이터(*** = 유저 ID)
   ├ area.log          금지 지역 데이터
   ├ admin.log         메신저에서 관리자에게 보내는 메시지 로그
   ├ flag.log          플래그 파일
   ├ member.log        등록 인원 데이터
   ├ messenger.log     메신저 메시지 데이터
   ├ mes_mem.log       메신저 로그인 멤버 데이터
   ├ news.log          진행 상황 데이터
   ├ sound.log         소리 데이터
   ├ time.log          시작 시간
   └ win.log           우승자 데이터
※ 각 폴더 안 index.html은 폴더 내부 파일 확인을 막기 위한 더미 파일입니다. 


◆ 갱신 이력 (번역 안함)
2016-02-23 2.1.1 -> 2.2.0
  1:item.csvで使用可能な構文を追加しました。追加したのは次の通り
      * A|B:AまたはB
            エリア番号、効果、回数で使用可能です。
            エリア番号で使用した場合、エリア番号AかBに設置されます
            設置数が複数の場合、一つずつエリアAかBにランダムで割り振ります
            効果または回数で使用した場合、効果または回数がAかBの何れかになります
            3つ以上連結可能でA|B|Cと記述するとAまたはBまたはCとなります
        A&B:AかつB
            エリア番号でのみ使用可能です。エリアAとBの両方に設置します
            設置数が複数の場合、指定した数をAとBの両方に設置します
            3つ以上連結可能でA&B&CでエリアA,B,Cに指定された数が設置されます
        A-B:AからB
            効果または回数で使用可能です
             A以上B以下となるような値がランダムに決定されます
    使用例:
      1|3|5,10,弾丸<>Y,1,10|20,
        →効果1、回数10または20のいずれかの「弾丸<>Y」を合計で10個になるようにZ
          エリア番号1または3または5に設置します
      3&6&9,7,石<>WC,5|10|15,15-30,
        →効果5または10または15、回数15から30のいずれかになる「石<>WC」を
          エリア番号3と6と9に7個ずつ設置します
  2:一度に追加される禁止エリアの数をconfig.phpで変更できるようになりました
  3:MAPの形状をconfig.phpで変更できるようになりました
    変更すると会場地図とレーダー画面に変更が反映されるようになります
  4:自動初期化を終了からX日後だけでなく、X曜日でも行えるようになりました
  5:敵発見時、敵殺害時、死体発見時に規定のコマンド以外のコマンドを実行しようと
    するとエラーになるようにしました(多重窓対策)
  6:使用しているPHPのバージョンが5.3でshort_open_tagがoffの環境の時、
    画面の表示が壊れてしまっていたのを修正しました
  7:使用しているPHPのバージョンが5.3の時Noticeが大量発生するのを修正しました
  8:武器を装備していない時に敵と遭遇するとNoticeが発生するのを修正しました
  9:事前登録受付時に登録人数が設定された人数に達していてもトップページから
    regist.phpにアクセスが出来ていたのを修正しました
    (regist.phpでも人数チェックはされるので元々登録は出来ませんが)
 10:NPCのレベルを1にした場合、初期経験値がマイナスになってしまっていたのを
    修正しました
 11:説明書で前のバージョンで行われた記述の追加に漏れがあったので追記しました
 
2015-10-09 2.1.0 -> 2.1.1a
  1.回復コマンドの回復処理が2箇所(attack.php、dsp_main.php)あったのを
    lib2.phpに移動し1箇所に統一しました
  2.現在の経験値が次のレベルに必要な経験値を大幅に上回っていた場合、従来は1回の
    戦闘毎にレベルが1ずつ上がっていたのを一気に上昇するようにしました
  3.レベルアップ時に上昇した能力値を表示するようにしました
  4.登録時の名前チェックで「々」が引っかからないようにしました
  5.エディタによってはregist.phpが開けなかったり一部文字が想定通りに
    扱えない問題があったため対処を行いました
  6.バージョン2.0.0で$br['NOW']に変更し、2.1.0で削除したのを
    define('NOW',$_SERVER['REQUEST_TIME']);に変更しました。
    2.1.0では「$_SERVER['REQUEST_TIME']」と書く所を「NOW」で良くなっています
  7.datディレクトリ内の.datファイルの拡張子をcsvに変更し環境によっては
    編集ができなかった場合があったのを回避しました。
    これによりCSVに対応したエディタで開くと見やすくなるかもしれません
  8.error_reportingがE_ALLな環境の時に治療の続行を行った場合、Noticeの
    メッセージが出ていたのを修正しました
  9.回復コマンド実行時のコマンド表示を～続行、～解除に変更しました
 10.基本方針、応戦方針の微調整を行いました
 11.出力されるHTML、使用するCSSの微修正を行いました
 12.説明書に現行のバージョンに合わせて若干の記述の追加を行いました
 13.前のバージョン(2.1.0)でNPC生成のコード等、テスト用の設定、コードが
    残ってしまっていたのを修正しました
 14.行動時に相手に先手を取られた場合、相手のログには「戦闘」ではなく「奇襲」と
    表示するように変更(2.1.0で変更していたのですが記載を忘れてました)
 15.その他挙動がほぼ変わらないようなコードの微修正

2015-03-25 2.0.0 -> 2.1.0
  1.出力するHTMLをHTML5に。見た目の制御に関してCSSへの依存度が高くなりました
    古いブラウザだとレイアウトが崩れる可能性が非常に高いです
    次に記載されているバージョンで想定通りの表示になっていることを確認済みです
    (Safari以外はWindows 8.1、Internet Explorer以外はOSX Yosemiteで確認)
      * Internet Explorer 11
      * Mozilla Firefox 35
      * Google Chrome 40
      * Safari 8
    また、Windows7+IE10でもレイアウトに関しては問題ないことを確認済みです。
    (ただしレイアウト以外の部分で若干問題があります)
    調べたところ、今回のバージョンで使用した記述に対応したブラウザに
    ついては次の通りとなっています。
      * Internet Explorer 10以降
      * Mozilla Firefox 28以降
      * Google Chrome 21以降
      * Safari 6.1以降
      * Safari(iOS) iOS7以降
      * Android標準のブラウザ Android4.4以降
    この条件をクリアしていればレイアウトに関しては恐らく大丈夫ですが
    確認はしていないので保障はできません。
  2.出力されるHTMLの中で省略しても問題無い終了タグを省略することで
    出力されるHTMLを若干軽量化
  3.前回$br['XXX'] = 'YYY';に変更したところをdefine('XXX','YYY')に戻しました
  4.メッセンジャーから管理者にメッセージを送ることが出来るようにしました
    管理者へのメッセージは管理モードから確認できます
  5.管理モードからニュースにメッセージを載せることが出来るようにしました
  6.メッセンジャーでページの更新のみを行うボタンを追加しました
  7.初期化時にすぐ開始しない場合、事前登録が可能かどうかトップページや
     ニュースで分かるようにしました
  8.口癖設定や管理モードでの個別修正等、入力がユーザーデータとなる処理について
    一部の文字(&,<,>等)が2重に変換されないように修正しました
  9.管理モードでの個別修正にてグループの修正処理が誤っていたのを修正しました
    (前回のコード変更絡みでの修正忘れだったのですが3の修正により結果的に修正)
 10.説明書での作者のサイトのURLが昔のまま修正されてなかったのを修正しました
 11.説明書のCSSの記述を外部ファイル(rule.css)へ移動しました

2014-06-01  1.3.1 -> 2.0.0
  1.相当長い期間が空いたためPHP4のみを使用するサーバーがほぼ無くなったことと、
    今のPHP5の環境で意図しない動作をすることがあった為、PHP5向けのコードに修正
    これに伴いPHP4環境のサポートを打ち切りました
  2.テスト向け環境(PHP本体の設定でerror_reportingがE_ALLな環境)でNotice等の
    メッセージが大量に出ていたのを修正。
    潰し切れていない場所があるかも知れません。
  3.以前のバージョンではdefine('XXX','YYY')と記述していた設定関連の記述を
    $br['XXX'] = 'YYY'に統一
    これに伴いかなり広範囲にわたってコードが変更されました
  4.pref.phpから設定関連の部分を抜き出してconfig.phpに分離
  5.ユーザーデータのファイルの命名規則を変更。
    これに伴いユーザーデータ取得絡みのコードが変わりました。
  6.datディレクトリ内の各種datファイルにてコメントを使用可能に
    「##」(シャープ2つ)で始まる行はコメントとして扱い、無視されます。
    データ量を増やす時や整理する時等にメモを残すなどの使い方が出来ます。
  7.キャラクタ番号の設定にミスがあって番号が重複していたのを修正
    これまでのバージョンでは最初の方に登録するキャラとNPCの番号が重複してました
    そのためNPCを使ったりすると生存者一覧でNPC→PCの順に表示されませんでした
  8.初期化時に開始時間を設定する際、「次の時間に開始」を選択した場合、
    指定した時間に関係なくすぐ開始してしまっていたのを修正
  9.アイテム譲渡に重大なミスがあったのを修正
    同じ場所にいればグループに関係なく譲渡出来るようになってしまっていました
 10.複数登録及び死亡後の再登録の二重チェックが怪しかったのを修正した、と思う
    コードを大幅に変えたのでもっと怪しくなったかもしれないですが。
 11.男子のクラス分けが正しく行われていなかったのを修正(変数名ミスってた･･･)
 12.メッセンジャーでキャラが死亡していても会話出来るように変更
 13.メッセンジャーでこちらからの送信メッセージの相手が分かりにくかったため
    誰に送ったか分かるようにした
 14.生存者一覧でのコメントで改行をサポート
 15.優勝者の個別閲覧にて最大スタミナ、経験値ベース、熟練度ベースの数値を
    開催を重ねるうちに変更しても元々の数値に合わせられるようにした
 16.所々でlabelタグを使ってちょっとだけ操作性を向上
 17.所々で<input type="submit">の代わりに<button type="submit">を使用するように
    この変更によりバージョン7以前のIEでは正しく動作しません。
 18.作者のサイトのアドレス及びメールアドレスの変更
 19.その他、見た目がほとんど変わらないが細かいコード変更を行う

2007-07-01  1.3.0 -> 1.3.1
  1.初期化時にすぐ開始しない場合、進行状況での開始時間がおかしかったのを修正

2007-06-25  1.2.2 -> 1.3.0
  1.プレイヤーが所持できるアイテムの最大数を簡単に変更できるようにした
    (この変更の影響で前のバージョンの優勝者データの互換性が無くなりました)
  2.初期化時に開始時間を設定できるようにした
  3.初期化時にすぐ開始しない場合は事前登録を認めるかどうか決められるようにした

2007-04-11  1.2.1 -> 1.2.2
  1.敵発見時、敵殺害時、死体発見時に誤ってブラウザのウィンドウ又はタブを閉じて
    再ログインした場合にその画面からやり直せるようにした
  2.ログインしても睡眠、治療、休憩が解除されないようにした
  3.登録時の名前チェックで「ヵ」「ヶ」が引っかからないようにした
  4.その他細かい修正

2006-09-07  1.2.0 -> 1.2.1
  1.属性が異なっていてアイテム名が同じアイテム同士を合成しようとした場合に
    「合成に同じアイテムは選択できません」というメッセージが出なかったのを修正
  2.アイテムを複数使用した場合に使用が無効になることがあったのを修正
  3.レーダー系のアイテムが使えなかったのを修正
    ちなみにレーダー系のアイテムを使用すると以降のアイテムは使われないのは仕様
  4.レーダーを使用した画面で禁止エリアが×になっていなかったのを修正
  5.エラーメッセージを表示させていた場合にWarningが頻繁に出ていたのを修正

2006-09-04  1.1.0 -> 1.2.0
  1.アイテム合成のスクリプトを再び書き直した
  2.アイテムを同時に複数個使用できるようにした

2006-05-22  1.0.1 -> 1.1.0
  1.アイテム合成のスクリプトを全面的に書き直した
  2.登録画面で簡易入力チェックを行うjavascriptを追加
  3.ランダムなエリアに配置されるNPCの配置がおかしかったのを修正
  4.禁止エリアから移動するはずのNPCが禁止エリアで死亡してしまっていたのを修正
  5.大きなサイズの投稿へ対処し忘れていたので対処

2006-03-11  1.0.0 -> 1.0.1
  1.バックアップ読み込みが正しく動作しなかったのを修正
  2.毒中和を使えなかったのを修正
  3.スピーカを使用したときにメッセージが保存されなかったのを修正
  4.ハッキングが100%成功してしまっていたのを修正
  5.lib2.phpにあったfunction membercountをlib1.phpに移動した
  6.move.phpの連闘行動の部分に誤りがあったのを修正
  7.先手を取られて死亡した場合、相手に戦闘のログが表示されていなかったのを修正
  8.各ディレクトリ内にある空のindex.htmlにBRのトップページへのリンクを加えた

2006-03-05  1.0.0
  1.PHPに移植した一番最初のバージョン