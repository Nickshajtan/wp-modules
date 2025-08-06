<?php

function deleteRecursive(string $path): void {
    if (!file_exists($path) && !is_dir($path)) {
        return;
    }

    if (is_file($path) || is_link($path)) {
        @unlink($path);
    } else {
        foreach (scandir($path) as $item) {
            if ($item === '.' || $item === '..') continue;
            deleteRecursive($path . DIRECTORY_SEPARATOR . $item);
        }
        @rmdir($path);
    }
}

deleteRecursive(__DIR__ . '/_dependencies/vendor');
