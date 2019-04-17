<?php

use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Order;
use \Hcode\Model\OrderStatus;


$app->get("/admin/orders/:idorder/status", function($idorder){
	User::verifyLogin();

	$order = new Order();
	$order->get((int)$idorder);

	$page = new PageAdmin();
	$page->setTpl("order-status", [
		'order'=>$order->getValues(),
		'status'=>OrderStatus::listAll(),
		'msgError'=>Order::getError(),
		'msgSuccess'=>Order::getSuccess()
	]);

});

$app->post("/admin/orders/:idorder/status", function($idorder){
	User::verifyLogin();

	if (!isset($_POST['idstatus']) || !(int)$_POST['idstatus'] > 0){
		Order::setError("Informe o status atual do pedido.");		
		header("Location: /admin/orders/".$idorder."/status");
		exit;
	}
	
	$order = new Order();
	$order->get((int)$idorder);
	$order->setidstatus((int)$_POST['idstatus']);
	$order->save();

	Order::setSuccess("Status atualizado com sucesso!");
	header("Location: /admin/orders/".$idorder."/status");
	exit;

});

$app->get("/admin/orders/:idorder/delete", function($idorder){	
	User::verifyLogin();

	$order = new Order();
	$order->get((int)$idorder);
	$order->delete();
	
	header("Location: /admin/orders");
	exit;
});

$app->get("/admin/orders/:idorder", function($idorder){	
	User::verifyLogin();

	$order = new Order();
	$order->get((int)$idorder);

	$cart = $order->getCart();

	$page = new PageAdmin();
	$page->setTpl("order", [
		'order'=>$order->getValues(),
		'cart'=>$cart->getValues(),
		'products'=>$cart->getProducts()
	]);
});

$app->get("/admin/orders", function(){	
	User::verifyLogin();

	$page = new PageAdmin();
	$page->setTpl("orders", [
		'orders'=>Order::listAll()
	]);
});

$app->get("/admin/categories/create", function(){	
	User::verifyLogin();
	
	$page = new PageAdmin();
	$page->setTpl("categories-create");
});

$app->post("/admin/categories/create", function(){
	User::verifyLogin();

	$category = new Category();
	$category->setData($_POST);
	$category->save();

	header("Location: /admin/categories");
	exit;
});

$app->get("/admin/categories/:idcategory/delete", function($idcategory){
	User::verifyLogin();

	$category = new Category();
	$category->get((int)$idcategory);
	$category->delete();

	header("Location: /admin/categories");
	exit;
});

$app->get("/admin/categories/:idcategory", function($idcategory){
	User::verifyLogin();

	$category = new Category();
	$category->get((int)$idcategory);

	$page = new PageAdmin();
	$page->setTpl("categories-update", [
		"category"=>$category->getValues()
	]);
});

$app->post("/admin/categories/:idcategory", function($idcategory){
	User::verifyLogin();

	$category = new Category();
	$category->get((int)$idcategory);
	$category->setData($_POST);
	$category->save();

	header("Location: /admin/categories");
	exit;
});

$app->get("/admin/categories/:idcategory/products", function($idcategory){
	User::verifyLogin();	
	
	$category = new Category();
	$category->get((int)$idcategory);
	
	$page = new PageAdmin();
	$page->setTpl("categories-products", [
		'category'=>$category->getValues(),
		'productsRelated'=>$category->getProducts(),
		'productsNotRelated'=>$category->getProducts(false)
	]);
});

$app->get("/admin/categories/:idcategory/products/:idproduct/add", function($idcategory, $idproduct){
	User::verifyLogin();	
	
	$category = new Category();
	$category->get((int)$idcategory);
	
	$product = new Product();
	$product->get((int)$idproduct);
	$category->addProduct($product);

	header("Location: /admin/categories/".$idcategory."/products");
	exit;
});

$app->get("/admin/categories/:idcategory/products/:idproduct/remove", function($idcategory, $idproduct){
	User::verifyLogin();	
	
	$category = new Category();
	$category->get((int)$idcategory);
	
	$product = new Product();
	$product->get((int)$idproduct);
	$category->removeProduct($product);

	header("Location: /admin/categories/".$idcategory."/products");
	exit;
});

?>
