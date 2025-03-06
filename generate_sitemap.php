<?php
/**
 * Recursively scans a directory and generates a textual sitemap.
 *
 * @param string $dir Directory to scan.
 * @param int $depth Current depth for indentation.
 * @return string Sitemap content.
 */
function generateSitemap($dir, $depth = 0) {
    $sitemap = "";
    $indent = str_repeat("  ", $depth); // Indentation based on depth
    $items = scandir($dir);
    $files = [];
    $directories = [];
    // Separate files and directories
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        if (is_dir($path)) {
            $directories[] = $item;
        } else {
            $files[] = $item;
        }
    }
    // Add root-level files first
    foreach ($files as $file) {
        $sitemap .= $indent."└── ".$file."\n";
    }
    // Add directories and their contents
    foreach ($directories as $directory) {
        $sitemap .= $indent."/".$directory."\n";
        $sitemap .= generateSitemap($dir . DIRECTORY_SEPARATOR . $directory, $depth + 1);
    }
    return $sitemap;
}
// Define the root directory for the sitemap
$rootDir = __DIR__;
$sitemapContent = "/project\n" . generateSitemap($rootDir, 1); // Start depth at 1 for better alignment
// Save the sitemap to a file
$file = $rootDir . DIRECTORY_SEPARATOR . "sitemap.txt";
if (file_put_contents($file, $sitemapContent)) {
    echo "Sitemap generated successfully!<br>";
    echo "View it here: <a href='sitemap.txt' target='_blank'>sitemap.txt</a>";
} else {
    echo "Failed to generate sitemap.";
}
?>
