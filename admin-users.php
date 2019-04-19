<?php

use \Hcode\PageAdmin;
use \Hcode\Model\User;

$app->get('/admin/users', function() {	
	User::verifyLogin();	

	$search = [
		'name'=>(isset($_GET["name"])) ? $_GET["name"] : '',
		'email'=>(isset($_GET["email"])) ? $_GET["email"] : '',
		'login'=>(isset($_GET["login"])) ? $_GET["login"] : ''
	];

	$page = (isset($_GET["page"])) ? (int)$_GET["page"] : 1;
	
	if ($search['name'] != '' ||
	    $search['email'] != '' ||
	    $search['login'] != '') {
		$pagination = User::getPageSearch($search, $page);
	} else {
		$pagination = User::getPage($page);
	}

	$pages = [];

	for ($x=0; $x < $pagination['pages']; $x++) { 
		array_push($pages, [
			'href'=>'/admin/users?'.http_build_query([
				'page'=>$x+1,
				'search'=>$search
			]),
			'text'=>$x+1
		]);
	}
	$page = new PageAdmin();
	$page->setTpl("users", array(
		"users"=>$pagination['data'],
		"search"=>$search,
		'pages'=>$pages
	));
});

$app->get('/admin/users/create', function() {	
	User::verifyLogin();
	
	$page = new PageAdmin();
	$page->setTpl("users-create");
});

$app->get("/admin/users/:iduser/delete", function($iduser){
	User::verifyLogin();

	$user = new User();
	$user->get((int)$iduser);
	$user->delete();

	header("Location: /admin/users");
	exit;
});

$app->get('/admin/users/:iduser', function($iduser) {	
	User::verifyLogin();
	
	$user = new User();
	$user->get((int)$iduser);

	$page = new PageAdmin();
	$page->setTpl("users-update", array(
		"user"=>$user->getValues()
	));
});

$app->post("/admin/users/create", function(){
	User::verifyLogin();
	
	$user = new User();
	
	$_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;

	$user->setData($_POST);
	$user->save();

	header("Location: /admin/users");
	exit;
});

$app->post("/admin/users/:iduser", function($iduser){
	User::verifyLogin();

	$user = new User();

	$_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;

	$user->get((int)$iduser);
	$user->setData($_POST);
	$user->update();

	header("Location: /admin/users");
	exit;
});

$app->get("/admin/forgot", function(){
	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);
	$page->setTpl("forgot");
});

$app->post("/admin/forgot", function(){	
	$user = User::getForgot($_POST["email"]);

	header("Location: /admin/forgot/sent");
	exit;
});

$app->get("/admin/forgot/sent", function(){
	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);
	$page->setTpl("forgot-sent");
});

$app->get("/admin/forgot/reset", function(){
	$user = User::validForgotDecrypt($_GET["code"]);

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);
	$page->setTpl("forgot-reset", array(
		"name"=>$user["desperson"],
		"code"=>$_GET["code"]
	));
});

$app->post("/admin/forgot/reset", function(){	
	$forgot = User::validForgotDecrypt($_POST["code"]);

	User::setForgotUsed($forgot["idrecovery"]);
	
	$user = new User();
	$user->get((int)$forgot["iduser"]);
	
	$password = User::getPasswordHash($_POST["password"]);
	$user->setpassword($password);
	
	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);
	$page->setTpl("forgot-reset-success");
});

?>