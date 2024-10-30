<?php
require_once(dirname(__FILE__). "/../../../../wp-load.php");

include(dirname(__FILE__).'/class-wc-media-bibliomundi.php');
include(dirname(__FILE__).'/class-wc-catalog-bibliomundi.php');


$argv = $_SERVER['argv'];
$post_id = $argv[1];
$bbmProductTitle = $argv[2];
$file = $argv[3];
$totalProduct = $argv[4];

WC_Media_BiblioMundi::insert( $post_id, $file, $bbmProductTitle );

$result = file_get_contents(dirname(__FILE__).'/../log/import.lock');
$result = json_decode($result, true);

$result['current'] = !isset($result['current']) ? 1 : $result['current'] + 1;
	
$result['current'] = ($result['current'] >= $totalProduct) ? $totalProduct : $result['current'];
if ($result['current'] == $totalProduct) {
    $result['status'] = 'complete';
}
WC_Catalog_BiblioMundi::write_lock(null, $result);