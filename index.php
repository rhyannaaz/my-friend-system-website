<?php
include("functions/header.php");
include("functions/functions.php");
$data = array();
?>

<article class="briefintro">
  <h2 class="welcome">Welcome!</h2>
  <br>
  <p>Name: Rhyanna<br>
    <br>I declare that this assignment is my individual work. I have not worked
    collaboratively nor have I copied from any other student’s work or from any other source.
  </p>
  <br><br><br>
</article>

<img src="images/friends_image1.png" alt="My Friends Background Image" class="friendsimg">

<?php
    require_once("functions/settings.php");
    if ($conn) {
        require_once("functions/settings.php");
        createTables($conn);
        echo checkTabelContents($conn);
    } else {
        $state = "error";
        array_push($data, "No Connection to Database", "Please check that you have valid credential");
        echo displayMessage($data, $state);
    }

    mysqli_close($conn);
?>

        </nav>
    </nav>
</body>
</html>
