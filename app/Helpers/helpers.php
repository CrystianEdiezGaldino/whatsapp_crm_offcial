<?php

if (!function_exists('feedback')) {
    function feedback()
    {
        return new \App\Helpers\FeedbackHelper();
    }
}
