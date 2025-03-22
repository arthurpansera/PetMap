<?php
	function conecta_db(){
		$db_name = "petmap";
		$user 	 = "root";
		$pass    = "";
		$server  = "localhost:3306"; // Pode ser 3307
		$conexao = new mysqli($server, $user, $pass, $db_name);
		return $conexao;
	}
?>