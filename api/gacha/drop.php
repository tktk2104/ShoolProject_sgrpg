<?php // ガチャAPI

// 外部ファイルの読み込み
require_once("../utility.php");
require_once('../../model/userModel.php');
require_once('../../model/gachaModel.php');
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
$gacha = new GachaModel();
$chara_id = $gacha->randomChooseCharacterId();

try
{
	$user = new UserModel();
	// ユーザーIDを取得

	$uid = $user->getUserIdByToken($token);
	if($uid === false)
	{
	  	sendResponse(false, 'Not Found User');
	  	exit(1);
	}

	// トランザクション開始
	$user->begin();
	// お金を消費
	$ret = $user->useMoney($uid, 300);
	
	if($ret === false)
	{
		sendResponse(false, $user->getErrorMessage());
		exit(1);
	}

	// キャラクターを所有
	$user->addNewCharacter($uid, $chara_id);
	  
	$user->commit();
}
catch( PDOException $e )
{
  	$user->rollback();
  	sendResponse(false, 'Database error1: '. $user->getErrorMessage());
  	exit(1);
}

//-------------------------------------------------
// 実行結果を返却
//-------------------------------------------------
try
{
  	$chara = new CharacterModel();
  	$buff = $chara->getRecordById($chara_id);
}
catch(PDOException $e)
{
  	sendResponse(false, 'Database error2: '.$e->getErrorMessage());
  	exit(1);
}

// データが0件
if( $buff === false )
{
  	sendResponse(false, 'System Error');
}
// データを正常に取得
else
{
  	sendResponse(true, $buff);
}