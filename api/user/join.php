<?php
/* MySQLに接続しデータを追加する */

// 外部ファイルの読み込み
require_once("../utility.php");

// 以下のコメントを外すと実行時エラーが発生した際にエラー内容が表示される
// ini_set('display_errors', 'On');
// ini_set('error_reporting', E_ALL);

//-------------------------------------------------
/* 定数 */
	// 初期レベル
	define('DEFAULT_LV', 1);
	// 初期経験値
	define('DEFAULT_EXP', 1);
	// 初期所持金額
	define('DEFAULT_MONEY', 3000);
//-------------------------------------------------

//-------------------------------------------------
/* 実行したいSQL */
	// 新たなプレイヤーデータをデータベースに登録する
	$sql1 = 'INSERT INTO user(lv, exp, money) VALUES(:lv, :exp, :money)';
	// AUTO INCREMENTした値を取得する
	$sql2 = 'SELECT LAST_INSERT_ID() as id';
//-------------------------------------------------

// SQLを実行
try
{
	// データベースへのアクセスを開始する
	$dbh = beginAccess();

	// 指定のsql文を実行する（１回目）
	$sth = $dbh->prepare($sql1);
	$sth->bindValue(':lv',    DEFAULT_LV,    PDO::PARAM_INT);
	$sth->bindValue(':exp',   DEFAULT_EXP,   PDO::PARAM_INT);
	$sth->bindValue(':money', DEFAULT_MONEY, PDO::PARAM_INT);
	$sth->execute();

	// 指定のsql文を実行する（２回目）
	$sth = $dbh->prepare($sql2);
	$sth->execute();

	// 結果を取得する
	$buff = $sth->fetch(PDO::FETCH_ASSOC);
}
catch( PDOException $e )
{
	// 本来エラーメッセージはサーバ内のログへ保存する(悪意のある人間にヒントを与えない)
	sendResponse(false, 'Database error: '.$e->getMessage());
	exit(1);
}

// 実行結果を返却
returnResult($buff);


