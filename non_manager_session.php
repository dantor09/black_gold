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
        <?php
            require_once("nav.php");
        ?>
    </div>
    <h2>
    <?php
        $fname = $_SESSION['fname'];
        $lname = $_SESSION['lname'];
    ?>
    </h2>

    <?php
        echo "<h2>Manager: " . $_SESSION['manager_first_name'] . " " . $_SESSION['manager_last_name'] . "</h2>";
    ?>
   
    <?php
            
        $db = get_mysqli_connection();
        $query4 = $db->prepare("CALL GetMembers(?)");
        $query4->bind_param('s', $_SESSION['SessionID']);
        if($query4->execute())
        {
            $result4 = $query4->get_result();
            $rows4 = $result4->fetch_all(MYSQLI_ASSOC);
            
            if(count($rows4) > 1 || count($rows4) == 0){
                echo "There are " . count($rows4) . " members in session " . $_SESSION['SessionID'];
            }
            else{ 
                echo "There is " . count($rows4) . " member in session " . $_SESSION['SessionID'];
            }
            echo makeTable($rows4);
            $query4->close();
            echo "<br><br>";
        }
        else
        {
            echo "Error: " . mysqli_error();
            echo "Additinal Error: " . mysqli_errno();
            echo "Even more errors: " . $query4->error;
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
        if(isset($_POST["Submit"]) && !empty($_POST["item"]) && !empty($_POST["price"]))
        {
            if(!$_POST["date"])
            {
                date_default_timezone_set('America/Los_Angleles');
                $_POST["date"] = date('Y-m-d H:i');
            }
        
            $db = get_mysqli_connection();
            $stmt = $db->prepare("CALL AddTransaction (?,?,?,?,?,?)");
            $stmt->bind_param('sssssd',$_SESSION['userid'], $_SESSION['SessionID'], $_POST['category'], $_POST["date"],$_POST["item"], $_POST["price"]);
            if($stmt->execute()){    
                echo "Transaction added successfully <br>";
                $stmt->close();
            }
            else
            {
                echo "Transaction not added<br>";
                echo "Error: " . $stmt->error . "<br>";
            }
        }
    ?>
    <br>
    <?php  
       $db = get_mysqli_connection();
        $query = $db->prepare("CALL GetTransactions(?)");        
        $query->bind_param('s', $_SESSION['SessionID']);

        if($query->execute())
        {
            $result = $query->get_result();
            $rows = $result->fetch_all(MYSQLI_ASSOC);
            if(count($rows) > 1 || count($rows) == 0){
                echo "There are " . count($rows) . " transactions";
            }
            else
            { 
                echo "There is " . count($rows) . " transaction";
            }
            echo makeTable($rows);
            $query->close();
            
            
        }
        else
        {
            echo "Error: " . mysqli_error();
            echo "Additinal Error: " . mysqli_errno();
            echo "Even more errors: " . $query->error;
        }
    ?>  
    <?php  

        //$_SESSION['userid']
        //$_SESSION['SessionID']

        $db = get_mysqli_connection();
        $totalquery = $db->prepare("SELECT SUM((Transaction.Price)* ((Joins.Percentage)/100)) AS 'Total'  
            FROM Joins 
            JOIN Transaction ON(Transaction.UserID = Joins.UserID)  
            WHERE Joins.SessionID = ? AND  Transaction.SessionID = ?");
        $totalquery->bind_param("ss", $_SESSION['SessionID'], $_SESSION['SessionID']);

        if($totalquery->execute())
        {
            $result = $totalquery->get_result();
            $data = $result->fetch_all(MYSQLI_ASSOC);
            $totaldue = number_format($data[0]['Total'],2);
            $totalquery->close();
        } 
        else 
        {
            echo "Error: " . mysqli_error();
            echo "Additinal Error: " . mysqli_errno();
            echo "Even more errors: " . $totalquery->error;
        }

        $totalquery2 = $db->prepare("SELECT SUM(Price) AS 'Total'  
            FROM Transaction
            WHERE SessionID = ?");
        $totalquery2->bind_param("s", $_SESSION['SessionID']);

        if($totalquery2->execute())
        {
            $result = $totalquery2->get_result();
            $data = $result->fetch_all(MYSQLI_ASSOC);
            $totaldue2 = number_format($data[0]['Total'],2);
            $totalquery2->close();
        } 
        else 
        {
            echo "Error: " . mysqli_error();
            echo "Additinal Error: " . mysqli_errno();
            echo "Even more errors: " . $totalquery2->error;
        }

        echo "<h2>Session Transaction Total: $"; 
        echo $totaldue2;
        echo "</h2>";
        echo "<h2>Total due to Session: $"; 
        echo $totaldue;
        echo"</h2>";



    ?>  
</body>
</html>
