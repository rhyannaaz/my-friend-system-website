<?php
if (session_status() == PHP_SESSION_NONE) session_start();
session_unset();                // free all session variables
session_destroy();              // destroy all of the data associated with the current session
header("location: index.php");  // redirect to home page
exit();
?>
