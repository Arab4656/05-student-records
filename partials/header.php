<?php

$flashMessages = getFlash();
$pageTitle = $pageTitle ?? 'Student Records Manager';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <link rel="stylesheet" href="assets/app.css">
</head>
<body>
    <header class="topbar">
        <div class="container topbar-inner">
            <a class="brand" href="index.php">Student Records</a>
            <span class="topbar-label">Administration</span>
        </div>
    </header>
    <main class="container main-content">
        <?php foreach ($flashMessages as $type => $message): ?>
            <?php $alertType = in_array($type, ['success', 'error', 'warning', 'info'], true) ? $type : 'info'; ?>
            <div class="alert alert-<?= e($alertType) ?>" role="status"><?= e($message) ?></div>
        <?php endforeach; ?>
