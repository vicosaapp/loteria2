<?php
// Incluir arquivos necessários
include_once('../includes/db.php');
include_once('../includes/auth.php');
include_once('../includes/functions.php');

// Verificar autenticação como apostador
verify_user_auth('usuario');

// Obter dados do usuário
$user_id = $_SESSION['user_id'];
$apostas_usuario = get_user_bets($user_id);
$apostas_vencedoras = get_winning_bets($user_id);
$total_apostado = get_total_bet_amount($user_id);
$total_ganho = get_total_winnings($user_id);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Área do Apostador - Loteria</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="usuario-container">
        <header>
            <h1>Área do Apostador</h1>
            <div class="user-info">
                Bem-vindo, <?php echo $_SESSION['username']; ?>
                <a href="../includes/auth.php?logout=1" class="logout-btn">Sair</a>
            </div>
        </header>
        
        <nav class="usuario-menu">
            <ul>
                <li><a href="#" class="active">Meu Painel</a></li>
                <li><a href="minhas_apostas.php">Minhas Apostas</a></li>
                <li><a href="resultados.php">Resultados</a></li>
                <li><a href="jogos_disponiveis.php">Jogos Disponíveis</a></li>
                <li><a href="perfil.php">Meu Perfil</a></li>
            </ul>
        </nav>
        
        <main class="usuario-content">
            <section class="user-summary">
                <div class="summary-card">
                    <h3>Total Apostado</h3>
                    <p class="stat-number">R$ <?php echo number_format($total_apostado, 2, ',', '.'); ?></p>
                </div>
                <div class="summary-card">
                    <h3>Total de Prêmios</h3>
                    <p class="stat-number">R$ <?php echo number_format($total_ganho, 2, ',', '.'); ?></p>
                </div>
                <div class="summary-card">
                    <h3>Apostas Realizadas</h3>
                    <p class="stat-number"><?php echo count($apostas_usuario); ?></p>
                </div>
                <div class="summary-card">
                    <h3>Apostas Premiadas</h3>
                    <p class="stat-number"><?php echo count($apostas_vencedoras); ?></p>
                </div>
            </section>
            
            <section class="recent-bets">
                <h3>Minhas Apostas Recentes</h3>
                <?php if (!empty($apostas_usuario)): ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Jogo</th>
                                <th>Números</th>
                                <th>Valor</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $apostas_recentes = array_slice($apostas_usuario, 0, 5);
                            foreach ($apostas_recentes as $aposta): 
                            ?>
                                <tr>
                                    <td><?php echo date('d/m/Y', strtotime($aposta['data_aposta'])); ?></td>
                                    <td><?php echo $aposta['nome_jogo']; ?></td>
                                    <td><?php echo $aposta['numeros_escolhidos']; ?></td>
                                    <td>R$ <?php echo number_format($aposta['valor'], 2, ',', '.'); ?></td>
                                    <td>
                                        <span class="status-<?php echo $aposta['status']; ?>">
                                            <?php echo get_status_text($aposta['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="detalhe_aposta.php?id=<?php echo $aposta['id']; ?>" class="action-link">Ver</a>
                                        <a href="imprimir_comprovante.php?id=<?php echo $aposta['id']; ?>" class="action-link">Imprimir</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <a href="minhas_apostas.php" class="view-all">Ver Todas</a>
                <?php else: ?>
                    <p class="empty-message">Você ainda não realizou nenhuma aposta.</p>
                    <a href="jogos_disponiveis.php" class="action-btn">Apostar Agora</a>
                <?php endif; ?>
            </section>
            
            <section class="next-draws">
                <h3>Próximos Sorteios</h3>
                <?php
                $proximos_sorteios = get_upcoming_draws();
                if (!empty($proximos_sorteios)): 
                ?>
                    <div class="draws-list">
                        <?php foreach ($proximos_sorteios as $sorteio): ?>
                            <div class="draw-card">
                                <h4><?php echo $sorteio['nome_jogo']; ?></h4>
                                <p class="draw-date">Sorteio: <?php echo date('d/m/Y H:i', strtotime($sorteio['data_sorteio'])); ?></p>
                                <p class="draw-prize">Prêmio Estimado: R$ <?php echo number_format($sorteio['premio_estimado'], 2, ',', '.'); ?></p>
                                <a href="apostar.php?jogo=<?php echo $sorteio['jogo_id']; ?>" class="bet-now-btn">Apostar</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="empty-message">Não há sorteios programados no momento.</p>
                <?php endif; ?>
            </section>
        </main>
    </div>
</body>
</html> 