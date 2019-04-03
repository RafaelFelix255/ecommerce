<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class Category extends Model {

    public static function listAll(){
        $sql = new Sql();

        return $sql->select("select * from tb_categories a order by a.idcategory;");
    }

    public function save(){
        $sql = new Sql();

        $results = $sql->query("CALL sp_categories_save(:idcategory, :descategory)", array(
            ":idcategory"=>$this->getidcategory(),
            ":descategory"=>$this->getdescategory()            
        ));

        $this->setData($results[0]);

        Category::updateFile();

    }

    public function get($idcategory){        
        $sql = new Sql();
        
        $results = $sql->select("select * from tb_categories a where a.idcategory = :idcategory", array(
            ":idcategory"=>$idcategory
        ));
        
        $this->setData($results[0]);
    }

    public function delete(){
        $sql = new Sql();
        
        $sql->query("delete from tb_categories where idcategory = :idcategory", array(
            ":idcategory"=>$this->getidcategory()
        ));

        Category::updateFile();
    }

    public static function updateFile(){
        $categories = Category::listAll();

        $html = [];

        foreach ($categories as $row) {
            array_push($html, '<li><a href="/categories/'.$row["idcategory"].'">'.$row["descategory"].'</a></li>');
        }
        
        file_put_contents($_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR."views".DIRECTORY_SEPARATOR."categories-menu.html", implode('', $html));
    }

    public function getProducts($related = true){
        $sql = new Sql();

        if ($related === true){
            return $sql->select("select * from tb_products a where a.idproduct in (
                                    select a.idproduct
                                      from tb_products a, tb_productscategories b
                                     where a.idproduct = b.idproduct
                                       and b.idcategory = :idcategory);",
                            
                                [':idcategory'=>$this->getidcategory()
            ]);
        } else {
            return $sql->select("select * from tb_products a where a.idproduct not in (
                                    select a.idproduct
                                      from tb_products a, tb_productscategories b
                                     where a.idproduct = b.idproduct
                                       and b.idcategory = :idcategory);",
                            
                                [':idcategory'=>$this->getidcategory()
            ]);
        }
    }

    public function getProductsPage($page = 1, $itensPerPage = 12){
        
        $start = ($page - 1) * $itensPerPage;
        $sql = new Sql();
        $results = $sql->select("select sql_calc_found_rows *
                                   from tb_products a,
                                        tb_categories b,
                                        tb_productscategories c
                                  where a.idproduct = c.idproduct
                                    and b.idcategory = c.idcategory
                                    and b.idcategory = :idcategory
                                  limit $start, $itensPerPage;", [
                                        ':idcategory'=>$this->getidcategory()
                                ]);
    
        $resultsTotal = $sql->select("select found_rows() as nrtotal;");

        return [
            'data'=>Product::checkList($results),
            'total'=>(int)$resultsTotal[0]["nrtotal"],
            'pages'=>ceil((int)$resultsTotal[0]["nrtotal"] / $itensPerPage)
        ];
    }

    public function addProduct(Product $product){
        $sql = new Sql();
        $sql->query("insert into tb_productscategories
                        (idcategory, idproduct)
                    values
                        (:idcategory, :idproduct);", [
                            ':idcategory'=>$this->getidcategory(),
                            ':idproduct'=>$product->getidproduct()
                    ]);
    }

    public function removeProduct(Product $product){
        $sql = new Sql();
        $sql->query("delete from tb_productscategories
                      where idcategory = :idcategory
                        and idproduct = :idproduct;", [
                            ':idcategory'=>$this->getidcategory(),
                            ':idproduct'=>$product->getidproduct()
                    ]);
    }
    
}

?>