<?php
    require_once("config.php");
    if(!isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == false){
    header("Location: signin.php");
    }
?>

<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title>Session <?= $_SESSION['SessionID']?> | <?= $PROJECT_NAME?></title>
        <link rel="stylesheet" href="style.css">
    </head>
<body>
    <div class>
        <a href="signin.php"><img src="payool_logo.png" id="logo"/></a>
        <?php require_once("nav.php"); ?>
    </div>
    <?php echo "<H2> You are the manager of this session </H2> <br>"; ?>
    <?php
        $add_user_form = new PhpFormBuilder();
        $add_user_form->set_att("method", "POST");
        $add_user_form->add_input("usermail", array(
            "type" => "submit",
            "value" => "Add by Email"
        ), "email_button");
        $add_user_form-> add_input("Friend's Email", array(
            "type" => "text",
            "placeholder" => "Enter email"
        ), "email_input");
        $add_user_form->build_form();
        
        if(!empty($_POST['email_input']) && isset($_POST['email_button'])) {
            $db = get_mysqli_connection();
            $query_first_name = $db->prepare("SELECT FName 
            FROM UserProfile 
            WHERE Email = ?");
            $query_first_name->bind_param('s',$_POST['email_input']);
            $query_first_name->execute();
            $result = $query_first_name->get_result();
            $first_name = $result->fetch_all(MYSQLI_ASSOC);
            if(!empty($first_name)) {
                $db = get_mysqli_connection();
                $already_in_session = $db->prepare("SELECT FName FROM Joins Join UserProfile ON(Joins.UserID = UserProfile.UserID) WHERE SessionID = ?");
                $already_in_session->bind_param('s', $_SESSION['SessionID']);
                $already_in_session->execute();
                $result2 =  $already_in_session->get_result();
                $row_names = $result2->fetch_all(MYSQLI_ASSOC);
                $found = false;
                $index_count = 0;
                //while statement to make add everyone listed
                while($index_count < count($row_names) && !$found) {
                    if($row_names[$index_count]['FName'] == $first_name[0]['FName']) {
                        $found = true;
                    }
                    $index_count++;
                }
                //check to see if user is in the session already
                if($found) {
                    echo "User is already in this paypool session <br>";
                }
                else {
                    $db = get_mysqli_connection();
                    $name = $db->prepare("SELECT UserID FROM UserProfile WHERE FName = ?");
                    $name->bind_param('s', $first_name[0]['FName']);
                    $name->execute();
                    $result = $name->get_result();
                    $user_id = $result->fetch_all(MYSQLI_ASSOC);
                    $name->close();
                    $query = $db->prepare("CALL AddUserToSession(?,?)");
                    $query->bind_param('ss', $user_id[0]['UserID'], $_SESSION['SessionID']);
                    $query->execute();
                    echo $first_name[0]['FName']. " was added successfully <br>";
                    $query->close();  
                }
            }   
            else {
                echo "User does not exist <br>";
            }
        }

        $db = get_mysqli_connection();
        $update_percentage = $db->prepare("CALL UpdatePercentages(?)");
        $update_percentage->bind_param('s', $_SESSION['SessionID']);
        $update_percentage->execute();
        $update_percentage->close();
    ?>

    <?php  
        $db = get_mysqli_connection();
        $query = $db->prepare("CALL GetMembers(?)");
        $query->bind_param('s', $_SESSION['SessionID']);
        if($query->execute()) {
            $result = $query->get_result();
            $rows = $result->fetch_all(MYSQLI_ASSOC);
            echo "You have " . count($rows) . " users in session: " . $_SESSION['SessionID'];
            echo makeTable($rows);
            $query->close();
            echo "<br><br>";
        }
        else {
            echo "Error: " . mysqli_error();
            echo "Additinal Error: " . mysqli_errno();
            echo "Even more errors: " . $query->error;
        }
    ?>
    <form method="POST">
        <label for="category">Transaction Category:</label>
        <select name="category" id="category">
            <option value="food">Food</option>
            <option value="gas">Gas</option>
            <option value="transportation">Transportation</option>
            <option value="hotel">Hotel</option>
            <option value="bars">Bars</option>
            <option value="entertainment">Entertainment</option>
            <option value="electronics">Electronics</option>
            <option value="other">Other</option>
        </select>
        <br>

        <label for="item">Item: </label>
        <input type="text" placeholder="Tacos" name = "item"><br> 

        <label for="price">Price: </label>
        <input type="text" placeholder = "4.00" name = "price"><br>

        <!--there is a default for today, have if statement to default if nothing is entered -->
        <label>Date of Transaction: </label>
        <input type="datetime-local" id="test_datetimelocal" name = "date" ><br>

        <input type="submit" value="Submit" name = "Submit">
        <br>
    </form>

    <?php
        if(isset($_POST["Submit"]) && !empty($_POST["item"]) && !empty($_POST["price"])) {
            if(!$_POST["date"]) {
                date_default_timezone_set('America/Los_Angleles');
                $_POST["date"] = date('Y-m-d H:i');
            }
        
            $db = get_mysqli_connection();
            $stmt = $db->prepare("CALL AddTransaction (?,?,?,?,?,?)");
            $stmt->bind_param('sssssd',$_SESSION['userid'], $_SESSION['SessionID'], $_POST['category'], $_POST["date"],$_POST["item"], $_POST["price"]);
            if($stmt->execute()) {    
                echo "Transaction added successfully <br>";
                $stmt->close();
            }
            else {
                echo "Transaction not added<br>";
                echo "Error: " . $stmt->error . "<br>";
            }
        }
    ?>
    <br>
    <div class="">
        <h2></h2>
        <br>
        <?php
            $remove_transaction_form = new PhpFormBuilder();
            $remove_transaction_form->set_att("method", "POST");
            $remove_transaction_form->add_input("transaction", array(
                "type" => "submit",
                "value" => "Remove Transaction"
            ), "remove_transaction_button");
            $remove_transaction_form-> add_input("Transaction", array(
                "type" => "text",
                "placeholder" => "9"
            ), "transaction_input");
            $remove_transaction_form->build_form();
            
            if(!empty($_POST['transaction_input']) && isset($_POST['remove_transaction_button'])) {
                $db = get_mysqli_connection();
                $remove_transaction = $db->prepare("CALL RemoveTransaction(?,?)");
                $remove_transaction->bind_param('ss',$_POST['transaction_input'], $_SESSION['SessionID']);
                $remove_transaction->execute();
            }
        ?> 
        <?php  
            $db = get_mysqli_connection();
            $query = $db->prepare("CALL GetTransactions(?)");        
            $query->bind_param('s', $_SESSION['SessionID']);
            if($query->execute()) {
                $result = $query->get_result();
                $rows = $result->fetch_all(MYSQLI_ASSOC);
                if(count($rows) > 1 || count($rows) == 0) {
                    echo "There are " . count($rows) . " transactions";
                }
                else { 
                    echo "There is " . count($rows) . " transaction";
                }
            
                echo makeTable($rows);
                $query->close();
            }
            else {
                echo "Error: " . mysqli_error();
                echo "Additinal Error: " . mysqli_errno();
                echo "Even more errors: " . $query->error;
            }
        ?>
        <h2></h2><h2></h2>
        <?php
            $delete_form = new PhpFormBuilder();
            $delete_form->set_att("method", "POST");
            $delete_form->add_input("Insert", array(
                "type" => "submit",
                "value" => "Delete this Paypool Session - WARNING: IRREVERSIBLE"
            ), "delete_id");
            $delete_form->build_form();

            if (isset($_POST["delete_id"])) {
                $db = get_mysqli_connection();
                $query2 = $db->prepare("DELETE FROM Joins WHERE SessionID = (?)");
                $query2->bind_param("s", $_SESSION['SessionID']);
                if($query2->execute()) {
                    $query2->close();
                    $_SESSION["affected_rows"] = $db->affected_rows;
                } 
                else {
                    echo "Error: " . mysqli_error();
                    echo "Additinal Error: " . mysqli_errno();
                    echo "Even more errors: " . $query2->error;
                }

                $query3 = $db->prepare("UPDATE Transaction SET SessionID = 1 WHERE SessionID = (?)");
                $query3->bind_param("s", $_SESSION['SessionID']);
                if($query3->execute()) {
                    $query3->close();
                    $_SESSION['affected_rows'] = $db->affected_rows;
                } 
                else {
                    echo "Error: " . mysqli_error();
                    echo "Additinal Error: " . mysqli_errno();
                    echo "Even more errors: " . $query3->error;
                }

                $query1 = $db->prepare("delete from PaypoolSession where SessionID = ?");
                $query1->bind_param("s", $_SESSION['SessionID']);
                if($query1->execute()) {
                    $query1->close();
                    $_SESSION["affected_rows"] = $db->affected_rows;
                    $_SESSION['SessionID'] = NULL;
                    header("Location: dashboard.php");
                } 
                else {
                    echo "Error: " . mysqli_error();
                    echo "Additinal Error: " . mysqli_errno();
                    echo "Even more errors: " . $query1->error;
                }
            }
        ?> 
    </div>
    
    <?php  

        //$_SESSION['userid']
        //$_SESSION['SessionID']

        $db = get_mysqli_connection();
        $totalquery = $db->prepare("SELECT SUM((Transaction.Price)* ((Joins.Percentage)/100)) AS 'Total'  
            FROM Joins 
            JOIN Transaction ON(Transaction.UserID = Joins.UserID)  
            WHERE Joins.SessionID = ? AND  Transaction.SessionID = ?");
        $totalquery->bind_param("ss", $_SESSION['SessionID'], $_SESSION['SessionID']);

        if($totalquery->execute()) {
            $result = $totalquery->get_result();
            $data = $result->fetch_all(MYSQLI_ASSOC);
            $totaldue = number_format($data[0]['Total'],2);
            $totalquery->close();
        } 
        else {
            echo "Error: " . mysqli_error();
            echo "Additinal Error: " . mysqli_errno();
            echo "Even more errors: " . $totalquery->error;
        }

        $totalquery2 = $db->prepare("SELECT SUM(Price) AS 'Total'  
            FROM Transaction
            WHERE SessionID = ?");
        $totalquery2->bind_param("s", $_SESSION['SessionID']);

        if($totalquery2->execute()) {
            $result = $totalquery2->get_result();
            $data = $result->fetch_all(MYSQLI_ASSOC);
            $totaldue2 = number_format($data[0]['Total'],2);
            $totalquery2->close();
        } 
        else {
            echo "Error: " . mysqli_error();
            echo "Additinal Error: " . mysqli_errno();
            echo "Even more errors: " . $totalquery2->error;
        }

        echo "<h2>Session Transaction Total: $"; 
        echo $totaldue2;
        echo "</h2>";
        echo "<h2>Total Each Member Owes to Session: $"; 
        echo $totaldue;
        echo"</h2>";
    ?> 

</body>
</html>
