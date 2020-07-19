<?php // MySQLに接続しデータを追加する

// 外部ファイルの読み込み
require_once("../utility.php");
require_once("../../model/userModel.php");

// 以下のコメントを外すと実行時エラーが発生した際にエラー内容が表示される
// ini_set('display_errors', 'On');
// ini_set('error_reporting', E_ALL);

// SQLを実行
try
{
	$user = new UserModel();
	$user->addNewUser();
}
catch(PDOException $e)
{
	// 本来エラーメッセージはサーバ内のログへ保存する(悪意のある人間にヒントを与えない)
	sendResponse(false, 'Database error: '.$e->getMessage());
	exit(1);
}

//sendResponse(true, $user->m_userToken);
//exit(1);

// 作成したユーザーのIDの取得に失敗した場合
if($user->m_userId === null)
{
	// エラーを返す
	sendResponse(false, 'Database error: can not fetch LAST_INSERT_ID()');
}
// データを正常に取得した場合
else
{
	sendResponse(true, $user->m_userToken);
}