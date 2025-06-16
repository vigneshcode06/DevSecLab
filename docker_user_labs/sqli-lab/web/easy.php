

<html>
 
 <h1> this is the test lab you can doo :D </h1>


</html>


<?php
$conn = new mysqli("db", "lab", "lab123", "sqli_lab");

$user = $_GET['user'] ?? '';

$sql = "SELECT * FROM users WHERE username = '$user'";
$result = $conn->query($sql);

if (!$result) {
    echo "SQL Error: " . $conn->error;
} else {
    while ($row = $result->fetch_assoc()) {
        echo "Username: " . $row['username'] . " | Password: " . $row['password'] . "<br>";
    }
}
?>

<form>
    Username: <input name="user" />
    <button type="submit">Submit</button>
</form>
 


<!-- 
?user=' OR '1'='1
?user=' UNION SELECT null,version() --
?user=' UNION SELECT null,database() -- -->
