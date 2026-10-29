<?php

require_once __DIR__ . '/src/helpers.php';
require_once __DIR__ . '/src/Database.php';

$confirmed = ($_GET['confirm'] ?? null) === 'yes';
$insertedCount = 0;
$databaseUnavailable = false;

if ($confirmed) {
    $students = [
        ['Amina', 'Abubakar'],
        ['Chinedu', 'Okafor'],
        ['Zainab', 'Bello'],
        ['Tunde', 'Adeyemi'],
        ['Ngozi', 'Eze'],
        ['Ibrahim', 'Musa'],
        ['Funmi', 'Adebayo'],
        ['Emeka', 'Nwosu'],
        ['Hauwa', 'Sani'],
        ['Kelechi', 'Okonkwo'],
        ['Chioma', 'Nwachukwu'],
        ['Yusuf', 'Abdullahi'],
        ['Bisi', 'Ogunleye'],
        ['Obinna', 'Ibe'],
        ['Maryam', 'Lawal'],
        ['Kunle', 'Olawale'],
        ['Amaka', 'Ezeani'],
        ['Seyi', 'Babatunde'],
        ['Aisha', 'Garba'],
        ['Uche', 'Anyanwu'],
        ['Tolulope', 'Fashola'],
        ['Babatunde', 'Akinyemi'],
        ['Nneka', 'Ekwueme'],
        ['Mariam', 'Yusuf'],
        ['Damilola', 'Ojo'],
        ['Oluwaseun', 'Balogun'],
        ['Adaeze', 'Onyeka'],
        ['Suleiman', 'Ibrahim'],
        ['Temitope', 'Alabi'],
        ['Chisom', 'Eze'],
        ['Rahmat', 'Ahmed'],
        ['Olumide', 'Adeleke'],
        ['Ifeoma', 'Umeh'],
        ['Garba', 'Mohammed'],
        ['Kemi', 'Ogunbiyi'],
        ['Ebuka', 'Maduka'],
        ['Safiya', 'Bashir'],
        ['Ayo', 'Oladipo'],
        ['Nkiru', 'Opara'],
        ['Musa', 'Danladi'],
        ['Yetunde', 'Olatunji'],
        ['Tochukwu', 'Eze'],
        ['Rukayya', 'Abubakar'],
        ['Akin', 'Olowo'],
        ['Ijeoma', 'Nwankwo'],
        ['Bashir', 'Sule'],
        ['Olamide', 'Afolabi'],
        ['Chukwuemeka', 'Obi'],
        ['Halima', 'Bello'],
        ['Tosin', 'Akinola'],
    ];
    $courses = [
        'Web Development',
        'Software Engineering',
        'Data Analysis',
        'UI/UX Design',
    ];
    $batchId = bin2hex(random_bytes(4));

    try {
        $database = db();
        $insert = $database->prepare(
            'INSERT INTO students (first_name, last_name, email, phone, course, enrolled_on)
             VALUES (:first_name, :last_name, :email, :phone, :course, :enrolled_on)'
        );

        $database->beginTransaction();
        foreach ($students as $index => [$firstName, $lastName]) {
            $number = $index + 1;
            $insert->execute([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => sprintf('seed.%s.%02d@example.com', $batchId, $number),
                'phone' => sprintf('080%08d', $number),
                'course' => $courses[$index % count($courses)],
                'enrolled_on' => date('Y-m-d', strtotime('-' . (1 + ($index % 300)) . ' days')),
            ]);
            $insertedCount++;
        }
        $database->commit();
    } catch (PDOException $exception) {
        if (isset($database) && $database->inTransaction()) {
            $database->rollBack();
        }
        http_response_code(503);
        $databaseUnavailable = true;
    }
}

$pageTitle = $databaseUnavailable
    ? 'Records temporarily unavailable'
    : 'Seed student records';
require __DIR__ . '/partials/header.php';
?>
<?php if ($databaseUnavailable): ?>
    <section class="error-card">
        <h1>Records temporarily unavailable</h1>
        <p>We couldn't add sample students right now. Please check that MySQL is running and try again.</p>
        <a class="button button-primary" href="index.php">Back to students</a>
    </section>
<?php elseif ($confirmed): ?>
    <section class="error-card">
        <h1>Sample students added</h1>
        <p><?= e($insertedCount) ?> fictional student records were added. You can now test searching and pagination.</p>
        <a class="button button-primary" href="index.php">View students</a>
    </section>
<?php else: ?>
    <section class="error-card">
        <h1>Add sample students?</h1>
        <p>This will insert 50 fictional Nigerian student records into your database for testing pagination.</p>
        <a class="button button-primary" href="seed.php?confirm=yes">Insert 50 students</a>
        <a class="button button-secondary" href="index.php">Cancel</a>
    </section>
<?php endif; ?>
<?php require __DIR__ . '/partials/footer.php'; ?>
