<?php

require_once __DIR__ . '/src/helpers.php';
require_once __DIR__ . '/src/Database.php';
require_once __DIR__ . '/src/StudentValidation.php';

$courses = studentCourses();
$student = [
    'first_name' => '',
    'last_name' => '',
    'email' => '',
    'phone' => '',
    'course' => '',
    'enrolled_on' => date('Y-m-d'),
];
$errors = [];
$formAction = 'create.php';
$submitLabel = 'Add student';
$csrfToken = csrfToken();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($student as $field => $default) {
        $value = $_POST[$field] ?? $default;
        $student[$field] = is_string($value) ? trim($value) : '';
    }

    try {
        if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            $errors['form'] = 'Your session could not be verified. Please try submitting the form again.';
        } else {
            $errors = validateStudent(db(), $student, $courses);
        }

        if ($errors === []) {
            $insert = db()->prepare(
                'INSERT INTO students (first_name, last_name, email, phone, course, enrolled_on)
                 VALUES (:first_name, :last_name, :email, :phone, :course, :enrolled_on)'
            );
            $insert->execute([
                'first_name' => $student['first_name'],
                'last_name' => $student['last_name'],
                'email' => $student['email'],
                'phone' => $student['phone'] !== '' ? $student['phone'] : null,
                'course' => $student['course'],
                'enrolled_on' => $student['enrolled_on'],
            ]);

            flash('success', 'Student added');
            redirect('index.php');
        }
    } catch (PDOException $exception) {
        if ($exception->getCode() === '23000') {
            $errors['email'] = 'A student with this email address already exists.';
        } else {
            http_response_code(503);
            $databaseUnavailable = true;
        }
    }
}

if (isset($databaseUnavailable)) {
    $pageTitle = 'Records temporarily unavailable';
    require __DIR__ . '/partials/header.php';
    ?>
    <section class="error-card">
        <h1>Records temporarily unavailable</h1>
        <p>We couldn't save the student record right now. Please check that MySQL is running and try again.</p>
        <a class="button button-primary" href="create.php">Try again</a>
    </section>
    <?php
    require __DIR__ . '/partials/footer.php';
    exit;
}

$pageTitle = 'Add student | Student Records Manager';
require __DIR__ . '/partials/header.php';
?>
<section class="page-heading">
    <div>
        <h1>Add student</h1>
        <p>Enter the student's details below.</p>
    </div>
</section>

<?php if (isset($errors['form'])): ?>
    <div class="alert alert-error" role="alert"><?= e($errors['form']) ?></div>
<?php endif; ?>

<?php require __DIR__ . '/partials/student-form.php'; ?>
<?php require __DIR__ . '/partials/footer.php'; ?>
