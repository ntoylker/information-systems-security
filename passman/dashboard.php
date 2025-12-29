<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        table {
            border-collapse: collapse;
            width: 30%;
            border: 1px solid black;
        }
        td, tr {
            width: 50%;
            padding: 8px;
            text-align: left;
        }
    </style>
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

// If not logged in redirect to login page
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['username'] == '') {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];

// Connect to the database
// $conn=mysqli_connect("localhost","root","","pwd_mgr");
$conn=mysqli_connect("localhost", "sec_user", "mexriNAsbhseiOhlios5911!", "pwd_mgr");

// Check connection
if (mysqli_connect_errno())	{
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  exit();
}

// Check if 'Insert-new-website' button is selected
if(isset($_POST['new_website'], $_POST['new_username'], $_POST['new_password']) && 
   trim($_POST['new_website']) !='' && trim($_POST['new_username']) != '' && trim($_POST['new_password']) != '') {
	$new_website = trim($_POST["new_website"]);
	$new_username = trim($_POST["new_username"]);
	$new_password = trim($_POST["new_password"]);

	// Prepared Statement για Insert
	$stmt = $conn->prepare("INSERT INTO websites (login_user_id, web_url, web_username, web_password) VALUES ((SELECT id FROM login_users WHERE username = ?), ?, ?, ?)");
	$stmt->bind_param("ssss", $username, $new_website, $new_username, $new_password);
	$result = $stmt->execute();
	$stmt->close();
	$conn -> close();

	// After processing, redirect to the same page to clear the form
	unset($_POST['new_website']);
	unset($_POST['new_username']);
	unset($_POST['new_password']);
	header("Location: " . $_SERVER['PHP_SELF']);
	exit();
}

// Check if 'Delete-website' button was selected
if(isset($_POST['delete_website']) && trim($_POST["websiteid"] != '')) {
	$webid = trim($_POST["websiteid"]);

	// Prepared Statement για Delete
	$stmt = $conn->prepare("DELETE FROM websites WHERE webid = ?");
	$stmt->bind_param("i", $webid); // "i" για integer
	$result = $stmt->execute();
	$stmt->close();
	$conn -> close();

	// After processing, redirect to the same page to clear the form
	unset($_POST['websiteid']);
	header("Location: " . $_SERVER['PHP_SELF']);
	exit();
}

// Prepared Statement για Select
$stmt = $conn->prepare("SELECT * FROM websites INNER JOIN login_users ON websites.login_user_id=login_users.id WHERE login_users.username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

//echo htmlspecialchars($username);
echo "<h3>Entries of " . $username . "</h3>";

if (!empty($result) && $result->num_rows >= 1) {
	while ($row = $result -> fetch_assoc()) {
		echo "<table border=0>";
		echo	"<tr style='background-color: #f4f4f4;'><td colspan=2>" . $row["web_url"] . "</td></tr>" . 
				"<tr><td>Username: " . $row["web_username"] . "</td><td>Password: " . $row["web_password"] . "</td></tr>";

		echo	"<tr><td><form method='POST' style='height: 3px'>" . 
				"<input type='hidden' name='websiteid' value='" . $row["webid"] . "'>" .
				"<button type='submit' name='delete_website'>Delete</button></form></td></tr>";

		echo	"<tr><td colspan=2 style=height: 20px;></td></tr>";
		echo "</table><p/>";
	}

	// Free result set
	$result -> free_result();
} else {
	echo "<p><font color=red>No entries found.</font></p>";
}

$conn -> close();

?>

<body>
	<p/>
	<form method="POST" action="dashboard.php">
        <input type="text" name="new_website" placeholder="website"><br />
        <input type="text" name="new_username" placeholder="Username"><br />
        <input type="password" name="new_password" placeholder="Password"><br />
        <button type="submit">Insert new website</button>
    </form>
	<p/>
    <a href="notes.php">Notes - announcements</a>
	<p/>
    <a href="logout.php">Logout</a>
	<p/>
	<a href="index.html">Home page</a>
</body>
</html>