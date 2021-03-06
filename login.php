<?php 
	session_start();

	$url=parse_url(getenv("CLEARDB_DATABASE_URL"));

	$server = $url["host"];
	$username = $url["user"];
	$password = $url["pass"];
	$db = substr($url["path"],1);

	$link = mysqli_connect($server, $username, $password);
	mysqli_select_db($link, $db) or die("Cannot connect to database.");
?>

<html>
	<head>
		<link href='http://fonts.googleapis.com/css?family=Oswald:400,300,700' rel='stylesheet' type='text/css'>
		<link href='http://fonts.googleapis.com/css?family=Titillium+Web:700,400' rel='stylesheet' type='text/css'>
	    <link rel="stylesheet" type="text/css" href="css/main.css">
		<link rel="shortcut icon" href="img/icon.ico" />
    	<script type="text/javascript" src="js/jQuery.js"></script>
		<title>
			Login
		</title>
	</head>
	<body>
		<div id = "bgMenuBar">
			<div class = "logo">Slavehack: Legacy</div>
			<ul id = "homeMenu">
				<li><a href="index.php">Home</a></li>
				<li><a href="about.php">About</a></li>
				<li><a href="register.php">Register</a></li>
				<li><a href="terms.html">Terms of Use</a></li>
				<li><span id="logswitch"></span></li>
			</ul>
		</div>

		<div id = "wrapper">
			<div id = "entryBlock">
				<div id="ipLog">
					116.96.54.52@loginServer
					<div id="date"></div>
				</div>
				<div id="container">
					<div id="message">
						<div id="title">
							<b>Log in</b><br /><br />
						</div>
						<div id="error"></div><div id="success"></div>
						<form id="register" method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
							<label><b>Username: </b></label><input type = "text" name = "user" autocomplete = "off">
							<label><b>Password: </b></label><input type = "password" name = "pass" autocomplete = "off"><br /><br />
							<input type = "submit" value = "Login" name = "login" id = "login">
						</form>
					</div>
				</div>
			</div>
		</div>
	</body>
</html>

<?php
	echo("Config test.");

	$timestamp = $_SERVER['REQUEST_TIME'];
	date_default_timezone_set('UTC');

	if(isset($_SESSION['tz'])){
		$tz = $_SESSION['tz'];
	} else {
		$tz = "America/Chicago";
	}

	if(isset($_SESSION['user'])){
		?><script>
			$("#logswitch").html("<a href='logout.php'>Logout</a>");
		</script><?php 
	} else {
		echo "Not log in detected.";
		?><script>
			$("#logswitch").html("<a href='login.php'>Login</a>");
		</script><?php 
	}

	$dtzone = new DateTimeZone($tz);
	$dtime = new DateTime();

	$dtime->setTimestamp($timestamp);
	$dtime->setTimeZone($dtzone);
	$time = $dtime->format('g:i A m/d/y');
		
	?>
	<script>
	    $("#date").html('<?php echo $time; ?>');
	</script>
	<?php

	if(isset($_POST['login'])){
	    
        function verify($password, $hashedPassword){
            return crypt($password, $hashedPassword) == $hashedPassword;
        }

		if(isset($_POST['user'])){
			$user = mysqli_real_escape_string($link, stripslashes($_POST['user']));
		} else {
			$user = "";
		}

		if(isset($_POST['pass'])){
			$pass = mysqli_real_escape_string($link, stripslashes($_POST['pass']));
		} else {
			$pass = "";
		}

        $qry = "SELECT * FROM users WHERE login='" . $user . "'";
		if(!mysqli_query($link,$qry)){
			?><script>
				$("#error").html("Invalid username or password.");
			</script><?php 
		} else {
			$result = mysqli_query($link, $qry);
			$row = mysqli_fetch_array($result);
			$hash = $row['hash'];
			echo("User located.");
			if($row['email_confirmed'] == 0){
				echo("Unconfirmed email detected.");
				?><script>
					$("#error").html('<?php echo "Please confirm your email. If you need another e-mail, please click <a href=\"register.php?resend=true&user=$user\">here.</a>"; ?>');
				</script><?php 
			}
			else if(verify($pass, $hash)){
				$_SESSION['user'] = $user;
				$_SESSION['tz'] = $row['timezone'];
				$dtzone = new DateTimeZone($_SESSION['tz']);
				$dtime->setTimestamp($timestamp);
				$dtime->setTimeZone($dtzone);
				$tz = $_SESSION['tz'];
				$time = $dtime->format('g:i A m/d/y');
				$_SESSION['TWLI'] = $time;
				$logTime = $_SESSION['TWLI'];
				?><script>
					$("#date").html('<?php echo $logTime; ?>');
					$("#logswitch").html("<a href='logout.php'>Logout</a>");
					$("#success").html('<?php echo "Successfully logged in at: ".$logTime."- you will be redirected in 3 seconds."; ?>');
					window.setTimeout( function() {
						window.location.href = "/game/index.php?login='success'";
					}, 3000);
				</script><?php
			} else {
				?><script>
					$("#error").html("Invalid username or password.");
				</script><?php 
			}
		}
	}
?>