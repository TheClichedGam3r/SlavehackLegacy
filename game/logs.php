<?php
	require('../vendor/autoload.php');
	use Aws\S3\S3Client;
	use Aws\S3\StreamWrapper;

	session_start();

	$s3path = getenv('S3_BUCKET_NAME');
	$s3key = getenv('AWS_ACCESS_KEY_ID');
	$s3auth = getenv('AWS_SECRET_ACCESS_KEY');

	$client = S3Client::factory(array(
	 	'key'	 => $s3key,
	 	'secret' => $s3auth
	));
	$client -> registerStreamWrapper();

	$bucket = $s3path;
	$key = $_SESSION['user'] . ".txt";
?>

<html>
	<head>
		<title>
			SHL - Logs
		</title>
    	<link rel="stylesheet" type="text/css" href="css/logs.css">
		<link href='http://fonts.googleapis.com/css?family=Oswald:400,300,700' rel='stylesheet' type='text/css'>
		<link href='http://fonts.googleapis.com/css?family=Titillium+Web:700,400' rel='stylesheet' type='text/css'>
 		<script type="text/javascript" src="http://www.google.com/recaptcha/api/js/recaptcha_ajax.js"></script>
     	<script type="text/javascript" src="../js/jQuery.js"></script>
    	<!--Captcha Stuff-->
	</head>
	<body>
		<div id = "leftColumn">
			<ul>
				<li><a href = "index.php"><img src = "img/ico_comp.png">My Computer</a></li>
				<li><a href = "processes.php"><img src = "img/ico_procs.png">Processes</a></li>
				<li><a href = "logs.php"><img src = "img/ico_logs.png">Computer Logs</a></li>
				<li><a href = "files.php"><img src = "img/ico_files.png">Files</a></li>
				<li><a href = "internet.php"><img src = "img/ico_world.png">Internet</a></li>
				<li><a href = "slaves.php"><img src = "img/ico_slaves.png">My Slaves</a></li>
			</ul>
		</div>

		<div id = "background">
			<div id = "container">
				<div id = "header">
					<span id = "ipuser"></span>
					<span id = "timedate"></span>
				</div>
				<hr>
				<div id = "title">My Log File</div>
				<div id = "wrapper">
					<div id = "contentHolder">
						Your log file holds important information about you.<br />
						This ranges from things such as accessing your computer, to managing software.<br />
						You can even converse with other players using your log file.<br />
						It's important you make sure unwanted eyes are kept away from this.<br />
					</div><br />
					<form method = "POST" action = "<?php echo $_SERVER['PHP_SELF'];?>" method="post">
						<?php 
							if(isset($_POST['message'])){
								$message = mysqli_real_escape_string($link, $_POST['message']);
								$stream = fopen("s3://".$bucket."/".$key, 'w');
								fwrite($stream, $message);
								fclose($stream);
							} else { }
							echo "<textarea name='message' cols='90' rows='20'>";
							$stream = fopen("s3://".$bucket."/".$key, 'r');
							if (!$stream) {
								die('Could not open stream for reading.');
							}
							while(!feof($stream)) 
							{
								$lineLog = fgets($stream);
								echo $lineLog;
							}
							fclose($stream);
							echo "</textarea>";
						?>
						<br />
						<input type = "submit" value = "Edit Log" id = "submit" style = "width: 8em; margin-left: 45%; text-align: center;">
					</form>
				</div>
			</div>
		</div>
	</body>
</html>

<?php
	$url=parse_url(getenv("CLEARDB_DATABASE_URL"));

	$server = $url["host"];
	$username = $url["user"];
	$password = $url["pass"];
	$db = substr($url["path"],1);

	$link = mysqli_connect($server, $username, $password);
	mysqli_select_db($link, $db) or die("Cannot connect to database.");

    ?><script>
		var img = new Image();
		img.src = "backgrounds/default.png";
		document.body.background = img.src;
	</script><?php 

	$user = $_SESSION['user'];
	$pass = $_SESSION['pass'];
	$tz = $_SESSION['tz'];
	$ip = $_SESSION['ip'];

	$timestamp = $_SERVER['REQUEST_TIME'];

	$dtzone = new DateTimeZone($tz);
	$dtime = new DateTime();

	$dtime->setTimestamp($timestamp);
	$dtime->setTimeZone($dtzone);

	$curTime = $dtime->format('g:i A m/d/y');
	?><script>
		$("#ipuser").html("<?php echo $ip;?>@<?php echo $user;?>");
		$("#timedate").html("<?php echo ($curTime); ?>");
		$("#ip").html("<?php echo $ip; ?><a href='index.php?reset=1'> Reset</a>");
		$("#pass").html("<?php echo $pass; ?><a href='index.php?reset=2'> Reset</a>");
		var socket = io.connect('http://localhost:3000');
	</script><?php
?>