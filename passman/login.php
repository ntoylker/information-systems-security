<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Form</title>
</head>

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
    // Redirect to the dashboard page
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
	if(!isset($_POST['username'], $_POST['password']) || trim($_POST['username']) =='' || trim($_POST['password']) == '') {
		$login_message = "Missing username or password.";
	}
	else {
		// Get user submitted information
		$username = trim($_POST['username']);
		$password = trim($_POST['password']);

		// Connect to the database
		//$conn=mysqli_connect("localhost","root","","pwd_mgr");
		$conn=mysqli_connect("localhost", "sec_user", "mexriNAsbhseiOhlios5911!", "pwd_mgr");
		// Check connection
		if (mysqli_connect_errno())	{
		  echo "Failed to connect to MySQL: " . mysqli_connect_error();
		  exit();
		}

		// Χρήση Prepared Statement για προστασία από SQL Injection
		$stmt = $conn->prepare("SELECT * FROM login_users WHERE username = ?");
		$stmt->bind_param("s", $username); // Το "s" δηλώνει ότι η παράμετρος είναι string
		$stmt->execute();
		$result = $stmt->get_result();
		$stmt->close(); // Κλείνουμε το statement


		unset($_POST['username']);
		unset($_POST['password']);

		if (!empty($result) && $result->num_rows >= 1) {
			// Παίρνουμε τα δεδομένα του χρήστη από τη βάση
			$row = $result->fetch_assoc();

			// Ελέγχουμε αν ο κωδικός ταιριάζει με το hash
			if (password_verify($password, $row['password'])) {
				// Σωστός κωδικός! (Κώδικας που υπήρχε και πριν)
				$_SESSION['username'] = $username;
				$_SESSION['loggedin'] = true;

				$result->free_result();
				$conn->close();

				header("Location: dashboard.php");
				exit;
			} else {
				// Λάθος κωδικός
				$login_message = "Invalid username or password";
			}
		} else {
			// Δεν βρέθηκε ο χρήστης
			$login_message = "Invalid username or password";
		}

		$conn -> close();
	}
}
?>

<body>
    <h3>Password Manager</h3>
    <form method="POST" action="">
        <input type="text" name="username" placeholder="Username" required><br />
        <input type="password" name="password" placeholder="Password"><br />
        <button type="submit">Login</button>
    </form>
	<br />
    <?php if (!empty($login_message)) { echo "<font color=red>$login_message</font>"; } ?>
	<p/>
    <a href="register.php">Register new user</a>
	<p/>
	<a href="index.html">Home page</a>
</body>
</html>