<nav>
	<?php if(isset($_SESSION['logged_in']) && $_SESSION['logged_in']) { ?>
	    <!--If user is signed in show these options -->
            <a href = "dashboard.php" class="nav_btn">Dashboard</a>
            <a href = "settings.php" class="nav_btn">Profile Settings</a>
            <a href = "signout.php" class="nav_btn">Sign Out</a>
    <?php } else { ?>
		<!--If not signed in show these options -->
            <a href = "signin.php" class="nav_btn">Sign In</a>
            <a href = "signup.php" class="nav_btn">Sign Up</a>
    <?php } ?>
</nav>