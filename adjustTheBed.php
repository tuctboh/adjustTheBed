<?php

//
// Based on https://philipp-guttmann.de/Blog/Alexa_Skill_Endpoint_PHP/
//

$p_input = file_get_contents('php://input');
$t_post = json_decode($p_input);

$t_uniqid = uniqid();

$t_myname = basename(__FILE__, '.php');
$c_array = parse_ini_file($t_myname.'.ini');
require_once $t_myname.'-main.php';

if (!isset($c_array['time_limit'])) {
    $c_array['time_limit'] = 5;
}
set_time_limit($c_array['time_limit']);
date_default_timezone_set('UTC');
$SignatureCertChainUrl = $_SERVER['HTTP_SIGNATURECERTCHAINURL'];

if ('amzn1.ask.skill.'.$c_array['skillId'] == $t_post->session->application->applicationId and $t_post->request->timestamp > date('Y-m-d\TH:i:s\Z', time() - 150) and preg_match('/https:\/\/s3\.amazonaws\.com(:433)?\/echo\.api\//', $SignatureCertChainUrl)) {
} else {
    http_response_code(400);
    exit;
}

$SignatureCertChainUrl_File = md5($SignatureCertChainUrl);
$SignatureCertChainUrl_File = '.'.$SignatureCertChainUrl_File.'.pem';

if (!file_exists($SignatureCertChainUrl_File)) {
    file_put_contents($SignatureCertChainUrl_File, file_get_contents($SignatureCertChainUrl));
}

$SignatureCertChainUrl_Content = file_get_contents($SignatureCertChainUrl_File);
$Signature_Content = $_SERVER['HTTP_SIGNATURE'];
$SignatureCertChainUrl_Content_Array = openssl_x509_parse($SignatureCertChainUrl_Content);
$Signature_PublicKey = openssl_pkey_get_public($SignatureCertChainUrl_Content);
$Signature_PublicKey_Data = openssl_pkey_get_details($Signature_PublicKey);
$Signature_Content_Decoded = base64_decode($Signature_Content);
$Signature_Verify = openssl_verify($p_input, $Signature_Content_Decoded, $Signature_PublicKey_Data['key'], 'sha1');

if (preg_match('/echo-api\.amazon\.com/', array_values($SignatureCertChainUrl_Content_Array)[0]) and $SignatureCertChainUrl_Content_Array['validTo_time_t'] > time() and $SignatureCertChainUrl_Content_Array['validFrom_time_t'] < time() and $Signature_Content and 1 == $Signature_Verify) {
} else {
    http_response_code(400);
    exit;
}

doMain($t_post);
