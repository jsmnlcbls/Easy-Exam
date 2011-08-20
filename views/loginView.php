<!DOCTYPE HTML>
<html>
	<head>
		<link type="text/css" href="style.css" rel="stylesheet" />
		<style>
			#login-text {
				font-size: 140%;
				color: gold;
				font-weight: bold;
			}
			#login-form {
				color: gold;
			}
			
			#login-form input {
				margin-bottom: 5px;
			}
			#failedLoginNotice {
				color:VIOLET;
				font-size:80%;
				font-style: italic;
			}
		</style>
		<title>Easy Exam</title>
	</head>
<body>
	<div id ="header">
		<span id ="program-name">Easy Exam
	</div>
	<div id = "left-panel">
		<span id = "login-text">Login</span>
		<form id = "login-form" method = "post" action = "login.php">
			<input type = "hidden" name = "action" value = "login"/>
			<label>Username</label>
					<input type = "text" name = "username"/>
			<label>Password</label>
			<input type = "password" name = "password"/>
			<input type = "submit" value = "Login"/>
			<div id = "failedLoginNotice">
			<?php
			if (isset($loginFailed) && $loginFailed) {
				echo "Login failed. Check username and password, then try again.";
			} else if (isset($logout) && $logout) {
				echo "You have successfully logout.";
			}
			?>
			</div>
		</form>
	</div>
	<div id = "main-panel">
	</div>
</body>
</html>