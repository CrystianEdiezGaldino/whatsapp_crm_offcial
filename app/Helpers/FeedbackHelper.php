<?php

namespace App\Helpers;

class FeedbackHelper
{
    public static function success(string $message)
    {
        session()->flash('success', $message);
    }

    public static function error(string $message)
    {
        session()->flash('error', $message);
    }

    public static function warning(string $message)
    {
        session()->flash('warning', $message);
    }

    public static function info(string $message)
    {
        session()->flash('info', $message);
    }
}
