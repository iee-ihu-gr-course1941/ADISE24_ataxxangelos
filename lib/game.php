<?php


function get_stats(){
	global $mysqli;
	$query = "SELECT * FROM game_stats";
	$result = $mysqli->query($query);
	return $result->fetch_all(MYSQLI_ASSOC);
}

function initialize_game(){
	global $mysqli;
	$query="UPDATE game_stats SET g_status = 'initialized'";
	if (!$mysqli->query($query)) {
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['message' => 'Failed to start game.']);
    } 
}

function begin_round(){
	global $mysqli;
	$query = "UPDATE game_stats SET g_status = 'started'";
	if (!$mysqli->query($query)) {
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['message' => 'Failed to start game.']);
    } 
}

function abort_game(){
	global $mysqli;
	$query = "UPDATE game_stats SET g_status = 'aborted'";
	if (!$mysqli->query($query)) {
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['message' => 'Unexpected error.']);
    } 
}

function game_over($winner){
	global $mysqli;
	if ($winner == 'Y'){
		$query = "UPDATE game_stats SET g_status = 'ended', result = 'Y'";
	}else if ($winner == 'R'){
		$query = "UPDATE game_stats SET g_status = 'ended', result = 'R'";
	}else if ($winner == 'D'){
		$query = "UPDATE game_stats SET g_status = 'ended', result = 'D'";
	}
	if (!$mysqli->query($query)) {
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['message' => 'Unexpected error.']);
    }
}

?>