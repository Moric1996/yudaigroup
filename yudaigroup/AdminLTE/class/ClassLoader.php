<?php

class ClassLoader
{

    /**
     * クラスが見つからなかった場合呼び出されるメソッド
     * spl_autoload_register でこのメソッドを登録してください
     * @param  string $class 名前空間など含んだクラス名
     * @return bool 成功すればtrue
     */
    public static function loadClass($class)
    {
        // 名前空間や疑似名前空間をここでパースして
        // 適切なファイルパスにしてください
        $file_name = "../AdminLTE/class/{$class}.php";

        if (is_file($file_name)) {
            require $file_name;

            return true;
        }
    }


}

// これを実行しないとオートローダーとして動かない
spl_autoload_register(array('ClassLoader', 'loadClass'));

