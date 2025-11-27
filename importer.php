<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importar CSV</title>
    <style>
     body{
        font-family: Arial, sans-serif;
        margin: 40px;
      }
      
      h2{
        color: #333;
      }
     form{
        margin-top: 20px;
        display: flex;
        flex-direction: column;
        gap: 10px;
        width: 300px;

      }
    </style>
</head>
<body>
    <h2>Importador CSV</h2>

    <form action="./classes/ImportadorCSV.php" method="POST" enctype="multipart/form-data">
        <input type="file" name="arquivo" accept=".csv" required>
        <button type="submit">Enviar</button>
    </form>
</body>
</html>