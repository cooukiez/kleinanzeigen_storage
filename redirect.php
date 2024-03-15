<?php
session_name("kleinanzeigen");
session_start();

// check if the previous page is set in the session
if(isset($_SESSION['previous_page'])) {
    // redirect based on the previous page
    $previous_page = $_SESSION['previous_page'];
    header("Location: $previous_page");
    exit;
} else {
    // default redirection if no previous page is set
    header("Location: index.php");
    exit;
}
?>
