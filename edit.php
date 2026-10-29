<?php

require_once __DIR__ . '/src/helpers.php';
require_once __DIR__ . '/src/Database.php';
require_once __DIR__ . '/src/StudentValidation.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$id = is_int($id) && $id > 0 ? $id : null;
$courses = studentCourses();
$errors = [];
$formAction = $id === null ? 'edit.php' : 'edit.php?id=' . $id;
$submitLabel = 'Save changes';
$csrfToken = csrfToken();
$student = [];
$studentNotFound = false;

if ($id === null) {
    http_response_code(404);
    $studentNotFound = true;
} else {
    try {
        $select = db()->prepare(
            'SELECT id, first_name, last_name, email, phone, course, enrolled_on
             FROM students
             WHERE id = :id'
        );
        $select->execute(['id' => $id]);
        $student = $select->fetch() ?: [];

        if ($student === []) {
            http_response_code(404);
            $studentNotFound = true;
        } else {
            unset($student['id']);

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                foreach (['first_name', 'last_name', 'email', 'phone', 'course', 'enrolled_on'] as $field) {
                    $value = $_POST[$field] ?? '';
                    $student[$field] = is_string($value) ? trim($value) : '';
                }

                if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
                    $errors['form'] = 'Your session could not be verified. Please try submitting the form again.';
                } else {
                    $errors = validateStudent(db(), $student, $courses, $id);
                }

                if ($errors === []) {
                    $update = db()->prepare(
                        'UPDATE students
                         SET first_name = :first_name,
                             last_name = :last_name,
                             email = :email,
                             phone = :phone,
                             course = :course,
                             enrolled_on = :enrolled_on
                         WHERE id = :id'
                    );
                    $update->execute([
                        'first_name' => $student['first_name'],
                        'last_name' => $student['last_name'],
                        'email' => $student['email'],
                        'phone' => $student['phone'] !== '' ? $student['phone'] : null,
                        'course' => $student['course'],
                        'enrolled_on' => $student['enrolled_on'],
                        'id' => $id,
                    ]);

                    flash('success', 'Student updated');
                    redirect('index.php');
                }
            }
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
        <p>We couldn't load or save this student record right now. Please check that MySQL is running and try again.</p>
        <a class="button button-primary" href="index.php">Back to students</a>
    </section>
    <?php
    require __DIR__ . '/partials/footer.php';
    exit;
}

$pageTitle = 'Edit student | Student Records Manager';
require __DIR__ . '/partials/header.php';
?>
<?php if ($studentNotFound): ?>
    <section class="error-card">
        <h1>Student not found</h1>
        <p>The requested student record could not be found.</p>
        <a class="button button-primary" href="index.php">Back to students</a>
    </section>
<?php else: ?>
    <section class="page-heading">
        <div>
            <h1>Edit student</h1>
            <p>Update the student's details below.</p>
        </div>
    </section>

    <?php if (isset($errors['form'])): ?>
        <div class="alert alert-error" role="alert"><?= e($errors['form']) ?></div>
    <?php endif; ?>

    <?php require __DIR__ . '/partials/student-form.php'; ?>
<?php endif; ?>
<?php require __DIR__ . '/partials/footer.php'; ?>
