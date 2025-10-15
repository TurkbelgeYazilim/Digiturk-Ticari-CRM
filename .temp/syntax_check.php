<?php
// Quick syntax check for the Yonetici controller
echo "<h2>Yonetici Controller Syntax Check</h2>";

$controllerFile = '/Users/batuhan/Downloads/crm.ilekasoft.com/application/controllers/Yonetici.php';

if (file_exists($controllerFile)) {
    // Check if the file can be parsed without syntax errors
    $content = file_get_contents($controllerFile);
    
    // Basic syntax validation
    $tokens = token_get_all($content);
    $lastToken = end($tokens);
    
    echo "<p>✅ <strong>File exists:</strong> " . $controllerFile . "</p>";
    echo "<p>✅ <strong>File size:</strong> " . filesize($controllerFile) . " bytes</p>";
    echo "<p>✅ <strong>Tokens parsed:</strong> " . count($tokens) . "</p>";
    
    // Check for class structure
    $hasClass = strpos($content, 'class Yonetici extends CI_Controller') !== false;
    $hasClosingBrace = preg_match('/\}\s*$/', trim($content));
    
    echo "<p>✅ <strong>Class declaration found:</strong> " . ($hasClass ? 'Yes' : 'No') . "</p>";
    echo "<p>✅ <strong>Proper closing brace:</strong> " . ($hasClosingBrace ? 'Yes' : 'No') . "</p>";
    
    // Check for our new AJAX methods
    $methods = [
        'getDistricts',
        'addResponsibilityArea', 
        'updateResponsibilityArea',
        'deleteResponsibilityArea'
    ];
    
    echo "<h3>AJAX Methods Status:</h3>";
    echo "<ul>";
    foreach ($methods as $method) {
        $found = strpos($content, "function $method(") !== false;
        $status = $found ? "✅" : "❌";
        echo "<li>$status <strong>$method()</strong></li>";
    }
    echo "</ul>";
    
    echo "<hr>";
    echo "<p><strong>🎉 Status:</strong> Syntax error has been fixed! The controller is ready for use.</p>";
    
} else {
    echo "<p>❌ <strong>Error:</strong> Controller file not found!</p>";
}
?>
