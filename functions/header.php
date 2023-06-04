<?php
    if (session_status() == PHP_SESSION_NONE) session_start();

    // obtain current page name and remove file extension
    $currentPage = basename($_SERVER['PHP_SELF']);
    $currentPage = str_replace('.php', '', $currentPage);
    $currentPage = ucfirst($currentPage);

    // set default time zone to Melbourne
    date_default_timezone_set('Australia/Melbourne');
    // set date to current date
    $currentDate = date("Y/m/d");

    if($currentPage == "Index") {
        $currentPage = "Home";
    } else if($currentPage == "Friendadd") {
        $currentPage = $_SESSION['name']."'s Add Friend";
    } elseif ($currentPage == "Friendlist") {
        $currentPage = $_SESSION['name']."'s Friend List";
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="description" content="COS30020 Assignment 2">
    <meta name="keywords" content="HTML, CSS, JavaScript">
    <meta name="author" content="Rhyanna">
    <title>My Friends System</title>
    <link href="images/friends.png" rel="icon" type="image/gif" sizes="16x16" />
    <link rel="stylesheet" type="text/css" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css?family=Poppins:wght" rel="stylesheet"/>
  </head>

<body>
    <header>
        <h1>My Friends System</h1>
    </header>

    <nav class="navMenu">
      <ul>
        <li><a href="index.php" <?php echo(($currentPage == "Home")? : ""); ?>>Home</a></li>
        <?php
            // if user has logged in successfully, display the 'friendadd.php' and 'friendlist.php' links
            if (isset($_SESSION['login']) && $_SESSION['login']  == "success") {
                $_SESSION['ID'] = "";
        ?>
            <li><a href="friendadd.php" <?php echo(($currentPage == $_SESSION['name']."'s Add Friend")? : ""); ?>>Add Friend</a></li>
            <li><a href="friendlist.php" <?php echo(($currentPage == $_SESSION['name']."'s Friend List")? : ""); ?>>Friend List</a></li>
            <li><a href="logout.php" <?php echo(($currentPage == "Logout")? : ""); ?>>Logout</a></li>
        <?php
            // else, display the 'signup.php' and 'friendlist.php' links
            } else {
        ?>
            <li><a href="signup.php" <?php echo(($currentPage == "Signup")? : ""); ?>>Sign Up</a></li>
            <li><a href="login.php" <?php echo(($currentPage == "Login")? : ""); ?>>Login</a></li>
        <?php
            }
        ?>
        <li><a href="about.php" <?php echo(($currentPage == "About")? : ""); ?>>About</a></li>
      </ul>
    </nav>

    <h2 class="pageTitle"><?php echo $currentPage; ?> Page</h2>
