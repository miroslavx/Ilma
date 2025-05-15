<?php
$parool = 'admin123';
$sool = 'taiestisuvalinetekst';
$kryp = crypt($parool, $sool);
echo $kryp;
?>
