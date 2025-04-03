<?php
$base_path = __DIR__;
echo "<h1>Directory Structure Check</h1>";
echo "<pre>";
echo "Base path: " . $base_path . "\n\n";

// Function to list directory contents
function list_directory($path, $indent = '') {
    $files = scandir($path);
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            $full_path = $path . DIRECTORY_SEPARATOR . $file;
            echo $indent . ($indent ? '└── ' : '') . $file;
            if (is_dir($full_path)) {
                echo " (directory)\n";
                list_directory($full_path, $indent . '    ');
            } else {
                echo " (file)\n";
            }
        }
    }
}

// List contents
list_directory($base_path);

// Check permissions
echo "\nPermissions Check:\n";
echo "Current script: " . substr(sprintf('%o', fileperms(__FILE__)), -4) . "\n";
echo "Parent directory: " . substr(sprintf('%o', fileperms(dirname(__FILE__))), -4) . "\n";

// Check PHP configuration
echo "\nPHP Configuration:\n";
echo "document_root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "script_filename: " . $_SERVER['SCRIPT_FILENAME'] . "\n";
?> 