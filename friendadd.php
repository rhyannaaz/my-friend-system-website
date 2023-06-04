<?php
    include("functions/header.php");
    include("functions/functions.php");

    if(!isset($_SESSION['login'])) {
        header("Location: index.php");
        exit();
    }

    echo "
    <article class='noOfFriends'>
    <p>Total number of friends is ".$_SESSION['noOfFriends']."</p>
    </article>
    ";
    //if pageNum doesn't exist, set var pageNum as a GET method
    //else set it as 1
    if(isset($_GET['pageNum'])) {
        $pageNum = $_GET['pageNum'];
    } else {
        $pageNum = 1;
    }

    require_once("functions/settings.php");
    $numFriendsPerPage = 5;
    $offSet = ($pageNum-1) * $numFriendsPerPage;
    $totalUser = obtainTotalNumberofUsers($conn);
    //round totalPage as a whole number
    $totalPage = ceil(($totalUser) / $numFriendsPerPage);

        if ($pageNum < 2) {
            echo "<a class='nextButton' href='?pageNum=".($pageNum+1)."'> Next </a>";
        } elseif ($pageNum > $totalPage-1) {
            echo "<a class='prevButton' href='?pageNum=".($pageNum-1)."'> Previous </a>";
        } else {
            echo "<a class='prevButton' href='?pageNum=".($pageNum-1)."'> Previous </a>";
            echo "<a class='nextButton' href='?pageNum=".($pageNum+1)."'> Next </a>";
        }

?>
        <form method="POST" action="<?php echo $_SERVER['PHP_SELF'] ?>">
            <table class="friendList">
               <?php
                require_once("functions/settings.php");
                displayExistingUsers($conn, $offSet, $numFriendsPerPage);
                ?>
            </table>
        </form>

      </nav>
  </nav>
</body>
</html>
