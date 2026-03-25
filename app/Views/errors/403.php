<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Accès refusé</title>
    <link rel="stylesheet" href="<?= APP_URL ?>/public/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .error-page {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            text-align: center;
            padding: 20px;
        }
        .error-content h1 {
            font-size: 120px;
            margin: 0;
            color: #e67e22;
        }
        .error-content h2 {
            font-size: 32px;
            margin: 20px 0;
        }
        .error-content p {
            font-size: 18px;
            color: #666;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="error-page">
        <div class="error-content">
            <h1>403</h1>
            <h2><i class="fas fa-lock"></i> Accès refusé</h2>
            <p>Vous n'avez pas les permissions nécessaires pour accéder à cette page.</p>
            <a href="<?= APP_URL ?>/dashboard" class="btn btn-primary">
                <i class="fas fa-home"></i> Retour à l'accueil
            </a>
        </div>
    </div>
</body>
</html>
