<?php
/* ガチャAPI */

// 外部ファイルの読み込み
require_once("../utility.php");

// 以下のコメントを外すと実行時エラーが発生した際にエラー内容が表示される
// ini_set('display_errors', 'On');
// ini_set('error_reporting', E_ALL);

//-------------------------------------------------
/* 定数 */
	// キャラクター数
	define('MAX_CHARA', 10);

	// ガチゃ1回の価格
	define('GACHA_PRICE', 300);
//-------------------------------------------------

//-------------------------------------------------
/* 実行したいSQL */
	// Userテーブルから所持金を取得
	$sql1 = 'SELECT money FROM user WHERE id=:userid';

	// Userテーブルの所持金を減産
	$sql2 = 'UPDATE user SET money=money-:price';

	// UserCharaテーブルにキャラクターを追加
	$sql3 = 'INSERT INTO userChara(user_id, chara_id) VALUES(:userid,:charaid)';

	// Charaテーブルから1レコード取得
	$sql4 = 'SELECT * FROM characterData WHERE id=:charaid';
//-------------------------------------------------

// ユーザーIDを受け取る
$uid = isset($_GET['uid'])?  $_GET['uid']:null;

// 入力が正しいかの判定
if( ($uid === null) || (!is_numeric($uid)) )
{
 	sendResponse(false, 'Invalid uid');
	exit(1);
}

// SQLを実行
try
{
	// データベースへのアクセスを開始する
	$dbh = beginAccess();
	
	// トランザクション開始
	$dbh->beginTransaction();

	// 指定のsql文を実行する（１回目）
	$sth = $dbh->prepare($sql1);
	$sth->bindValue(':userid', $uid, PDO::PARAM_INT);
	$sth->execute();
	
	// 結果を取得する
	$buff = $sth->fetch(PDO::FETCH_ASSOC);

	// ユーザーが存在しているかチェック
	if( $buff === false )
	{
		sendResponse(false, 'Not Found User');
		exit(1);
	}

	// 残高が足りているかチェック
	if( $buff['money'] < GACHA_PRICE )
	{
		sendResponse(false, 'The balance is not enough');
		exit(1);
	}

	// 指定のsql文を実行する（２回目）
	$sth = $dbh->prepare($sql2);
	$sth->bindValue(':price', GACHA_PRICE, PDO::PARAM_INT);
	$sth->execute();

	// キャラクターを抽選
	$charaid = random_int(1, MAX_CHARA);
	
	// 指定のsql文を実行する（３回目）
	$sth = $dbh->prepare($sql3);
	$sth->bindValue(':userid',  $uid,     PDO::PARAM_INT);
	$sth->bindValue(':charaid', $charaid, PDO::PARAM_INT);
	$sth->execute();

	// 指定のsql文を実行する（４回目）
	$sth = $dbh->prepare($sql4);
	$sth->bindValue(':charaid', $charaid, PDO::PARAM_INT);
	$sth->execute();
	
	// 結果を取得する
	$chara = $sth->fetch(PDO::FETCH_ASSOC);

	// トランザクション確定
	$dbh->commit();
}
catch( PDOException $e )
{
	// ロールバック
	$dbh->rollBack();

	// 本来エラーメッセージはサーバ内のログへ保存する(悪意のある人間にヒントを与えない)
	sendResponse(false, 'Database error: '.$e->getMessage());  
	exit(1);
}

// 実行結果を返却
returnResult($chara);


