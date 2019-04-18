<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class User extends Model {

    const SESSION = "User";
    const SECRET = "HcodePhp7_Secret";
    const ERROR = "UserError";
    const ERROR_REGISTER = "UserErrorRegister";
    const SUCCESS = "UserSuccess";

    public static function getFromSession(){
        $user = new User();

        if (isset($_SESSION[User::SESSION]) && (int)$_SESSION[User::SESSION]['iduser'] > 0){
            $user->setData($_SESSION[User::SESSION]);          
        }

        return $user;
    }

    public static function checkLogin($inadmin = true){
        if (!isset($_SESSION[User::SESSION]) ||
            !$_SESSION[User::SESSION] ||
            !(int)$_SESSION[User::SESSION]["iduser"] > 0) {
                //Não está logado
                return false;
        } else {
            if ((bool)$_SESSION[User::SESSION]["inadmin"] === $inadmin){
                return true;
            } else if ($inadmin === false){
                return true;
            } else {
                return false;
            }
        }
    }

    public static function login($deslogin, $password){
        $sql = new Sql();

        $results  = $sql->select("select *
                                    from tb_users a, tb_persons b
                                   where a.idperson = b.idperson
                                     and a.deslogin = :deslogin", array(
                                    ":deslogin"=>$deslogin
        ));
        
        if (count($results) === 0){
            throw new \Exception("Usuário inexistente ou senha inválida.");
        }

        $data = $results[0];

        if (password_verify($password, $data["despassword"]) === true){
            $user = new User();

            $data['desperson'] = utf8_encode($data['desperson']);

            $user->setData($data);
            
            $_SESSION[User::SESSION] = $user->getValues();
            
            return $user;

        } else {
            throw new \Exception("Usuário inexistente ou senha inválida.");
        }


    }

    public static function verifyLogin($inadmin = true){
        if(!User::checkLogin($inadmin)) {
            if ($inadmin) {
                header("Location: /admin/login");
            } else {
                header("Location: /login");
            }
            exit;
        }

    }

    public static function logout(){
        $_SESSION[User::SESSION] = NULL;
    }

    public static function listAll(){
        $sql = new Sql();

        return $sql->select("select *
                               from tb_users a, tb_persons b
                              where a.idperson = b.idperson
                              order by a.iduser;");
    }

    public function save(){
        $sql = new Sql();

        $results = $sql->query("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
            ":desperson"=>utf8_decode($this->getdesperson()),
            ":deslogin"=>$this->getdeslogin(),
            ":despassword"=>User::getPasswordHash($this->getdespassword()),
            ":desemail"=>$this->getdesemail(),
            ":nrphone"=>$this->getnrphone(),
            ":inadmin"=>$this->getinadmin()
            
        ));

        $this->setData($results[0]);

    }

    public function get($iduser){
        $sql = new Sql();
        $results = $sql->select("select *
                                   from tb_users a, tb_persons b
                                  where a.idperson = b.idperson
                                    and a.iduser = :iduser",
                                array(
                                    ":iduser"=>$iduser
        ));

        $data = $results[0];
        $data['desperson'] = utf8_encode($data['desperson']);

        $this->setData($data);
    }

    public function update(){
        $sql = new Sql();

        $results = $sql->query("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
            ":iduser"=>$this->getiduser(),            
            ":desperson"=>utf8_decode($this->getdesperson()),
            ":deslogin"=>$this->getdeslogin(),
            ":despassword"=>User::getPasswordHash($this->getdespassword()),
            ":desemail"=>$this->getdesemail(),
            ":nrphone"=>$this->getnrphone(),
            ":inadmin"=>$this->getinadmin()
            
        ));

        $this->setData($results[0]); 
    }

    public function delete(){
        $sql = new Sql();

        $sql->query("CALL sp_users_delete(:iduser)", array(
            ":iduser"=>$this->getiduser()
        ));
    }

    public static function getForgot($email, $inadmin = true){
        $sql = new Sql();
        $results = $sql->select("select *
                                   from tb_users a,
                                        tb_persons b
                                  where a.idperson = b.idperson
                                    and b.desemail = :email", array(
            ":email"=>$email
        ));
    
        if (count($results) === 0) {
            throw new \Exception("Não foi possível recuperar sua senha.");            
        } else {
            
            $data = $results[0];

            $results2 = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser, :desip)", array(
                ":iduser"=>$data["iduser"],
                ":desip"=>$_SERVER["REMOTE_ADDR"]
            ));

            if (count($results2) === 0) {
                throw new \Exception("Não foi possível recuperar sua senha.");            
            } else {
                //$dataRecovery = $results2[0];
                
                //$iv = random_bytes(openssl_cipher_iv_length('aes-256-cbc'));
                
                //$code = openssl_encrypt($dataRecovery['idrecovery'], 'aes-256-cbc', User::SECRET, 0, $iv);
                
                //$result = base64_encode($iv.$code);
                
                $result = $results2[0]['idrecovery'];

                if ($inadmin === true) {
                    $link = "http://www.hcodecommerce.com.br/admin/forgot/reset?code=$result";
                } else {
                    $link = "http://www.hcodecommerce.com.br/forgot/reset?code=$result";
                }

                $mailer = new Mailer($data["desemail"], $data["desperson"], "Redefinir senha do sistema!", "forgot", array(
                        "name"=>$data["desperson"],
                        "link"=> $link
                ));
                
                $mailer->send();
                    
                return $data;   
            }

            
        }
    }

    public static function validForgotDecrypt($idrecovery){

        //$result = base64_decode($result);
        
        //$code = mb_substr($result, openssl_cipher_iv_length('aes-256-cbc'), null, '8bit');
        
        //$iv = mb_substr($result, 0, openssl_cipher_iv_length('aes-256-cbc'), '8bit');;

        //$idrecovery = openssl_decrypt($code, 'aes-256-cbc', User::SECRET, 0, $iv);
        
        $sql = new Sql();
        
        $results = $sql->select("select *
                                   from tb_userspasswordsrecoveries a,
                                        tb_users b,
                                        tb_persons c
                                  where a.iduser = b.iduser
                                    and b.idperson = c.idperson
                                    and a.idrecovery = :idrecovery
                                    and a.dtrecovery is null
                                    and date_add(a.dtregister, interval 1 hour) >= now();",
                                array(                                
                                    ":idrecovery"=>$idrecovery
        ));

        if (count($results) === 0){
            throw new \Exception("Muitas tentativas de recuperar a senha, tente novamente mais tarde!");
        } else {
            return $results[0];
        }
    }

    public static function setForgotUsed($idrecovery){
        $sql = new Sql();
        
        $sql->query("update tb_userspasswordsrecoveries
                        set dtrecovery = now()
                      where idrecovery = :idrecovery",
                    array(
                        ":idrecovery"=>$idrecovery
        ));

    }
    
    public function setPassword($password){
        $sql = new Sql();

        $sql->query("update tb_users u
                        set u.despassword = :password
                      where u.iduser = :iduser",
            array(
                ":password"=>$password,
                ":iduser"=>$this->getiduser()        
        ));
    }

    public static function setError($msg){
        $_SESSION[User::ERROR] = $msg;
    }
    
    public static function getError(){
        $msg = (isset($_SESSION[User::ERROR]) && $_SESSION[User::ERROR]) ? $_SESSION[User::ERROR] : '';

        User::clearError();

        return $msg;
    }

    public static function clearError(){
        $_SESSION[User::ERROR] = NULL;
    }

    public static function setErrorRegister($msg){
        $_SESSION[User::ERROR_REGISTER] = $msg;
    }
    
    public static function getErrorRegister(){
        $msg = (isset($_SESSION[User::ERROR_REGISTER]) && $_SESSION[User::ERROR_REGISTER]) ? $_SESSION[User::ERROR_REGISTER] : '';

        User::clearErrorRegister();

        return $msg;
    }

    public static function clearErrorRegister(){
        $_SESSION[User::ERROR_REGISTER] = NULL;
    }

    public static function checkLoginExist($deslogin){
        $sql = new Sql();

        $results  = $sql->select("select * from tb_users u where u.deslogin = :deslogin", array(
            ":deslogin"=>$deslogin
        ));

        return (count($results) > 0);
    }

    public static function getPasswordHash($password){
        return password_hash($password, PASSWORD_DEFAULT, [
            'cost'=>12
        ]);
    }

    public static function setSuccess($msg){
        $_SESSION[User::SUCCESS] = $msg;
    }
    
    public static function getSuccess(){
        $msg = (isset($_SESSION[User::SUCCESS]) && $_SESSION[User::SUCCESS]) ? $_SESSION[User::SUCCESS] : '';

        User::clearSuccess();

        return $msg;
    }

    public static function clearSuccess(){
        $_SESSION[User::SUCCESS] = NULL;
    }

    public function getOrders(){
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
                                    and a.iduser = :iduser", [
            ':iduser'=>$this->getiduser()
        ]);
      
        return $results;
    }

    public static function getPage($page = 1, $itensPerPage = 10){
        
        $start = ($page - 1) * $itensPerPage;
        
        $sql = new Sql();
        $results = $sql->select("select sql_calc_found_rows *
                                   from tb_users a, tb_persons b
                                  where a.idperson = b.idperson
                                  order by a.iduser
                                  limit $start, $itensPerPage;");
        
        $resultsTotal = $sql->select("select found_rows() as nrtotal;");
        
        return [
            'data'=>$results,
            'total'=>(int)$resultsTotal[0]["nrtotal"],
            'pages'=>ceil((int)$resultsTotal[0]["nrtotal"] / $itensPerPage)
        ];
    }

    public static function getPageSearch($search, $page = 1, $itensPerPage = 10){
        
        $start = ($page - 1) * $itensPerPage;
        
        $sql = new Sql();
        $results = $sql->select("select sql_calc_found_rows *
                                   from tb_users a, tb_persons b
                                  where a.idperson = b.idperson
                                    and b.desperson like :search
                                     or b.desemail = :search
                                     or a.deslogin like :search
                                  order by a.iduser
                                  limit $start, $itensPerPage;", [
                                      ':search'=>'%'.$search.'%'
                                  ]);
        
        $resultsTotal = $sql->select("select found_rows() as nrtotal;");
        
        return [
            'data'=>$results,
            'total'=>(int)$resultsTotal[0]["nrtotal"],
            'pages'=>ceil((int)$resultsTotal[0]["nrtotal"] / $itensPerPage)
        ];
    }
}

?>