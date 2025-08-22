<?php
echo "<h2>Apache Module Test</h2>";
echo "<p>mod_rewrite enabled: " . (in_array('mod_rewrite', apache_get_modules()) ? 'YES' : 'NO') . "</p>";
echo "<p>Current URL: " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p>Script Name: " . $_SERVER['SCRIPT_NAME'] . "</p>";
echo "<p>Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
?>
