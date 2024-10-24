<?php

/**
 * Basic�F�؂�v������y�[�W�̐擪�Ŏg���֐�
 * ���񎞂܂��͎��s���ɂ̓w�b�_�𑗐M����exit����
 *
 * @return string ���O�C���������[�U��
 */
function require_basic_auth()
{
    // ���O�ɐ����������[�U���Ƃ̃p�X���[�h�n�b�V���̔z��
    $hashes = array(
        'test' => "9f86d081884c7d659a2feaa0c55ad015a3bf4f1b2b0b822cd15d6c15b0f00a08",
    );
    if (!isset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) || !(hash('sha256',$_SERVER['PHP_AUTH_PW']) === $hashes[$_SERVER['PHP_AUTH_USER']])) {
        // ���񎞂܂��͔F�؂����s�����Ƃ�
        header('WWW-Authenticate: Basic realm="Enter username and password."');
        header('Content-Type: text/plain; charset=utf-8');
        exit('���̃y�[�W������ɂ̓��O�C�����K�v�ł�');
    }

    // �F�؂����������Ƃ��̓��[�U����Ԃ�
    return $_SERVER['PHP_AUTH_USER'];
}

/**
 * htmlspecialchars�̃��b�p�[�֐�
 *
 * @param string $str
 * @return string
 */
function h($str)
{
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}