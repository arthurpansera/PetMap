<?php
	function conecta_db(){
		$db_name = "petmap";
		$user = "root";
		$pass = "";
		$server = "localhost"; // Pode ser 3306 ou 3307
		$conexao = new mysqli($server, $user, $pass, $db_name);
	
		if ($conexao->connect_error) {
			die("Erro de conexão: " . $conexao->connect_error);
		}
		return $conexao;
	}
?>