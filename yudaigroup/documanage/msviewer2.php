<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
include('../inc/ybase.inc');

$ybase = new ybase();
$ybase->session_get();

//$kind = 1;
$company_id = 1;
/////////////////////////////////////////
if(!$company_id){
	$ybase->error("�p�����[�^�[�G���[");
}
if(!$file_id){
	$ybase->error("�p�����[�^�[�G���[");
}

// 7���O�̓��t�擾
$ago = date("Y-m-d", strtotime("-1 day"));

// JPG�t�@�C���ꗗ���擾
$res = glob("tmp/*.*");
foreach ($res as $file) {
	// �t�@�C���̃^�C���X�^���v���擾
	$unixdate = filemtime($file);
	// �^�C���X�^���v����t�̃t�H�[�}�b�g�ɕύX
	$filedate = date("Y-m-d", $unixdate);
	// ���t���r���āA7�����O�̃t�@�C���Ȃ�폜
	if($filedate < $ago){
		unlink($file);
	}
}


$ua = $_SERVER['HTTP_USER_AGENT'];
if(preg_match("/iPhone/i",$ua)){
}

$conn = $ybase->connect();
$sql = "select filename,kind,ext from docu_manage where file_id = $file_id and company_id = $company_id and status = '1' and type='2'";
$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$ybase->error("�Y���Ȃ�:$sql");
}
list($q_filename,$q_kind,$q_ext) = pg_fetch_array($result,0);
if(!$ybase->mime_type[$q_ext]){
	$ybase->error("�Y���Ȃ�");
}
$filename = "/home/yournet/yudai/documanage/".$q_kind."/".$q_filename.".".$q_ext;
$dlfn = $q_filename.".".$q_ext;
$size = filesize($filename);

$handle = fopen($filename, "r");
$contents = fread($handle, $size);
fclose($handle);
header("Content-Type: {$ybase->mime_type[$q_ext]}");
header("content-disposition: attachment; filename={$dlfn}");

echo $contents;
exit;



/*
$tempfilename = "tmp/".$q_filename.time().rand(1000,9999).".".$q_ext;
$cpfilename = "/usr/local/htdocs/yudaigroup/documanage/".$tempfilename;
$urlname = "./".$tempfilename;
$flg = copy("$filename","$cpfilename");

$ybase->HTMLheader();
$ybase->ST_PRI .= <<<HTML

</body>
</html>
HTML;

//$ybase->HTMLfooter();
$ybase->priout();
////////////////////////////////////////////////
*/
?>