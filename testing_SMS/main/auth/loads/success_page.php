<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Success</title>
    <link rel="stylesheet" href="success_page.css">
</head>
<body>
    <?php require_once 'includes/header.php'; ?>
    <h1>Success</h1>
    <p><?= htmlspecialchars($_GET['message'] ?? 'Update successfully!') ?></p>
    <a href="javascript:history.back()"><button type="button">Go Back</button></a>
    <?php require_once 'includes/footer.php'; ?>
</body>
</html>




