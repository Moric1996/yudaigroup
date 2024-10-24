<?php

class ClassLoader
{

    /**
     * �N���X��������Ȃ������ꍇ�Ăяo����郁�\�b�h
     * spl_autoload_register �ł��̃��\�b�h��o�^���Ă�������
     * @param  string $class ���O��ԂȂǊ܂񂾃N���X��
     * @return bool ���������true
     */
    public static function loadClass($class)
    {
        // ���O��Ԃ�^�����O��Ԃ������Ńp�[�X����
        // �K�؂ȃt�@�C���p�X�ɂ��Ă�������
        $file_name = "../AdminLTE/class/{$class}.php";

        if (is_file($file_name)) {
            require $file_name;

            return true;
        }
    }


}

// ��������s���Ȃ��ƃI�[�g���[�_�[�Ƃ��ē����Ȃ�
spl_autoload_register(array('ClassLoader', 'loadClass'));

