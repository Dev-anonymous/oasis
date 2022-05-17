<?php

use App\Models\Compte;
use App\Models\Transaction;

function getMimeType($filename)
{
    if (!file_exists($filename)) return '';
    $mimetype = mime_content_type($filename);
    if (strpos($mimetype, 'image') !== false) {
        $mimetype = 'image';
    } else if (strpos($mimetype, 'audio') !== false) {
        $mimetype = 'audio';
    } else if (strpos($mimetype, 'video') !== false) {
        $mimetype = 'video';
    }
    return $mimetype;
}

function numeroCompte()
{
    $compte = Compte::all();
    $n = $compte->count() + 1;

    if ($n < 10) {
        $c = "C00$n";
    } else if ($n >= 10 and $n < 100) {
        $c = "C0$n";
    } else {
        $c = "C$n";
    }
    $c = $c . '.' . makeRand() . '.' . makeRand();
    return $c;
}

function makeRand($max = 5)
{
    $max = (int) $max;
    if (!$max or $max <= 0) {
        return 0;
    }

    $num = '';
    while ($max > 0) {
        $max--;
        $num .= rand(1, 9);
    }
    return $num;
}

function trans_id()
{
    $tr = Transaction::where('type', 'transfert')->get();
    $n = $tr->count() + 1;

    if ($n < 10) {
        $c = "TRANS-00$n";
    } else if ($n >= 10 and $n < 100) {
        $c = "TRANS-0$n";
    } else {
        $c = "TRANS-$n";
    }
    $c = $c . '.' . makeRand() . '.' . makeRand();
    return $c;
}
