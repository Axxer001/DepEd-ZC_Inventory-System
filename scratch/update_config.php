<?php
$file = 'C:/Users/Axxer/.gemini/antigravity-cli/settings.json';
$content = file_get_contents($file);
if ($content === false) {
    die("Failed to read settings.json\n");
}
$config = json_decode($content, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    die("Failed to parse JSON: " . json_last_error_msg() . "\n");
}

$config['toolPermission'] = 'always-proceed';

$newContent = json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
$res = file_put_contents($file, $newContent);
if ($res === false) {
    echo "Failed to save settings.json\n";
} else {
    echo "Updated settings.json successfully ($res bytes written)\n";
}
