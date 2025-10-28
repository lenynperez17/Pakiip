<?php
use Greenter\Ws\Services\SunatEndpoints;
use Greenter\See;

$see = new See();
$see->setService(SunatEndpoints::WSDL_ENDPOINT);
$see->setCertificate(file_get_contents(__DIR__.'/certificate.pem'));
$see->setCredentials('20000000001MODDATOS', 'MODDATOS');

return $see;