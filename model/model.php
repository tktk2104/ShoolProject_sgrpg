<?php 

// モデルの基底クラス
class Model
{
//------------------------------------------------------------
    // メンバ変数達
    private     $m_dataSourceName   = 'mysql:dbname=sgrpg;host=127.0.0.1';  // データベースの名前と接続するネットワークの情報
    private     $m_userName         = 'senpai';                             // 使用するmysqlのユーザー名
    private     $m_password         = 'indocurry';                          // 使用するmysqlのパスワード
    protected   $m_useTableName     = null;                                 // 使用するテーブル名
    private     $m_preErrorMessage  = ['message' => null];                  // 最後に発生したエラーメッセージ
    // 「PhpDataObjects」PHPからデータベースにアクセスするためのインターフェース
    private     $m_dataBaseHandle   = null;                                 // 上記のインターフェース（PDO）の機能を使用するためのデータベースハンドルを取得する
    private     $m_statementHandle  = null;                                 // SQLステートメント関数を実行するときに必要なハンドル
//------------------------------------------------------------
    
    function __construct($dataSourceName = null, $userName = null, $password = null)
    {
        // メンバ変数の初期化
        if ($dataSourceName !== null) $this->m_dataSourceName   = $dataSourceName;
        if ($userName       !== null) $this->m_userName         = $userName;
        if ($password       !== null) $this->m_password         = $password;
    }

    // データベースに接続する
    function connect()
    {
        // 「PhpDataObjects」のインスタンスを作り、データベースハンドルを取得する
        $this->m_dataBaseHandle = new PDO($this->m_dataSourceName, $this->m_userName, $this->m_password);

        // 問題発生時に例外を投げるように設定する
        $this->m_dataBaseHandle->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);    
    }
    
    // SQL文を実行する（※実行の成否がbool型で返る）
    // 「Structured English Query Language」?
    function query($sql, $bind = null)
    {
        // データベースハンドルが取得できていれば
        if($this->m_dataBaseHandle === null)
        {
            // DBへ接続する
            $this->connect();
        }
    
        // sql文をデータベースハンドルに設定してステートメントハンドルを取得する
        $this->m_statementHandle = $this->m_dataBaseHandle->prepare($sql);
        
        // 引数のsql文に書き込むパラメータの名前と値の配列が正しくこの関数に渡されていたら（nullチェック、配列型かのチェック）
        if($bind !== null && is_array($bind))
        {
            // 書き込むパラメータの名前と値の配列を巡回する
            for( $i=0; $i<count($bind); $i++ )
            {
                // パラメータの名前を取得する
                $name  = $bind[$i]['name'];

                // パラメータの値を取得する
                $value = $bind[$i]['value'];

                // パラメータの値の型情報を取得する
                $type  = $bind[$i]['type'];
                
                // 引数のsql文に書き込むパラメータの情報を設定する
                $this->m_statementHandle->bindValue($name, $value, $type);
            }
        }

        // SQLを実行して、成否をbool型で返す
        return $this->m_statementHandle->execute();
    }

    // SQLの実行結果を１行だけ配列で取得する（※取得に失敗した場合はbool型でfalseが返る）
    function fetch()
    {
        return $this->m_statementHandle->fetch(PDO::FETCH_ASSOC);
    }

    // トランザクションを開始する
    function begin()
    {
        // データベースハンドルが取得できていれば
        if($this->m_dataBaseHandle === null)
        {
            // DBへ接続する
            $this->connect();
        }

        // トランザクションを開始する
        $this->m_dataBaseHandle->beginTransaction();
    }

    // トランザクションを適用（コミット）する形で終了する
    function commit()
    {
        // 結果をコミット！！！
        $this->m_dataBaseHandle->commit();  
    }

    // トランザクションを破棄（ロールバック）する形で終了する
    function rollback()
    {
        // ロールバックする
        $this->m_dataBaseHandle->rollBack();
    }

    // idで検索した結果を取得する（※取得に失敗した場合はbool型でfalseが返る）
    function getRecordById($id)
    {
        // idで検索するsql文を作る
        $sql  = sprintf('SELECT * FROM %s WHERE id=:id', $this->m_useTableName);

        // sql文に書き込むパラメータの情報を作る
        $bind = [ ['name'=>':id', 'value'=>$id, 'type'=>PDO::PARAM_INT] ];

        // SQL文を実行する
        $this->query($sql, $bind);
        
        // SQLの実行結果を配列で取得する
        return $this->fetch();
    }

    // エラーメッセージを更新する
    function setErrorMessage($message)
    {
        // メンバ変数の更新
        $this->m_preErrorMessage['message'] = $message;
    }

    // エラーメッセージを取得する
    function getErrorMessage()
    {
        // メンバ変数を返す
        return ($this->error['message']);
    }
}