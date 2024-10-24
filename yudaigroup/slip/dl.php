<?php
///////////////////////////////////////////////////////////////
//
//
//
///////////////////////////////////////////////////////////////
if(isset($_POST)){
	foreach($_POST as $key => $value){
		${$key} = $value;
	}
}
if(isset($_GET)){
	foreach($_GET as $key => $value){
		${$key} = $value;
	}
}
mb_language("Japanese");
mb_internal_encoding("UTF-8");

include(dirname(__FILE__).'/../inc/ybase.inc');
include(dirname(__FILE__).'/inc/slip.inc');
include(dirname(__FILE__).'/../../TCPDF/tcpdf.php');
include(dirname(__FILE__).'/../../TCPDF/fpdi/fpdi.php');


$ybase = new ybase();
$slip = new slip();
$ybase->session_get();

// TCPDFインスタンスを作成
$orientation = 'Landscape'; // 用紙の向き
$unit = 'mm'; // 単位
$format = 'A4'; // 用紙フォーマット
$unicode = true; // ドキュメントテキストがUnicodeの場合にTRUEとする
$encoding = 'UTF-8'; // 文字コード
$diskcache = false; // ディスクキャッシュを使うかどうか
//$tcpdf = new TCPDF($orientation, $unit, $format, $unicode, $encoding, $diskcache);

mb_internal_encoding("UTF-8");

$ybase->my_company_id = 5;

$ybase->make_yournet_employee_list("1");
$slip->supplier_make();

$conn = $ybase->connect(3);

if(!preg_match("/^[0-9]+$/",$slip_id)){
	$ybase->error("パラメーターエラー。ERROR_CODE:17981");
}
if(!preg_match("/^[0-9]+$/",$attach_no)){
	$ybase->error("パラメーターエラー。ERROR_CODE:17980");
}

///////////////////////////////データ取得

$sql = "select attach,to_char(month,'YYYYMM'),money,supplier from slip where slip_id = $slip_id and company_id = $ybase->my_company_id and status > '0'";

$result = $ybase->sql($conn,$sql);
$num = pg_num_rows($result);
if(!$num){
	$ybase->error("データエラー。ERROR_CODE:17983");
}
list($q_attach,$q_month,$q_money,$q_supplier) = pg_fetch_array($result,0);
	$q_attach_arr = json_decode($q_attach,true);
	if(!$q_attach_arr){
		$ybase->error("データエラー。ERROR_CODE:17983");
	}
	$comp_name = $slip->supplier_list[$q_supplier];
	$comp_name = str_replace(" ","",$comp_name);
	$comp_name = str_replace("　","",$comp_name);

	$filename = $q_attach_arr[$attach_no];
	$ext = substr($filename,strrpos($filename,'.') + 1);
	$data = file_get_contents($filename);
//	$ContentType = $ybase->mime_type[$ext];
//header("Content-Type: $ContentType");
$ext = mb_strtolower($ext);

$invno = "Y".$slip_id.sprintf('%02d',$attach_no);
$str_invno = "管理番号: {$invno}";


$newfilename = "slip_{$q_month}_{$comp_name}_{$invno}".".".$ext;

if($ext == 'pdf'){

	$pdf = new FPDI();
	$pdf->setSourceFile("$filename");
	$tpl = $pdf->importPage(1);
	$size = $pdf->getTemplateSize($tpl);
	$w = $size['w'];
	$h = $size['h'];
	if($w < $h){
		$paperdirect = "P";
	}else{
		$paperdirect = "L";
	}
$pdf = new FPDI("$paperdirect", 'mm',array($w, $h));
$pdf->SetMargins(0, 0, 0);
$pdf->SetAutoPageBreak(true,0);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetFont("kozminproregular", "", 12);

$pageCnt = 0;
$pageCnt = $pdf->setSourceFile("$filename");

$pdf->setSourceFile("$filename");
$pdf->SetFillColor(255);
$xpoint = $w - 70;
for($i=1;$i<=$pageCnt;$i++){
	$pdf->addPage();
	$pdf->useTemplate($pdf->importPage($i), 0, 0, $w, $h);
	if($i==1){
//		$pdf->Text(1,10,"証憑番号: 7891",false,false,true,1,0,'',true);
//		$pdf->MultiCell(50,5,"$str_invno",1,'C',true,1,$xpoint,1);

		$pdf->Cell(50,5,"$str_invno",1,0,'C',true);
	}
}

//$pdf->Output("$newfilename", "I");
$data = $pdf->Output("$newfilename", "S");

}elseif($ext == 'png'){
$imagick = new Imagick();
$draw = new ImagickDraw();
$draw->setFillColor('black');
$draw->setFont('./BIZ-UDGOTHICR.TTC');
$draw->setFontSize(25);
$draw->setTextUnderColor('white');
$draw->setTextDecoration(2);
//$str_invno = mb_convert_encoding($str_invno, 'sjis', 'UTF-8');

$imagick->readImage("$filename");
$imagick->annotateImage($draw, 40, 30, 0,"$str_invno");

$data = $imagick->getImageBlob();
/*
	$imgPng = imageCreateFromPng("$filename");
	$black = imagecolorallocate($imgPng, 0,0,0);
//	$res = imagestring($img,15,1,1, "$str_invno", $black);
//	$res = imagechar($img,4, 0, 0,"$str_invno",$black);
	$fontfile = "/usr/local/htdocs/TCPDF/fonts/kozminproregular.php";
//	$font = imageloadfont('./k18gm.bdf');
	$res = imagechar ($imgPng,100,10, 10,"$invno", $black);
	ob_start();
	imagepng($imgPng);
	$data = ob_get_contents();
	ob_end_clean();
*/
}elseif(($ext == 'jpg')||($ext == 'jpeg')){
$imagick = new Imagick();
$draw = new ImagickDraw();
$draw->setFillColor('black');
$draw->setFont('./BIZ-UDGOTHICR.TTC');
$draw->setFontSize(25);
$draw->setTextUnderColor('white');
$draw->setTextDecoration(2);
//$str_invno = mb_convert_encoding($str_invno, 'sjis', 'UTF-8');

$imagick->readImage("$filename");
$imagick->annotateImage($draw, 40, 30, 0,"$str_invno");

$data = $imagick->getImageBlob();
}

$flsize = strlen($data);

header('Content-Type: application/force-download');
 
header("Content-Length: $flsize");
 
header('Content-Disposition: attachment; filename="'.$newfilename.'"');
 
echo $data;
exit;


////////////////////////////////////////////////
?>