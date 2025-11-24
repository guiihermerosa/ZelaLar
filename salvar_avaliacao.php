<?php
require_once 'config/config.php';
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: listagem.php');
  exit;
}

$profissional_id = (int)($_POST['profissional_id'] ?? 0);
$cliente_nome = trim($_POST['cliente_nome'] ?? '');
$nota = (int)($_POST['nota'] ?? 0);
$comentario = trim($_POST['comentario'] ?? '');

if ($profissional_id <= 0 || $cliente_nome === '' || $nota < 1 || $nota > 5) {
  header('Location: avaliacao.php?profissional_id=' . urlencode($profissional_id) . '&m=' . urlencode('Dados inválidos'));
  exit;
}

try {
  $pdo = getDatabase();
  $pdo->beginTransaction();

  // Inserir avaliação
  $stmt = $pdo->prepare(
    "INSERT INTO avaliacoes (profissional_id, cliente_nome, nota, comentario, status, data_criacao)
         VALUES (?, ?, ?, ?, 'aprovada', NOW())"
  );
  $stmt->execute([$profissional_id, $cliente_nome, $nota, $comentario]);

  // Calcular média (só avaliações aprovadas)
  $avg = $pdo->prepare("SELECT AVG(nota) FROM avaliacoes WHERE profissional_id = ? AND status = 'aprovada'");
  $avg->execute([$profissional_id]);
  $media = $avg->fetchColumn();
  $media = $media === null ? 0 : (float)$media;

  // Atualizar média na tabela profissionais
  $update = $pdo->prepare("UPDATE profissionais SET media_avaliacao = ? WHERE id = ?");
  $update->execute([number_format($media, 2, '.', ''), $profissional_id]);

  $pdo->commit();

  header('Location: avaliacao.php?profissional_id=' . urlencode($profissional_id) . '&m=' . urlencode('Avaliação enviada com sucesso.'));
  exit;
} catch (PDOException $e) {
  if (isset($pdo) && $pdo->inTransaction()) {
    $pdo->rollBack();
  }
  error_log('Erro ao salvar avaliação (PDO): ' . $e->getMessage());
  // Mensagem amigável; detalhe técnico no log
  header('Location: avaliacao.php?profissional_id=' . urlencode($profissional_id) . '&m=' . urlencode('Erro ao salvar avaliação. Verifique o log.'));
  exit;
} catch (Exception $e) {
  if (isset($pdo) && $pdo->inTransaction()) {
    $pdo->rollBack();
  }
  error_log('Erro ao salvar avaliação: ' . $e->getMessage());
  header('Location: avaliacao.php?profissional_id=' . urlencode($profissional_id) . '&m=' . urlencode('Erro ao salvar avaliação.'));
  exit;
}
