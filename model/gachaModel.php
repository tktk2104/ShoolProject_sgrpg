<?php
require_once('model.php'); // モデルクラスのインクルード

// ガチゃモデルクラス
class GachaModel extends Model
{
//------------------------------------------------------------
    // 定数達
    public  $PRICE                  = 300;  // ガチゃ１回分の価格
    private $CHARACTER_TYPE_NUM     = 10;   // ガチゃから排出されるキャラクターの種類
//------------------------------------------------------------

    // キャラクターIDをランダムに１つ抽選する
    function randomChooseCharacterId()
    {
        // 乱数を生成
        $num  = random_int(1, $this->CHARACTER_TYPE_NUM);
        return $num;
    }
}