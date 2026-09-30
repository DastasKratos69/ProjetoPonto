<?php
// editoras.php - Gestão de Editoras

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config/db.php';
require_authentication();

$usuario = get_logged_user();
$pdo = get_db_connection();

$msg = $_GET['msg'] ?? '';
$tipo_msg = $_GET['tipo'] ?? 'success';

// Buscar editoras
$stmt = $pdo->query("SELECT id_editora, nome, cidade, uf FROM editora ORDER BY nome ASC");
$editoras = $stmt->fetchAll();

$estados = [
    'AC' => 'Acre', 'AL' => 'Alagoas', 'AP' => 'Amapá', 'AM' => 'Amazonas', 'BA' => 'Bahia',
    'CE' => 'Ceará', 'DF' => 'Distrito Federal', 'ES' => 'Espírito Santo', 'GO' => 'Goiás',
    'MA' => 'Maranhão', 'MT' => 'Mato Grosso', 'MS' => 'Mato Grosso do Sul', 'MG' => 'Minas Gerais',
    'PA' => 'Pará', 'PB' => 'Paraíba', 'PR' => 'Paraná', 'PE' => 'Pernambuco', 'PI' => 'Piauí',
    'RJ' => 'Rio de Janeiro', 'RN' => 'Rio Grande do Norte', 'RS' => 'Rio Grande do Sul',
    'RO' => 'Rondônia', 'RR' => 'Roraima', 'SC' => 'Santa Catarina', 'SP' => 'São Paulo',
    'SE' => 'Sergipe', 'TO' => 'Tocantins'
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Editoras - Sistema Escolar Digital</title>
    <link rel="stylesheet" href="assets/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>

    <header>
        <div class="header-container">
            <a href="index.php" class="logo-area">
                <i data-lucide="graduation-cap" style="width: 28px; height: 28px;"></i>
                <span>Sistema Escolar Digital</span>
            </a>
            <div class="user-nav">
                <div class="user-info">
                    <i data-lucide="user" style="width: 18px; height: 18px;"></i>
                    <span><?php echo htmlspecialchars($usuario['nome']); ?></span>
                </div>
                <a href="logout.php" class="btn btn-outline" style="color: #FFFFFF; border-color: rgba(255,255,255,0.4);">
                    <i data-lucide="log-out" style="width: 16px; height: 16px;"></i>
                    Sair
                </a>
            </div>
        </div>
    </header>

    <main class="main-wrapper">
        <div class="card">
            <div class="page-header">
                <div class="page-title">
                    <i data-lucide="building" style="width: 28px; height: 28px;"></i>
                    <h1>Gestão de Editoras</h1>
                </div>
                <a href="index.php" class="btn btn-outline">
                    <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i>
                    Voltar ao Início
                </a>
            </div>

            <?php if (!empty($msg)): ?>
                <div class="alert-box <?php echo $tipo_msg === 'success' ? 'alert-success' : 'alert-danger'; ?>">
                    <i data-lucide="<?php echo $tipo_msg === 'success' ? 'check-circle' : 'alert-triangle'; ?>" style="width: 20px; height: 20px;"></i>
                    <span><?php echo htmlspecialchars($msg); ?></span>
                </div>
            <?php endif; ?>

            <!-- Formulário de Cadastro de Editora -->
            <form action="salvar_editora.php" method="POST" style="margin-bottom: 2rem;">
                <h2 style="font-size: 1.1rem; margin-bottom: 1rem; color: var(--color-primary);">Cadastrar Nova Editora</h2>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="nome">Nome da Editora</label>
                        <input type="text" id="nome" name="nome" placeholder="Ex: Companhia das Letras" required>
                    </div>

                    <div class="form-group">
                        <label for="cidade">Cidade</label>
                        <input type="text" id="cidade" name="cidade" placeholder="Ex: São Paulo" required>
                    </div>

                    <div class="form-group">
                        <label for="uf">UF (Estado)</label>
                        <select id="uf" name="uf" required>
                            <option value="">Selecione um estado</option>
                            <?php foreach ($estados as $sigla => $nomeEstado): ?>
                                <option value="<?php echo $sigla; ?>"><?php echo $sigla . ' - ' . $nomeEstado; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn btn-success">
                    <i data-lucide="save" style="width: 18px; height: 18px;"></i>
                    Salvar Editora
                </button>
            </form>

            <!-- Tabela de Editoras Cadastradas -->
            <h2 style="font-size: 1.1rem; margin-bottom: 1rem; color: var(--color-text);">Editoras Cadastradas</h2>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID Editora</th>
                            <th>Nome</th>
                            <th>Cidade</th>
                            <th>UF</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($editoras) > 0): ?>
                            <?php foreach ($editoras as $editora): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($editora['id_editora']); ?></td>
                                    <td><?php echo htmlspecialchars($editora['nome']); ?></td>
                                    <td><?php echo htmlspecialchars($editora['cidade']); ?></td>
                                    <td><?php echo htmlspecialchars($editora['uf']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align: center; color: var(--color-text-muted);">
                                    Nenhuma editora cadastrada até o momento.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </main>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Sistema Escolar Digital — Todos os direitos reservados.</p>
    </footer>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
