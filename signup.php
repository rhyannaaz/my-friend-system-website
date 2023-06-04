<?php
    include("functions/header.php");
    include("functions/functions.php");
    $errorMsg = array();
?>

    <form method="POST" action="<?php echo $_SERVER['PHP_SELF'] ?>">
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
                    <p>Profile Name</p>
                </td>
                <td>
                    <p><input type="text" name="profileName" value="<?php echo isset($_POST["profileName"]) ? $_POST["profileName"] : ''; ?>"></p>
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
                    <p>Confirm Password</p>
                </td>
                <td>
                    <p><input type="password" name="cPassword"></p>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="reset" name="resetForm" class="resetButton" value="Clear">
                </td>
                <td>
                    <input type="submit" name="submitForm" class="submitButton" value="Register">
                </td>
            </tr>
        </table>
    </form>

<?php
    if(isset($_POST['email'])) $userEmail = sanitiseInput($_POST['email']);
    if(isset($_POST['profileName'])) $userProfileName = sanitiseInput($_POST['profileName']);
    if(isset($_POST['password'])) $userPassword = sanitiseInput($_POST['password']);
    if(isset($_POST['cPassword'])) $userCPassword = sanitiseInput($_POST['cPassword']);

    if(isset($_POST['submitForm'])) {
        $state = "error";
        require_once("functions/settings.php");

        // input data validation
        // email validation
        if($userEmail == "") {                                                  // check if input is empty
            array_push($errorMsg, "Email", "Required to be filled out");
        } else if(checkEmailExists($conn, $userEmail)) {                        // check if email arleady exists in database
            array_push($errorMsg, "Email", "Email already registered. Try a different one");
        }

        if(strlen($userEmail) > 50) {                                           // check if email is greater than 50 characters
            array_push($errorMsg, "Email", "Characters amount exceeded. Must be less than 50 charaters");
        }

        // profile name validation
        if($userProfileName == "") {                                            // check if input is empty
            array_push($errorMsg, "Profile Name", "Required to be filled out");
        } else if(!preg_match("/^([A-Za-z][\s]*){1,20}$/", $userProfileName)) { // check if profile name contains only letters
            if(strlen($userProfileName) > 30) {
                array_push($errorMsg, "Profile Name", "Characters amount exceeded. Must be less than 30 charaters");
            } else {
                array_push($errorMsg, "Profile Name", "Cannot contain number or any non-alpha characters");
            }
        }

        // password validation
        if ($userPassword == "") {                                              // check if input is empty
            array_push($errorMsg, "Password", "Required to be filled out");
        } else if(!preg_match("/^(\w*){1,20}$/", $userPassword)) {
            if(strlen($userPassword) > 20) {                                    // check if password is greater than 20 chaeacters
                array_push($errorMsg, "Password", "Characters amount exceeded. Must be less than 20 charaters");
            } else {
                array_push($errorMsg, "Password", "Cannot contain any non-alphanumeric characters");
            }
        }

        // password and confirm password validation
        if(strcmp($userCPassword, $userPassword)) {
            array_push($errorMsg, "Password", "Does not match. Try again");
        }

        if($errorMsg == array()) {
            require_once("functions/settings.php");

            if ($conn) {
                // insert values to the 'friends' table
                $query = "INSERT INTO friends
                (friend_email, password, profile_name, date_started)
                VALUES ('$userEmail', '$userPassword', '$userProfileName', '$currDate')
                ";
                $insert = mysqli_query($conn, $query);

                if ($insert) {
                    // change state after data successfully inserted
                    $state = "success";
                    $_SESSION['login'] = "success";
                    $_SESSION['name'] = $userProfileName;
                    $_SESSION['noOfFriends'] = 0;
                    header("Location: friendadd.php");                          // redirect to Add Friends page
                } else {
                    array_push($errorMsg, "Failed", "Cannot enter your last request. Please try again");
                }
            }
            mysqli_close($conn);
        }
        echo displayMessage($errorMsg, $state);
    }

?>

        </nav>
    </nav>
</body>
</html>
