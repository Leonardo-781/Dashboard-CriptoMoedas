<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');

// Tratamento de OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // URL da API CoinGecko
    // Obtém dados das 10 principais criptomoedas
    $url = 'https://api.coingecko.com/api/v3/coins/markets' . 
           '?vs_currency=brl' .
           '&order=market_cap_desc' .
           '&per_page=10' .
           '&page=1' .
           '&sparkline=false' .
           '&locale=pt';

    // Configurar opções para cURL
    $options = array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_ENCODING => 'gzip'
    );

    // Inicializar cURL
    $curl = curl_init();
    curl_setopt_array($curl, $options);

    // Executar requisição
    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $error = curl_error($curl);
    curl_close($curl);

    // Verificar se houve erro
    if ($error) {
        throw new Exception("Erro de conexão: " . $error);
    }

    if ($httpCode !== 200) {
        throw new Exception("Erro HTTP: " . $httpCode);
    }

    // Decodificar resposta
    $data = json_decode($response, true);

    if ($data === null) {
        throw new Exception("Erro ao decodificar JSON");
    }

    // Formatar dados para exibição
    $formattedData = array_map(function($crypto) {
        return array(
            'id' => $crypto['id'] ?? 'unknown',
            'name' => $crypto['name'] ?? 'Desconhecido',
            'symbol' => $crypto['symbol'] ?? 'N/A',
            'current_price' => $crypto['current_price'] ?? 0,
            'market_cap' => $crypto['market_cap'] ?? 0,
            'market_cap_rank' => $crypto['market_cap_rank'] ?? null,
            'total_volume' => $crypto['total_volume'] ?? 0,
            'high_24h' => $crypto['high_24h'] ?? 0,
            'low_24h' => $crypto['low_24h'] ?? 0,
            'price_change_percentage_24h' => $crypto['price_change_percentage_24h'] ?? 0,
            'market_cap_change_percentage_24h' => $crypto['market_cap_change_percentage_24h'] ?? 0,
            'circulating_supply' => $crypto['circulating_supply'] ?? 0,
            'total_supply' => $crypto['total_supply'] ?? 0,
            'last_updated' => $crypto['last_updated'] ?? date('Y-m-d H:i:s')
        );
    }, $data);

    // Retornar dados em formato JSON
    echo json_encode($formattedData, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array(
        'error' => true,
        'message' => 'Erro ao obter dados: ' . $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ));
}
