<?php
/**
 * ZelaLar - Configuração Avançada do Banco de Dados
 * Sistema robusto de conexão e gerenciamento de banco de dados
 */

// Configurações do banco de dados
define('DB_HOST', 'localhost');
define('DB_NAME', 'zelalar_db');
define('DB_USER', 'root');
define('DB_PASS', '2341');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', 'utf8mb4_unicode_ci');

// Configurações de conexão
define('DB_OPTIONS', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
    PDO::ATTR_PERSISTENT => false,
    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
]);

// Configurações de pool de conexões
define('DB_MAX_CONNECTIONS', 10);
define('DB_MIN_CONNECTIONS', 2);
define('DB_CONNECTION_TIMEOUT', 30);
define('DB_QUERY_TIMEOUT', 60);

// Configurações de cache
define('DB_CACHE_ENABLED', true);
define('DB_CACHE_TTL', 3600); // 1 hora
define('DB_CACHE_PREFIX', 'zelalar_db_');

/**
 * Classe Database - Gerenciamento avançado de banco de dados
 */
class Database {
    private static $instance = null;
    private $connections = [];
    private $currentConnection = null;
    private $cache = [];
    private $queryLog = [];
    private $transactionLevel = 0;
    
    private function __construct() {
        $this->initializeConnections();
    }
    
    /**
     * Singleton pattern
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Inicializa pool de conexões
     */
    private function initializeConnections() {
        try {
            for ($i = 0; $i < DB_MIN_CONNECTIONS; $i++) {
                $this->connections[] = $this->createConnection();
            }
            $this->currentConnection = $this->connections[0];
        } catch (Exception $e) {
            $this->logError('Erro ao inicializar conexões: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Cria nova conexão PDO
     */
    private function createConnection() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $pdo = new PDO($dsn, DB_USER, DB_PASS, DB_OPTIONS);
            
            // Configurações específicas do MySQL
            $pdo->exec("SET SESSION sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO'");
            $pdo->exec("SET SESSION time_zone = '-03:00'");
            
            return $pdo;
        } catch (PDOException $e) {
            $this->logError('Erro na conexão PDO: ' . $e->getMessage());
            throw new Exception('Falha na conexão com o banco de dados');
        }
    }
    
    /**
     * Obtém conexão disponível
     */
    public function getConnection() {
        if ($this->currentConnection === null) {
            $this->currentConnection = $this->createConnection();
        }
        
        // Verificar se a conexão ainda está ativa
        try {
            $this->currentConnection->query('SELECT 1');
        } catch (Exception $e) {
            $this->currentConnection = $this->createConnection();
        }
        
        return $this->currentConnection;
    }
    
    /**
     * Executa query com cache
     */
    public function query($sql, $params = [], $useCache = true) {
        $cacheKey = $this->generateCacheKey($sql, $params);
        
        // Verificar cache
        if ($useCache && DB_CACHE_ENABLED && isset($this->cache[$cacheKey])) {
            $cached = $this->cache[$cacheKey];
            if (time() - $cached['timestamp'] < DB_CACHE_TTL) {
                $this->logQuery($sql, $params, 'CACHE_HIT');
                return $cached['data'];
            }
        }
        
        // Executar query
        $startTime = microtime(true);
        try {
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetchAll();
            
            $executionTime = microtime(true) - $startTime;
            $this->logQuery($sql, $params, 'SUCCESS', $executionTime);
            
            // Armazenar no cache
            if ($useCache && DB_CACHE_ENABLED) {
                $this->cache[$cacheKey] = [
                    'data' => $result,
                    'timestamp' => time()
                ];
            }
            
            return $result;
        } catch (PDOException $e) {
            $executionTime = microtime(true) - $startTime;
            $this->logQuery($sql, $params, 'ERROR', $executionTime, $e->getMessage());
            throw new Exception('Erro na execução da query: ' . $e->getMessage());
        }
    }
    
    /**
     * Executa query de inserção/atualização
     */
    public function execute($sql, $params = []) {
        $startTime = microtime(true);
        try {
            $stmt = $this->getConnection()->prepare($sql);
            $result = $stmt->execute($params);
            
            $executionTime = microtime(true) - $startTime;
            $this->logQuery($sql, $params, 'EXECUTE', $executionTime);
            
            return [
                'success' => $result,
                'lastInsertId' => $this->getConnection()->lastInsertId(),
                'rowCount' => $stmt->rowCount()
            ];
        } catch (PDOException $e) {
            $executionTime = microtime(true) - $startTime;
            $this->logQuery($sql, $params, 'ERROR', $executionTime, $e->getMessage());
            throw new Exception('Erro na execução: ' . $e->getMessage());
        }
    }
    
    /**
     * Busca um único registro
     */
    public function fetchOne($sql, $params = []) {
        $result = $this->query($sql, $params);
        return $result ? $result[0] : null;
    }
    
    /**
     * Busca um valor específico
     */
    public function fetchValue($sql, $params = []) {
        $result = $this->fetchOne($sql, $params);
        return $result ? array_values($result)[0] : null;
    }
    
    /**
     * Inicia transação
     */
    public function beginTransaction() {
        if ($this->transactionLevel === 0) {
            $this->getConnection()->beginTransaction();
        }
        $this->transactionLevel++;
    }
    
    /**
     * Confirma transação
     */
    public function commit() {
        if ($this->transactionLevel === 1) {
            $this->getConnection()->commit();
        }
        $this->transactionLevel = max(0, $this->transactionLevel - 1);
    }
    
    /**
     * Reverte transação
     */
    public function rollback() {
        if ($this->transactionLevel === 1) {
            $this->getConnection()->rollback();
        }
        $this->transactionLevel = max(0, $this->transactionLevel - 1);
    }
    
    /**
     * Verifica se está em transação
     */
    public function inTransaction() {
        return $this->transactionLevel > 0;
    }
    
    /**
     * Gera chave de cache
     */
    private function generateCacheKey($sql, $params) {
        return DB_CACHE_PREFIX . md5($sql . serialize($params));
    }
    
    /**
     * Log de queries
     */
    private function logQuery($sql, $params, $status, $executionTime = 0, $error = null) {
        $log = [
            'sql' => $sql,
            'params' => $params,
            'status' => $status,
            'execution_time' => $executionTime,
            'timestamp' => date('Y-m-d H:i:s'),
            'error' => $error
        ];
        
        $this->queryLog[] = $log;
        
        // Manter apenas as últimas 1000 queries
        if (count($this->queryLog) > 1000) {
            array_shift($this->queryLog);
        }
        
        // Log de queries lentas
        if ($executionTime > 1.0) {
            error_log("Query lenta detectada: {$executionTime}s - {$sql}");
        }
    }
    
    /**
     * Obtém log de queries
     */
    public function getQueryLog() {
        return $this->queryLog;
    }
    
    /**
     * Limpa cache
     */
    public function clearCache() {
        $this->cache = [];
    }
    
    /**
     * Obtém estatísticas do banco
     */
    public function getStats() {
        return [
            'connections' => count($this->connections),
            'cache_entries' => count($this->cache),
            'queries_executed' => count($this->queryLog),
            'cache_hits' => count(array_filter($this->queryLog, fn($q) => $q['status'] === 'CACHE_HIT')),
            'avg_execution_time' => $this->calculateAverageExecutionTime()
        ];
    }
    
    /**
     * Calcula tempo médio de execução
     */
    private function calculateAverageExecutionTime() {
        $successfulQueries = array_filter($this->queryLog, fn($q) => $q['status'] === 'SUCCESS');
        if (empty($successfulQueries)) return 0;
        
        $totalTime = array_sum(array_column($successfulQueries, 'execution_time'));
        return $totalTime / count($successfulQueries);
    }
    
    /**
     * Log de erros
     */
    private function logError($message) {
        error_log("[ZelaLar Database] " . $message);
    }
    
    /**
     * Fecha todas as conexões
     */
    public function closeConnections() {
        foreach ($this->connections as $connection) {
            $connection = null;
        }
        $this->connections = [];
        $this->currentConnection = null;
    }
    
    /**
     * Destrutor
     */
    public function __destruct() {
        $this->closeConnections();
    }
}

/**
 * Função de conveniência para obter instância do banco
 */
function getDatabase() {
    return Database::getInstance();
}

/**
 * Função de conveniência para executar query
 */
function dbQuery($sql, $params = [], $useCache = true) {
    return getDatabase()->query($sql, $params, $useCache);
}

/**
 * Função de conveniência para executar comando
 */
function dbExecute($sql, $params = []) {
    return getDatabase()->execute($sql, $params);
}

/**
 * Função de conveniência para buscar um registro
 */
function dbFetchOne($sql, $params = []) {
    return getDatabase()->fetchOne($sql, $params);
}

/**
 * Função de conveniência para buscar valor
 */
function dbFetchValue($sql, $params = []) {
    return getDatabase()->fetchValue($sql, $params);
}

/**
 * Função de conveniência para transações
 */
function dbTransaction($callback) {
    $db = getDatabase();
    try {
        $db->beginTransaction();
        $result = $callback($db);
        $db->commit();
        return $result;
    } catch (Exception $e) {
        $db->rollback();
        throw $e;
    }
}

/**
 * Verifica se o banco está acessível
 */
function isDatabaseAccessible() {
    try {
        $db = getDatabase();
        $db->query('SELECT 1');
        return true;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Obtém informações do banco
 */
function getDatabaseInfo() {
    try {
        $db = getDatabase();
        $version = $db->fetchValue('SELECT VERSION()');
        $database = $db->fetchValue('SELECT DATABASE()');
        $charset = $db->fetchValue('SELECT @@character_set_database');
        
        return [
            'version' => $version,
            'database' => $database,
            'charset' => $charset,
            'host' => DB_HOST,
            'user' => DB_USER
        ];
    } catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

// ===== INICIALIZAÇÃO AUTOMÁTICA =====
if (!function_exists('zelalar_db_init')) {
    function zelalar_db_init() {
        // Verificar se o banco está acessível
        if (!isDatabaseAccessible()) {
            error_log('ZelaLar: Banco de dados não está acessível');
            return false;
        }
        
        // Verificar se as tabelas existem
        try {
            $db = getDatabase();
            $tables = $db->query("SHOW TABLES");
            
            if (empty($tables)) {
                // Criar tabelas se não existirem
                createDefaultTables();
            }
            
            return true;
        } catch (Exception $e) {
            error_log('ZelaLar: Erro ao verificar tabelas: ' . $e->getMessage());
            return false;
        }
    }
    
    // Registrar shutdown function para fechar conexões
    register_shutdown_function(function() {
        if (Database::getInstance()) {
            Database::getInstance()->closeConnections();
        }
    });
}

/**
 * Cria tabelas padrão se não existirem
 */
function createDefaultTables() {
    try {
        $db = getDatabase();
        
        // Tabela de profissionais
        $sql = "CREATE TABLE IF NOT EXISTS profissionais (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(255) NOT NULL,
            telefone VARCHAR(20) NOT NULL,
            categoria VARCHAR(100) NOT NULL,
            descricao TEXT,
            foto VARCHAR(500),
            endereco TEXT,
            cidade VARCHAR(100),
            estado VARCHAR(2),
            cep VARCHAR(10),
            avaliacao DECIMAL(3,2) DEFAULT 0.00,
            total_avaliacoes INT DEFAULT 0,
            disponivel BOOLEAN DEFAULT TRUE,
            data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            ultima_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_categoria (categoria),
            INDEX idx_cidade (cidade),
            INDEX idx_disponivel (disponivel),
            INDEX idx_avaliacao (avaliacao)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $db->execute($sql);
        
        // Tabela de categorias
        $sql = "CREATE TABLE IF NOT EXISTS categorias (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(100) NOT NULL UNIQUE,
            descricao TEXT,
            icone VARCHAR(100),
            ativa BOOLEAN DEFAULT TRUE,
            ordem INT DEFAULT 0,
            data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $db->execute($sql);
        
        // Inserir categorias padrão
        $categorias = [
            ['CFTV', 'Sistemas de Câmeras e Segurança', 'fas fa-video', 1],
            ['Pedreiro', 'Construção e Reformas', 'fas fa-hammer', 2],
            ['Pintor', 'Pintura e Acabamento', 'fas fa-paint-roller', 3],
            ['Encanador', 'Hidráulica e Esgoto', 'fas fa-wrench', 4],
            ['Eletricista', 'Instalações Elétricas', 'fas fa-bolt', 5],
            ['Jardineiro', 'Jardinagem e Paisagismo', 'fas fa-seedling', 6],
            ['Limpeza', 'Serviços de Limpeza', 'fas fa-broom', 7],
            ['Manutenção', 'Manutenção Geral', 'fas fa-tools', 8]
        ];
        
        foreach ($categorias as $categoria) {
            $db->execute(
                "INSERT IGNORE INTO categorias (nome, descricao, icone, ordem) VALUES (?, ?, ?, ?)",
                $categoria
            );
        }
        
        return true;
    } catch (Exception $e) {
        error_log('ZelaLar: Erro ao criar tabelas: ' . $e->getMessage());
        return false;
    }
}

// Inicializar banco automaticamente
zelalar_db_init();
?>
