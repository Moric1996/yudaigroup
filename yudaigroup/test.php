<?php
require_once '/usr/local/htdocs/yudaigroup/inc/mimeDecode.php';



$message = file_get_contents("php://stdin");

$decoder = new Mail_mimeDecode($message);
$params = array("include_bodies"=>true);
$structure = $decoder->decode($params);

$subject = mb_convert_encoding(mb_decode_mimeheader($structure->headers["subject"]),mb_internal_encoding(), "auto");
$from = mb_convert_encoding(mb_decode_mimeheader($structure->headers['from']), mb_internal_encoding(), "auto");
$charset = $structure->parts[1]->ctype_parameters['charset'];
$partname = mb_convert_encoding(mb_decode_mimeheader($structure->parts[1]->ctype_parameters['name']),"UTF-8", "auto");
$partname2 = $structure->parts[1]->ctype_parameters['name'];
$body = $structure->body;

$q_email = "katsumata@yournet-jp.com";
$body2 = $subject.$body."OKOK";
$subject = "yudaigijirokutest";


$results = print_r($structure, true);


mail("$q_email","$subject","$charset:$partname:$partname2","From: yudai.system@yournet-jp.com");

print "OK";

?>