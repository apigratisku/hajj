<?php
echo "exec: " . (function_exists('exec') ? 'Aktif' : 'Dinonaktifkan') . "<br>";
echo "shell_exec: " . (function_exists('shell_exec') ? 'Aktif' : 'Dinonaktifkan') . "<br>";
echo "passthru: " . (function_exists('passthru') ? 'Aktif' : 'Dinonaktifkan') . "<br>";
?>