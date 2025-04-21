<?php
header('Content-Type: application/json');

$jsonData = file_get_contents('data.json');


if ($jsonData === false) {
    echo json_encode(["error" => "Erro ao carregar os dados"]);
    exit;
}


echo $jsonData;
