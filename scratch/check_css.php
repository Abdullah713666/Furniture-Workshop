<?php
$css_file = 'css/style.css';
if (file_exists($css_file)) {
    echo "File exists. Size: " . filesize($css_file) . " bytes.\n";
    echo "First 100 bytes: " . substr(file_get_contents($css_file), 0, 100) . "\n";
    echo "Last 100 bytes: " . substr(file_get_contents($css_file), -100) . "\n";
} else {
    echo "File does not exist at $css_file\n";
}
?>
