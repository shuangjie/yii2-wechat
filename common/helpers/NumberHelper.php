<?php
namespace common\helpers;

class NumberHelper {

    /**
     * 生成随机数字
     * @param int $length 长度
     * @return integer
     */
    public static function generateRandomNumber($length = 8){
        srand((float)microtime()*1000000);
        $random = strval(rand(0, pow(10, $length) -1));
        strlen($random) < $length && $random = str_pad($random, $length, "0", STR_PAD_LEFT);
        return $random;
    }
}