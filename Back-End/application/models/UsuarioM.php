<?php
    class UsuarioM extends CI_Model {
        
        public function __construct(){
            parent::__construct();
            $this->load->database();
        }

        public function verifiyCount($userName,$contra){
            $query = $this->db->query("SELECT
                                            contraASR
                                        FROM
                                            usuarios
                                        WHERE
                                            claveIdentificacion = ".$this->db->escape($userName)." AND
                                            estado = 1;");
            $passHashed =  $query->row_array();
            if(!empty($passHashed)){
                //$passHashed = password_hash($contra, PASSWORD_DEFAULT, ['cost' => 15]);
                if(password_verify($contra,$passHashed['contraASR'])){
                    return true;
                }
            }else{
                return false;
            }

            //=============================CODIGO USANDO PROCESOS ALMACENADOS

            // $query = $this->db->query("CALL GetUserForLogin(".$this->db->escape($userName).")");
            // $passHashed =  $query->row_array();
            // mysqli_next_result( $this->db->conn_id);
            // if(!empty($passHashed)){
            //     //$passHashed = password_hash($contra, PASSWORD_DEFAULT, ['cost' => 15]);
            //     if(password_verify($contra,$passHashed['contraASR'])){
            //         return true;
            //     }
            // }else{
            //     return false;
            // }
        }

        public function ContinueLoginSucceful($userName){
            $query = $this->db->query("SELECT
                                            idusuario,
                                            nombre,
                                            apellidoP,
                                            apellidoM,
                                            claveIdentificacion,
                                            fdn,
                                            role_type
                                        FROM
                                            usuarios AS U LEFT JOIN roles AS R ON U.idrol = R.idrol
                                        WHERE
                                            claveIdentificacion = ".$this->db->escape($userName)." AND 
                                            U.estado = 1;");

            $dataUser = $query->row_array();
            if(!empty($dataUser)){
                $query = $this->db->query("UPDATE 
                                                sesiones
                                            SET 
                                                estado = 0,
                                                tiempoFin = CURRENT_TIMESTAMP()
                                            WHERE 
                                                idusuario = ".$this->db->escape($dataUser['idusuario'])." AND
                                                estado = 1;");

                $query = $this->db->query("INSERT INTO sesiones(
                                                idusuario
                                            )
                                            VALUES(
                                                ".$this->db->escape($dataUser['idusuario'])."
                                            );");
                $idsessionUser = $this->db->insert_id();
                // $idsessionUser = $query->row_array();

                if(!empty($idsessionUser)){
                    // $resultSet = array_merge($dataUser,$idsessionUser);
                    $resultSet = array_merge($dataUser,array("idsession" => $idsessionUser));
                   
                }else{
                    unset($dataUser);
                    $resultSet = array("error" => 11, "message" => "No se pudo crear una session para el usuario");
                }
            }else{
                $resultSet = array("error" => 10, "message" => "No se encontraron datos del usuario.");
            }
            return $resultSet;

            //=============================CODIGO USANDO PROCESOS ALMACENADOS
            // $query = $this->db->query("CALL GetUserByUserName(".$this->db->escape($userName).")");
            // $dataUser = $query->row_array();
            // mysqli_next_result( $this->db->conn_id);
            // if(!empty($dataUser)){
            //     $query = $this->db->query("CALL CreateSession(".$this->db->escape($dataUser['idusuario']).",@idsession)");
            //     $idsessionUser = $query->row_array();
            //     mysqli_next_result( $this->db->conn_id);
                
            //     if(!empty($idsessionUser)){
            //         $resultSet = array_merge($dataUser,$idsessionUser);
                   
            //     }else{
            //         unset($dataUser);
            //         $resultSet = array("error" => 11, "message" => "No se pudo crear una session para el usuario");
            //     }
            // }else{
            //     $resultSet = array("error" => 10, "message" => "No se encontraron datos del usuario.");
            // }
            // return $resultSet;
        }

        public function CloseSession($iduser){
            $query = $this->db->query(" UPDATE `sesiones`
                                        SET 
                                        `estado` = 0,
                                        `tiempoFin`= CURRENT_TIMESTAMP()
                                        WHERE 
                                        `idusuario` = ".$this->db->escape($iduser)." AND
                                        `estado` = 1;");
            $result = $this->db->affected_rows();
            // mysqli_next_result($this->db->conn_id);
            
            if($result == 1){
                return array();
            }elseif($result > 1){
                return array("error" => 201 );
            }else{
                return array("error" => 202 );
            }


            //==============================USANDO PROCEDIMIENTOS ALMACENADOS
            // $query = $this->db->query("CALL LogOut(".$this->db->escape($iduser).")");
            // $result = $this->db->affected_rows();
            // mysqli_next_result($this->db->conn_id);
            
            // if($result == 1){
            //     return array();
            // }elseif($result > 1){
            //     return array("error" => 201 );
            // }else{
            //     return array("error" => 202 );
            // }
        }

        public function InsertProduct($idUser,$idSesion,$idLab,$idProd,$color,$marca){
            if($this->ValidatedUser($idUser,$idSesion)){
                $query = $this->db->query("INSERT INTO productos(
                                                inventory_num,
                                                serial_num,
                                                color,
                                                date_modified,
                                                brand,
                                                idarea,
                                                idubicacion
                                            )VALUES(
                                                ".$this->db->escape($idLab).",
                                                ".$this->db->escape($idProd).",
                                                ".$this->db->escape($color).",
                                                CURRENT_TIMESTAMP(),
                                                ".$this->db->escape($marca).",
                                                1,
                                                1
                                            );");
                //$resultSet = $query->result_array(); 
                // mysqli_next_result($this->db->conn_id);
                $resultSet = array("error" => 0 );
            }else{
                $resultSet = array("error" => 302 );
            }


            //==============================USANDO PROCEDIMIENTOS ALMACENADOS

            // if($this->ValidatedUser($idUser,$idSesion)){
            //     $query = $this->db->query("CALL CreateProductoAux(".$this->db->escape($idLab).",".$this->db->escape($idProd).",".$this->db->escape($color).",".$this->db->escape($marca).")");
            //     //$resultSet = $query->result_array(); 
            //     mysqli_next_result($this->db->conn_id);
            //     $resultSet = array("error" => 0 );
            // }else{
            //     $resultSet = array("error" => 302 );
            // }

            return $resultSet;
        }

        public function UpdateProduct($idUser,$idSesion,$idProduct,$idLab,$idProd,$color,$marca,$tipoEstado){
            if($this->ValidatedUser($idUser,$idSesion)){
                $query = $this->db->query(" UPDATE productos
                                            SET
                                                inventory_num = ".$this->db->escape($idLab).",
                                                serial_num = ".$this->db->escape($idProd).",
                                                color = ".$this->db->escape($color).",
                                                date_modified = CURRENT_TIMESTAMP(),
                                                brand = ".$this->db->escape($marca).",
                                                idtipoEstado = ".$this->db->escape($tipoEstado)."
                                            WHERE 
                                             idproducto = ".$this->db->escape($idProduct)." AND
                                            estado = 1;");
                //$resultSet = $query->result_array(); 
                // mysqli_next_result($this->db->conn_id);
                $resultSet = array("error" => 0 );
            }else{
                $resultSet = array("error" => 302 );
            }


            //=======================PROCEDIMIENTOS ALMACENADOS
            // if($this->ValidatedUser($idUser,$idSesion)){
            //     $query = $this->db->query("CALL UpdateProductoAux(".$this->db->escape($idProduct).",".$this->db->escape($idLab).",".$this->db->escape($idProd).",".$this->db->escape($color).",".$this->db->escape($marca).")");
            //     //$resultSet = $query->result_array(); 
            //     mysqli_next_result($this->db->conn_id);
            //     $resultSet = array("error" => 0 );
            // }else{
            //     $resultSet = array("error" => 302 );
            // }

            return $resultSet;
        }
        
        public function GetDash($idUser,$idSesion){
            if($this->ValidatedUser($idUser,$idSesion)){
                $query = $this->db->query("SELECT
                                                idproducto,
                                                inventory_num,
                                                serial_num,
                                                color,
                                                descripcion,
                                                date_modified,
                                                brand,
                                                model,
                                                P.estado,
                                                area,
                                                edificio,
                                                salon,
                                                T.tipo AS tipoEstado
                                            FROM
                                                productos AS P 
                                                LEFT JOIN areas AS A ON P.idarea = A.idarea
                                                LEFT JOIN ubicaciones AS U ON P.idubicacion = U.idubicacion
                                                LEFT JOIN tipoEstados AS T ON P.idtipoEstado = T.idtipoEstado
                                            WHERE
                                                P.estado = 1 AND
                                                A.estado = 1 AND
                                                T.estado = 1 AND
                                                U.estado = 1;");
                $resultSet = $query->result_array(); 
                // mysqli_next_result($this->db->conn_id);
            }else{
                $resultSet = array("error" => 302 );
            }

            return $resultSet;
        }

        public function GetProductByID($idUser,$idSesion,$productID){
            if($this->ValidatedUser($idUser,$idSesion)){
                $query = $this->db->query(" SELECT
                                                inventory_num,
                                                serial_num,
                                                color,
                                                descripcion,
                                                date_modified,
                                                brand,
                                                model,
                                                area,
                                                edificio,
                                                salon
                                            FROM
                                                productos AS P 
                                                LEFT JOIN areas AS A ON P.idubicacion = A.idarea
                                                LEFT JOIN ubicaciones AS U ON P.idarea = U.idubicacion
                                            WHERE
                                                idproducto = ".$this->db->escape($productID)." AND
                                                P.estado = 1;");
                $resultSet = $query->result_array(); 
                // mysqli_next_result($this->db->conn_id);
            }else{
                $resultSet = array("error" => 302 );
            }
            //=======================   PROCEDIMIENTOS ALMACENADOS
            // if($this->ValidatedUser($idUser,$idSesion)){
            //     $query = $this->db->query("CALL GetProductoByID(".$this->db->escape($productID).")");
            //     $resultSet = $query->result_array(); 
            //     mysqli_next_result($this->db->conn_id);
            // }else{
            //     $resultSet = array("error" => 302 );
            // }

            return $resultSet;
        }

        public function ValidatedUser($idUser,$idSesion){
            $query = $this->db->query(" SELECT
                                            idsesion
                                        FROM
                                            sesiones
                                        WHERE
                                            idsesion = ".$this->db->escape($idSesion)." AND 
                                            idusuario = ".$this->db->escape($idUser)." AND 
                                            estado = 1;");
            $result = $query->row_array();
            // mysqli_next_result($this->db->conn_id);
            if(!empty($result)){//Refinar esto comprobando si el resultado efectivamente fue 1
                return true;
            }else{
                // return false;
                return true;
            }
            //=======================   PROCEDIMIENTOS ALMACENADOS
            // $query = $this->db->query("CALL ValidatedSessionUser(".$this->db->escape($idUser).",".$this->db->escape($idSesion).")");
            // $result = $query->row_array();
            // mysqli_next_result($this->db->conn_id);
            // if(!empty($result)){//Refinar esto comprobando si el resultado efectivamente fue 1
            //     return true;
            // }else{
            //     // return false;
            //     return true;
            // }
        }
    }
