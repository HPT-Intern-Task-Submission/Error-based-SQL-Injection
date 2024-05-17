Introduction to Error-Based SQL Injection
======================

## What is Error-based SQL Injection?
Error-based SQL Injection is an In-band attack technique allowing attackers to exploit SQL Injection via the returns from server. The attacker can gather valuable information based on errors that server returns. In this In-band attack, the payload sent and the result will be conducted in one channel.

## How to test Error-based SQL Injection
Our main purpose for this type of bug is to cause error on server-side and extract information from that. Therefore, we can try to inject some special characters like `'`,`"`,`||`,`%` or input a string where accepts numbers.
## Error-based SQL Injection examples
This is a lab that I created to demonstrate the bug. 

![Main page](https://github.com/HPT-Intern-Task-Submission/Error-based-SQL-Injection/blob/main/image/main%20page.png)

The page looks quite simple as we can enter UserID and the server will return the user with that ID. Just like that!!!!

```
<?php

$servername = "localhost";

$username = "root";

$password = "";

$dbname = "vulnerable_db";

  

// Create connection

$conn = new  mysqli($servername, $username, $password, $dbname);

  

// Check connection

if ($conn->connect_error) {

die("Connection failed: "  .  $conn->connect_error);

}

  

// Function to filter out common SQL keywords in lowercase

function  containsSqlKeywords($input) {

$sqlKeywords = '/\b(SELECT|UNION|WHERE|DELETE|DROP TABLE|AND)\b/';

return  preg_match($sqlKeywords, $input);

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

echo  "Error: "  .  $conn->error  .  "Full SQL query: "  .  $sql;

}

  

echo  "<div id='result'>";

if ($result->num_rows > 0) {

while ($row = $result->fetch_assoc()) {

echo  "<p>Username: "  .  $row["username"] .  " - Email: "  .  $row["email"] .  "</p>";

}

} else {

echo  "No results found.";

}

echo  "</div>";

  

$result->free();

} else {

echo  "Please provide a username.";

}

  

$conn->close();

?>
```
We can see that, the server implemented security checks as it filters some common SQL syntax.
```
// Function to filter out common SQL keywords in lowercase

function  containsSqlKeywords($input) {

$sqlKeywords = '/\b(SELECT|UNION|WHERE|DELETE|DROP TABLE|AND)\b/';

return  preg_match($sqlKeywords, $input);

}
```
However, user's input will be inserted directly to the SQL query which provides us a potential attack vector
```
// Vulnerable SQL query (no prepared statement)

$sql = "SELECT username, password, email FROM users WHERE username = '$username'";

$result = $conn->query($sql);
```
As usual, we will try to inject a single quote `'` to see how the server returns.  Url: `http://localhost/sql_injection/index.php?username=admin'`. 

![Error message](https://github.com/HPT-Intern-Task-Submission/Error-based-SQL-Injection/blob/main/image/Error_message.png)

As expected, the server returns an error message to us. This is a valuable information for further exploitation. Now, let's try to inject a basic payload `'or 1=1 -- ;`. here we use lowercase SQL syntax as the server has a check to search for SQL syntax but in uppercase form.
 
 ![](https://github.com/HPT-Intern-Task-Submission/Error-based-SQL-Injection/blob/main/image/or%201%3D1.png)

Great! We have successfully injected a payload to the server. Now let's try another payload to enumerate all tables in the database. Payload use: `admin' union select table_name, null,null from information_schema.tables where table_schema=database()-- -
`. Let's break the payload down to understand more:

- ' union select table_name, null: This will select all table name and display in `Username:` and null value will be displayed in `Email:`
-   `FROM information_schema.tables WHERE table_schema=DATABASE()`: This part specifies that we want to retrieve table names from the current database schema.
-    `-- -`: This comment sequence ensures that the rest of the original SQL query is ignored.

![All table name](https://github.com/HPT-Intern-Task-Submission/Error-based-SQL-Injection/blob/main/image/all_table_name.png)

We have successfully enumerate all tables in the database, note that the first is the result for the query to search for `username` `admin`. We see that there's a table name `users`, but we don't know its columns' name. We can use this payload to extract the column name: `' union select column_name, null,null from information_schema.columns where table_name='users' and table_schema=database()-- -`

![Enum column name](https://github.com/HPT-Intern-Task-Submission/Error-based-SQL-Injection/blob/main/image/enum_column_name.png)

Okay, we see that there's a `password` column. Let's retrieve that information. `admin%27%20union%20select%20username,email,password%20from%20users%20--%20;`

![Enum pass](https://github.com/HPT-Intern-Task-Submission/Error-based-SQL-Injection/blob/main/image/enum_pass.png)

Good job!! We've just finished a lab. It's time to find a way to secure our code, which will prevent attackers from injecting malicious code. One of the best solution is to use parameterized query. Here's the update version of the PHP script

```
// Prepare statement to prevent SQL injection  
$stmt = $conn->prepare("SELECT username, email FROM users WHERE username = ?"); 
$stmt->bind_param("s", $username); 
$stmt->execute(); 
$result = $stmt->get_result();

```
In this updated version, I replace the `$username` variable with a placeholder and then use the `bind_param` function. This practice will ensure our input will  be treated as data instead of a part of SQL query.

This is the end of the blog, I leart a lot from this. Although there're still many things to dive deeply, but somehow I gained some more knowledge by creating and solving labs. See you next week. Happy hacking!!!
