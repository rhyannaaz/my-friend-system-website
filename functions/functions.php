<?php

    // remove any illegal character from the user input
    function sanitiseInput($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    // display alert message
    function displayMessage($errorMsg, $condition) {
        $noOfErrorMsg = count($errorMsg);
        $fieldName = array();
        $description = array();
        $data = "";

        // break $errorMsg into 2 separate data ($fieldName and $description)

        //field name
        for($i=0; $i < $noOfErrorMsg; $i+=2) {
            array_push($fieldName, $errorMsg[$i]);
        }

        // description
        for($i=1; $i < $noOfErrorMsg; $i+=2) {
            array_push($description, $errorMsg[$i]);
        }

        // assemble the message for display
        for($i=0; $i < count($description); $i++) {
            $data .= "
            <p>*".$fieldName[$i].":
            ".$description[$i].".</p>
            ";
        }

        // determine if the condition is an error, reminder, success, or none.
        switch ($condition) {
            case 'error':
                $condition = "alertFail";
                break;

            case 'reminder':
                $condition = "alertReminder";
                break;

            case 'success':
                $condition = "alertSuccess";
                break;

            default:
                $condition = "none";
                break;
        }

        $displayMessage = "
        <nav class='alertMessage' id='$condition'>
            $data
        </nav>
        ";
        return $displayMessage;
    }

    // INDEX PAGE FUNCTIONS
    // check if table contains any data
    function checkTabelContents($conn) {
        $condition = "error";
        $data = array();

        if(!$conn) {
            array_push($data, "Server", "Unable to connect to the database");
            return displayMessage($data, $condition);
        } else {
            $query = "SELECT * FROM myfriends WHERE 1";
            $result = mysqli_query($conn, $query);

            if($result) {
                if(mysqli_num_rows($result) == 0) {     // if tables are unpopulated
                    populateFriendsTable($conn);
                    populateMyFriendsTable($conn);
                    updateNumOfFriends($conn);
                    $condition = "success";
                    array_push($data, "Success", "Table 'friends' successfully created and populated");
                    array_push($data, "Success", "Table 'myfriends' successfully created and populated");
                    return displayMessage($data, $condition);
                } else {                                // if tables are populated
                    $condition = "reminder";
                    array_push($data, "Reminder", "Table 'friends' already created and populated");
                    array_push($data, "Reminder", "Table 'myfriends' already created and populated");
                    return displayMessage($data, $condition);
                }
            }
        }
    }

    // create 'friends' and 'myfriends' table if deos not already exists within database
    function createTables($conn) {
        $condition = "error";
        $errorMsg = array();

        if(!$conn) {
            array_push($errorMsg, "Server", "Unable to connect to the database");
            return displayMessage($errorMsg, $condition);
        } else {
            // create 'friends' table
            $query = "CREATE TABLE IF NOT EXISTS friends (
            friend_id int(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            friend_email varchar(50) NOT NULL,
            password varchar(20) NOT NULL,
            profile_name varchar(30) NOT NULL,
            date_started date NOT NULL,
            num_of_friends int(10) UNSIGNED NOT NULL
            );";
            mysqli_query($conn, $query);

            // create 'myfriends' table
            $query = "CREATE TABLE IF NOT EXISTS myfriends (
            friend_id1 int(10) UNSIGNED NOT NULL,
            friend_id2 int(10) UNSIGNED NOT NULL,
            FOREIGN KEY (friend_id1) REFERENCES friends(friend_id),
            FOREIGN KEY (friend_id2) REFERENCES friends(friend_id)
            );";
            mysqli_query($conn, $query);
        }
    }

    // LOG IN PAGE FUNCTIONS
    // check if login credentials exist within the ‘friends’ table
    function verifyLoginInfo($conn, $userEmail, $userPassword) {
        $condition = "error";
        $errorMsg = array();
        if(!$conn) {
            array_push($errorMsg, "Server", "Unable to connect to the database");
            return displayMessage($errorMsg, $condition);
        } else {
            $query = "SELECT * FROM friends";
            $result = mysqli_query($conn, $query);

            if(!$result) {
                array_push($errorMsg, "SQL Query", "Unable to fetch requested query");
                return displayMessage($errorMsg, $condition);
            } else {
                while($row = mysqli_fetch_assoc($result)) {
                    // check if 'friend_email' and 'password' within the database matches user input
                    if($row["friend_email"] == $userEmail && $row["password"] == $userPassword) {
                        $_SESSION['name'] = $row['profile_name'];
                        $_SESSION['noOfFriends'] = $row['num_of_friends'];
                        return true;
                    }
                }
                return false;
            }
        }
    }

    // SIGN UP PAGE FUNCTIONS
    // check if email input already exists within the ‘friends’ table
    function checkEmailExists($conn, $userInput) {
        $condition = "error";
        $errorMsg = array();
        if(!$conn) {
            array_push($errorMsg, "Server", "Unable to connect to the database");
            return displayMessage($errorMsg, $condition);
        } else {
            $query = "SELECT friend_email FROM friends WHERE friend_email  = '$userInput'";
            $result = mysqli_query($conn, $query);

            while($row = mysqli_fetch_assoc($result)) {
                if($row["friend_email"] == $userInput) {
                    return true;
                }
            }
            return false;
        }
    }

    // obtain current session ID and assign it as a session variable
    function obtainCurrentSessionID($conn) {
        $condition = "error";
        $errorMsg = array();
        $query = "SELECT * FROM friends ORDER BY profile_name ASC";
        $result = mysqli_query($conn, $query);

        if(!$result) {
            array_push($errorMsg, "SQL Query", "Unable to fetch requested query");
            return displayMessage($errorMsg, $condition);
        } else {
            while ($row = mysqli_fetch_assoc($result)) {
                if ($_SESSION['name'] == $row['profile_name']) {
                    $_SESSION['ID'] = $row['friend_id'];
                }
            }
        }
    }

    // FRIEND ADD PAGE FUNCTIONS

    // obtain total number of users within the 'friends' table
    function obtainTotalNumberofUsers($conn) {
        $condition = "error";
        $errorMsg = array();
        $query = "SELECT COUNT(*) total FROM friends";
        $result = mysqli_query($conn, $query);

        if(!$result) {
            array_push($errorMsg, "SQL Query", "Unable to fetch requested query");
            return displayMessage($errorMsg, $condition);
        } else {
            $row = mysqli_fetch_assoc($result);
            return $row['total'];
        }
    }

    // FRIEND LIST PAGE FUNCTIONS
    // enable 'Unfriend' button functionality, assign button as friend ID
    function removeFriendButton($conn) {
        $condition = "error";
        $errorMsg = array();

        if(!$conn) {
            array_push($errorMsg, "Server", "Unable to connect to the database");
            return displayMessage($errorMsg, $condition);
        } else {
            $query = "SELECT * FROM myfriends WHERE friend_id1 = '".$_SESSION['ID']."'";
            $result = mysqli_query($conn, $query);

            if (!$result) {
                array_push($errorMsg, "SQL Query", "Unable to fetch requested query");
                return displayMessage($errorMsg, $condition);
            } else {
                while ($row = mysqli_fetch_assoc($result)) {
                    $myfriends_friendID2 = $row['friend_id2'];
                    /*set the buttons to FRND_(their id) and called removeFriend to get functions*/
                    echo((isset($_POST["FRND_$myfriends_friendID2"]))? removeFriend($conn, $myfriends_friendID2): "");
                }
                mysqli_free_result($result);
                mysqli_close($conn);
            }
        }
    }

    // enable remove friend function when user clicks 'Unfriend' button
    function removeFriend($conn, $userID) {
        $condition = "error";
        $errorMsg = array();

        if (!$conn) {
            array_push($errorMsg, "Server", "Unable to connect to the database");
            return displayMessage($errorMsg, $condition);
        } else {
            $query = "DELETE FROM myfriends WHERE friend_id1 = ".$_SESSION['ID']." AND friend_id2 = $userID";
            $result = mysqli_query($conn, $query);

            if (!$result) {
                array_push($errorMsg, "SQL Query", "Unable to fetch requested query");
                return displayMessage($errorMsg, $condition);
            } else {
                $condition = "success";
                $_SESSION['noOfFriends']--;
                $query = "UPDATE friends SET num_of_friends = '".$_SESSION['noOfFriends']."' WHERE friend_id  = '".$_SESSION['ID']."'";
                $result = mysqli_query($conn, $query);

                $query = "SELECT profile_name FROM friends WHERE friend_id  = '$userID'";
                $result = mysqli_query($conn, $query);

                while ($row = mysqli_fetch_assoc($result)) {
                    array_push($errorMsg, "Successfully Removed Friend", $row['profile_name']." is not your friend anymore. <br> <em>Changes can be seen after refreshing the page.</em>");
                    return displayMessage($errorMsg, $condition);
                }
            }
        }
    }

    // display user's friend list
    function displayFriendsList($conn, $offset, $numOfPage) {
        $condition = "error";
        $errorMsg = array();

        if(!$conn) {
            array_push($errorMsg, "Server", "Unable to connect to the database");
            return displayMessage($errorMsg, $condition);
        } else {
            $query = "SELECT * FROM friends ORDER BY profile_name ASC";
            $result = mysqli_query($conn, $query);

            if(!$result) {
                array_push($errorMsg, "SQL Query", "Unable to fetch requested query");
                return displayMessage($errorMsg, $condition);
            } else {
                obtainCurrentSessionID($conn);
                while ($row = mysqli_fetch_assoc($result)) {
                    $friends_friendID = $row['friend_id'];
                    $friends_name = $row['profile_name'];

                    $searchQuery = "SELECT * FROM myfriends WHERE friend_id1 = '".$_SESSION['ID']."' LIMIT $offset, $numOfPage";
                    $searchResult = mysqli_query($conn, $searchQuery);

                    while ($row = mysqli_fetch_assoc($searchResult)) {
                        $myfriends_friendID2 = $row['friend_id2'];
                        if ($myfriends_friendID2 == $friends_friendID) {
                            echo "
                            <tr>
                                <td>
                                    <p> $friends_name </p>
                                </td>
                                <td>
                                    <input type='submit' class='unfriendButton' name='FRND_".$friends_friendID."' value='Unfriend'>
                                </td>
                            </tr>
                            ";
                        }
                    }
                }
                mysqli_free_result($searchResult);
                mysqli_free_result($result);
                removeFriendButton($conn);
            }
        }
    }

    // FRIEND ADD PAGE FUNCTIONS
    // enable 'Add Friend' button functionality, assign button as friend ID
    function addFriendButton($conn) {
        $condition = "error";
        $errorMsg = array();

        if(!$conn) {
            array_push($errorMsg, "Server", "Unable to connect to the database");
            return displayMessage($errorMsg, $condition);
        } else {
            $query = "SELECT * FROM friends WHERE friend_id != '".$_SESSION['ID']."'";
            $result = mysqli_query($conn, $query);

            if (!$result) {
                array_push($errorMsg, "SQL Query", "Unable to fetch requested query");
                return displayMessage($errorMsg, $condition);
            } else {
                while ($row = mysqli_fetch_assoc($result)) {
                    $friends_userID = $row['friend_id'];
                    echo((isset($_POST["FRND_$friends_userID"]))? addFriend($conn, $friends_userID): "");
                }

                mysqli_free_result($result);
                mysqli_close($conn);
            }
        }
    }

    // enable add friend function when user clicks 'Add Friend' button
    function addFriend($conn, $userID) {
        $condition = "error";
        $errorMsg = array();

        if (!$conn) {
            array_push($errorMsg, "Server", "Unable to connect to the database");
            return displayMessage($errorMsg, $condition);
        } else {
            obtainCurrentSessionID($conn);
            $query = "INSERT INTO myfriends VALUES(".$_SESSION['ID'].", $userID)";
            $result = mysqli_query($conn, $query);

            if (!$result) {
                array_push($errorMsg, "SQL Query", "Unable to fetch requested query");
                return displayMessage($errorMsg, $condition);
            } else {
                $condition = "success";
                $_SESSION['noOfFriends']++;
                $query = "UPDATE friends SET num_of_friends = '".$_SESSION['noOfFriends']."' WHERE friend_id = '".$_SESSION['ID']."'";
                $result = mysqli_query($conn, $query);

                $query = "SELECT profile_name FROM friends WHERE friend_id  = '$userID'";
                $result = mysqli_query($conn, $query);

                while ($row = mysqli_fetch_assoc($result)) {
                    array_push($errorMsg, "Successfully Added Friend", $row['profile_name']." is your new friend.<br> <em>Changes can be seen after refreshing the page.</em>");
                    return displayMessage($errorMsg, $condition);
                }
            }
        }
    }

    // display all existing users within database except those who are friends with the logged in user
    function displayExistingUsers($conn, $offset, $numOfPage) {
        $condition = "error";
        $errorMsg = array();
        if (!$conn) {
            array_push($errorMsg, "Server", "Unable to connect to the database");
            return displayMessage($errorMsg, $condition);
        } else {
            obtainCurrentSessionID($conn);
            $query = "SELECT friend_id, profile_name FROM friends WHERE friend_id
            NOT IN (SELECT friend_id2 FROM myfriends WHERE friend_id1=".$_SESSION['ID'].")
            AND friend_id != ".$_SESSION['ID']." GROUP BY profile_name ASC LIMIT $offset, $numOfPage";
            $result = mysqli_query($conn, $query);

            if (!$result) {
                array_push($errorMsg, "SQL Query", "Unable to fetch requested query");
                return displayMessage($errorMsg, $condition);
            } else {
                while ($row = mysqli_fetch_assoc($result)) {
                    $friends_userName = $row['profile_name'];
                    $friends_userID = $row['friend_id'];

                    echo "
                        <tr>
                            <td>
                                <p>$friends_userName</p>
                            </td>
                            <td>
                                <input type='submit' class='addFriendButton' name='FRND_".$friends_userID."' value='Add Friend'>
                            </td>
                        </tr>
                        ";
                }
                mysqli_free_result($result);
                addFriendButton($conn);
            }
        }
    }

    // FUNCTIONS TO GENERATE DATA FOR 'FRIENDS' AND 'MYFRIENDS' TABLE
    // generate random Profile Name
    function randProfileName() {
        $fistNames = ["James", "Robert", "John", "Michael", "David", "William", "Richard", "Joseph", "Thomas", "Charles", "Mary", "Patricia", "Jennifer", "Linda", "Elizabeth",
                  "Barbara", "Susan", "Jessica", "Sarah", "Karen", "Lisa", "Nancy", "Ashley", "Andrew", "Joshua", "Timothy", "Stephanie", "Amanda", "Jacob", "Pamela", "Emma"];
        $lastNames = ["Smith", "Johnson", "Williams", "Brown", "Jones", "Garcia", "Miller", "Davis", "Rodriguez", "Martinez", "Hernandez", "Lopez", "Gonzalez", "Wilson", "Anderson",
                  "Thomas", "Taylor", "Moore", "Jackson", "Martin", "Lee", "Perez", "Thompson", "White", "Harris", "Sanchez", "Clark", "Ramirez", "Lewis", "Robinson", "Walker",
                  "Young", "Allen", "King", "Wright", "Scott", "Torres", "Nguyen", "Hill", "Flores", "Green", "Adams", "Nelson", "Baker", "Hall", "Rivera", "Campbell", "Mitchell", "Carter", "Roberts"];

        $randFirstName = rand(0, count($fistNames)-1);
        $randLastName = rand(0, count($lastNames)-1);
        return "$fistNames[$randFirstName] $lastNames[$randLastName]";
    }

    // generate random Email
    function randEmail() {
        $atEmail = ["@gmail", "@yahoo", "@hotmail"];
        $profileNames = randProfileName();

        $atEmailTemp = rand(0, count($atEmail)-1);
        $shorten = substr($profileNames, 0, 1);
        $shortenName = explode(" ", "$profileNames");
        $email = strtolower($shorten.$shortenName[1]);
        $email = "$email$atEmail[$atEmailTemp].com";
        return $email;
    }

    // generate random Password
    function randPassword() {
        $randNum = rand(8, 20);
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890*-!?";
        $pass = array();
        $len = strlen($chars) - 1;
        for ($i = 0; $i < $randNum; $i++) {
            $lenNum = rand(0, $len);
            $pass[] = $chars[$lenNum];
        }
        return implode($pass);
    }

    // populate 'friends' table
    function populateFriendsTable($conn) {
        $condition = "error";
        $errorMsg = array();
        $currentDate = date("Y/m/d");

        if (!$conn) {
            array_push($errorMsg, "Server", "Unable to connect to the database");
            return displayMessage($errorMsg, $condition);
        } else {
            for ($i=0; $i < 20; $i++) {
                $uProfileName = randProfileName();
                $uEmail = randEmail();
                $uPassword = randPass();

                $query = "INSERT INTO friends
                (friend_email, password, profile_name, date_started)
                VALUES ('$uEmail', '$uPassword', '$uProfileName', '$currentDate')
                ";
                mysqli_query($conn, $query);
            }
        }
    }

    // obtain total number of 'friends' from friends table
    function obtainTotalNumFriendsFromTable($conn) {
        $condition = "error";
        $errorMsg = array();

        if (!$conn) {
            array_push($errorMsg, "Server", "Unable to connect to the database");
            return displayMessage($errorMsg, $condition);
        } else {
            $query = "SELECT friend_id FROM friends";

            if($result = mysqli_query($conn, $query)) {
                $totalRecords = mysqli_num_rows($result);
                mysqli_free_result($result);
                return $totalRecords;
            }
        }
    }

    // populate 'myfriends' table
    function populateMyFriendsTable($conn) {
        $condition = "error";
        $errorMsg = array();

        if (!$conn) {
            array_push($errorMsg, "Server", "Unable to connect to the database");
            return displayMessage($errorMsg, $condition);
        } else {
            $myID = 1;
            for($j=0; $j < obtainTotalNumFriendsFromTable($conn); $j++) {

                $arrayMyFriends = array();
                $myFriendsTotal = rand(1, 12);

                for ($i=0; $i < $myFriendsTotal; $i++) {
                    $myFriendID = rand(1, obtainTotalNumFriendsFromTable($conn));

                    if ($myID != $myFriendID) {
                        array_push($arrayMyFriends, $myFriendID);
                    }
                }
                $arrayMyFriends = array_unique($arrayMyFriends);
                sort($arrayMyFriends);

                for ($i=0; $i < count($arrayMyFriends); $i++) {
                    $query = "INSERT INTO myfriends VALUES ($myID, $arrayMyFriends[$i])";
                    mysqli_query($conn, $query);
                }
                $myID++;
            }
        }
    }

    // update 'num_of_friends' in 'myfriends' table to 'friends' table
    function updateNumOfFriends($conn) {
        $condition = "error";
        $errorMsg = array();

        if (!$conn) {
            array_push($errorMsg, "Server", "Unable to connect to the database");
            return displayMessage($errorMsg, $condition);
        } else {
            $query = "UPDATE friends SET num_of_friends = (SELECT COUNT(*) FROM myfriends WHERE friend_id1 = friends.friend_id)";
            mysqli_query($conn, $query);
        }
    }
?>
