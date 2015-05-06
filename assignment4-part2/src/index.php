<?php

// Display errors

ini_set('display_errors', 'On');

// Create new MySQL object

$mysqli = new mysqli("oniddb.cws.oregonstate.edu", "robinsti-db", "j0dbptMAE8H6RoqT", "robinsti-db");
if ($mysqli->connect_errno) {
	echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}

echo '<!DOCTYPE html>
	<html lang="en">
		<head>
			<meta charset="utf-8" />
			<title>Video Library</title>';

// CSS Styling

echo '<style>
html {
	width: 100%;
	height: 100%;
}

body {
	margin: 0;
	height: 100%;
	min-height: 100%;
	min-width: 610px;
	box-shadow: 0 0 20px grey;
}

#header_box {
	width: 100%;
	height: 25%;
	min-height: 98px;
	max-height: 98px;
	background-color: grey;
	font-family: sans-serif;
	box-shadow: 0 0 15px grey;
}

#header {
	padding-top: 40px;
	padding-left: 5px;
	font-size: 2em;
}

#sub_header {
	padding-left: 5px;
}

#content_box {
	display: inline-block;
	padding-left: 10px;
	float: left;
	width: 70%;
	min-width: 315px;

}

#side_bar {
	width: 30%;
	min-width: 115px;
	max-width: 133px;
	height: 50%;
	float: left;
	border-right-style: solid;
	border-color: #cccccc;
	border-width: 2px;
}

table {
	border: 0;
	border-collapse: collapse;
}

th {
	text-align: left;
	padding-bottom: 0.6em;
	min-width: 60px;
}

td {
	padding-top: 4px;
	border-left: 1px solid black;
	border-right: 1px solid black;
	min-width: 60px;
}

table tr td:first-child {
	border: none;
}
table tr td:last-child {
	border: none;
}
		</style>';	
echo	'</head>
		<body>';
echo '<div id="header_box">
			<div id="header">Tim Robinson</div>
			<div id="sub_header">PHP MySQL Assignment</div>
		<div id="sub_header_box"></div>
		</div>';



// If the DELETE ALL button has been pressed
if(isset($_POST["delete_box"])) {
	if(!$mysqli->query("DROP TABLE IF EXISTS videos")) {
		echo "Table deletion failed: (" . $mysqli->errno . ") " . $mysqli->error;
	}
}

// Create MySQL table if it hasn't been already
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


// ID is auto-incremented, video defaults to available (which means $rented variable is true)
$id;
$rented = 1;

// Add a new video form

echo '<div id="side_bar">';
echo '<br><br><form action="index.php" method="get" name="add_form">
	<input type="text" name="name_field" placeholder="Title"><br>
		<input type="text" name="cat_field" placeholder="Category"><br>
		<input type="number" name="length_field" min="0" max="10000" placeholder="Length"><br>
		<input type="submit" name="sub_button" value="Add Video"><br>
	</form>';

echo '</div>';


// Use do-while loop so we can break the sequence if invalid input was given
do {

	// Only check for values given if a video title has been given
	if(isset($_GET["name_field"])) {
		$name = $_GET["name_field"];
		$cat = $_GET["cat_field"];
		$length = $_GET["length_field"];

		// Remove white space around a given category
		$cat = trim($cat);

		// Validate input for empty name/name that is all white space
		if(($name == '') || (trim($name) == '')) {
			echo "You must provide a name!<br>";
			break;
		}
		
		// Bind parameters
		if(!$stmt->bind_param("issii", $id, $name, $cat, $length, $rented)) {
			echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		}

		// Validate for repeat title name
		if(!$stmt->execute()) {
			if($stmt->errno == 1062) {
				echo "You already entered that title!";
			} else {
				echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
			}
		}
	}
} while(0);

// Updates the checked-in/checked-out with bit flip if check-in/out button is pressed
if(isset($_GET["rentalChange"])) {
	$tmp = $_GET["rentalChange"];
	if(!$mysqli->query("UPDATE videos SET rented = rented ^ 1 WHERE id = $tmp")) {
		echo "Update failed: (" . $mysqli->errno . ") " . $mysqli->error;
	}
}

// Deletes single entry if delete button is pressed
if(isset($_GET["deleteEntry"])) {
	$tmp = $_GET["deleteEntry"];
	if(!$mysqli->query("DELETE FROM videos WHERE id = $tmp")) {
		echo "Deletion failed: (" . $mysqli->errno . ") " . $mysqli->error;
	}
}

echo '<div id="content_box">';

// Category filter form
echo '<form action="index.php" method="get">
		<select name="catSelect">
			<option value="all_movies">All Movies</option>';

// This wil store our list of categories
$tmpArr = array();

if(!($stmt = $mysqli->prepare("SELECT category FROM videos"))) {
	echo "Prepare failed (" . $mysqli->errno . ") " . $mysqli->error;
}
if(!$stmt->execute()) {
	echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
}

// This will store categories used to create the category filter
$tmpCat = NULL;
// This helps store categories in the category list array
$count = 0;

if(!$stmt->bind_result($tmpCat)) {
	echo "Binding output parameter failed: (" . $stmt->errno . ") " . $stmt->error;
}

// Iterate through the categories and create select options for the form
//		if it is not already an option (and is not NULL)
while($stmt->fetch()) {
	if((!in_array($tmpCat, $tmpArr)) && (trim($tmpCat) != '')) {
		echo '<option>' . $tmpCat . '</option>';
		$tmpArr[$count] = $tmpCat;
		$count = $count + 1;
	}
}

echo	'</select>
		<input type="submit" value="Filter">
	</form><br>';


// Print MySQL table

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
		<th>ID
		<th>Name
		<th>Category
		<th>Length
		<th>Rented
	';

while($stmt->fetch()) {

	// If the drop down filter menu has been used
	if(isset($_GET["catSelect"])) {
		$tmpCat = $_GET["catSelect"];

		// If the category matches the selected category
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

		// If the "All Movies" filter has been selected
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

	// Just display the table normally
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

		// Check-in/out button for each entry
		echo '<td>
			<form action="index.php" method="get">
				<input type="hidden" name="rentalChange" value="' . $out_id . '" />
				<input type="submit" value="Check In/Out">
			</form>';

		// Delete button for each entry
		echo '<td>
			<form action="index.php" method="get">
				<input type="hidden" name="deleteEntry" value="' . $out_id . '" />
				<input type="submit" value="Delete">
			</form>';

	}
}
echo '</table><br>';

// Delete all videos
echo '<form action="index.php" method="post">
		<input type="hidden" name="delete_box" value="1">
		<input type="submit" name="delete_all" value="Delete All Videos"><br>
	</form>';

echo '</div>';
echo '</body>
	</html>';
?>