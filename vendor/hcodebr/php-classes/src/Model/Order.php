<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;

class Order extends Model {

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
}

?>