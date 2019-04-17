<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Model\Cart;

class Order extends Model {

    const ERROR = "Order-Error";
    const SUCCESS = "Order-Success";

    public function save(){
        $sql = new Sql();
        
        $results = $sql->select("CALL sp_orders_save(:idorder, :idcart, :iduser,
            :idstatus, :idaddress, :vltotal)", [
            ':idorder'=>$this->getidorder(),
            ':idcart'=>$this->getidcart(),
            ':iduser'=>$this->getiduser(),
            ':idstatus'=>$this->getidstatus(),
            ':idaddress'=>$this->getidaddress(),
            ':vltotal'=>$this->getvltotal()
        ]);

        if (count($results) > 0){
            $this->setData($results[0]);
        }
    }

    public function get($idorder){
        $sql = new Sql();

        $results = $sql->select("select *
                                   from tb_orders a,
                                        tb_ordersstatus b,
                                        tb_carts c,
                                        tb_users d,
                                        tb_addresses e,
                                        tb_persons f
                                  where a.idstatus = b.idstatus
                                    and a.idcart = c.idcart
                                    and a.iduser = d.iduser
                                    and a.idaddress = e.idaddress
                                    and f.idperson = d.idperson
                                    and a.idorder = :idorder", [
            ':idorder'=>$idorder
        ]);

        if (count($results) > 0) {
            $this->setData($results[0]);
        }        
    }

    public static function listAll(){
        $sql = new Sql();

        return $sql->select("select *
                               from tb_orders a,
                                    tb_ordersstatus b,
                                    tb_carts c,
                                    tb_users d,
                                    tb_addresses e,
                                    tb_persons f
                               where a.idstatus = b.idstatus
                                 and a.idcart = c.idcart
                                 and a.iduser = d.iduser
                                 and a.idaddress = e.idaddress
                                 and f.idperson = d.idperson
                               order by a.dtregister desc");
    }

    public function delete(){
        $sql = new Sql();

        $sql->query("delete from tb_orders
                      where idorder = :idorder", [
                    ':idorder'=>$this->getidorder()
                ]);
    }

    public function getCart():Cart{
        $cart = new Cart();
        $cart->get((int)$this->getidcart());
        return $cart;
    }

    public static function setError($msg){
        $_SESSION[Order::ERROR] = $msg;
    }
    
    public static function getError(){
        $msg = (isset($_SESSION[Order::ERROR]) && $_SESSION[Order::ERROR]) ? $_SESSION[Order::ERROR] : '';

        Order::clearError();

        return $msg;
    }

    public static function clearError(){
        $_SESSION[Order::ERROR] = NULL;
    }

    public static function setSuccess($msg){
        $_SESSION[Order::SUCCESS] = $msg;
    }
    
    public static function getSuccess(){
        $msg = (isset($_SESSION[Order::SUCCESS]) && $_SESSION[Order::SUCCESS]) ? $_SESSION[Order::SUCCESS] : '';

        Order::clearSuccess();

        return $msg;
    }

    public static function clearSuccess(){
        $_SESSION[Order::SUCCESS] = NULL;
    }

}

?>