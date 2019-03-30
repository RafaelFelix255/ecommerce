<?php 

require_once("vendor/autoload.php");

use \Slim\Slim;
use \Hcode\Page;

$app = new Slim();

$app->config('debug', true);

$app->get('/', function() {
    
	//$sql = new Hcode\DB\sql();
	//$results = $sql->select("select * from tb_users u");
	//echo json_encode($results);

	$page = new Page();
	$page->setTpl("index");



});

$app->run();

 ?>