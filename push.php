<?php
// Define source and destination paths
$source = __DIR__; // Current directory (where this script is placed)
$destination = '/home/benax/snowhotel.benax.rw';
// Ensure destination directory exists
if (!file_exists($destination)) {
    mkdir($destination, 0755, true); // Create the directory with proper permissions
}
// Use `find` to exclude specific directories and `cp` to copy
$excluded = ['well-known', 'cgi-bin'];
$excludePatterns = array_map(function ($dir) {
    return "! -path './$dir*'";
}, $excluded);
$excludeCommand = implode(' ', $excludePatterns);
// Build the full command
$command = "find . -type f $excludeCommand -exec cp --parents -u {} $destination \; 2>&1";
$output = shell_exec($command);
// Determine success or failure
$success = is_dir($destination) && count(scandir($destination)) > 2;
// Log the output for debugging
file_put_contents('copy_log.txt', $output ?: 'No output from the cp command.');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Push Operation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            overflow: hidden;
        }
        .container {
            max-width: 600px;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .success {
            color: #28a745;
        }
        .error {
            color: #dc3545;
        }
        button {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #0056b3;
        }
        pre {
            text-align: left;
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            overflow-x: auto;
        }
        .loading {
            display: none;
            margin-top: 20px;
        }
        .loading.visible {
            display: block;
        }
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #007bff;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }
        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Push Operation</h1>
        <?php if ($success): ?>
            <p class="success">Files copied and updated successfully to <strong><?= htmlspecialchars($destination) ?></strong>.</p>
        <?php else: ?>
            <p class="error">Failed to copy files. Please check permissions and paths.</p>
        <?php endif; ?>
        <h3>Command Output</h3>
        <pre><?= htmlspecialchars($output ?: 'No output from the command.') ?></pre>
        <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" id="pushForm">
            <button type="submit" onclick="showLoading()">Push Again</button>
        </form>
        <div class="loading" id="loadingIndicator">
            <div class="spinner"></div>
            <p>Processing, please wait...</p>
        </div>
    </div>
    <script>
        function showLoading() {
            const loadingIndicator = document.getElementById('loadingIndicator');
            loadingIndicator.classList.add('visible');
        }
    </script>
</body>
</html>
