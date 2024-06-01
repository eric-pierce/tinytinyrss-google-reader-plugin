
<?php
require_once 'init.php';

$googleReaderAPI = new GoogleReaderAPI($pdo);
$googleReaderAPI->handleRequest();
?>
