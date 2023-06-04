<?php
    include("functions/header.php");
    include("functions/functions.php");
?>

    <form method="POST" action="<?php echo $_SERVER['PHP_SELF']?>">
      <br>
        <table>
            <tr>
                <td>
                    <p>Email</p>
                </td>
                <td>
                    <p><input type="email" name="email" value="<?php echo isset($_POST["email"]) ? $_POST["email"] : ''; ?>"></p>
                </td>
            </tr>
            <tr>
                <td>
                    <p>Password</p>
                </td>
                <td>
                    <p><input type="password" name="password"></p>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="reset" name="resetForm" class="resetButton" value="Clear">
                </td>
                <td>
                    <input type="submit" name="submitForm" class="submitButton" value="Login">
                </td>
            </tr>
        </table>
    </form>

<?php
    $errorMsg = array();
    $state = "error";
    if(isset($_POST['email'])) $userEmail = sanitiseInput($_POST['email']);
    if(isset($_POST['password'])) $userPassword = sanitiseInput($_POST['password']);

    if(isset($_POST['submitForm'])) {
        include_once("functions/settings.php");
        if(verifyLoginInfo($conn, $userEmail, $userPassword)) {                 // verifies that 'friend_email' and 'password' exists within database
            $state = "success";
            $_SESSION['login'] = "success";
            header("Location: friendlist.php");                                 // redirect to Friend List page
        } else {
            array_push($errorMsg, "Login", "Incorrect login details");
        }
        echo displayMessage($errorMsg, $state);
    }

?>

        </nav>
    </nav>
</body>
</html>
