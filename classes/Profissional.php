<?php


class Profissional {
    private $db;
    
    public function __construct() {
        $this->db = getDatabase();
    }
    
   
    public function autenticar($telefone, $senha) {
        try {
            $profissional = dbFetchOne(
                "SELECT * FROM profissionais WHERE telefone = ? AND ativo = TRUE",
                [$telefone]
            );
            
            if (!$profissional) {
                return ['sucesso' => false, 'mensagem' => 'Profissional não encontrado'];
            }
            
            if (!password_verify($senha, $profissional['senha'])) {
                return ['sucesso' => false, 'mensagem' => 'Senha incorreta'];
            }
            
            // Atualizar último login
            dbExecute(
                "UPDATE profissionais SET ultimo_login = NOW() WHERE id = ?",
                [$profissional['id']]
            );
            
            return [
                'sucesso' => true,
                'profissional' => $profissional,
                'mensagem' => 'Login realizado com sucesso'
            ];
        } catch (Exception $e) {
            error_log('Erro ao autenticar: ' . $e->getMessage());
            return ['sucesso' => false, 'mensagem' => 'Erro ao autenticar'];
        }
    }
    
   
    public function obterAvaliacoes($profissional_id, $pagina = 1, $por_pagina = 10) {
        try {
            $offset = ($pagina - 1) * $por_pagina;
            
            // Total de avaliações
            $total = dbFetchValue(
                "SELECT COUNT(*) FROM avaliacoes WHERE profissional_id = ? AND status = 'aprovada'",
                [$profissional_id]
            );
            
            // Avaliações
            $avaliacoes = dbQuery(
                "SELECT * FROM avaliacoes 
                WHERE profissional_id = ? AND status = 'aprovada'
                ORDER BY data_avaliacao DESC
                LIMIT ? OFFSET ?",
                [$profissional_id, $por_pagina, $offset]
            );
            
            // Estatísticas
            $stats = dbFetchOne(
                "SELECT 
                    COUNT(*) as total,
                    AVG(nota) as media,
                    MAX(nota) as maxima,
                    MIN(nota) as minima
                FROM avaliacoes 
                WHERE profissional_id = ? AND status = 'aprovada'",
                [$profissional_id]
            );
            
            return [
                'avaliacoes' => $avaliacoes,
                'total' => $total,
                'paginas' => ceil($total / $por_pagina),
                'pagina_atual' => $pagina,
                'stats' => $stats
            ];
        } catch (Exception $e) {
            error_log('Erro ao obter avaliações: ' . $e->getMessage());
            return ['avaliacoes' => [], 'total' => 0, 'stats' => null];
        }
    }
    
   
    public function obterContatos($profissional_id, $pagina = 1, $por_pagina = 15) {
        try {
            $offset = ($pagina - 1) * $por_pagina;
            
         
            $total = dbFetchValue(
                "SELECT COUNT(*) FROM contatos_recebidos WHERE profissional_id = ?",
                [$profissional_id]
            );
            
            
            $contatos = dbQuery(
                "SELECT * FROM contatos_recebidos 
                WHERE profissional_id = ?
                ORDER BY data_contato DESC
                LIMIT ? OFFSET ?",
                [$profissional_id, $por_pagina, $offset]
            );
            
            return [
                'contatos' => $contatos,
                'total' => $total,
                'paginas' => ceil($total / $por_pagina),
                'pagina_atual' => $pagina
            ];
        } catch (Exception $e) {
            error_log('Erro ao obter contatos: ' . $e->getMessage());
            return ['contatos' => [], 'total' => 0];
        }
    }
    
    
    public function registrarContato($profissional_id, $cliente_nome, $cliente_telefone, $cliente_email, $mensagem = '') {
        try {
            $ip_cliente = $_SERVER['REMOTE_ADDR'] ?? 'desconhecido';
            
            $resultado = dbExecute(
                "INSERT INTO contatos_recebidos 
                (profissional_id, cliente_nome, cliente_telefone, cliente_email, mensagem, ip_cliente)
                VALUES (?, ?, ?, ?, ?, ?)",
                [$profissional_id, $cliente_nome, $cliente_telefone, $cliente_email, $mensagem, $ip_cliente]
            );
            
            return [
                'sucesso' => $resultado['success'],
                'mensagem' => 'Contato registrado com sucesso'
            ];
        } catch (Exception $e) {
            error_log('Erro ao registrar contato: ' . $e->getMessage());
            return ['sucesso' => false, 'mensagem' => 'Erro ao registrar contato'];
        }
    }
    
    
    public function obterEstatisticas($profissional_id) {
        try {
            $stats = dbFetchOne(
                "SELECT 
                    (SELECT COUNT(*) FROM avaliacoes WHERE profissional_id = ? AND status = 'aprovada') as total_avaliacoes,
                    (SELECT AVG(nota) FROM avaliacoes WHERE profissional_id = ? AND status = 'aprovada') as media_avaliacoes,
                    (SELECT COUNT(*) FROM contatos_recebidos WHERE profissional_id = ?) as total_contatos,
                    (SELECT COUNT(*) FROM contatos_recebidos WHERE profissional_id = ? AND DATE(data_contato) = CURDATE()) as contatos_hoje,
                    (SELECT COUNT(*) FROM contatos_recebidos WHERE profissional_id = ? AND WEEK(data_contato) = WEEK(NOW())) as contatos_semana,
                    (SELECT COUNT(*) FROM avaliacoes WHERE profissional_id = ? AND status = 'pendente') as avaliacoes_pendentes
                ",
                [$profissional_id, $profissional_id, $profissional_id, $profissional_id, $profissional_id, $profissional_id]
            );
            
            return $stats ?: [];
        } catch (Exception $e) {
            error_log('Erro ao obter estatísticas: ' . $e->getMessage());
            return [];
        }
    }
    
    
    public function atualizar($profissional_id, $dados) {
        try {
            $campos_permitidos = ['nome', 'descricao', 'telefone', 'email', 'endereco', 'cidade', 'estado', 'cep'];
            $updates = [];
            $valores = [];
            
            foreach ($dados as $campo => $valor) {
                if (in_array($campo, $campos_permitidos)) {
                    $updates[] = "$campo = ?";
                    $valores[] = $valor;
                }
            }
            
            if (empty($updates)) {
                return ['sucesso' => false, 'mensagem' => 'Nenhum campo para atualizar'];
            }
            
            $valores[] = $profissional_id;
            
            $sql = "UPDATE profissionais SET " . implode(", ", $updates) . ", ultima_atualizacao = NOW() WHERE id = ?";
            
            $resultado = dbExecute($sql, $valores);
            
            return [
                'sucesso' => $resultado['success'],
                'mensagem' => 'Dados atualizados com sucesso'
            ];
        } catch (Exception $e) {
            error_log('Erro ao atualizar: ' . $e->getMessage());
            return ['sucesso' => false, 'mensagem' => 'Erro ao atualizar'];
        }
    }
}

?>