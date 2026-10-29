<?php

require_once __DIR__ . '/src/helpers.php';
require_once __DIR__ . '/src/Database.php';

$courses = [
    'Web Development',
    'Software Engineering',
    'Data Analysis',
    'UI/UX Design',
];
$query = $_GET['q'] ?? '';
$query = is_string($query) ? trim($query) : '';
$selectedCourse = $_GET['course'] ?? '';
$selectedCourse = is_string($selectedCourse) && in_array($selectedCourse, $courses, true)
    ? $selectedCourse
    : '';
$requestedPage = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
$currentPage = is_int($requestedPage) && $requestedPage > 0 ? $requestedPage : 1;
$perPage = 10;
$where = [];
$parameters = [];

if ($query !== '') {
    $where[] = '(first_name LIKE :first_name OR last_name LIKE :last_name OR email LIKE :email)';
    $searchPattern = '%' . $query . '%';
    $parameters['first_name'] = $searchPattern;
    $parameters['last_name'] = $searchPattern;
    $parameters['email'] = $searchPattern;
}

if ($selectedCourse !== '') {
    $where[] = 'course = :course';
    $parameters['course'] = $selectedCourse;
}

$whereSql = $where === [] ? '' : ' WHERE ' . implode(' AND ', $where);

try {
    $database = db();
    $countStatement = $database->prepare('SELECT COUNT(*) FROM students' . $whereSql);
    $countStatement->execute($parameters);
    $totalStudents = (int) $countStatement->fetchColumn();
    $totalPages = max(1, (int) ceil($totalStudents / $perPage));
    $currentPage = min($currentPage, $totalPages);
    $offset = ($currentPage - 1) * $perPage;

    $statement = $database->prepare(
        'SELECT id, first_name, last_name, email, phone, course, enrolled_on
         FROM students'
         . $whereSql .
        ' ORDER BY last_name, first_name
         LIMIT :limit OFFSET :offset'
    );
    foreach ($parameters as $name => $value) {
        $statement->bindValue(':' . $name, $value, PDO::PARAM_STR);
    }
    $statement->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
    $statement->execute();
    $students = $statement->fetchAll();
} catch (PDOException $exception) {
    http_response_code(503);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Records temporarily unavailable</title>
        <link rel="stylesheet" href="assets/app.css">
    </head>
    <body>
        <main class="container main-content">
            <section class="error-card">
                <h1>Records temporarily unavailable</h1>
                <p>We couldn't load the student records right now. Please check that MySQL is running and that the database connection settings are correct, then try again.</p>
                <a class="button button-primary" href="index.php">Try again</a>
            </section>
        </main>
    </body>
    </html>
    <?php
    exit;
}

$pageTitle = 'Students | Student Records Manager';
require __DIR__ . '/partials/header.php';
?>
<section class="page-heading">
    <div>
        <h1>Students</h1>
        <p>Manage student records and enrollment details.</p>
    </div>
    <a class="button button-primary" href="create.php">Add student</a>
</section>

<form class="card student-filters" action="index.php" method="get">
    <div class="filter-field filter-search">
        <label for="q">Search students</label>
        <input id="q" name="q" type="search" value="<?= e($query) ?>" placeholder="Name or email">
    </div>
    <div class="filter-field">
        <label for="course">Course</label>
        <select id="course" name="course">
            <option value="">All courses</option>
            <?php foreach ($courses as $course): ?>
                <option value="<?= e($course) ?>" <?= $selectedCourse === $course ? 'selected' : '' ?>>
                    <?= e($course) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <button class="button button-primary" type="submit">Apply filters</button>
    <?php if ($query !== '' || $selectedCourse !== ''): ?>
        <a class="button button-secondary" href="index.php">Clear</a>
    <?php endif; ?>
</form>

<p class="results-summary">
    <?php if ($totalStudents === 0): ?>
        Showing 0–0 of 0 students
    <?php else: ?>
        <?php $rangeStart = $offset + 1; ?>
        <?php $rangeEnd = min($offset + count($students), $totalStudents); ?>
        Showing <?= e($rangeStart) ?>–<?= e($rangeEnd) ?> of <?= e($totalStudents) ?> students
    <?php endif; ?>
</p>

<section class="card" aria-label="Student records">
    <?php if ($students === []): ?>
        <div class="empty-state">
            <strong><?= $totalStudents === 0 && ($query !== '' || $selectedCourse !== '') ? 'No matching students' : 'No students found' ?></strong>
            <?= $query !== '' || $selectedCourse !== ''
                ? 'Try changing your search or course filter.'
                : 'Student records will appear here once they have been added.' ?>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="student-table">
                <thead>
                    <tr>
                        <th scope="col">Student</th>
                        <th scope="col">Email</th>
                        <th scope="col">Phone</th>
                        <th scope="col">Course</th>
                        <th scope="col">Enrolled</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                        <?php $studentName = $student['first_name'] . ' ' . $student['last_name']; ?>
                        <tr>
                            <td class="student-name"><a href="show.php?id=<?= e($student['id']) ?>"><?= e($studentName) ?></a></td>
                            <td><?= e($student['email']) ?></td>
                            <td><?= e($student['phone']) ?></td>
                            <td><?= e($student['course']) ?></td>
                            <td><?= e((new DateTimeImmutable($student['enrolled_on']))->format('d M Y')) ?></td>
                            <td>
                                <div class="actions">
                                    <a class="button button-secondary" href="edit.php?id=<?= e($student['id']) ?>">Edit</a>
                                    <form action="delete.php" method="post"
                                        onsubmit="return confirm(this.dataset.confirm)"
                                        data-confirm="<?= e('Delete ' . $studentName . '?') ?>">
                                        <input type="hidden" name="id" value="<?= e($student['id']) ?>">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                                        <button class="button button-danger" type="submit">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<?php if ($totalPages > 1): ?>
    <?php
    $pageUrl = static function (int $page) use ($query, $selectedCourse): string {
        $parameters = [];
        if ($query !== '') {
            $parameters['q'] = $query;
        }
        if ($selectedCourse !== '') {
            $parameters['course'] = $selectedCourse;
        }
        $parameters['page'] = $page;

        return 'index.php?' . http_build_query($parameters);
    };
    ?>
    <nav class="pagination" aria-label="Student list pages">
        <?php if ($currentPage > 1): ?>
            <a class="pagination-link" href="<?= e($pageUrl($currentPage - 1)) ?>" rel="prev">Previous</a>
        <?php endif; ?>
        <?php for ($page = 1; $page <= $totalPages; $page++): ?>
            <?php if ($page === $currentPage): ?>
                <span class="pagination-link is-current" aria-current="page"><?= e($page) ?></span>
            <?php else: ?>
                <a class="pagination-link" href="<?= e($pageUrl($page)) ?>"><?= e($page) ?></a>
            <?php endif; ?>
        <?php endfor; ?>
        <?php if ($currentPage < $totalPages): ?>
            <a class="pagination-link" href="<?= e($pageUrl($currentPage + 1)) ?>" rel="next">Next</a>
        <?php endif; ?>
    </nav>
<?php endif; ?>

<?php require __DIR__ . '/partials/footer.php'; ?>
