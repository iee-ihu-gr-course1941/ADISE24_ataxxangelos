<?php
// 1. Start the session (if needed)
session_start();

// 2. Include database connection file
require_once('lib/dbconnect.php');  // Assuming you have a connection file to your DB

// 3. Check if the form was submitted and the player names are set
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['player1box']) && isset($_POST['player2box'])) {
    // 4. Get the player names from the form
    $player1 = $_POST['player1box'];
    $player2 = $_POST['player2box'];

    // 5. Sanitize the input to prevent SQL Injection
    $player1 = mysqli_real_escape_string($mysqli, $player1);
    $player2 = mysqli_real_escape_string($mysqli, $player2);

    // 6. Insert player data into the database
    $query = "INSERT INTO players (player1, player2) VALUES ('$player1', '$player2')";
    $result = mysqli_query($mysqli, $query);

    if ($result) {
        // 7. Redirect to the game page or load the game page dynamically
        header('Location: game.html');  // Redirect to the game page (you can create this page)
        exit();
    } else {
        echo "Error: " . mysqli_error($mysqli);
    }
}
?>
