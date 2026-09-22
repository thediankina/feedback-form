<?php

namespace app\helpers;

class HtmlHelper
{
    public static function sanitize(string $value): string
    {
        return htmlspecialchars(trim($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
