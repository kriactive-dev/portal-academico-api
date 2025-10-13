<?php

namespace App;

class WhatsAppHelper
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public static function formatHeaderText($text)
    {
        $maxLength = 24;
        if (mb_strlen($text) > $maxLength) {
            // Corta e adiciona reticências
            return mb_substr($text, 0, $maxLength - 3) . '...';
        }
        return $text;
    }

    public static function formatRowTitle($text)
    {
        $maxLength = 24;
        if (mb_strlen($text) > $maxLength) {
            return mb_substr($text, 0, $maxLength - 3) . '...';
        }
        return $text;
    }

    public static function formatButtonTitle($text)
    {
        $maxLength = 24;
        if (mb_strlen($text) > $maxLength) {
            return mb_substr($text, 0, $maxLength - 3) . '...';
        }
        return $text;
    }
}
