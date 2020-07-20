<?php
require_once('model.php'); // モデルクラスのインクルード

// キャラクターモデルクラス
class CharacterModel extends Model
{
//------------------------------------------------------------
    // メンバ変数達
    protected $m_useTableName = 'characterData'; // 「characterData」テーブルを使用する
//------------------------------------------------------------

    // 引数のキャラクターIDの配列に対応したキャラクター名を配列で取得する
    function getUserCharacterNames($characterIdArray)
    {
        // キキャラクター名を配列で取得するためのsql文
        $sql  = 'SELECT DISTINCT * FROM characterData';

        // sql文に書き込むパラメータ
        $bind = [['name'=>':id', 'value'=>$characterIdArray, 'type'=>PDO::PARAM_INT]];

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
            //array_push($result, $fetchData);

            for($i = 0; $i < count($characterIdArray); $i++)
            {
                if ($characterIdArray[$i]["chara_id"] == $fetchData["id"])
                {
                    // 「result」に追加する
                    array_push($result, $fetchData);
                    break;
                }
            }
        }
        return $result;
      }
}