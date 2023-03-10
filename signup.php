<?php
    require_once "config.php";
  
    $fname = null;
    $lname = null;
    $email = null;
    $pword = null; 
    $confirmedpword = null; 

    $fnameerr = null;
    $lnameerr = null;
    $emailerr = null;
    $pworderr = null;
    $confirmpworderr = null;



    $fnamevalid=true;
    $lnamevalid=true;
    $emailvalid=true;
    $pwordvalid=true;
    $confirmpwordvalid=true;
    $validpassword=false;

if (isset($_POST['submit'])){


    if (empty($_POST["fname"])) {
        $fnamevalid = false;
      $fnameerr = "First Name is required";
    } else {
      $fname = cleaninput($_POST["fname"]);
    }

    if (empty($_POST["lname"])) {
        $lnamevalid = false;
      $lnameerr = "Last Name is required";
    } else {
      $lname = cleaninput($_POST["lname"]);
    }

    
    
    if (empty($_POST["email"])) {
        $emailvalid=false;
      $emailerr = "Email is required";
    } else {
      $email = cleaninput($_POST["email"]);
    }
      
  
    if (empty($_POST["pword"])) {
        $pwordvalid=false;
      $pworderr = "Password is required";
    } else {
      $pword = cleaninput($_POST["pword"]);
    }
  
    if (empty($_POST["confirmpword"])) {
        $confirmpwordvalid=false;
      $confirmpworderr = "Confirmed password is required";
    } else {
      $confirmedpword = cleaninput($_POST["confirmpword"]);
    }

    if($pwordvalid && $confirmedpword){
        if(strcmp($pword,$confirmedpword) == 0){
            $validpassword = true;
            $hashed = password_hash($pword, PASSWORD_DEFAULT);
            echo "Both passwords are the same and is valid, thank you!<br>";
        } else{
            $validpassword = false;
            echo "Passwords are not the same<br>";
        }
    }

    
    if($validpassword) {
            Echo "All entries are valid. Ready to enter info into database<br>";

            $db = get_mysqli_connection();
            $update = $db->prepare("call CreateNewUser(?, ?, ?, ?)");
            $update->bind_param("ssss",$email, $hashed, $fname, $lname);

        if ($update->execute()) {
            
            $_SESSION['logged_in'] = true;
            $_SESSION['fname'] = $fname;
            $_SESSION['lname'] = $lname;
            $_SESSION['email'] = $email;
            header("Location: dashboard.php");
        } else {
            Echo "Error entering information into database<br><br>";
            var_dump($db->error);
            echo "<br><br>";

            var_dump($update);
        } 
    } 
  
    /* */
    echo "<br>";
    echo "$fname<br>";
    echo "$lname<br>";
    echo "$email<br>";
    echo "$pword<br>";
    echo "$confirmedpword<br>";
   
    //var_dump($_POST);
  }


function cleaninput($data){
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Sign Up | <?= $PROJECT_NAME?></title>
	<link rel="stylesheet" href="style.css">
</head>
<body>
	<div>
    <a href="signin.php"><img src="black_gold_logo.png" id="logo"></a>
		<?php require "nav.php"; ?>
	</div>
	
	<div class="signup">
	<?php if(!$validpassword) { ?>
		<div class = "signupForm">
      <h2>Welcome to Sign Up!</h2>
		  <p>You're almost there. Just need some information about you first!</p>
			<form method="POST" id='signup'>
				<label for="fname">First Name:</label>
				<input type="text" class="input_areas" name="fname" placeholder="" <?php if(!$fnamevalid){ echo " style = 'border: 2px solid red'";} else{ echo "value = '$fname' ";} ?> > <?php echo '<p class="error">' . $fnameerr . '</p>'; ?> <br>
				<label for="lname">Last Name:</label>
				<input type="text" class="input_areas" name="lname" placeholder="" <?php if(!$lnamevalid){ echo " style = 'border: 2px solid red'";} else{ echo "value = '$lname' ";} ?>?> <?php echo '<p class="error">' . $lnameerr . '</p>';?><br>
				<label for="email">Email:</label>
				<input type="email" class="input_areas" name="email" placeholder="" <?php if(!$emailvalid){ echo " style = 'border: 2px solid red'";}else{ echo "value = '$email' ";} ?> ?> <?php echo '<p class="error">' . $emailerr . '</p>';?><br>
				<label for="pword">Password:</label>
				<input type="password" class="input_areas" name="pword" placeholder="" <?php if(!$pwordvalid){ echo " style = 'border: 2px solid red'";} ?>><?php echo '<p class="error">' . $pworderr . '</p>';?><br>
				<label for="confirmpword">Confirm Password:</label>
				<input type="password" class="input_areas" name="confirmpword" placehorlder="" <?php if(!$confirmpwordvalid){ echo " style = 'border: 2px solid red'";} ?>><?php echo '<p class="error">' . $confirmpworderr . '</p>';?><br>
				<input type="submit" value="Submit" name="submit" class="signup_btn">
			</form>
      <p>Already a member? <a class="darkLinks" href="signin.php">Sign In</a></p>
		</div>
  <?php }else echo "Thank you $fname, you will be entered in our database. "?>
	</div>
</body>
</html>