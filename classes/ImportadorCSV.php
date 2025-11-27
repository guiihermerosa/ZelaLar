<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';


class ImportadorCSV {
    private $db;
    private $colunas_obrigatorias = ['nome', 'telefone', 'categoria', 'email'];
    
    public function __construct() {
        $this->db = getDatabase();
    }
    
    /**
     * Valida e importa arquivo CSV
     */
    public function importarArquivo($arquivo_path, $senha_padrao = '123456') {
        try {
            if (!file_exists($arquivo_path)) {
                return ['sucesso' => false, 'mensagem' => 'Arquivo não encontrado'];
            }
            
            // Validar extensão
            $extensao = strtolower(pathinfo($_FILES['arquivo']['name'], PATHINFO_EXTENSION));
            if ($extensao !== 'csv') {
                return ['sucesso' => false, 'mensagem' => 'Arquivo deve ser CSV'];
            }
            
            // Abrir arquivo
            $handle = fopen($arquivo_path, 'r');
            if (!$handle) {
                return ['sucesso' => false, 'mensagem' => 'Erro ao abrir arquivo'];
            }
            
            // Ler cabeçalhos
            $headers = fgetcsv($handle, 1000, ';');
            if (!$headers) {
                fclose($handle);
                return ['sucesso' => false, 'mensagem' => 'Arquivo CSV vazio'];
            }
            
            // Normalizar headers
            $headers = array_map('strtolower', $headers);
            $headers = array_map('trim', $headers);
            
            // Validar colunas obrigatórias
            $validacao = $this->validarColunas($headers);
            if (!$validacao['sucesso']) {
                fclose($handle);
                return $validacao;
            }
            
            // Processar linhas
            $importacao_id = $this->criarRegistroImportacao($arquivo_path);
            $total = 0;
            $importados = 0;
            $erros = [];
            
            while (($row = fgetcsv($handle, 1000, ';')) !== FALSE) {
                $total++;
                
                // Mapear dados
                $dados = array_combine($headers, $row);
                
                // Validar linha
                $validacao_linha = $this->validarLinha($dados, $total);
                if (!$validacao_linha['sucesso']) {
                    $erros[] = $validacao_linha['mensagem'];
                    continue;
                }
                
                // Inserir profissional
                $resultado = $this->inserirProfissional($dados, $senha_padrao);
                if ($resultado['sucesso']) {
                    $importados++;
                } else {
                    $erros[] = "Linha $total: " . $resultado['mensagem'];
                }
            }
            
            fclose($handle);
            
            // Atualizar registro de importação
            $this->atualizarRegistroImportacao($importacao_id, $importados, $total - $importados, $erros);
            
            return [
                'sucesso' => true,
                'mensagem' => "Importação concluída: $importados de $total registros importados",
                'importacao_id' => $importacao_id,
                'total' => $total,
                'importados' => $importados,
                'erros' => $erros
            ];
            
        } catch (Exception $e) {
            error_log('Erro ao importar CSV: ' . $e->getMessage());
            return ['sucesso' => false, 'mensagem' => 'Erro ao processar importação'];
        }
    }
    
    /**
     * Valida se as colunas obrigatórias existem
     */
    private function validarColunas($headers) {
        $ausentes = [];
        
        foreach ($this->colunas_obrigatorias as $coluna) {
            if (!in_array($coluna, $headers)) {
                $ausentes[] = $coluna;
            }
        }
        
        if (!empty($ausentes)) {
            return [
                'sucesso' => false,
                'mensagem' => 'Colunas obrigatórias ausentes: ' . implode(', ', $ausentes)
            ];
        }
        
        return ['sucesso' => true];
    }
    
    /**
     * Valida dados de uma linha
     */
    private function validarLinha($dados, $numero_linha) {
        // Validar nome
        if (empty(trim($dados['nome']))) {
            return ['sucesso' => false, 'mensagem' => "Linha $numero_linha: Nome vazio"];
        }
        
        // Validar telefone
        $telefone = preg_replace('/\D/', '', $dados['telefone']);
        if (strlen($telefone) < 10) {
            return ['sucesso' => false, 'mensagem' => "Linha $numero_linha: Telefone inválido"];
        }
        
        // Validar categoria
        $categoria_existe = dbFetchValue(
            "SELECT COUNT(*) FROM categorias WHERE nome = ?",
            [$dados['categoria']]
        );
        if (!$categoria_existe) {
            return ['sucesso' => false, 'mensagem' => "Linha $numero_linha: Categoria '{$dados['categoria']}' não existe"];
        }
        
        // Validar email
        if (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
            return ['sucesso' => false, 'mensagem' => "Linha $numero_linha: Email inválido"];
        }
        
        // Verificar se telefone já existe
        $existe = dbFetchValue(
            "SELECT COUNT(*) FROM profissionais WHERE telefone = ?",
            [$telefone]
        );
        if ($existe) {
            return ['sucesso' => false, 'mensagem' => "Linha $numero_linha: Profissional com este telefone já existe"];
        }
        
        return ['sucesso' => true];
    }
    
 
    private function inserirProfissional($dados, $senha_padrao) {
    try {
        $telefone = preg_replace('/\D/', '', $dados['telefone']);
        $telefone_formatado = preg_replace('/(\d{2})(\d{5})(\d{4})/', '+55$1$2$3', $telefone);

        $senha_hash = password_hash($senha_padrao, PASSWORD_BCRYPT);

        $resultado = dbExecute(
            "INSERT INTO profissionais 
            (nome, telefone, categoria, email, endereco, cidade, estado, cep, descricao, senha, disponivel)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, TRUE)",
            [
                trim($dados['nome']),
                $telefone_formatado,
                trim($dados['categoria']),
                strtolower(trim($dados['email'])),
                $dados['endereco'] ?? '',
                $dados['cidade'] ?? '',
                strtoupper($dados['estado'] ?? ''),
                $dados['cep'] ?? '',
                $dados['descricao'] ?? '',
                $senha_hash
            ]
        );

        return [
            'sucesso' => $resultado > 0,
            'mensagem' => 'Profissional inserido com sucesso'
        ];
    } catch (Exception $e) {
        error_log('Erro ao inserir profissional: ' . $e->getMessage());
        return ['sucesso' => false, 'mensagem' => $e->getMessage()];
    }
}
    
    
   private function criarRegistroImportacao($nome_arquivo) {
    $pdo = getDatabase();
    $stmt = $pdo->prepare(
        "INSERT INTO importacoes (nome_arquivo, status) VALUES (?, 'processando')"
    );
    $stmt->execute([basename($nome_arquivo)]);

    return $pdo->lastInsertId();
}
    
  
    private function atualizarRegistroImportacao($importacao_id, $importados, $erros_count, $erros_detalhes) {
        $status = $erros_count === 0 ? 'concluída' : 'concluída';
        $detalhes = implode('; ', array_slice($erros_detalhes, 0, 10));
        
        dbExecute(
            "UPDATE importacoes SET registros_importados = ?, registros_erro = ?, status = ?, detalhes_erro = ? WHERE id = ?",
            [$importados, $erros_count, $status, $detalhes, $importacao_id]
        );
    }
    
   
    public function obterHistorico($limite = 10) {
        try {
            return dbQuery(
                "SELECT * FROM importacoes ORDER BY data_importacao DESC LIMIT ?",
                [$limite]
            );
        } catch (Exception $e) {
            error_log('Erro ao obter histórico: ' . $e->getMessage());
            return [];
        }
    }
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
            <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['arquivo'])) {
                $importador = new ImportadorCSV();
                $resultado = $importador->importarArquivo($_FILES['arquivo']['tmp_name']);
                
                if ($resultado['sucesso']) {
                    echo "<p style='color: green;'>{$resultado['mensagem']}</p>";
                } else {
                    echo "<p style='color: red;'>{$resultado['mensagem']}</p>";
                }
            }
            ?>

</body>
</html>