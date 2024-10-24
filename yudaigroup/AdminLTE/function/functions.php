<?php

/**
 * Basic認証を要求するページの先頭で使う関数
 * 初回時または失敗時にはヘッダを送信してexitする
 *
 * @return string ログインしたユーザ名
 */
function require_basic_auth()
{
    // 事前に生成したユーザごとのパスワードハッシュの配列
    $hashes = array(
        'test' => "9f86d081884c7d659a2feaa0c55ad015a3bf4f1b2b0b822cd15d6c15b0f00a08",
    );
    if (!isset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) || !(hash('sha256',$_SERVER['PHP_AUTH_PW']) === $hashes[$_SERVER['PHP_AUTH_USER']])) {
        // 初回時または認証が失敗したとき
        header('WWW-Authenticate: Basic realm="Enter username and password."');
        header('Content-Type: text/plain; charset=utf-8');
        exit('このページを見るにはログインが必要です');
    }

    // 認証が成功したときはユーザ名を返す
    return $_SERVER['PHP_AUTH_USER'];
}

/**
 * htmlspecialcharsのラッパー関数
 *
 * @param string $str
 * @return string
 */
function h($str)
{
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}