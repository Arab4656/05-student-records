<?php

$fieldError = static fn (string $field): string => $errors[$field] ?? '';
?>
<form class="card student-form" action="<?= e($formAction) ?>" method="post" novalidate>
    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">

    <div class="form-grid">
        <div class="form-field">
            <label for="first_name">First name <span aria-hidden="true">*</span></label>
            <input id="first_name" name="first_name" type="text" required minlength="2" maxlength="60"
                pattern="[\p{L} -]{2,60}" autocomplete="given-name"
                value="<?= e($student['first_name'] ?? '') ?>"
                <?= $fieldError('first_name') !== '' ? 'aria-invalid="true" aria-describedby="first_name-error"' : '' ?>>
            <?php if ($fieldError('first_name') !== ''): ?>
                <p class="field-error" id="first_name-error"><?= e($fieldError('first_name')) ?></p>
            <?php endif; ?>
        </div>

        <div class="form-field">
            <label for="last_name">Last name <span aria-hidden="true">*</span></label>
            <input id="last_name" name="last_name" type="text" required minlength="2" maxlength="60"
                pattern="[\p{L} -]{2,60}" autocomplete="family-name"
                value="<?= e($student['last_name'] ?? '') ?>"
                <?= $fieldError('last_name') !== '' ? 'aria-invalid="true" aria-describedby="last_name-error"' : '' ?>>
            <?php if ($fieldError('last_name') !== ''): ?>
                <p class="field-error" id="last_name-error"><?= e($fieldError('last_name')) ?></p>
            <?php endif; ?>
        </div>

        <div class="form-field">
            <label for="email">Email address <span aria-hidden="true">*</span></label>
            <input id="email" name="email" type="email" required maxlength="254" autocomplete="email"
                value="<?= e($student['email'] ?? '') ?>"
                <?= $fieldError('email') !== '' ? 'aria-invalid="true" aria-describedby="email-error"' : '' ?>>
            <?php if ($fieldError('email') !== ''): ?>
                <p class="field-error" id="email-error"><?= e($fieldError('email')) ?></p>
            <?php endif; ?>
        </div>

        <div class="form-field">
            <label for="phone">Phone number</label>
            <input id="phone" name="phone" type="tel" inputmode="numeric" pattern="0[789][01][0-9]{8}"
                autocomplete="tel" placeholder="08012345678"
                value="<?= e($student['phone'] ?? '') ?>"
                <?= $fieldError('phone') !== '' ? 'aria-invalid="true" aria-describedby="phone-error"' : '' ?>>
            <?php if ($fieldError('phone') !== ''): ?>
                <p class="field-error" id="phone-error"><?= e($fieldError('phone')) ?></p>
            <?php endif; ?>
        </div>

        <div class="form-field">
            <label for="course">Course <span aria-hidden="true">*</span></label>
            <select id="course" name="course" required
                <?= $fieldError('course') !== '' ? 'aria-invalid="true" aria-describedby="course-error"' : '' ?>>
                <option value="">Select a course</option>
                <?php foreach ($courses as $course): ?>
                    <option value="<?= e($course) ?>" <?= ($student['course'] ?? '') === $course ? 'selected' : '' ?>>
                        <?= e($course) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if ($fieldError('course') !== ''): ?>
                <p class="field-error" id="course-error"><?= e($fieldError('course')) ?></p>
            <?php endif; ?>
        </div>

        <div class="form-field">
            <label for="enrolled_on">Enrollment date <span aria-hidden="true">*</span></label>
            <input id="enrolled_on" name="enrolled_on" type="date" required max="<?= e(date('Y-m-d')) ?>"
                value="<?= e($student['enrolled_on'] ?? '') ?>"
                <?= $fieldError('enrolled_on') !== '' ? 'aria-invalid="true" aria-describedby="enrolled_on-error"' : '' ?>>
            <?php if ($fieldError('enrolled_on') !== ''): ?>
                <p class="field-error" id="enrolled_on-error"><?= e($fieldError('enrolled_on')) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="form-actions">
        <a class="button button-secondary" href="index.php">Cancel</a>
        <button class="button button-primary" type="submit"><?= e($submitLabel) ?></button>
    </div>
</form>
