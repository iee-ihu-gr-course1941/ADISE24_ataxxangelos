<?php
require_once('lib/dbconnect.php');

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $player1 = sanitize_input($_POST['player1box']);
    $player2 = sanitize_input($_POST['player2box']);
    $layout = sanitize_input($_POST['layout']);
    
    update_players($player1, $player2);

    update_board_layout($layout);

    header('Location: game.html');
    exit();
}

function sanitize_input($data) {
    global $mysqli;
    return mysqli_real_escape_string($mysqli, trim($data));
}

function update_players($player1, $player2) {
    global $mysqli;

    $mysqli->query("DELETE FROM players");

    $stmt1 = $mysqli->prepare("INSERT INTO players (username, piece_color, token) VALUES (?, 'Y', ?)");
    $token1 = generate_token();
    $stmt1->bind_param("ss", $player1, $token1);
    $stmt1->execute();

    $stmt2 = $mysqli->prepare("INSERT INTO players (username, piece_color, token) VALUES (?, 'R', ?)");
    $token2 = generate_token();
    $stmt2->bind_param("ss", $player2, $token2);
    $stmt2->execute();
}

function update_board_layout($layout) {
    global $mysqli;

    // Determine the JSON file path
    $file_path = __DIR__ . "/layouts/$layout.json";

    if (!file_exists($file_path)) {
        die("Error: Layout file '$layout.json' not found.");
    }

    $layout_data = file_get_contents($file_path);

    // Call the stored procedure to update obstacles
    $stmt = $mysqli->prepare("CALL update_obstacles(?)");
    $stmt->bind_param("s", $layout_data);
    $stmt->execute();
}

function generate_token() {
    return bin2hex(random_bytes(16));
}
?>
