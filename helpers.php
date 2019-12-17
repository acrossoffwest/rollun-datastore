<?php

if (!function_exists('dd')) {
    function dd($outputContent)
    {
        if (is_string($outputContent)) {
            echo $outputContent."\n";
            return die();
        }
        var_dump($outputContent);
        die();
    }
}