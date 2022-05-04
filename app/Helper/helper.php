<?php


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
