<?php
$conn = new mysqli("db", "lab", "lab123", "sqli_lab");

// Filter input to remove simple characters
$user = $_GET['user'] ?? '';
$sanitized = str_replace(["'", '"', "--", "#"], "", $user);

$query = "SELECT * FROM users WHERE username = '$sanitized'";
$result = $conn->query($query);
?>
<h2>Medium Level</h2>
<form>
    Username: <input name="user" />
    <button type="submit">Check</button>
</form>
<?php
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "User: " . $row['username'] . " | Password: " . $row['password'] . "<br>";
    }
} else {
    echo "No user found.";
}
?>
