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
    <title>Profile Settings | <?= $PROJECT_NAME ?></title>
    <link rel="stylesheet" href="style.css">

</head>
<body>
    
	<div class>
        <a href="signin.php"><img src="black_gold_logo.png" id="logo"/></a>
		<?php require_once "nav.php";?>
    </div>

    <div>
        <h2>Manage Your Profile</h2>

        <h3>Basic Information</h3>

        <img src="https://www.clipartmax.com/png/full/434-4349876_lutheran-symbols-clip-art.png" width="150" height="150">

        <form method="POST">
            <label for="fname">First name:</label>
            <input type="text" id="fname" name="fname" placeholder=<?= $_SESSION['fname'] ?> disabled><br><br>
            <label for="lname">Last name:</label>
            <input type="text" id="lname" name="lname" placeholder=<?= $_SESSION['lname'] ?>  disabled><br><br>
            <label for="email">Email:</label>
            <input type="text" id="emailPlaceholder" name="email" placeholder=<?= $_SESSION['email'] ?> ><br><br>
            <input type="submit" value="Update Email" name="update">
        </form>

        <?php

            $dbConnection = get_mysqli_connection();

            $email = $_POST['email'];
            $emailError = "";

            if(isset($_POST['update'])) {
                if(empty($_POST['email'])) {
                    $emailError = "Please enter a new email to update.";
                } else {
                    if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $emailError = "Please enter a valid email.";
                    } else {
                        $checkEmail = $dbConnection->prepare("SELECT Email FROM UserProfile WHERE Email = ?");
                        $checkEmail->bind_param("s", $email);
                        $checkEmail->execute();
                        $notUniqueEmail = $checkEmail->fetch();

                        if($notUniqueEmail) {
                            $emailError = "The email entered already exists.";
                        } else {

                            $_SESSION['email'] = $_POST['email'];
                            $updateInfo = $dbConnection->prepare("UPDATE UserProfile SET Email = ? WHERE UserID = ?");
                            $updateInfo->bind_param("ss", $_POST['email'], $_SESSION['userid']);
                            $updateInfo->execute();

                            header("Location: settings.php");
                        }
                    }
                }
            }

            echo "<p class='error'>$emailError</p>";
        ?>

        <!-- Update as of 5:18 am : need to get PaymentInfo information -->

        <h3>Payment Information</h3>
        <form method="POST">
            <label for="cname">Card Holder:</label>
            <input type="text" id="cname" name="cname" placeholder="<?= $_SESSION['fname']?> <?= $_SESSION['lname']?>" disabled><br><br>
            <label for="cnum">Card Number:</label>
            <input type="text" id="cnum" name="cnum" placeholder="" disabled><br><br>
            <label for="exp">EXP:</label>
            <input type="text" id="exp" name="exp" placeholder=""> <br><br>
            <label for="exp">CVV:</label>
            <input type="text" id="cvv" name="cvv" placeholder=""> <br><br>
            <input type="submit" value="Update" name="update">
        </form>


    </div>

    <div>
        <h2>Export Your Information</h2>
        <?php
            $minset = false;
            $maxset = false;
            if(isset($_POST["Submit"])) {
                if(empty($_POST["min"])){
                    $minset = true;
                    $min = "2021-01-01";
                }
                if(empty($_POST['max'])){
                    $maxset = true;
                    $max = date('Y-m-d', time()); //if max not set, set to whatever "today" is 
                }
                if(!$minset){
                    $min = $_POST['min'];
                }
                if(!$maxset){
                    $max = $_POST['max']; 
                }

            
                $db = get_mysqli_connection();
                $stmt = $db->prepare("SELECT * FROM Transaction WHERE UserID = ? and PurchaseDate >= ? AND PurchaseDate <= ?");
                $stmt->bind_param('sss',$_SESSION['userid'], $min, $max);
                if($stmt->execute()){    
                    echo "Below are the transactions within the given time frame: $min to $max <br>";
                    $result = $stmt->get_result();
                    $rows = $result->fetch_all(MYSQLI_ASSOC);
                    echo makeTable($rows);
                    $stmt->close();
                }
                else {
                    echo "Transaction not added<br>";
                    echo "Error: " . $stmt->error . "<br>";
                }

            
            }
        ?>

        <br>
        <form method="POST">       
            <label>Select date criteria to export your transactions. <br>
            If no time frame was given then all transactions from your profile will be shown. </label><br><hr>
            <label>Start Date:</label>
            <input type="date" id="min" name = "min" ><br>
            <label>End Date:</label>
            <input type="date" id="max" name = "max" >

            <input type="submit" value="Submit" name = "Submit">
            <br>
        </form>
    </div>

</body>
</html>
