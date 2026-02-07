<?php
// Simple script to display the manual in a printer-friendly format
$manualContent = file_get_contents(__DIR__ . '/MANUAL.md');
// Basic Markdown to HTML conversion (very simple regex)
$html = htmlspecialchars($manualContent);
$html = preg_replace('/^# (.*)$/m', '<h1>$1</h1>', $html);
$html = preg_replace('/^## (.*)$/m', '<h2>$1</h2>', $html);
$html = preg_replace('/^### (.*)$/m', '<h3>$1</h3>', $html);
$html = preg_replace('/^- (.*)$/m', '<li>$1</li>', $html);
$html = preg_replace('/\n<li>/', "\n<ul>\n<li>", $html); // This is weak but okay for demo
// Clean up lists (this regex approach is fragile but sufficient for this specific file structure)
$html = str_replace("\n\n", "<br><br>", $html);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Manual de Usuario RDL</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; max-width: 800px; margin: 0 auto; padding: 20px; }
        h1, h2, h3 { color: #0056b3; }
        ul { margin-bottom: 1rem; }
        code { background: #f4f4f4; padding: 2px 5px; border-radius: 3px; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 5px; overflow-x: auto; }
        @media print {
            body { max-width: 100%; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: right; margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #28a745; color: white; border: none; cursor: pointer;">Imprimir a PDF</button>
    </div>
    <?= $html ?>
</body>
</html>
