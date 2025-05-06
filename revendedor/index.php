<?php
// Incluir arquivos necessários
include_once('../includes/db.php');
include_once('../includes/auth.php');
include_once('../includes/functions.php');

// Verificar autenticação como revendedor
verify_user_auth('revendedor');

// Obter estatísticas para o dashboard do revendedor
$revendedor_id = $_SESSION['user_id'];
$total_vendas = get_total_sales_by_reseller($revendedor_id);
$total_apostas = get_total_bets_by_reseller($revendedor_id);
$comissao_total = get_total_commission($revendedor_id);
$apostas_recentes = get_recent_bets_by_reseller($revendedor_id, 5);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Revendedor - Loteria</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="revendedor-container">
        <header>
            <h1>Painel do Revendedor</h1>
            <div class="user-info">
                Bem-vindo, <?php echo $_SESSION['username']; ?> (Revendedor)
                <a href="../includes/auth.php?logout=1" class="logout-btn">Sair</a>
            </div>
        </header>
        
        <nav class="revendedor-menu">
            <ul>
                <li><a href="#" class="active">Dashboard</a></li>
                <li><a href="vender.php">Registrar Aposta</a></li>
                <li><a href="apostas.php">Apostas Vendidas</a></li>
                <li><a href="comissoes.php">Minhas Comissões</a></li>
                <li><a href="clientes.php">Meus Clientes</a></li>
                <li><a href="perfil.php">Meu Perfil</a></li>
            </ul>
        </nav>
        
        <main class="revendedor-content">
            <section class="dashboard-stats">
                <div class="stat-card">
                    <h3>Total de Vendas</h3>
                    <p class="stat-number">R$ <?php echo number_format($total_vendas, 2, ',', '.'); ?></p>
                </div>
                <div class="stat-card">
                    <h3>Apostas Registradas</h3>
                    <p class="stat-number"><?php echo $total_apostas; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Comissão Total</h3>
                    <p class="stat-number">R$ <?php echo number_format($comissao_total, 2, ',', '.'); ?></p>
                </div>
            </section>
            
            <section class="quick-actions">
                <h3>Ações Rápidas</h3>
                <div class="action-buttons">
                    <a href="vender.php" class="action-btn">Nova Aposta</a>
                    <a href="resultados.php" class="action-btn">Verificar Resultados</a>
                    <a href="relatorio_diario.php" class="action-btn">Relatório do Dia</a>
                    <a href="gerar_pdf.php" class="action-btn">Gerar PDF</a>
                </div>
            </section>
            
            <section class="recent-bets">
                <h3>Apostas Recentes</h3>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Cliente</th>
                            <th>Jogo</th>
                            <th>Valor</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($apostas_recentes)): ?>
                            <?php foreach ($apostas_recentes as $aposta): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y H:i', strtotime($aposta['data_aposta'])); ?></td>
                                    <td><?php echo $aposta['nome_cliente']; ?></td>
                                    <td><?php echo $aposta['nome_jogo']; ?></td>
                                    <td>R$ <?php echo number_format($aposta['valor'], 2, ',', '.'); ?></td>
                                    <td><span class="status-<?php echo $aposta['status']; ?>"><?php echo get_status_text($aposta['status']); ?></span></td>
                                    <td>
                                        <a href="visualizar_aposta.php?id=<?php echo $aposta['id']; ?>" class="action-link">Ver</a>
                                        <a href="imprimir_aposta.php?id=<?php echo $aposta['id']; ?>" class="action-link">Imprimir</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6">Nenhuma aposta registrada recentemente.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <a href="apostas.php" class="view-all">Ver Todas</a>
            </section>
        </main>
    </div>
</body>
</html> 