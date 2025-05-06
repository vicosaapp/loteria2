<?php
// Incluir arquivos necessários
include_once('../includes/db.php');
include_once('../includes/auth.php');
include_once('../includes/functions.php');

// Verificar autenticação como administrador
verify_user_auth('admin');

// Obter estatísticas para o dashboard
$total_usuarios = count_users_by_type('usuario');
$total_revendedores = count_users_by_type('revendedor');
$total_apostas = count_total_bets();
$arrecadacao_total = get_total_revenue();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo - Loteria</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="admin-container">
        <header>
            <h1>Painel Administrativo</h1>
            <div class="user-info">
                Bem-vindo, <?php echo $_SESSION['username']; ?> (Admin)
                <a href="../includes/auth.php?logout=1" class="logout-btn">Sair</a>
            </div>
        </header>
        
        <nav class="admin-menu">
            <ul>
                <li><a href="#" class="active">Dashboard</a></li>
                <li><a href="usuarios.php">Gerenciar Usuários</a></li>
                <li><a href="revendedores.php">Gerenciar Revendedores</a></li>
                <li><a href="jogos.php">Configurar Jogos</a></li>
                <li><a href="resultados.php">Lançar Resultados</a></li>
                <li><a href="relatorios.php">Relatórios</a></li>
            </ul>
        </nav>
        
        <main class="admin-content">
            <section class="dashboard-stats">
                <div class="stat-card">
                    <h3>Apostadores</h3>
                    <p class="stat-number"><?php echo $total_usuarios; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Revendedores</h3>
                    <p class="stat-number"><?php echo $total_revendedores; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Total de Apostas</h3>
                    <p class="stat-number"><?php echo $total_apostas; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Arrecadação Total</h3>
                    <p class="stat-number">R$ <?php echo number_format($arrecadacao_total, 2, ',', '.'); ?></p>
                </div>
            </section>
            
            <section class="recent-activity">
                <h3>Atividades Recentes</h3>
                <div class="activity-list">
                    <?php
                    $recent_activities = get_recent_activities(10);
                    if (!empty($recent_activities)) {
                        foreach ($recent_activities as $activity) {
                            echo '<div class="activity-item">';
                            echo '<span class="activity-time">' . $activity['timestamp'] . '</span>';
                            echo '<span class="activity-desc">' . $activity['description'] . '</span>';
                            echo '</div>';
                        }
                    } else {
                        echo '<p>Nenhuma atividade recente.</p>';
                    }
                    ?>
                </div>
            </section>
        </main>
    </div>
</body>
</html> 