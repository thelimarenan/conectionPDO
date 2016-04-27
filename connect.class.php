<?php
/*
  @thelimarenan
  @package
  @version 1.0 # 27/04/2016 #
*/

class Obj {
    private $db;
    private $query;
    private $table;
    private $fields;
    public $meta;

        function __construct($table, $id = NULL) {
            $this->table = $table;
            try {
                $this->Connect();

                $this->query = $this->db->prepare("SHOW COLUMNS FROM ".$table."");
                $this->query->execute();

                foreach($this->query->fetchAll() as $row) {
                    $this->{$row['Field']} = '';
                    $strpos                = strpos($row['Type'], '(');
                    $meta[$row['Field']]   = ($strpos) ? substr($row['Type'], 0, $strpos) : $metatype = $row['Type'];
                    $fields[]              = $row['Field'];
                    $this->meta            = $meta;
                }

                $this->fields = $fields;
                if($id != NULL) {
                    self::Select($id);  
                }   
            } catch (Exception $e) {
                $e->getMessage();
            }
        }
    public function Connect(){
        $pdo_sgbd = "mysql";
        $pdo_host = "localhost";
        $pdo_db = "";
        $pdo_user = "";
        $pdo_pass = "";
        $pdo_encode = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8');

        try {            
            //Parâmetro de Conexão ao BD -
            $this->db = new PDO($pdo_sgbd.":host=".$pdo_host.";dbname=".$pdo_db,$pdo_user,$pdo_pass,$pdo_encode);
        } catch(Exception $e) {
            echo $e->getMessage();
        }
    }

    public function Select($id = NULL, $where = NULL, $obj = false) {
        $this->Connect();
        if ($id == NULL) {
            if($where == NULL) {
                $this->query = $this->db->prepare("SELECT * FROM ".$this->table."");
            } else {
                $this->query = $this->db->prepare("SELECT * FROM ".$this->table." WHERE ".$where."");
            }
            $this->query->execute();
            $retorna = $this->query->fetchAll();
            if($obj == false) {
                return $retorna; 
            } else {
                $i = 0;
                foreach($retorna as $result) {
                    foreach($this->fields as $campos) {
                         $this->$campos = $result[$campos];
                     }
                }
            }
        } else {
            $this->query = $this->db->prepare("SELECT * FROM ".$this->table." WHERE ".$this->fields[0]." = :id");
            $this->query->bindValue(':id', $id);
            $this->query->execute();
            $retorna = $this->query->fetchAll();
            $i = 0;
            foreach($retorna as $result) {
                foreach($this->fields as $campos) {
                    $this->$campos = $result[$campos];
                 }
            }
            return $retorna[0];
        }   
    }

    public function Insert($return = NULL) {        
        $this->Connect();
        $campos ='';$valores='';$prepared='';

        foreach($this->fields as $field) {
            if($this->$field != ''){
                $dados[$field] = $this->$field;
            }
        }        
        $cont = count($dados);
        $i = 0;
        foreach($dados as $campo => $valor) {
            $i++;
            if($i == $cont) {
                $campos .= $campo;
                $valores .= '"'.$valor.'"';
                $prepared .= ':'.$campo;
            } else {
                $campos .= $campo.',';
                $valores .= '"'.$valor.'", ';
                $prepared .= ':'.$campo.', ';
            }
        }
        $sql = 'INSERT INTO '.$this->table.' ('.$campos.') VALUES ('.$prepared.')';
        $this->query = $this->db->prepare($sql);
        foreach($dados as $campox => $valorx) {
            $this->query->bindValue(":".$campox, "".$valorx."");            
        }        
        $this->query->execute();        
        if($return == true) {        		
            return $this->db->lastInsertId();
        }
    }

    public function Update() {
        $this->Connect();
        foreach($this->fields as $field) {
                $dados[$field] .= $this->$field;
         }
        $cont = count($dados);
        $i = 0;
        foreach($dados as $campo => $valor) {
            $i++;
            if($i == $cont) {
                $valores .= ''.$campo.' = :'.$campo.'';
            } else {
                $valores .= ''.$campo.' = :'.$campo.', ';
            }
        }
        $sql = 'UPDATE '.$this->table.' SET '.$valores.' WHERE '.$this->fields[0].' = '.$dados[$this->fields[0]].' ';
        $this->query = $this->db->prepare($sql);
        foreach($dados as $campox => $valorx) {
            $this->query->bindValue(":".$campox, "".$valorx."");
        }
        $this->query->execute();
    }

    public function Delete() {
        $this->Connect();
        foreach($this->fields as $field) {
            $dados[$field] .= $this->$field;
        }
        $sql = 'UPDATE '.$this->table.' SET excluido = "" WHERE '.$this->fields[0].' = '.$dados[$this->fields[0]].'';
        $this->query = $this->db->prepare($sql);
        $this->query->execute();
    }

    public function specialQuery($query,$insert=false) {
        $this->Connect();
        $this->query = $this->db->prepare($query);
        $this->query->execute();
        if($insert){
            return $this->db->lastInsertId();
        }else{
            return $this->query->fetchAll();            
        }
    }
    public function auth($login, $pass) {
        $this->Connect();

        $sql = "SELECT * FROM ".$this->table." WHERE username = :username AND password = md5(:password) AND ativo = '*'";
      
        $this->query = $this->db->prepare($sql);
        $this->query->bindValue(':username', $login);
        $this->query->bindValue(':password', $pass);
        $this->query->execute();
        
        return $this->query->fetchAll();        
    }
}
?>
