<?php

require_once __DIR__ . '/src/helpers.php';
require_once __DIR__ . '/src/Database.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$id = is_int($id) && $id > 0 ? $id : null;
$student = false;

if ($id !== null) {
    try {
        $statement = db()->prepare(
            'SELECT id, first_name, last_name, email, phone, course, enrolled_on, created_at, updated_at
             FROM students
             WHERE id = :id'
        );
        $statement->execute(['id' => $id]);
        $student = $statement->fetch();
    } catch (PDOException $exception) {
        http_response_code(503);
        $databaseUnavailable = true;
    }
}

if (isset($databaseUnavailable)) {
    $pageTitle = 'Records temporarily unavailable';
    require __DIR__ . '/partials/header.php';
    ?>
    <section class="error-card">
        <h1>Records temporarily unavailable</h1>
        <p>We couldn't load this student record right now. Please check that MySQL is running and try again.</p>
        <a class="button button-primary" href="index.php">Back to students</a>
    </section>
    <?php
    require __DIR__ . '/partials/footer.php';
    exit;
}

if ($student === false) {
    http_response_code(404);
}

$pageTitle = $student === false
    ? 'Student not found | Student Records Manager'
    : $student['first_name'] . ' ' . $student['last_name'] . ' | Student Records Manager';
require __DIR__ . '/partials/header.php';
?>
<?php if ($student === false): ?>
    <section class="error-card">
        <h1>Student not found</h1>
        <p>The requested student record could not be found.</p>
        <a class="button button-primary" href="index.php">Back to students</a>
    </section>
<?php else: ?>
    <section class="page-heading">
        <div>
            <h1><?= e($student['first_name'] . ' ' . $student['last_name']) ?></h1>
            <p>Student record details.</p>
        </div>
        <a class="button button-primary" href="edit.php?id=<?= e($student['id']) ?>">Edit student</a>
    </section>

    <dl class="card detail-list">
        <div class="detail-item">
            <dt>Email</dt>
            <dd><a href="mailto:<?= e($student['email']) ?>"><?= e($student['email']) ?></a></dd>
        </div>
        <div class="detail-item">
            <dt>Phone</dt>
            <dd><?= $student['phone'] !== null && $student['phone'] !== '' ? e($student['phone']) : 'Not provided' ?></dd>
        </div>
        <div class="detail-item">
            <dt>Course</dt>
            <dd><?= e($student['course']) ?></dd>
        </div>
        <div class="detail-item">
            <dt>Enrolled</dt>
            <dd><?= e((new DateTimeImmutable($student['enrolled_on']))->format('d M Y')) ?></dd>
        </div>
        <div class="detail-item">
            <dt>Record created</dt>
            <dd><?= e((new DateTimeImmutable($student['created_at']))->format('d M Y, H:i')) ?></dd>
        </div>
        <div class="detail-item">
            <dt>Last updated</dt>
            <dd><?= e((new DateTimeImmutable($student['updated_at']))->format('d M Y, H:i')) ?></dd>
        </div>
    </dl>

    <p class="detail-back"><a href="index.php">← Back to students</a></p>
<?php endif; ?>
<?php require __DIR__ . '/partials/footer.php'; ?>
