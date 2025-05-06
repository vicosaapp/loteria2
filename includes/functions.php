<?php
/**
 * Arquivo de funções gerais
 * Contém funções utilizadas em várias partes do sistema
 */

// Incluir conexão com o banco de dados
require_once 'db.php';

/**
 * Conta o número de usuários por tipo
 * @param string $tipo Tipo de usuário (admin, revendedor, usuario)
 * @return int Número de usuários do tipo especificado
 */
function count_users_by_type($tipo) {
    $tipo = escape_string($tipo);
    $sql = "SELECT COUNT(*) as total FROM usuarios WHERE tipo = ? AND status = 'ativo'";
    $result = prepared_query($sql, "s", [$tipo]);
    return $result[0]['total'] ?? 0;
}

/**
 * Conta o número total de apostas
 * @return int Número total de apostas
 */
function count_total_bets() {
    $sql = "SELECT COUNT(*) as total FROM apostas";
    $result = query($sql);
    return $result[0]['total'] ?? 0;
}

/**
 * Calcula a receita total de todas as apostas
 * @return float Valor total arrecadado
 */
function get_total_revenue() {
    $sql = "SELECT SUM(valor) as total FROM apostas";
    $result = query($sql);
    return $result[0]['total'] ?? 0;
}

/**
 * Obtém as atividades recentes do sistema
 * @param int $limit Número máximo de atividades a retornar
 * @return array Lista de atividades recentes
 */
function get_recent_activities($limit = 10) {
    $limit = (int) $limit;
    $sql = "SELECT l.*, u.username, u.tipo 
            FROM logs_atividades l
            JOIN usuarios u ON l.usuario_id = u.id
            ORDER BY l.data_hora DESC
            LIMIT $limit";
    return query($sql);
}

/**
 * Calcula o total de vendas de um revendedor
 * @param int $revendedor_id ID do revendedor
 * @return float Total de vendas
 */
function get_total_sales_by_reseller($revendedor_id) {
    $sql = "SELECT SUM(valor) as total FROM apostas WHERE revendedor_id = ?";
    $result = prepared_query($sql, "i", [$revendedor_id]);
    return $result[0]['total'] ?? 0;
}

/**
 * Conta o número de apostas de um revendedor
 * @param int $revendedor_id ID do revendedor
 * @return int Número de apostas
 */
function get_total_bets_by_reseller($revendedor_id) {
    $sql = "SELECT COUNT(*) as total FROM apostas WHERE revendedor_id = ?";
    $result = prepared_query($sql, "i", [$revendedor_id]);
    return $result[0]['total'] ?? 0;
}

/**
 * Calcula a comissão total de um revendedor
 * @param int $revendedor_id ID do revendedor
 * @return float Valor total da comissão
 */
function get_total_commission($revendedor_id) {
    $sql = "SELECT SUM(comissao) as total FROM comissoes WHERE revendedor_id = ?";
    $result = prepared_query($sql, "i", [$revendedor_id]);
    return $result[0]['total'] ?? 0;
}

/**
 * Obtém as apostas recentes de um revendedor
 * @param int $revendedor_id ID do revendedor
 * @param int $limit Número máximo de apostas a retornar
 * @return array Lista de apostas recentes
 */
function get_recent_bets_by_reseller($revendedor_id, $limit = 5) {
    $sql = "SELECT a.*, u.nome as nome_cliente, j.nome as nome_jogo
            FROM apostas a
            JOIN usuarios u ON a.usuario_id = u.id
            JOIN jogos j ON a.jogo_id = j.id
            WHERE a.revendedor_id = ?
            ORDER BY a.data_aposta DESC
            LIMIT ?";
    return prepared_query($sql, "ii", [$revendedor_id, $limit]);
}

/**
 * Obtém as apostas de um usuário
 * @param int $user_id ID do usuário
 * @return array Lista de apostas do usuário
 */
function get_user_bets($user_id) {
    $sql = "SELECT a.*, j.nome as nome_jogo
            FROM apostas a
            JOIN jogos j ON a.jogo_id = j.id
            WHERE a.usuario_id = ?
            ORDER BY a.data_aposta DESC";
    return prepared_query($sql, "i", [$user_id]);
}

/**
 * Obtém as apostas vencedoras de um usuário
 * @param int $user_id ID do usuário
 * @return array Lista de apostas vencedoras
 */
function get_winning_bets($user_id) {
    $sql = "SELECT a.*, j.nome as nome_jogo
            FROM apostas a
            JOIN jogos j ON a.jogo_id = j.id
            WHERE a.usuario_id = ? AND a.status = 'premiado'
            ORDER BY a.data_aposta DESC";
    return prepared_query($sql, "i", [$user_id]);
}

/**
 * Calcula o valor total apostado por um usuário
 * @param int $user_id ID do usuário
 * @return float Valor total das apostas
 */
function get_total_bet_amount($user_id) {
    $sql = "SELECT SUM(valor) as total FROM apostas WHERE usuario_id = ?";
    $result = prepared_query($sql, "i", [$user_id]);
    return $result[0]['total'] ?? 0;
}

/**
 * Calcula o valor total ganho em prêmios por um usuário
 * @param int $user_id ID do usuário
 * @return float Valor total dos prêmios
 */
function get_total_winnings($user_id) {
    $sql = "SELECT SUM(valor_premio) as total FROM apostas WHERE usuario_id = ? AND status = 'premiado'";
    $result = prepared_query($sql, "i", [$user_id]);
    return $result[0]['total'] ?? 0;
}

/**
 * Obtém os próximos sorteios programados
 * @return array Lista de sorteios
 */
function get_upcoming_draws() {
    $sql = "SELECT s.*, j.nome as nome_jogo
            FROM sorteios s
            JOIN jogos j ON s.jogo_id = j.id
            WHERE s.data_sorteio > NOW()
            ORDER BY s.data_sorteio ASC";
    return query($sql);
}

/**
 * Converte um código de status em texto descritivo
 * @param string $status Código do status
 * @return string Descrição do status
 */
function get_status_text($status) {
    $status_map = [
        'pendente' => 'Pendente',
        'processando' => 'Processando',
        'concluido' => 'Concluído',
        'premiado' => 'Premiado',
        'nao_premiado' => 'Não Premiado',
        'cancelado' => 'Cancelado'
    ];
    
    return $status_map[$status] ?? 'Desconhecido';
}

/**
 * Gera um bilhete de loteria em PDF
 * @param int $aposta_id ID da aposta
 * @return string Caminho para o arquivo PDF gerado
 */
function generate_bet_pdf($aposta_id) {
    // Obter dados da aposta
    $sql = "SELECT a.*, u.nome as nome_usuario, r.nome as nome_revendedor, j.nome as nome_jogo
            FROM apostas a
            JOIN usuarios u ON a.usuario_id = u.id
            JOIN usuarios r ON a.revendedor_id = r.id
            JOIN jogos j ON a.jogo_id = j.id
            WHERE a.id = ?";
    $result = prepared_query($sql, "i", [$aposta_id]);
    
    if (empty($result)) {
        return false;
    }
    
    $aposta = $result[0];
    
    // Verificar se a biblioteca TCPDF está disponível
    if (!file_exists(__DIR__ . '/tcpdf/tcpdf.php')) {
        die("Biblioteca TCPDF não encontrada. Por favor, instale a biblioteca.");
    }
    
    // Incluir a biblioteca TCPDF
    require_once(__DIR__ . '/tcpdf/tcpdf.php');
    
    // Criar objeto PDF
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    
    // Configurar o PDF
    $pdf->SetCreator('Sistema de Loteria');
    $pdf->SetAuthor('Loteria');
    $pdf->SetTitle('Bilhete de Loteria - ' . $aposta['nome_jogo']);
    $pdf->SetSubject('Bilhete de Aposta');
    
    // Remover cabeçalho e rodapé padrões
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    
    // Adicionar página
    $pdf->AddPage();
    
    // Conteúdo do bilhete
    $html = '
    <h1 style="text-align:center;">BILHETE DE LOTERIA</h1>
    <h2 style="text-align:center;">' . $aposta['nome_jogo'] . '</h2>
    <hr>
    <table>
        <tr>
            <td><strong>Nº do Bilhete:</strong></td>
            <td>' . $aposta_id . '</td>
        </tr>
        <tr>
            <td><strong>Data da Aposta:</strong></td>
            <td>' . date('d/m/Y H:i', strtotime($aposta['data_aposta'])) . '</td>
        </tr>
        <tr>
            <td><strong>Apostador:</strong></td>
            <td>' . $aposta['nome_usuario'] . '</td>
        </tr>
        <tr>
            <td><strong>Revendedor:</strong></td>
            <td>' . $aposta['nome_revendedor'] . '</td>
        </tr>
        <tr>
            <td><strong>Números Apostados:</strong></td>
            <td>' . $aposta['numeros_escolhidos'] . '</td>
        </tr>
        <tr>
            <td><strong>Valor da Aposta:</strong></td>
            <td>R$ ' . number_format($aposta['valor'], 2, ',', '.') . '</td>
        </tr>
    </table>
    <hr>
    <p style="text-align:center;"><small>Este bilhete é o comprovante oficial da sua aposta.</small></p>
    <p style="text-align:center;"><small>Guarde-o com cuidado para receber seu prêmio, caso seja contemplado.</small></p>
    <p style="text-align:center;"><barcode type="QR" value="ID:' . $aposta_id . '" style="width:30mm; height:30mm; margin: 0 auto;" /></p>
    ';
    
    // Gerar o PDF
    $pdf->writeHTML($html, true, false, true, false, '');
    
    // Nome do arquivo
    $filename = 'bilhete_' . $aposta_id . '_' . date('YmdHis') . '.pdf';
    $filepath = '../uploads/' . $filename;
    
    // Salvar arquivo
    $pdf->Output($filepath, 'F');
    
    // Registrar geração do PDF
    register_activity($aposta['usuario_id'], 'pdf_gerado', 'Bilhete de aposta gerado para o ID: ' . $aposta_id);
    
    return $filepath;
}

/**
 * Gera um relatório de vendas em PDF
 * @param int $revendedor_id ID do revendedor (opcional, se não fornecido gera para todos)
 * @param string $data_inicio Data de início (formato Y-m-d)
 * @param string $data_fim Data de fim (formato Y-m-d)
 * @return string Caminho para o arquivo PDF gerado
 */
function generate_sales_report_pdf($data_inicio, $data_fim, $revendedor_id = null) {
    // Verificar se a biblioteca TCPDF está disponível
    if (!file_exists(__DIR__ . '/tcpdf/tcpdf.php')) {
        die("Biblioteca TCPDF não encontrada. Por favor, instale a biblioteca.");
    }
    
    // Incluir a biblioteca TCPDF
    require_once(__DIR__ . '/tcpdf/tcpdf.php');
    
    // Obter dados das vendas
    $sql = "SELECT a.*, j.nome as nome_jogo, u.nome as nome_usuario, r.nome as nome_revendedor
            FROM apostas a
            JOIN jogos j ON a.jogo_id = j.id
            JOIN usuarios u ON a.usuario_id = u.id
            JOIN usuarios r ON a.revendedor_id = r.id
            WHERE a.data_aposta BETWEEN ? AND ?";
    
    $params = [$data_inicio . ' 00:00:00', $data_fim . ' 23:59:59'];
    $types = "ss";
    
    if ($revendedor_id) {
        $sql .= " AND a.revendedor_id = ?";
        $params[] = $revendedor_id;
        $types .= "i";
    }
    
    $sql .= " ORDER BY a.data_aposta";
    
    $apostas = prepared_query($sql, $types, $params);
    
    // Criar objeto PDF
    $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
    
    // Configurar o PDF
    $pdf->SetCreator('Sistema de Loteria');
    $pdf->SetAuthor('Loteria');
    $pdf->SetTitle('Relatório de Vendas');
    $pdf->SetSubject('Relatório de Vendas de Loteria');
    
    // Remover cabeçalho e rodapé padrões
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    
    // Adicionar página
    $pdf->AddPage();
    
    // Título do relatório
    $titulo = 'RELATÓRIO DE VENDAS';
    if ($revendedor_id) {
        $revendedor = prepared_query("SELECT nome FROM usuarios WHERE id = ?", "i", [$revendedor_id]);
        $titulo .= ' - Revendedor: ' . $revendedor[0]['nome'];
    }
    $titulo .= '<br>Período: ' . date('d/m/Y', strtotime($data_inicio)) . ' a ' . date('d/m/Y', strtotime($data_fim));
    
    // Calcular totais
    $total_vendas = 0;
    $total_comissoes = 0;
    foreach ($apostas as $aposta) {
        $total_vendas += $aposta['valor'];
        $total_comissoes += $aposta['valor'] * 0.10; // Considerando 10% de comissão
    }
    
    // Conteúdo do relatório
    $html = '
    <h1 style="text-align:center;">' . $titulo . '</h1>
    <hr>
    <table border="1" cellpadding="5">
        <thead>
            <tr style="background-color:#f5f5f5;">
                <th>Data</th>
                <th>Bilhete</th>
                <th>Jogo</th>
                <th>Apostador</th>
                <th>Revendedor</th>
                <th>Números</th>
                <th>Valor</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>';
    
    foreach ($apostas as $aposta) {
        $html .= '
            <tr>
                <td>' . date('d/m/Y H:i', strtotime($aposta['data_aposta'])) . '</td>
                <td>' . $aposta['id'] . '</td>
                <td>' . $aposta['nome_jogo'] . '</td>
                <td>' . $aposta['nome_usuario'] . '</td>
                <td>' . $aposta['nome_revendedor'] . '</td>
                <td>' . $aposta['numeros_escolhidos'] . '</td>
                <td>R$ ' . number_format($aposta['valor'], 2, ',', '.') . '</td>
                <td>' . get_status_text($aposta['status']) . '</td>
            </tr>';
    }
    
    $html .= '
        </tbody>
        <tfoot>
            <tr style="background-color:#f5f5f5;">
                <th colspan="6" style="text-align:right;">Total de Vendas:</th>
                <th>R$ ' . number_format($total_vendas, 2, ',', '.') . '</th>
                <th></th>
            </tr>
            <tr style="background-color:#f5f5f5;">
                <th colspan="6" style="text-align:right;">Total de Comissões:</th>
                <th>R$ ' . number_format($total_comissoes, 2, ',', '.') . '</th>
                <th></th>
            </tr>
        </tfoot>
    </table>
    <hr>
    <p style="text-align:center;"><small>Relatório gerado em ' . date('d/m/Y H:i:s') . '</small></p>
    ';
    
    // Gerar o PDF
    $pdf->writeHTML($html, true, false, true, false, '');
    
    // Nome do arquivo
    $filename = 'relatorio_vendas_' . date('YmdHis') . '.pdf';
    $filepath = '../uploads/' . $filename;
    
    // Salvar arquivo
    $pdf->Output($filepath, 'F');
    
    // Registrar geração do relatório
    if (isset($_SESSION['user_id'])) {
        register_activity($_SESSION['user_id'], 'relatorio_gerado', 'Relatório de vendas gerado: ' . $filename);
    }
    
    return $filepath;
}

/**
 * Gera números aleatórios para um jogo
 * @param int $jogo_id ID do jogo
 * @param int $quantidade Quantidade de números a gerar
 * @return string Números gerados formatados
 */
function generate_random_numbers($jogo_id, $quantidade) {
    // Obter configurações do jogo
    $sql = "SELECT * FROM jogos WHERE id = ?";
    $result = prepared_query($sql, "i", [$jogo_id]);
    
    if (empty($result)) {
        return false;
    }
    
    $jogo = $result[0];
    $min = $jogo['numero_minimo'];
    $max = $jogo['numero_maximo'];
    $quantidade = min($quantidade, $jogo['numeros_por_jogo']);
    
    // Gerar números aleatórios únicos
    $numeros = [];
    while (count($numeros) < $quantidade) {
        $numero = mt_rand($min, $max);
        if (!in_array($numero, $numeros)) {
            $numeros[] = $numero;
        }
    }
    
    // Ordenar números em ordem crescente
    sort($numeros);
    
    // Formatar números conforme o padrão do jogo
    $formatted = implode('-', $numeros);
    
    return $formatted;
}

/**
 * Verifica se uma aposta foi premiada
 * @param int $aposta_id ID da aposta
 * @param array $numeros_sorteados Números sorteados
 * @return bool|float Retorna false se não premiada ou o valor do prêmio se for premiada
 */
function check_winning_bet($aposta_id, $numeros_sorteados) {
    // Obter dados da aposta
    $sql = "SELECT a.*, j.* FROM apostas a JOIN jogos j ON a.jogo_id = j.id WHERE a.id = ?";
    $result = prepared_query($sql, "i", [$aposta_id]);
    
    if (empty($result)) {
        return false;
    }
    
    $aposta = $result[0];
    $numeros_apostados = explode('-', $aposta['numeros_escolhidos']);
    
    // Contar acertos
    $acertos = 0;
    foreach ($numeros_apostados as $numero) {
        if (in_array($numero, $numeros_sorteados)) {
            $acertos++;
        }
    }
    
    // Verificar regras de premiação do jogo
    $premio = 0;
    
    // Lógica simplificada - na prática, isso viria da tabela de premiações do jogo
    if ($acertos >= $aposta['acertos_minimos']) {
        // Exemplo: prêmio aumenta exponencialmente com o número de acertos
        $premio = $aposta['valor'] * pow(2, $acertos - $aposta['acertos_minimos'] + 1);
    }
    
    return $premio > 0 ? $premio : false;
}

/**
 * Formata um número de telefone para exibição
 * @param string $telefone Número de telefone
 * @return string Telefone formatado
 */
function format_phone($telefone) {
    $telefone = preg_replace('/[^0-9]/', '', $telefone);
    $len = strlen($telefone);
    
    if ($len == 11) {
        // Celular com DDD (11 dígitos)
        return '(' . substr($telefone, 0, 2) . ') ' . substr($telefone, 2, 5) . '-' . substr($telefone, 7);
    } else if ($len == 10) {
        // Telefone fixo com DDD (10 dígitos)
        return '(' . substr($telefone, 0, 2) . ') ' . substr($telefone, 2, 4) . '-' . substr($telefone, 6);
    } else if ($len == 9) {
        // Celular sem DDD (9 dígitos)
        return substr($telefone, 0, 5) . '-' . substr($telefone, 5);
    } else if ($len == 8) {
        // Telefone fixo sem DDD (8 dígitos)
        return substr($telefone, 0, 4) . '-' . substr($telefone, 4);
    }
    
    return $telefone;
}

/**
 * Formata um CPF para exibição
 * @param string $cpf Número do CPF
 * @return string CPF formatado
 */
function format_cpf($cpf) {
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    
    if (strlen($cpf) != 11) {
        return $cpf;
    }
    
    return substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
}

/**
 * Verifica se um CPF é válido
 * @param string $cpf Número do CPF
 * @return bool Retorna true se o CPF for válido
 */
function validate_cpf($cpf) {
    // Remove caracteres não numéricos
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    
    // Verifica se tem 11 dígitos
    if (strlen($cpf) != 11) {
        return false;
    }
    
    // Verifica se todos os dígitos são iguais
    if (preg_match('/^(\d)\1+$/', $cpf)) {
        return false;
    }
    
    // Calcula o primeiro dígito verificador
    $soma = 0;
    for ($i = 0; $i < 9; $i++) {
        $soma += (int)$cpf[$i] * (10 - $i);
    }
    $resto = $soma % 11;
    $dv1 = ($resto < 2) ? 0 : 11 - $resto;
    
    // Calcula o segundo dígito verificador
    $soma = 0;
    for ($i = 0; $i < 9; $i++) {
        $soma += (int)$cpf[$i] * (11 - $i);
    }
    $soma += $dv1 * 2;
    $resto = $soma % 11;
    $dv2 = ($resto < 2) ? 0 : 11 - $resto;
    
    // Verifica se os dígitos verificadores estão corretos
    return ($cpf[9] == $dv1 && $cpf[10] == $dv2);
}

/**
 * Função para limpar o cache dos PDFs gerados (uso administrativo)
 * @param int $dias_limite Número de dias para manter os arquivos
 * @return int Número de arquivos removidos
 */
function clear_pdf_cache($dias_limite = 30) {
    $dir = '../uploads/';
    $count = 0;
    
    if (is_dir($dir)) {
        $files = scandir($dir);
        $limite_timestamp = time() - ($dias_limite * 86400);
        
        foreach ($files as $file) {
            if ($file != '.' && $file != '..' && is_file($dir . $file)) {
                $file_timestamp = filemtime($dir . $file);
                
                if ($file_timestamp < $limite_timestamp && (strpos($file, '.pdf') !== false)) {
                    if (unlink($dir . $file)) {
                        $count++;
                    }
                }
            }
        }
    }
    
    return $count;
}
?> 