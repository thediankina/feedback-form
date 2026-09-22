<?php

namespace app;

class Captcha
{
    public function generate(): string
    {
        $a = random_int(1, 9);
        $b = random_int(1, 9);

        $ops = ['+', '-', '×'];
        $op = $ops[array_rand($ops)];

        $question = "$a $op $b =";

        $answer = match($op) {
            '+' => $a + $b,
            '-' => ($a < $b) ? ($b - $a) : ($a - $b),
            '×' => $a * $b,
        };

        $this->setAnswer($answer);

        return $question;
    }

    private function setAnswer(string $value): void
    {
        $_SESSION['captchaAnswer'] = $value;
    }

    public function getAnswer(): ?string
    {
        return $_SESSION['captchaAnswer'] ?? null;
    }
}
