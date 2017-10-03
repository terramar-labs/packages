<?php

/**
 * Copy a file, or recursively copy a folder and its contents
 *
 * @author      Aidan Lister <aidan@php.net>
 * @version     1.0.1
 * @see         http://aidanlister.com/2004/04/recursively-copying-directories-in-php/
 *
 * @param       string $source      Source path
 * @param       string $dest        Destination path
 * @param       int    $permissions New folder creation permissions
 *
 * @return      bool     Returns true on success, false on failure
 */
function xcopy($source, $dest, $permissions = 0755)
{
    // Check for symlinks
    if (is_link($source)) {
        return symlink(readlink($source), $dest);
    }

    // Simple copy for a file
    if (is_file($source)) {
        return copy($source, $dest);
    }

    // Make destination directory
    if ( ! is_dir($dest)) {
        mkdir($dest, $permissions);
    }

    // Loop through the folder
    $dir = dir($source);
    while (false !== $entry = $dir->read()) {
        // Skip pointers
        if ($entry == '.' || $entry == '..') {
            continue;
        }

        // Deep copy directories
        xcopy("$source/$entry", "$dest/$entry", $permissions);
    }

    // Clean up
    $dir->close();

    return true;
}

copy('vendor/twbs/bootstrap/dist/css/bootstrap.min.css', 'web/css/bootstrap.min.css');
copy('vendor/twbs/bootstrap/dist/js/bootstrap.min.js', 'web/js/bootstrap.min.js');
copy('vendor/components/jquery/jquery.min.js', 'web/js/jquery.min.js');
copy('vendor/datatables/datatables/media/js/jquery.dataTables.min.js', 'web/js/jquery.dataTables.min.js');
copy('vendor/datatables/datatables/media/css/jquery.dataTables.min.css', 'web/css/jquery.dataTables.min.css');
xcopy('vendor/datatables/datatables/media/images', 'web/images');
copy('vendor/fortawesome/font-awesome/css/font-awesome.min.css', 'web/css/font-awesome.min.css');
xcopy('vendor/fortawesome/font-awesome/fonts', 'web/fonts');
