<?php
    require_once("config.php");
    if(!isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == false){
    header("Location: signin.php");
    }
    
    if(!$_SESSION['userid']) 
    {
        $db = get_mysqli_connection();
        $stmt = $db->prepare("SELECT * FROM UserProfile WHERE Email = ?");
        $stmt->bind_param("s", $_SESSION['email']);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $_SESSION['userid'] = $data['UserID'];
        $stmt->close();
    }
?>

<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="style.css">
    <title>Dashboard | <?= $PROJECT_NAME?></title>

</head>
<body>

    <div class>
        <a href="signin.php"><img src="black_gold_logo.png" id="logo"/></a>
        <?php require_once("nav.php");?>
    </div>

    <h2>
        <?php
            $fname = $_SESSION['fname'];
            $lname = $_SESSION['lname'];
            echo "Welcome, $fname $lname!";
        ?>
    </h2>
    <hr>
    <div id="createSessionDiv">
        <?php
            $insert_form = new PhpFormBuilder();
            $insert_form->set_att("method", "POST");
            $insert_form->add_input("", array(
                "type" => "file",
                "value" => "Submit Core Image"
            ), "createbtn");
            $insert_form->build_form();
        ?>
    </div>
    <hr>
    <h2>My Core Sessions</h2>
    <div class="sessionContainer">
        <div class="mySessions">
            <div class="inSession"> 
                <?php
                $db = get_mysqli_connection();
                $query = $db->prepare("SELECT SessionID AS 'Managed Sessions\t', Percentage AS 'Percentage' FROM Joins WHERE UserID = ? AND SessionID IN (SELECT SessionID FROM PaypoolSession WHERE UserID = ?)");
                $query->bind_param('ss', $_SESSION['userid'], $_SESSION['userid'] );
                $query->execute();
                $result = $query->get_result();
                $rows = $result->fetch_all(MYSQLI_ASSOC);
                echo makeTable($rows);
                ?>
            </div>

            <div class="inSession">
                <?php
                $db = get_mysqli_connection();
                $query = $db->prepare("SELECT SessionID AS 'Joined Sessions\t', Percentage FROM Joins WHERE UserID = ? AND SessionID NOT IN (SELECT SessionID FROM PaypoolSession WHERE UserID = ?)");
                $query->bind_param('ss', $_SESSION['userid'], $_SESSION['userid'] );
                $query->execute();
                $result = $query->get_result();
                $rows = $result->fetch_all(MYSQLI_ASSOC);
                echo makeTable($rows);
                $query->close();
                ?>
            </div>
        </div>

        <div id="enterSession">
            <?php 
                //php to display certain users 
                $session_form = new phpFormBuilder();
                $session_form->set_att("method","POST");
                $session_form->add_input("Session Number:", array(
                    "type" => "text",
                    "placeholder" => "Enter a session ID to enter"
                ), "enter_session");
                $session_form->add_input("Session", array(
                    "type" => "submit",
                    "value" => "Enter Session"
                ), "sessionbtn");
                $session_form->build_form();

                if(!empty($_POST['enter_session'])) {
                    $db = get_mysqli_connection();
                    $query = $db->prepare("SELECT UserID FROM Joins WHERE SessionID = ? ");
                    $query->bind_param('s', $_POST['enter_session']);
                    $query->execute();
                    $result = $query->get_result();
                    $rows = $result->fetch_all(MYSQLI_ASSOC);
                    $index_count = 0;
                    $found = false;
                    while($index_count < count($rows) && !$found) {
                        if($rows[$index_count]['UserID'] == $_SESSION['userid']) {
                            $found = true;
                        }
                        $index_count++;
                    }
                    $query->close();
                    //if user is in session then check if they manage it
                    if($found) {
                        $db = get_mysqli_connection();
                        // Check that User Manages Session Entered 
                        $query = $db->prepare("SELECT Fname, Lname, PaypoolSession.UserID FROM PaypoolSession JOIN UserProfile ON(UserProfile.UserID = PaypoolSession.UserID) WHERE SessionID = ? ");
                        $query->bind_param('s', $_POST['enter_session']);
                        $query->execute();
                        $result = $query->get_result();
                        $rows = $result->fetch_all(MYSQLI_ASSOC);
                        
                        $_SESSION['manager_first_name'] = $rows[0]['Fname'];
                        $_SESSION['manager_last_name'] = $rows[0]['Lname'];
                        
                        //if user is manager then redirect to manager session page
                        if($rows[0]['UserID'] == $_SESSION['userid']) {
                            $_SESSION['SessionID'] = $_POST['enter_session'];
                            header("Location: manager_session.php");
                        }
                        else {
                            $_SESSION['SessionID'] = $_POST['enter_session'];
                            header("Location: non_manager_session.php");
                        }
                    }
                    else {
                        echo "You are not in session " . $_POST['enter_session'];
                    }    
                }
            ?>
        </div>
    </div>
</body>

</html>
