<?php

function nestedArray(string $key, $separator = '.'): array
{
    $array = [];
    $last = last(explode($separator, $key));
    Arr::set($array, $key, [$last]);
    return $array;
}

// "parent.parent.parent" => ["parent" => ["parent" => ["parent"] ] ]
