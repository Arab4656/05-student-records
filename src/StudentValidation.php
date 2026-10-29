<?php

/**
 * @return list<string>
 */
function studentCourses(): array
{
    return [
        'Web Development',
        'Software Engineering',
        'Data Analysis',
        'UI/UX Design',
    ];
}

/**
 * @param array<string, mixed> $student
 * @param list<string> $courses
 * @return array<string, string>
 */
function validateStudent(PDO $database, array $student, array $courses, ?int $excludeId = null): array
{
    $errors = [];

    foreach (['first_name' => 'First name', 'last_name' => 'Last name'] as $field => $label) {
        if ($student[$field] === '') {
            $errors[$field] = $label . ' is required.';
        } elseif (preg_match('/^[\p{L} -]{2,60}$/u', $student[$field]) !== 1) {
            $errors[$field] = $label . ' must be 2-60 characters and contain only letters, spaces, or hyphens.';
        }
    }

    if ($student['email'] === '' || filter_var($student['email'], FILTER_VALIDATE_EMAIL) === false) {
        $errors['email'] = 'Enter a valid email address.';
    } elseif (strlen($student['email']) > 254) {
        $errors['email'] = 'Email addresses must be 254 characters or fewer.';
    }

    if ($student['phone'] !== '' && preg_match('/^0[789][01]\d{8}$/', $student['phone']) !== 1) {
        $errors['phone'] = 'Enter a valid phone number, for example 08012345678.';
    }

    if (!in_array($student['course'], $courses, true)) {
        $errors['course'] = 'Select one of the available courses.';
    }

    $enrollmentDate = DateTimeImmutable::createFromFormat('!Y-m-d', $student['enrolled_on']);
    $dateErrors = DateTimeImmutable::getLastErrors();
    if (
        $enrollmentDate === false
        || ($dateErrors !== false && ($dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0))
        || $enrollmentDate->format('Y-m-d') !== $student['enrolled_on']
    ) {
        $errors['enrolled_on'] = 'Enter a valid enrollment date.';
    } elseif ($enrollmentDate > new DateTimeImmutable('today')) {
        $errors['enrolled_on'] = 'The enrollment date cannot be in the future.';
    }

    if (!isset($errors['email'])) {
        $sql = 'SELECT id FROM students WHERE email = :email';
        $parameters = ['email' => $student['email']];
        if ($excludeId !== null) {
            $sql .= ' AND id <> :exclude_id';
            $parameters['exclude_id'] = $excludeId;
        }
        $sql .= ' LIMIT 1';

        $emailCheck = $database->prepare($sql);
        $emailCheck->execute($parameters);
        if ($emailCheck->fetch() !== false) {
            $errors['email'] = 'A student with this email address already exists.';
        }
    }

    return $errors;
}
