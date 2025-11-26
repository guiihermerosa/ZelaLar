<?php

require_once 'config/config.php';
require_once 'config/database.php';
require_once 'classes/ImportadorCSV.php';

// Verificar se é admin (você pode implementar validação real depois)
session_start();

$mensagem = '';
$tipo_mensagem = '';
$historico = [];
$importador = new ImportadorCSV();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['arquivo_csv'])) {
        $arquivo = $_FILES['arquivo_csv'];
        
        if ($arquivo['error'] === UPLOAD_ERR_OK) {
            $extensao = pathinfo($arquivo['name'], PATHINFO_EXTENSION);
            
            if (strtolower($extensao) !== 'csv') {
                $tipo_mensagem = 'erro';
                $mensagem = 'Por favor, envie um arquivo CSV válido.';
            } else if ($arquivo['size'] > 5 * 1024 * 1024) { // 5MB
                $tipo_mensagem = 'erro';
                $mensagem = 'Arquivo muito grande (máximo 5MB).';
            } else {
                $resultado = $importador->importarArquivo($arquivo['tmp_name']);
                
                if ($resultado['sucesso']) {
                    $tipo_mensagem = 'sucesso';
                    $mensagem = $resultado['mensagem'];
                } else {
                    $tipo_mensagem = 'erro';
                    $mensagem = $resultado['mensagem'];
                }
            }
        } else {
            $tipo_mensagem = 'erro';
            $mensagem = 'Erro ao fazer upload do arquivo.';
        }
    }
}

$historico = $importador->obterHistorico(20);

// Exemplo de CSV
$exemplo_csv = "nome;telefone;categoria;email;endereco;cidade;estado;cep;descricao
João Silva;11 99999-9999;Pedreiro;joao@email.com;Rua das Flores 123;São Paulo;SP;01234-567;Pedreiro experiente
Maria Santos;11 98888-8888;Pintor;maria@email.com;Av. Paulista 456;São Paulo;SP;01310-100;Pintora profissional";
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importar Profissionais - ZelaLar Admin</title>
    <link rel="stylesheet" href="Styles/index.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background-color: #f5f7fa;
            font-family: 'Inter', sans-serif;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        header h1 {
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-sucesso {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-erro {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .card h2 {
            color: #333;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .card-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .drop-zone {
            border: 2px dashed #667eea;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .drop-zone:hover {
            background: #f0f4ff;
        }
        
        .drop-zone.dragover {
            background: #e8ecff;
            border-color: #764ba2;
        }
        
        .drop-zone i {
            font-size: 48px;
            color: #667eea;
            margin-bottom: 15px;
        }
        
        .drop-zone p {
            color: #666;
            margin-bottom: 10px;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: transform 0.2s;
            text-decoration: none;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-secondary {
            background: #e0e0e0;
            color: #333;
        }
        
        .btn-download {
            background: #28a745;
            color: white;
            margin-top: 20px;
        }
        
        #arquivo_csv {
            display: none;
        }
        
        .file-info {
            margin-top: 15px;
            padding: 15px;
            background: #f9fafc;
            border-radius: 5px;
            display: none;
        }
        
        .file-info.show {
            display: block;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .table th {
            background: #f5f7fa;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #666;
            border-bottom: 1px solid #ddd;
        }
        
        .table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }
        
        .table tr:hover {
            background: #f9fafc;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-processando {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-concluida {
            background: #d4edda;
            color: #155724;
        }
        
        .status-erro {
            background: #f8d7da;
            color: #721c24;
        }
        
        .info-box {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .info-box i {
            color: #2196f3;
            margin-right: 10px;
        }
        
        .template-code {
            background: #f5f5f5;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            overflow-x: auto;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.5;
            margin-top: 15px;
        }
        
        @media (max-width: 768px) {
            .cards {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>
                <i class="fas fa-upload"></i>
                Importar Profissionais
            </h1>
        </header>
        
        <?php if ($mensagem): ?>
            <div class="alert alert-<?php echo $tipo_mensagem === 'sucesso' ? 'sucesso' : 'erro'; ?>">
                <i class="fas fa-<?php echo $tipo_mensagem === 'sucesso' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo htmlspecialchars($mensagem); ?>
            </div>
        <?php endif; ?>
        
        <div class="cards">
            <!-- Card de Upload -->
            <div class="card">
                <h2>
                    <div class="card-icon">
                        <i class="fas fa-file-csv"></i>
                    </div>
                    Upload do CSV
                </h2>
                
                <div class="info-box">
                    <i class="fas fa-info-circle"></i>
                    <strong>Formato esperado:</strong> Arquivo CSV com separador de ponto e vírgula (;)
                </div>
                
                <form method="POST" enctype="multipart/form-data" id="form-upload">
                    <div class="drop-zone" id="drop-zone">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <p><strong>Arraste o arquivo aqui</strong></p>
                        <p style="font-size: 12px; color: #999;">ou clique para selecionar</p>
                        <input type="file" id="arquivo_csv" name="arquivo_csv" accept=".csv" required>
                    </div>
                    
                    <div class="file-info" id="file-info">
                        <p><strong>Arquivo selecionado:</strong> <span id="file-name"></span></p>
                        <p><strong>Tamanho:</strong> <span id="file-size"></span></p>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 20px;">
                        <i class="fas fa-upload"></i> Importar Agora
                    </button>
                </form>
                
                <a href="javascript:void(0);" onclick="downloadTemplate()" class="btn btn-download" style="width: 100%;">
                    <i class="fas fa-download"></i> Baixar Template
                </a>
                
                <div style="margin-top: 20px;">
                    <h3 style="margin-bottom: 10px; color: #333;">Estrutura do CSV:</h3>
                    <div class="template-code"><?php echo htmlspecialchars($exemplo_csv); ?></div>
                </div>
            </div>
            
            <!-- Card de Instruções -->
            <div class="card">
                <h2>
                    <div class="card-icon">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    Instruções
                </h2>
                
                <div style="line-height: 1.8;">
                    <h3 style="color: #333; margin-bottom: 15px;">Colunas Obrigatórias:</h3>
                    <ul style="list-style: none;">
                        <li><i class="fas fa-check" style="color: #4caf50; margin-right: 10px;"></i> <strong>nome</strong> - Nome do profissional</li>
                        <li><i class="fas fa-check" style="color: #4caf50; margin-right: 10px;"></i> <strong>telefone</strong> - Telefone com DDD</li>
                        <li>// filepath: d:\xamp rodando\htdocs\ZelaLar\admin_importacao.php

<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'classes/ImportadorCSV.php';

// Verificar se é admin (você pode implementar validação real depois)
session_start();

$mensagem = '';
$tipo_mensagem = '';
$historico = [];
$importador = new ImportadorCSV();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['arquivo_csv'])) {
        $arquivo = $_FILES['arquivo_csv'];
        
        if ($arquivo['error'] === UPLOAD_ERR_OK) {
            $extensao = pathinfo($arquivo['name'], PATHINFO_EXTENSION);
            
            if (strtolower($extensao) !== 'csv') {
                $tipo_mensagem = 'erro';
                $mensagem = 'Por favor, envie um arquivo CSV válido.';
            } else if ($arquivo['size'] > 5 * 1024 * 1024) { // 5MB
                $tipo_mensagem = 'erro';
                $mensagem = 'Arquivo muito grande (máximo 5MB).';
            } else {
                $resultado = $importador->importarArquivo($arquivo['tmp_name']);
                
                if ($resultado['sucesso']) {
                    $tipo_mensagem = 'sucesso';
                    $mensagem = $resultado['mensagem'];
                } else {
                    $tipo_mensagem = 'erro';
                    $mensagem = $resultado['mensagem'];
                }
            }
        } else {
            $tipo_mensagem = 'erro';
            $mensagem = 'Erro ao fazer upload do arquivo.';
        }
    }
}

$historico = $importador->obterHistorico(20);

// Exemplo de CSV
$exemplo_csv = "nome;telefone;categoria;email;endereco;cidade;estado;cep;descricao
João Silva;11 99999-9999;Pedreiro;joao@email.com;Rua das Flores 123;São Paulo;SP;01234-567;Pedreiro experiente
Maria Santos;11 98888-8888;Pintor;maria@email.com;Av. Paulista 456;São Paulo;SP;01310-100;Pintora profissional";
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importar Profissionais - ZelaLar Admin</title>
    <link rel="stylesheet" href="Styles/index.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background-color: #f5f7fa;
            font-family: 'Inter', sans-serif;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        header h1 {
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-sucesso {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-erro {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .card h2 {
            color: #333;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .card-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .drop-zone {
            border: 2px dashed #667eea;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .drop-zone:hover {
            background: #f0f4ff;
        }
        
        .drop-zone.dragover {
            background: #e8ecff;
            border-color: #764ba2;
        }
        
        .drop-zone i {
            font-size: 48px;
            color: #667eea;
            margin-bottom: 15px;
        }
        
        .drop-zone p {
            color: #666;
            margin-bottom: 10px;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: transform 0.2s;
            text-decoration: none;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-secondary {
            background: #e0e0e0;
            color: #333;
        }
        
        .btn-download {
            background: #28a745;
            color: white;
            margin-top: 20px;
        }
        
        #arquivo_csv {
            display: none;
        }
        
        .file-info {
            margin-top: 15px;
            padding: 15px;
            background: #f9fafc;
            border-radius: 5px;
            display: none;
        }
        
        .file-info.show {
            display: block;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .table th {
            background: #f5f7fa;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #666;
            border-bottom: 1px solid #ddd;
        }
        
        .table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }
        
        .table tr:hover {
            background: #f9fafc;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-processando {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-concluida {
            background: #d4edda;
            color: #155724;
        }
        
        .status-erro {
            background: #f8d7da;
            color: #721c24;
        }
        
        .info-box {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .info-box i {
            color: #2196f3;
            margin-right: 10px;
        }
        
        .template-code {
            background: #f5f5f5;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            overflow-x: auto;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.5;
            margin-top: 15px;
        }
        
        @media (max-width: 768px) {
            .cards {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>
                <i class="fas fa-upload"></i>
                Importar Profissionais
            </h1>
        </header>
        
        <?php if ($mensagem): ?>
            <div class="alert alert-<?php echo $tipo_mensagem === 'sucesso' ? 'sucesso' : 'erro'; ?>">
                <i class="fas fa-<?php echo $tipo_mensagem === 'sucesso' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo htmlspecialchars($mensagem); ?>
            </div>
        <?php endif; ?>
        
        <div class="cards">
            <!-- Card de Upload -->
            <div class="card">
                <h2>
                    <div class="card-icon">
                        <i class="fas fa-file-csv"></i>
                    </div>
                    Upload do CSV
                </h2>
                
                <div class="info-box">
                    <i class="fas fa-info-circle"></i>
                    <strong>Formato esperado:</strong> Arquivo CSV com separador de ponto e vírgula (;)
                </div>
                
                <form method="POST" enctype="multipart/form-data" id="form-upload">
                    <div class="drop-zone" id="drop-zone">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <p><strong>Arraste o arquivo aqui</strong></p>
                        <p style="font-size: 12px; color: #999;">ou clique para selecionar</p>
                        <input type="file" id="arquivo_csv" name="arquivo_csv" accept=".csv" required>
                    </div>
                    
                    <div class="file-info" id="file-info">
                        <p><strong>Arquivo selecionado:</strong> <span id="file-name"></span></p>
                        <p><strong>Tamanho:</strong> <span id="file-size"></span></p>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 20px;">
                        <i class="fas fa-upload"></i> Importar Agora
                    </button>
                </form>
                
                <a href="javascript:void(0);" onclick="downloadTemplate()" class="btn btn-download" style="width: 100%;">
                    <i class="fas fa-download"></i> Baixar Template
                </a>
                
                <div style="margin-top: 20px;">
                    <h3 style="margin-bottom: 10px; color: #333;">Estrutura do CSV:</h3>
                    <div class="template-code"><?php echo htmlspecialchars($exemplo_csv); ?></div>
                </div>
            </div>
            
            <!-- Card de Instruções -->
            <div class="card">
                <h2>
                    <div class="card-icon">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    Instruções
                </h2>
                
                <div style="line-height: 1.8;">
                    <h3 style="color: #333; margin-bottom: 15px;">Colunas Obrigatórias:</h3>
                    <ul style="list-style: none;">
                        <li><i class="fas fa-check" style="color: #4caf50; margin-right: 10px;"></i> <strong>nome</strong> - Nome do profissional</li>
                        <li><i class="fas fa-check" style="color: #4caf50; margin-right: 10px;"></i> <strong>telefone</strong> - Telefone com DDD</li>
                        <li>