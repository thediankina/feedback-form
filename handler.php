<?php

session_start();
header('Content-Type: application/json; charset=utf-8');

define('TEXT_MAX', 255);
define('AREA_MAX', 4096);

define('ALLOWED_THEMES', ['question', 'complaint', 'suggestion', 'other']);
define('PHONE_RE', '/^(\+7|8)\D*\d{3}\D*\d{3}\D*\d{2}\D*\d{2}$/');
define('EMAIL_RE', '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/');

require_once __DIR__ . '/classes/autoload.php';

use app\Request;
use app\Captcha;
use app\helpers\HtmlHelper;
use app\validators\EmailValidator;
use app\validators\PhoneValidator;
use app\validators\TextValidator;
use app\validators\ThemeValidator;

$request = new Request();
$captcha = new Captcha();

$action = $request->isGet() ? $request->get('action') : $request->post('action');

if ($action === 'captcha') {
    $question = $captcha->generate();

    echo json_encode([
        'question' => $question
    ]);
    exit();
}

if ($action === 'message') {
    $theme = HtmlHelper::sanitize($request->post('theme'));
    $fullName = HtmlHelper::sanitize($request->post('fullName'));
    $phone = HtmlHelper::sanitize($request->post('phone'));
    $email = HtmlHelper::sanitize($request->post('email'));
    $message = HtmlHelper::sanitize($request->post('message'));
    $agreement = $request->post('agreement') === 'on';

    $captchaAnswer = HtmlHelper::sanitize($request->post('captchaAnswer'));
    $storedAnswer = $captcha->getAnswer();

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

    $question = $captcha->generate();

    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode([
            'success'      => false,
            'message'      => 'Проверьте правильность заполнения полей',
            'errors'       => $errors,
            'captcha'      => $question
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
    . "Content-Type: text/plain; charset=utf-8\r\n";

    $isSent = @mail($email, $subject, $body, $headers);

    if ($isSent) {
        echo json_encode([
            'success' => true,
            'message' => 'Ваше обращение успешно отправлено! Мы свяжемся с вами в ближайшее время.',
            'captcha' => $question
        ]);
    } else {
        http_response_code(502);
        echo json_encode([
            'success' => false,
            'message' => 'Ошибка при отправке письма. Попробуйте позже или свяжитесь с нами другим способом.',
            'captcha' => $question
        ]);
    }
    exit();
}

http_response_code(400);
echo json_encode([
    'success' => false,
    'message' => 'Неизвестное действие'
]);
