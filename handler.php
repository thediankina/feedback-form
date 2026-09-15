<?php

session_start();
header('Content-Type: application/json; charset=utf-8');

define('TEXT_MAX', 255);
define('AREA_MAX', 4096);
define('TO_EMAIL', 'thediankina@yandex.ru');

define('ALLOWED_THEMES', ['question', 'complaint', 'suggestion', 'other']);
define('PHONE_RE', '/^(\+7|8)\D*\d{3}\D*\d{3}\D*\d{2}\D*\d{2}$/');
define('EMAIL_RE', '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/');

require_once __DIR__ . '/classes/autoload.php';

function cleanInput(string $value): string
{
    return htmlspecialchars(trim($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function generateCaptcha(): array
{
    $a = random_int(1, 9);
    $b = random_int(1, 9);
    $ops = ['+', '-', '×'];
    $op = $ops[array_rand($ops)];

    if ($op === '+') {
        $answer = $a + $b;
    } elseif ($op === '-') {
        if ($a < $b) {
            $tmp = $a;
            $a = $b;
            $b = $tmp;
        }
        $answer = $a - $b;
    } else {
        $answer = $a * $b;
    }

    $_SESSION['captchaAnswer'] = $answer;

    return [
        'question' => "$a $op $b ="
    ];
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'captcha') {
    $newCaptcha = generateCaptcha();

    echo json_encode([
        'question' => $newCaptcha['question']
    ]);
    exit();
}

if ($action === 'message') {
    $theme = cleanInput($_POST['theme'] ?? '');
    $fullName = cleanInput($_POST['fullName'] ?? '');
    $phone = cleanInput($_POST['phone'] ?? '');
    $email = cleanInput($_POST['email'] ?? '');
    $message = cleanInput($_POST['message'] ?? '');
    $agreement = isset($_POST['agreement']) && $_POST['agreement'] === 'on';

    $captchaAnswer = cleanInput($_POST['captchaAnswer'] ?? '');
    $storedAnswer = $_SESSION['captchaAnswer'] ?? null;

    $errors = [];

    $validator = new ThemeValidator(ALLOWED_THEMES);

    if (!$validator->validate($theme)) {
        $errors['theme'] = $validator->getErrorsAsString();
    }

    $validator = new TextValidator(TEXT_MAX);

    if (!$validator->validate($fullName)) {
        $errors['fullName'] = $validator->getErrorsAsString();
    }

    $validator = new PhoneValidator(PHONE_RE);
    $validator->invalidFormatErrorMessage = 'Формат: +7 (999) 000-00-99';

    if (!$validator->validate($phone)) {
        $errors['phone'] = $validator->getErrorsAsString();
    }

    $validator = new EmailValidator(TEXT_MAX, EMAIL_RE);

    if (!$validator->validate($email)) {
        $errors['email'] = $validator->getErrorsAsString();
    }

    $validator = new TextValidator(AREA_MAX);

    if (!$validator->validate($message)) {
        $errors['message'] = $validator->getErrorsAsString();
    }

    if ($captchaAnswer === '') {
        $errors['captchaAnswer'] = 'Введите ответ капчи';
    } elseif ($storedAnswer === null || (int)$captchaAnswer !== (int)$storedAnswer) {
        $errors['captchaAnswer'] = 'Неверный ответ';
    }

    if (!$agreement) {
        $errors['agreement'] = 'Необходимо согласие на обработку данных';
    }

    $newCaptcha = generateCaptcha();

    if (!empty($errors)) {
        echo json_encode([
            'success'      => false,
            'message'      => 'Проверьте правильность заполнения полей',
            'errors'       => $errors,
            'captcha'      => $newCaptcha['question']
        ]);
        exit();
    }

    $subject = 'Новое обращение: ' . $theme;
    $body = "Поступило новое обращение через форму обратной связи.\n\n"
    . "Тема: $theme\n"
    . "ФИО: $fullName\n"
    . "Телефон: $phone\n"
    . "E-mail: $email\n"
    . "Сообщение:\n$message\n";

    $headers = "From: noreply@localhost\r\n"
    . "Reply-To: $email\r\n"
    . "Content-Type: text/plain; charset=utf-8\r\n";

    $isSent = @mail(TO_EMAIL, $subject, $body, $headers);

    if ($isSent) {
        echo json_encode([
            'success' => true,
            'message' => 'Ваше обращение успешно отправлено! Мы свяжемся с вами в ближайшее время.',
            'captcha' => $newCaptcha['question']
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Ошибка при отправке письма. Попробуйте позже или свяжитесь с нами другим способом.',
            'captcha' => $newCaptcha['question']
        ]);
    }
    exit();
}

http_response_code(400);
echo json_encode([
    'success' => false,
    'message' => 'Неизвестное действие'
]);
