<!DOCTYPE html>
<html>
<body>

<?php
echo 'date_default_timezone_set: ' . date_default_timezone_get() . '<br />';
echo "Today is " . date("Y/m/d") . "<br>";
echo "The time is " . date("h:i:s") . "<br>";
echo ($_SERVER['SERVER_ADDR']);
?>

</body>
</html>