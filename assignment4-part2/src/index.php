<?php
ini_set('display_errors', 'On');

$mysqli = new mysqli("oniddb.cws.oregonstate.edu", "robinsti-db", "j0dbptMAE8H6RoqT", "robinsti-db");
if ($mysqli->connect_errno) {
	echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;

} else {
	echo "Video library connected!<br><br>";
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

if(!$mysqli->query("CREATE TABLE IF NOT EXISTS videos(
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
$rented = 1;

echo '<form action="index.php" method="get" name="add_form">
	Title: <input type="text" name="name_field"><br>
		Category: <input type="text" name="cat_field"><br>
		Length: <input type="number" name="length_field" min="0" max="10000"><br>
		<input type="submit" name="sub_button" value="Add Video"><br>
	</form>';

do {

	if(isset($_GET["name_field"])) {
		$name = $_GET["name_field"];
		$cat = $_GET["cat_field"];
		$length = $_GET["length_field"];

		if(($name == '') || ($name == ' ')) {
			echo "You must provide a name!<br>";
			break;
		} else if(isset($_GET["length_field"])) {
			if(is_int($length)) {
				echo "Length must be a positive integer!<br>";
				break;
			}
		} 
		

		if(!$stmt->bind_param("issii", $id, $name, $cat, $length, $rented)) {
			echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		}

		if(!$stmt->execute()) {
			echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
		}
	}
} while(0);

if(isset($_GET["rentalChange"])) {
	$tmp = $_GET["rentalChange"];
	if(!$mysqli->query("UPDATE videos SET rented = rented ^ 1 WHERE id = $tmp")) {
		echo "Update failed: (" . $mysqli->errno . ") " . $mysqli->error;
	}
}

if(isset($_GET["deleteEntry"])) {
	$tmp = $_GET["deleteEntry"];
	if(!$mysqli->query("DELETE FROM videos WHERE id = $tmp")) {
		echo "Deletion failed: (" . $mysqli->errno . ") " . $mysqli->error;
	}
}

// Get elements from the table




echo 'Show: 
	<form action="index.php" method="get">
		<select name="catSelect">
			<option value="all_movies">All Movies</option>';

$tmpArr = array();


if(!($stmt = $mysqli->prepare("SELECT category FROM videos"))) {
	echo "Prepare failed (" . $mysqli->errno . ") " . $mysqli->error;
}
if(!$stmt->execute()) {
	echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
}

$tmpCat = NULL;
$count = 0;
if(!$stmt->bind_result($tmpCat)) {
	echo "Binding output parameter failed: (" . $stmt->errno . ") " . $stmt->error;
}

while($stmt->fetch()) {
	if((!in_array($tmpCat, $tmpArr)) && ($tmpCat != '')) {
		echo '<option>' . $tmpCat . '</option>';
		$tmpArr[$count] = $tmpCat;
		$count = $count + 1;
	}
}

echo	'</select>
		<input type="submit" value="Filter">
	</form>';






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
	if(isset($_GET["catSelect"])) {
		$tmpCat = $_GET["catSelect"];
		if($tmpCat == $out_cat) {
			echo '<tr>';
			echo '<td>' . $out_id;
			echo '<td>' . $out_name;
			echo '<td>' . $out_cat;
			echo '<td>' . $out_length;
			if($out_rented == 1) {
				echo '<td>Available';
			} else {
				echo '<td>Checked Out';
			}
			echo '<td>
				<form action="index.php" method="get">
					<input type="hidden" name="rentalChange" value="' . $out_id . '" />
					<input type="submit" value="Check In/Out">
				</form>';
			echo '<td>
				<form action="index.php" method="get">
					<input type="hidden" name="deleteEntry" value="' . $out_id . '" />
					<input type="submit" value="Delete">
				</form>';
		} else if($tmpCat == 'all_movies'){
			echo '<tr>';
			echo '<td>' . $out_id;
			echo '<td>' . $out_name;
			echo '<td>' . $out_cat;
			echo '<td>' . $out_length;
			if($out_rented == 1) {
				echo '<td>Available';
			} else {
				echo '<td>Checked Out';
			}
			echo '<td>
				<form action="index.php" method="get">
					<input type="hidden" name="rentalChange" value="' . $out_id . '" />
					<input type="submit" value="Check In/Out">
				</form>';
			echo '<td>
				<form action="index.php" method="get">
					<input type="hidden" name="deleteEntry" value="' . $out_id . '" />
					<input type="submit" value="Delete">
				</form>';
			}
	} else {
		echo '<tr>';
		echo '<td>' . $out_id;
		echo '<td>' . $out_name;
		echo '<td>' . $out_cat;
		echo '<td>' . $out_length;
		if($out_rented == 1) {
			echo '<td>Available';
		} else {
			echo '<td>Checked Out';
		}
		echo '<td>
			<form action="index.php" method="get">
				<input type="hidden" name="rentalChange" value="' . $out_id . '" />
				<input type="submit" value="Check In/Out">
			</form>';
		echo '<td>
			<form action="index.php" method="get">
				<input type="hidden" name="deleteEntry" value="' . $out_id . '" />
				<input type="submit" value="Delete">
			</form>';

	}
}
echo '</table><br>';

echo '<form action="index.php" method="post">
		<input type="hidden" name="delete_box" value="1">
		<input type="submit" name="delete_all" value="Delete All Videos"><br>
	</form>';


echo '</body>
	</html>';
?>