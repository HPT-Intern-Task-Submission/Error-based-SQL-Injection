<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "vulnerable_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to filter out common SQL keywords in lowercase
function containsSqlKeywords($input) {
    $sqlKeywords = '/\b(SELECT|UNION|WHERE|DELETE|DROP TABLE|AND)\b/';
    return preg_match($sqlKeywords, $input);
}

if (isset($_GET['username'])) {
    $username = $_GET['username'];

    // Check for SQL keywords in lowercase
    if (containsSqlKeywords($username)) {
        die("Invalid input.");
    }

    // Vulnerable SQL query (no prepared statement)
    $sql = "SELECT username, password, email FROM users WHERE username = '$username'";
    $result = $conn->query($sql);

    if (!$result) {
        // Display error with injected SQL query for educational purposes
        echo "Error: " . $conn->error . "Full SQL query: " . $sql;
    }

    echo "<div id='result'>";
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<p>Username: " . $row["username"] .  " - Email: " . $row["email"] . "</p>";
        }
    } else {
        echo "No results found.";
    }
    echo "</div>";

    $result->free();
} else {
    echo "Please provide a username.";
}

$conn->close();
?>