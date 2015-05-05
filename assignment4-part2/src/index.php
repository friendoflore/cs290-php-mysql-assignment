<?php
ini_set('display_errors', 'On');

$mysqli = new mysqli("oniddb.cws.oregonstate.edu", "robinsti-db", "j0dbptMAE8H6RoqT", "robinsti-db");
if ($mysqli->connect_errno) {
	echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;

} else {
	echo "Connection worked!<br>";
}

echo '<!DOCTYPE html>
	<html lang="en">
		<head>
			<meta charset="utf-8" />';
echo '<style>
table {
	border: 1px solid black;
	border-collapse: collapse;
}
td {
	border: 1px solid black;
}
		</style>';	
echo	'</head>
		<body>';
	




// Create MySQL table

if(isset($_POST["delete_box"])) {
	if(!$mysqli->query("DROP TABLE IF EXISTS videos")) {
		echo "Table deletion failed: (" . $mysqli->errno . ") " . $mysqli->error;
	}
}

if(!$mysqli->query("CREATE TABLE videos(
		id INT PRIMARY KEY AUTO_INCREMENT,
		name VARCHAR(255) UNIQUE NOT NULL,
		category VARCHAR(255),
		length INT,
		rented  INT NOT NULL)")) {
	echo "Table creation failed: (" . $mysqli->errno . ") " . $mysqli->error;
}






//Insert elements in to table

// Prepare statement
if(!($stmt = $mysqli->prepare("INSERT INTO videos(id, name, category, length, rented) VALUES (?, ?, ?, ?, ?)"))) {
	echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
}




// Bind and execute prepared statement
$id;
/*
$name = "Test_name";
$cat = "Test_category";
$length = 10;
*/
$rented = 1;
/*
if(!$stmt->bind_param("issii", $id, $name, $cat, $length, $rented)) {
	echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
}

if(!$stmt->execute()) {
	echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
}
*/


echo '<form action="index.php" method="get" name="add_form">
	Title: <input type="text" name="name_field"><br>
		Category: <input type="text" name="cat_field"><br>
		Length: <input type="text" name="length_field"><br>
		<input type="submit" name="sub_button" value="Add Video"><br>
	</form>';

if(isset($_GET["name_field"])) {
	$name = $_GET["name_field"];
	$cat = $_GET["cat_field"];
	$length = $_GET["length_field"];

	if(!$stmt->bind_param("issii", $id, $name, $cat, $length, $rented)) {
		echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
	}

	if(!$stmt->execute()) {
		echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
	}

}


/*

$name = "Test_name_2";
$cat = "Test_category_2";
$length = 11;
$rented = 1;

if(!$stmt->execute()) {
	echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
}

*/


// Get elements from the table

if(!($stmt = $mysqli->prepare("SELECT id, name, category, length, rented FROM videos"))) {
	echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
}

if(!$stmt->execute()) {
	echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
}

$out_id = NULL;
$out_name = NULL;
$out_cat = NULL;
$out_length = NULL;
$out_rented = NULL;

if(!$stmt->bind_result($out_id, $out_name, $out_cat, $out_length, $out_rented)) {
	echo "Binding output paramters failed: (" . $stmt->errno . ") " . $stmt->error;
}

	echo '<table>
	<tr>
	<td>ID
	<td>Name
	<td>Category
	<td>Length
	<td>Rented
	';


while($stmt->fetch()) {
	echo '<tr>';
	echo '<td>' . $out_id;
	echo '<td>' . $out_name;
	echo '<td>' . $out_cat;
	echo '<td>' . $out_length;
	echo '<td>' . $out_rented;
}
echo '</table>';

echo '<form action="index.php" method="post">
		<input type="checkbox" name="delete_box">
		<input type="submit" name="delete_all" value="Delete All"><br>
	</form>';


echo '</body>
	</html>';
?>