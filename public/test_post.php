<?php
$input = file_get_contents('php://input');
file_put_contents('post_test.log', date("Y-m-d H:i:s") . " | DATA: " . $input . PHP_EOL, FILE_APPEND);
echo json_encode(["received" => true]);
