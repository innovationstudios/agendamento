<?php
header('Content-Type: application/json; charset=utf-8');

require_once '../config/database.php';
require_once '../vendor/autoload.php'; // EvolutionApiPlugin

date_default_timezone_set('America/Sao_Paulo');

$response = [
    'config' => null,
    'evolutionApi' => null,
    'mensalidades_count' => 0,
    'templates_ativos' => [],
    'mensalidades_processadas' => [],
    'errors' => [],
];

try {
    // 1) Conectar no banco
    $conn = getDBConnection();

    // 2) Carregar configurações whatsapp
    $config = [];
    $result = $conn->query("SELECT chave, valor FROM configuracoes WHERE tipo = 'whatsapp'");
    while ($row = $result->fetch_assoc()) {
        $config[$row['chave']] = $row['valor'];
    }
    $response['config'] = $config;

    if (empty($config['api_key'])) {
        throw new Exception("Evolution API não configurada - api_key faltando.");
    }

    // 3) Instanciar Evolution API
    $evolutionApi = new EvolutionApiPlugin\EvolutionApi(
        $config['api_key'],
        $config['api_url'] ?? ''
    );
    $response['evolutionApi'] = "Instanciada com sucesso";

    // 4) Buscar templates ativos
    $templates_ativos = [];
    $template_result = $conn->query("SELECT * FROM mensagem_templates WHERE ativo = TRUE");
    while ($tpl = $template_result->fetch_assoc()) {
        $templates_ativos[(int)$tpl['dias_antes']] = $tpl; // chave = dias_antes
    }
    $response['templates_ativos'] = array_keys($templates_ativos);

    // 5) Buscar mensalidades pendentes + usuarios/plano ativos (sem filtro por dias)
    $query = "SELECT m.id, m.data_vencimento, u.nome as cliente_nome, u.telefone, p.nome as plano_nome, p.valor
              FROM mensalidades m
              JOIN usuarios u ON m.id_usuario = u.id
              JOIN planos p ON m.id_plano = p.id
              WHERE m.status = 'pendente'
                AND u.ativo = TRUE
                AND p.ativo = TRUE";

    $result = $conn->query($query);
    if (!$result) {
        throw new Exception("Erro na query de mensalidades: " . $conn->error);
    }

    $response['mensalidades_count'] = $result->num_rows;

    // 6) Processar mensalidades
    while ($row = $result->fetch_assoc()) {
        $mensalidade = $row;

        // Calcular dias restantes (com sinal)
        $data_venc = new DateTime($row['data_vencimento']);
        $hoje = new DateTime();
        $interval = $hoje->diff($data_venc);
        $dias_restantes = (int)$interval->format('%r%a'); // pode ser negativo

        $mensalidade['dias_restantes'] = $dias_restantes;

        // Verificar se existe template para este dias_restantes
        if (isset($templates_ativos[$dias_restantes])) {
            $template = $templates_ativos[$dias_restantes];
            $mensalidade['template'] = $template;

            // Montar mensagem com placeholders
            $mensagem = str_replace(
                ['{cliente_nome}', '{plano_nome}', '{valor}', '{data_vencimento}', '{dias_restantes}'],
                [
                    $row['cliente_nome'],
                    $row['plano_nome'],
                    number_format($row['valor'], 2, ',', '.'),
                    date('d/m/Y', strtotime($row['data_vencimento'])),
                    $dias_restantes
                ],
                $template['mensagem']
            );
            $mensalidade['mensagem_montada'] = $mensagem;

            // Format telefone WhatsApp (somente números, com DDI 55)
            $telefone = preg_replace('/[^0-9]/', '', $row['telefone']);
            if (substr($telefone, 0, 2) !== '55') {
                $telefone = '55' . $telefone;
            }
            $mensalidade['telefone_formatado'] = $telefone;

            try {
                $instanceName = $config['instance_name'] ?? 'Teste';

                $responseSend = $evolutionApi->sendTextMessage(
                    $instanceName,
                    $telefone,
                    $mensagem,
                    [
                        'delay' => 2,
                        'presence' => 'composing'
                    ],
                    []
                );

                $status = isset($responseSend['error']) ? 'falha' : 'enviado';
                $resposta_json = json_encode($responseSend);

                // Log banco
                $log = $conn->prepare("INSERT INTO mensagem_logs (id_mensalidade, id_template, status, resposta) VALUES (?, ?, ?, ?)");
                $log->bind_param("iiss", $row['id'], $template['id'], $status, $resposta_json);
                $log->execute();

                // Colocar o JSON response da API no retorno para debug
                $mensalidade['envio'] = [
                    'status' => $status,
                    'response_api' => $responseSend
                ];

            } catch (Exception $ex) {
                $status = 'falha';
                $resposta = $ex->getMessage();

                $log = $conn->prepare("INSERT INTO mensagem_logs (id_mensalidade, id_template, status, resposta) VALUES (?, ?, ?, ?)");
                $log->bind_param("iiss", $row['id'], $template['id'], $status, $resposta);
                $log->execute();

                $mensalidade['envio'] = [
                    'status' => $status,
                    'error_message' => $resposta
                ];
            }
        } else {
            $mensalidade['template'] = null;
            $mensalidade['envio'] = null;
        }

        $response['mensalidades_processadas'][] = $mensalidade;
    }

    $conn->close();

} catch (Exception $e) {
    $response['errors'][] = $e->getMessage();
    if (isset($conn)) $conn->close();
}

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
