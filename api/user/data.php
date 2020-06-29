<?php
/* MySQLに接続したデータを取得する */

// 外部ファイルの読み込み
require_once("../utility.php");

// 以下のコメントを外すと実行時エラーが発生した際にエラー内容が表示される
// ini_set('display_errors', 'On');
// ini_set('error_reporting', E_ALL);

//-------------------------------------------------
/* 実行したいSQL */
	// Userテーブルの指定列を取得
	$sql = 'SELECT * FROM user WHERE id=:id';
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
	
	// 指定のsql文を実行する
	$sth = $dbh->prepare($sql);
	$sth->bindValue(':id', $uid, PDO::PARAM_INT);
	$sth->execute();

	// 結果を取得する
	$buff = $sth->fetch(PDO::FETCH_ASSOC);
}
catch( PDOException $e )
{
	sendResponse(false, 'Database error: '.$e->getMessage());  // 本来エラーメッセージはサーバ内のログへ保存する(悪意のある人間にヒントを与えない)
	exit(1);
}

// 実行結果を返却
returnResult($buff);


