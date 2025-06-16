<?php
$conn = new mysqli("db", "lab", "lab123", "sqli_lab");

$user = $_GET['user'] ?? '';

$stmt = $conn->prepare("SELECT username, password FROM users WHERE username = ?");
$stmt->bind_param("s", $user);
$stmt->execute();
$result = $stmt->get_result();
?>
<h2>Hard Level</h2>
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
