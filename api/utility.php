<?php

// データベースへの接続に必要な情報
class AccessPointDef
{
	// 接続先を定義
	public static $dsn  = 'mysql:dbname=sgrpg;host=127.0.0.1';
	// MySQLのユーザーID
	public static $user = 'senpai';
	// MySQLのパスワード
	public static $pw   = 'indocurry';
}

// データベースへのアクセスを開始する
function beginAccess()
{
	// 接続に必要な情報を入力
	$dbh = new PDO(AccessPointDef::$dsn, AccessPointDef::$user, AccessPointDef::$pw);
	
	// 問題発生時に例外を出すモードで実行するように設定
	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	
	return $dbh;
}

// 実行結果をJSON形式で返却する
function sendResponse($status, $value=[]){
  header('Content-type: application/json');
  echo json_encode([
    'status' => $status,
    'result' => $value
  ]);
}

// 実行結果が存在するかチェックした後にJSON形式で返却する
function returnResult($value=[])
{
	//-------------------------------------------------
	// 実行結果を返却
	//-------------------------------------------------
	// データが0件
	if( $value === false ){
	  sendResponse(false, 'System Error');
	}
	// データを正常に取得
	else{
	  sendResponse(true, $value);
	}
}
