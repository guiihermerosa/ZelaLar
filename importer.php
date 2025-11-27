<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importar CSV</title>
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
        
        #label_csv {
         padding: 100% ;
           
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
        
     
        
        
    </style>
    </style>
</head>
<body>
    <h2>Importador CSV</h2>
        <div class="container">
     <form action="./classes/ImportadorCSV.php" method="POST" enctype="multipart/form-data" id="form-upload">
                    <div class="drop-zone" id="drop-zone">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <p><strong>Arraste o arquivo aqui</strong></p>
                        <p style="font-size: 12px; color: #999;">ou clique para selecionar</p>
                        <input type="file" id="arquivo_csv" name="arquivo_csv" accept=".csv" required>
                        <label for="arquivo_csv" id="label_csv"></label>
                    </div>
                    
                    <div class="file-info" id="file-info">
                        <p><strong>Arquivo selecionado:</strong> <span id="file-name"></span></p>
                        <p><strong>Tamanho:</strong> <span id="file-size"></span></p>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 20px;">
                        <i class="fas fa-upload"></i> Importar Agora
                    </button>
                </form>
                </div>
</body>
</html>