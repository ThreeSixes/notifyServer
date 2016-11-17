<?php
# Check to make sure we're opened by index.php.
defined('openedByNotifyServer') or die('KTHXBYE');

# Auth tokens and their owners
$validTokens = [
    "SOMETOKEN" => "SOMEAPPNAME"
];
?>