<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Form</title>
</head>
<body>
    <h3>New user registration</h3>

<?php
// --- SECURITY FIX: ENFORCE HTTPS & SECURE COOKIES ---

// 1. Force HTTPS redirection (Ανακατεύθυνση σε HTTPS)
// Ελέγχουμε αν το HTTPS είναι ανενεργό
if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === "off") {
    $location = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header('HTTP/1.1 301 Moved Permanently');
    header('Location: ' . $location);
    exit;
}

// 2. Set Secure Session Parameters (Ασφαλή Cookies)
// Πρέπει να οριστούν ΠΡΙΝ το session_start()
$cookieParams = session_get_cookie_params();
session_set_cookie_params([
    'lifetime' => $cookieParams['lifetime'],
    'path' => $cookieParams['path'],
    'domain' => $cookieParams['domain'],
    'secure' => true,      // Στέλνεται ΜΟΝΟ μέσω HTTPS
    'httponly' => true,    // Δεν είναι προσβάσιμο από JavaScript (προστασία XSS)
    'samesite' => 'Strict' // Προστασία από CSRF
]);

// Start a new session (or resume an existing one)
session_start();

// Check if the user is already logged in
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && $_SESSION['username'] !== '') {
	echo "<font color=red>You are already logged in!</font></br>";
	echo "Please <a href='logout.php'>logout</a> first";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
	if(!isset($_POST['new_username'], $_POST['new_password']) || trim($_POST['new_username']) =='' || trim($_POST['new_password']) == '') {
		$login_message = "Missing username or password.";
	}
	else {
		// Get user submitted information
		$new_username = trim($_POST['new_username']);
		$new_password = trim($_POST['new_password']);
		
		/* Hash the password using Bcrypt */
		$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

		mysqli_report(MYSQLI_REPORT_OFF);  // disable exceptions

		// Connect to the database
		//$conn=mysqli_connect("localhost","root","","pwd_mgr");
		$conn=mysqli_connect("localhost", "sec_user", "mexriNAsbhseiOhlios5911!", "pwd_mgr");
		// Check connection
		if (mysqli_connect_errno())	{
		  echo "Failed to connect to MySQL: " . mysqli_connect_error();
		  exit();
		}

		// Prepared Statement για Εγγραφή Χρήστη
		$stmt = $conn->prepare("INSERT INTO login_users (username, password) VALUES (?, ?)");
		$stmt->bind_param("ss", $new_username, $hashed_password);
		$result = $stmt->execute();
		$stmt->close();

		unset($_POST['new_username']);
		unset($_POST['new_password']);

		if ($result == true) {
			echo "<font color=red>Successful registration!</font>";
			echo "<p />You can now use the <a href='login.php'>login</a> page";
			exit;
		}
		else 
			$login_message = "Error, probably user already exists!";

		// Free result set
		$conn -> close();
	}
}
?>


<body>
	<p/>
	<form method="POST" action="register.php">
        <input type="text" name="new_username" placeholder="Username"><br />
        <input type="password" name="new_password" placeholder="Password"><br />
        <button type="submit">Register</button>
    </form>

	<br />

    <?php
		if (!empty($login_message)) { 
			echo "<font color=red>$login_message</font>";
			echo "<p />Go to the <a href='login.php'>login</a> page";
		}
	?>

</body>
</html>