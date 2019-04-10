<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;
use \Hcode\Model\User;

class Cart extends Model {

    const SESSION = "Cart";
    const SESSION_ERROR = "CartError";


    public static function getFromSession(){
        $cart = new Cart();

        if (isset($_SESSION[Cart::SESSION]) && (int)$_SESSION[Cart::SESSION]['idcart'] > 0) {
            $cart->get((int)$_SESSION[Cart::SESSION]['idcart']);   
            
        } else {
            $cart->getFromSessionID();

            if (!(int)$cart->getidcart() > 0) {
                $data = [
                    'dessessionid'=>session_id()
                ];

                if (User::checkLogin(false)) {
                    $user = User::getFromSession();
                    $data['iduser'] = $user->getiduser();
                }
                
                $cart->setData($data);
                $cart->save();
                $cart->setToSession();
            }
        }
    }

    public function setToSession(){
        $_SESSION[Cart::SESSION] = $this->getValeus();
    }

    public function getFromSessionID(){
        $sql = new Sql();

        $results = $sql->select("select *
                                   from tb_carts a
                                  where a.dessessionid = :dessessionid", [
            ':dessessionid'=>session_id()
        ]);

        if (count($results) > 0) {
            $this->setData($results[0]);
        }
    }

    public function get(int $idcart){
        $sql = new Sql();

        $results = $sql->select("select *
                                   from tb_carts a
                                  where a.idcart = :idcart", [
            ':idcart'=>$idcart
        ]);

        if (count($results) > 0) {
            $this->setData($results[0]);
        }

        
    }

    public function save(){        
        $sql = new Sql();

        $results = $sql->select("call sp_carts_save(:idcart, :dessessionid, :iduser, :deszipcode, :vlfreight, :nrdays);", [
            ":idcart"=>$this->getidcart(),
            ":dessessionid"=>$this->getdessessionid(),
            ":iduser"=>$this->getiduser(),
            ":deszipcode"=>$this->getdeszipcode(),
            ":vlfreight"=>$this->getvlfreight(),
            ":nrdays"=>$this->getnrdays()
        ]);

        $this->setData($results[0]);
    }

    public function addProduct(Product $product){
        $sql = new Sql();
        
        $sql->query("inset into tb_cartsproducts
                        (idcart, idproduct)
                     values
                        (:idcart, :idproduct)", [
            ':idcart'=>$this->getidcart(),
            ':idproduct'=>$product->getidproduct()
        ]);

        $this->getCalculateTotal();

    }

    public function removeProduct(Product $product, $all = false){
        $sql = new Sql();
        
        if ($all = true){
            $sql->query("update tb_cartsproducts
                            set dtremoved = now()
                          where idcart = :idcart
                            and idproduct = :idproduct
                            and dtremoved is null", [
                ':idcart'=>$this->getidcart(),
                ':idproduct'=>$product->getidproduct()
            ]);
        } else {
            $sql->query("update tb_cartsproducts
                            set dtremoved = now()
                          where idcart = :idcart
                            and idproduct = :idproduct
                            and dtremoved is null
                          limit 1", [
                ':idcart'=>$this->getidcart(),
                ':idproduct'=>$product->getidproduct()
            ]);
        }

        $this->getCalculateTotal();

    }

    public function getProducts(){
        $sql = new Sql();
        
        $rows = $sql->select("select b.idproduct,
                                     b.desproduct,
                                     b.vlprice,
                                     b.vlwidth,
                                     b.vlheight,
                                     b.vllength,
                                     b.vlweight,
                                     b.desurl,
                                     count(*) as nrqtde,
                                     sum(b.vlprice) as vltotal
                                from tb_cartsproducts a, tb_products b
                               where a.idproduct = b.idproduct
                                 and a.idcart = :idcart
                                 and a.dtremove is null
                               group by b.idproduct,
                                        b.desproduct,
                                        b.vlprice,
                                        b.vlwidth,
                                        b.vlheight,
                                        b.vllength,
                                        b.vlweight
                                        b.desurl
                               order by b.idproduct", [
                                ':idcart'=>$this->getidcart()
                            ]);


        return Product::checkList($rows);

    }

    public function getProductsTotals(){
        $sql = new Sql();
        
        $results = $sql->select("select sum(vlprice) as vlprice,
                                        sum(vlwidth) as vlwidth,
                                        sum(vlheight) as vlheight,
                                        sum(vllength) as vllength,
                                        sum(vlweight) as vlweight,
                                        count(*) as nrqtde
                                   from tb_products a, tb_cartsproducts b
                                  where a.idproduct = b.idproduct
                                    and b.idcart = :idcart
                                    and dtremoved is null;", [
                                    ':idcart'=>$this->getidcart()
                                ]);
        
        if (count($results) > 0) {
            return $results[0];
        } else {
            return [];
        }
    }

    public function setFreight($nrzipcode){
        $nrzipcode = str_replace('-', '', $nrzipcode);
        $totals = $this->getProductsTotals();

        if ($totals['vlheight'] < 2) {
            $totals['vlheight'] = 2;
        }

        if ($totals['vllenght'] < 16) {
            $totals['vllenght'] = 16;
        }

        if ($totals['nrqtde'] > 0) {
            $qs = http_build_query([
                'nCdEmpresa'=>'',
                'sDsSenha'=>'',
                'nCdServico'=>'40010',
                'sCepOrigem'=>'86812000',
                'sCepDestino'=>$nrzipcode,
                'nVlPeso'=>$totals['vlweight'],
                'nCdFormato'=>'1',
                'nVlComprimento'=>$totals['vllenght'],
                'nVlAltura'=>$totals['vlheight'],
                'nVlLargura'=>$totals['vlwidth'],
                'nVlDiametro'=>'',
                'sCdMaoPropria'=>'S',
                'nVlValorDeclarado'=>$totals['vlprice'],
                'sCdAvisoRecebimento'=>'S'
            ]);

            $xml = simplexml_load_file("http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx/CalcPrecoPrazo?".$qs);

            $result = $xml->Servicos->cServico;

            if ($result->msgErro != ''){
                Cart::setMsgError($result->msgErro);
            } else {
                Cart::clearMsgError();
            }

            $this->setnrdays($result->PrazoEntrega);
            $this->setvlfreight(Cart::formatValueToDecimal($result->Valor));
            $this->setdeszipcode($nrzipcode);
            $this->save();

            return $result;
        } else {

        }

    }

    public static function formatValueToDecimal($value):float{
        $value = str_replace('.', '', $value);
        return str_replace(',', '.', $value);
    }

    public static function setMsgError($msg){
        $_SESSION[Cart::SESSION_ERROR] = $msg;
    }
    
    public static function getMsgError($msg){
        $msg = (isset($_SESSION[Cart::SESSION_ERROR])) ? $_SESSION[Cart::SESSION_ERROR] : "";

        Cart::clearMsgError();

        return $msg;
    }

    public static function clearMsgError(){
        $_SESSION[Cart::SESSION_ERROR] = NULL;
    }

    public function updateFreight(){
        if ($this->getdeszipcode() != ''){
            $this->setFreight($this->getdeszipcode());
        }
    }

    public function getValues(){
        $this->getCalculateTotal();
        return parent::getValues();
    }

    public function getCalculateTotal(){
        $this->updateFreight();

        $totals = $this->getProductsTotals();
        
        $this->setvlsubtotal($totals['vlprice']);
        $this->setvltotal($totals['vlprice'] + $this->getvlfreight());
    }

}

?>