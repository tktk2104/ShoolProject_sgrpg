<?php
require_once('model.php'); // モデルクラスのインクルード

// ユーザーモデルクラス
class UserModel extends Model
{
//------------------------------------------------------------
    // メンバ変数達
    public      $m_userId               = null;     // ユーザーID
    public      $m_userToken            = null;     // ユーザートークン（クライアントとやり取りする時にユーザーを識別する時に使うランダムな６４バイトのバイナリデータ）
    protected   $m_useTableName         = 'user';   // 「user」テーブルを使用する
    
    private     $m_createdUserInitValue =           // 新規プレイヤー追加時の初期パラメータ
    [
        ['name'=>':lv',    'value'=>1,    'type'=>PDO::PARAM_INT],  // レベルは１
        ['name'=>':exp',   'value'=>1,    'type'=>PDO::PARAM_INT],  // 経験値は１
        ['name'=>':money', 'value'=>3000, 'type'=>PDO::PARAM_INT],  // お金は３０００
        ['name'=>':token', 'value'=>null, 'type'=>PDO::PARAM_STR]   // トークンは後で生成
    ];
//------------------------------------------------------------

//------------------------------------------------------------
    // 定数達
    const  TOKEN_LENGTH   = 32;  // 文字列として見たときのトークンデータの長さ（いわゆる【w_cahr:2byte】形式）
//------------------------------------------------------------

    // 「?token=xxx」の形式でトークンが渡されていたらそのトークンを返す（※指定の形式でトークンが渡されていなかったらbool型でfalseを返す）
    // TODO : GETでの取得からPOSTでの取得にする
    static function getTokenfromQuery()
    {
        // GETでトークンを取得する
        $token = isset($_GET['token'])?  $_GET['token']:null;

        // トークンが正しい型で渡されていなかったら
        if( ($token === null) || (!is_string($token)) || (strlen($token) !== self::TOKEN_LENGTH) )
        {
            // falseを返す
            return false;
        }
        else
        {
            // トークンを返す
            return $token;
        }
    }

    // 新しいユーザーを追加する
    function addNewUser()
    {
        // ユーザーを追加するためのsql文
        $addUserSql = 'INSERT INTO user(lv, exp, money, token) VALUES(:lv, :exp, :money, :token)';

        // トークンを新たに生成
        $token = $this->createToken();

        // 新規プレイヤー追加時の初期パラメータのトークンを更新する
        $this->m_createdUserInitValue[3]['value'] = $token;

        // ユーザーを追加するためのsqlを実行する
        $this->query($addUserSql, $this->m_createdUserInitValue);

        // 追加したユーザーのIDを取得するためのsql文
        $getNewUserIdSql = 'SELECT LAST_INSERT_ID() as id';

        // 追加したユーザーのIDを取得するためのsqlを実行する
        $this->query($getNewUserIdSql);

        // 追加したユーザーのIDを取得する
        $buff = $this->fetch();

        // 自身の変数にユーザー識別情報を保存する
        $this->m_userToken  = $token;       // トークンを保存
        $this->m_userId     = $buff['id'];  // IDを保存
    }

    // トークンからユーザーIDを取得する（※取得できなかったらbool型でfalseを返す）
    function getUserIdByToken($token)
    {
        // トークンからユーザーIDを取得するためのsql文
        $sql = 'SELECT id FROM user WHERE token=:token';

        // sql文に書き込むパラメータ
        $bind = [ ['name'=>':token', 'value'=>$token, 'type'=>PDO::PARAM_STR] ];
    
        // sqlを実行する
        $this->query($sql, $bind);

        // sqlの実行結果（ユーザーID）を取得する
        $buff = $this->fetch();
        
        // IDを取得できていたら
        if($buff !== false)
        {
            // IDを返す
            return $buff['id'];
        }
        else
        {
            // bool型でfalseを返す
            return false;
        }
    }

    // 指定IDに対応するユーザーの所持金を取得する（※取得できなかったらbool型でfalseを返す）
    function getMoney($userId)
    {
        // 引数のIDに対応したユーザー情報を全て取得する
        $buff = $this->getRecordById($userId);

        // ユーザー情報を取得できていたら
        if($buff !== false)
        {
            // 所持金を返す
            return $buff['money'];
        }
        else
        {
            // bool型でfalseを返す
            return false;
        }
    }

    // 指定IDに対応するユーザーの所持金を指定量だけ減らす（※減らすことに失敗した場合はbool型でfalseを返す）（※第３引数の値をfalseにすると所持金がマイナスになる可能性を許容する）
    function useMoney($userId, $decreaseMoney, $safety = true)
    {
        // 残高がマイナスにならないかのチェック
        if($safety)
        {
            // ユーザーの所持金を取得する
            $money = $this->getMoney($userId);

            // ユーザーの所持金が取得できない時、また、取得した金額から第２引数の値を引いた数が負の数になる時
            if(($money === false) || ($money - $decreaseMoney) < 0)
            {
                // 残金が足りないエラーを記録する
                $this->setErrorMessage('The balance is not enough');

                // bool型でfalseを返す
                return false;
            }
        }

        // 所持金を減らすためのsql文
        $sql  = 'UPDATE user SET money=money-:price WHERE id=:userid';

        // sql文に書き込むパラメータ
        $bind =
        [
            ['name'=>':price',  'value'=>$decreaseMoney,    'type'=>PDO::PARAM_INT],
            ['name'=>':userid', 'value'=>$userId,           'type'=>PDO::PARAM_INT]
        ];

        // sqlを実行する
        return $this->query($sql, $bind);
    }

    // 引数のユーザーに引数のキャラクターを新たに登録する
    function addNewCharacter($userId, $characterId)
    {
        // キャラクターを新たに登録するためのsql文
        $sql  = 'INSERT INTO userChara(user_id, chara_id) VALUES(:user_id,:chara_id)';

        // sql文に書き込むパラメータ
        $bind =
        [
          ['name'=>':user_id',  'value'=>$userId,       'type'=>PDO::PARAM_INT],
          ['name'=>':chara_id', 'value'=>$characterId,  'type'=>PDO::PARAM_INT]
        ];
    
        // sqlを実行する
        return $this->query($sql, $bind);
    }

    // 引数のユーザーが所持している全てのキャラクターのIDを配列で取得する
    function getUserCharacters($userId)
    {
        // キャラクターIDを取得するためのsql文
        $sql  = 'SELECT DISTINCT chara_id FROM userChara WHERE user_id=:user_id';

        // sql文に書き込むパラメータ
        $bind = [['name'=>':user_id', 'value'=>$userId, 'type'=>PDO::PARAM_INT]];

        // sqlを実行する
        $this->query($sql, $bind);

        // 戻り値の配列
        $result = [];

        // sqlの実効結果が取得できなくなるまでループする
        while (true)
        {
            // SQLの実行結果を取得する
            $fetchData = $this->fetch();
            
            // ループを抜ける処理
            if ($fetchData === false) break;

            // 「result」に追加する
            array_push($result, $fetchData);
        }
        return $result;
      }

    // トークンを新たに乱数を使って生成する
    private function createToken()
    {
        $len = self::TOKEN_LENGTH;

        // 定番の指定の長さのランダムバイナリデータ作成処理
        return ( substr(bin2hex(random_bytes($len)), 0, $len) );
    }
}