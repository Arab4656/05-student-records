<?php

require_once __DIR__ . '/src/helpers.php';
require_once __DIR__ . '/src/Database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit('Method not allowed');
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    http_response_code(403);
    exit('Your request could not be verified. Return to the student list and try again.');
}

$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
if (!is_int($id) || $id < 1) {
    http_response_code(400);
    exit('A valid student ID is required.');
}

try {
    $delete = db()->prepare('DELETE FROM students WHERE id = :id');
    $delete->execute(['id' => $id]);

    if ($delete->rowCount() > 0) {
        flash('success', 'Student deleted');
    } else {
        flash('error', 'Student not found');
    }

    redirect('index.php');
} catch (PDOException $exception) {
    http_response_code(503);
    $pageTitle = 'Records temporarily unavailable';
    require __DIR__ . '/partials/header.php';
    ?>
    <section class="error-card">
        <h1>Records temporarily unavailable</h1>
        <p>We couldn't delete this student record right now. Please check that MySQL is running and try again.</p>
        <a class="button button-primary" href="index.php">Back to students</a>
    </section>
    <?php
    require __DIR__ . '/partials/footer.php';
}
