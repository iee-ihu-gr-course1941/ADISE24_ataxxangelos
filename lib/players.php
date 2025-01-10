<?php

//METHOD GET /players
function get_players() {
	global $mysqli;
    $query = "SELECT * FROM players ORDER BY piece_color DESC";
    $result = $mysqli->query($query);
    return $result->fetch_all(MYSQLI_ASSOC);
}

//METHOD POST /players
function add_player($name) {
    global $mysqli;

    $query = "SELECT COUNT(*) as count FROM players";
    $result = $mysqli->query($query);
    $row = $result->fetch_assoc();
    $player_count = $row['count'];
    if ($player_count == 0) {
        $color = 'Y'; 
    } elseif ($player_count == 1) {
        $color = 'R'; 
    } else {
        header('HTTP/1.1 400 Bad Request');
        echo json_encode(['message' => 'Table is full. Cannot add more players.']);
        exit;
    }
    $token = bin2hex(random_bytes(16)); // Generates a 32-character unique token
    $query = "INSERT INTO players (username, piece_color, token, score) VALUES ('$name', '$color', '$token', 2)";
    if (!$mysqli->query($query)) {
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['message' => 'Failed to add player.']);
    }
}


//METHOD DELETE /players
function remove_player($name) {
    global $mysqli;
    $query = "DELETE FROM players WHERE username = '$name'";
    if (!$mysqli->query($query)) {
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['message' => 'Failed to add player.']);
    }
}




?>
