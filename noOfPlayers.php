<?php
require_once lib/dbconnect.php

$query = "SELECT COUNT(*) AS player_count FROM players";
$result = $mysqli->query($query);

// Check if the query was successful
if ($result) {
    $row = $result->fetch_assoc();
    echo $row['player_count']; // Return the count as a response
} else {
    echo "Error fetching players count.";
}

?>
