<?php
// Definir as credenciais de conexão com o banco de dados
$hostname = 'localhost';     // Nome do host do banco de dados
$username = 'root';          // Nome de usuário do banco de dados
$password = '';              // Senha do banco de dados
$database = 'ECO';           // Nome do banco de dados
$port = 3307;                // Porta do banco de dados (caso necessário)

// Estabelecer a conexão com o banco de dados
$con = mysqli_connect($hostname, $username, $password, $database, $port);

// Verificar se a conexão falhou
if (mysqli_connect_errno()) {
    // Exibir erro e encerrar a execução se a conexão falhar
    printf("Erro Conexão: %s", mysqli_connect_error());
    exit();
}

// URL da API que fornece as informações meteorológicas
$url = "https://api.open-meteo.com/v1/forecast?latitude=-21.248833&longitude=-50.314750&current_weather=true";

// Inicializar a sessão cURL para fazer a requisição à API
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);            // Definir a URL da API
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Retornar a resposta como string
$response = curl_exec($ch);                     // Executar a requisição
curl_close($ch);                               // Fechar a conexão cURL

// Decodificar a resposta JSON da API
$data = json_decode($response, true);

// Verificar se os dados de clima foram recebidos corretamente
if (isset($data['current_weather'])) {
    $current = $data['current_weather']; // Armazenar os dados meteorológicos atuais

    // Definir a data atual
    $data_dados = date('Y-m-d');
    // Definir o ID do sensor (substitua pelo ID correto do seu banco de dados)
    $sensor_id = 0;

    // Inserir ou atualizar a temperatura (ID_Dados = 1)
    $temperatura = isset($current['temperature']) ? $current['temperature'] : 0; // Se não houver temperatura, atribui 0
    $sql_temp = "INSERT INTO Dados (ID_Dados, ID_Sensor, Valor_Dados, Data_Dados)
                 VALUES (1, '$sensor_id', '$temperatura', '$data_dados')
                 ON DUPLICATE KEY UPDATE Valor_Dados = '$temperatura', Data_Dados = '$data_dados'";
    $con->query($sql_temp); // Executar a consulta

    // Inserir ou atualizar a velocidade do vento (ID_Dados = 2)
    $vento = isset($current['windspeed']) ? $current['windspeed'] : 0; // Se não houver vento, atribui 0
    $sql_vento = "INSERT INTO Dados (ID_Dados, ID_Sensor, Valor_Dados, Data_Dados)
                  VALUES (2, '$sensor_id', '$vento', '$data_dados')
                  ON DUPLICATE KEY UPDATE Valor_Dados = '$vento', Data_Dados = '$data_dados'";
    $con->query($sql_vento); // Executar a consulta

    // Inserir ou atualizar a umidade (ID_Dados = 3)
    $umidade = isset($current['relative_humidity']) ? $current['relative_humidity'] : 0; // Se não houver umidade, atribui 0
    $sql_umidade = "INSERT INTO Dados (ID_Dados, ID_Sensor, Valor_Dados, Data_Dados)
                    VALUES (3, '$sensor_id', '$umidade', '$data_dados')
                    ON DUPLICATE KEY UPDATE Valor_Dados = '$umidade', Data_Dados = '$data_dados'";
    $con->query($sql_umidade); // Executar a consulta

    // Inserir ou atualizar a chance de chuva (ID_Dados = 4)
    if (isset($data['hourly']['precipitation_hours']) && count($data['hourly']['precipitation_hours']) > 0) {
        $precipitacao = $data['hourly']['precipitation_hours'][0]; // Pega a primeira previsão de precipitação
    } else {
        $precipitacao = 0; // Se não houver dados de precipitação, atribui 0
    }
    $sql_precipitacao = "INSERT INTO Dados (ID_Dados, ID_Sensor, Valor_Dados, Data_Dados)
                         VALUES (4, '$sensor_id', '$precipitacao', '$data_dados')
                         ON DUPLICATE KEY UPDATE Valor_Dados = '$precipitacao', Data_Dados = '$data_dados'";
    $con->query($sql_precipitacao); // Executar a consulta

    echo "Dados inseridos com sucesso!"; // Mensagem de sucesso
} else {
    echo "Erro: Dados não encontrados na API."; // Mensagem de erro caso os dados não sejam encontrados
}

// Fechar a conexão com o banco de dados
$con->close();
?>