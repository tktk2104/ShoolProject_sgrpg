<?php // MySQLに接続したデータを取得する

// 外部ファイルの読み込み
require_once("../utility.php");
require_once("../../model/userModel.php");
require_once('../../model/characterModel.php');

// 以下のコメントを外すと実行時エラーが発生した際にエラー内容が表示される
// ini_set('display_errors', 'On');
// ini_set('error_reporting', E_ALL);

// GETコマンドでトークンを受け取る
$token = UserModel::getTokenfromQuery();

// トークンを受け取れなかった場合
if(!$token)
{
	// エラーを返して終了する
	sendResponse(false, 'Invalid token');
	exit(1);
}

// SQLを実行
try
{
	$user = new UserModel();

	$character = new CharacterModel();

	// トークンからユーザーIDを取得する
	$uid  = $user->getUserIdByToken($token);

	// ユーザーIDを取得出来ていたら
	if( $uid !== false )
	{
		$baseUserData = $user->getRecordById($uid);
		$userCharecterIdArray = $user->getUserCharacters($uid);
		$characterNameArray = $character->getUserCharacterNames($userCharecterIdArray);

		$buff = $baseUserData;
		$buff["chara"] = $characterNameArray;
	}
	else
	{
		// ユーザーIDの取得に失敗していた場合はbool型でfalseを返す
	  	$buff = false;
	}
}
catch( PDOException $e )
{
	// 本来エラーメッセージはサーバ内のログへ保存する(悪意のある人間にヒントを与えない)
	sendResponse(false, 'Database error: '.$e->getMessage());
	exit(1);
}

// 取得に失敗していた場合
if( $buff === false )
{
	sendResponse(false, 'Not Found user');
}
else
{
	sendResponse(true, $buff);
}