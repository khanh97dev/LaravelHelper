<?php

function isCsv($file)
{
    if (!($handle = fopen($file, 'r')))
        return false;

    $cols = null;
    $rows = 0;
    $valid = true;

    while (($r = fgetcsv($handle)) !== false) {
        $c = count($r);
        if ($cols === null)
            $cols = $c;
        elseif ($cols !== $c) {
            $valid = false;
            break;
        }
        $rows++;
    }

    fclose($handle);
    return $valid && $rows > 1;
}
