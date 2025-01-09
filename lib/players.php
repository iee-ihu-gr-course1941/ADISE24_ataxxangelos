<?php

//METHOD GET /players
function get_players() {
	global $mysqli;
    $query = "SELECT username, piece_color, score FROM players ORDER BY piece_color DESC";
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













/*
function show_users() {
	global $mysqli;
	$sql = 'select username,piece_color from players';
	$st = $mysqli->prepare($sql);
	$st->execute();
	$res = $st->get_result();
	header('Content-type: application/json');
	print json_encode($res->fetch_all(MYSQLI_ASSOC), JSON_PRETTY_PRINT);
}
function show_user($b) {
	global $mysqli;
	$sql = 'select username,piece_color from players where piece_color=?';
	$st = $mysqli->prepare($sql);
	$st->bind_param('s',$b);
	$st->execute();
	$res = $st->get_result();
	header('Content-type: application/json');
	print json_encode($res->fetch_all(MYSQLI_ASSOC), JSON_PRETTY_PRINT);
}

function set_user($b,$input) {
	//print_r($input);
	if(!isset($input['username']) || $input['username']=='') {
		header("HTTP/1.1 400 Bad Request");
		print json_encode(['errormesg'=>"No username given."]);
		exit;
	}
	$username=$input['username'];
	global $mysqli;
	$sql = 'select count(*) as c from players where piece_color=? and username is not null';
	$st = $mysqli->prepare($sql);
	$st->bind_param('s',$b);
	$st->execute();
	$res = $st->get_result();
	$r = $res->fetch_all(MYSQLI_ASSOC);
	if($r[0]['c']>0) {
		header("HTTP/1.1 400 Bad Request");
		print json_encode(['errormesg'=>"Player $b is already set. Please select another color."]);
		exit;
	}
	$sql = 'update players set username=?, token=md5(CONCAT( ?, NOW()))  where piece_color=?';
	$st2 = $mysqli->prepare($sql);
	$st2->bind_param('sss',$username,$username,$b);
	$st2->execute();


	
	update_game_status();
	$sql = 'select * from players where piece_color=?';
	$st = $mysqli->prepare($sql);
	$st->bind_param('s',$b);
	$st->execute();
	$res = $st->get_result();
	header('Content-type: application/json');
	print json_encode($res->fetch_all(MYSQLI_ASSOC), JSON_PRETTY_PRINT);
	
	
}

function handle_user($method, $b,$input) {
	if($method=='GET') {
		show_user($b);
	} else if($method=='PUT') {
        set_user($b,$input);
    }
}
function current_color($token) {
	
	global $mysqli;
	if($token==null) {return(null);}
	$sql = 'select * from players where token=?';
	$st = $mysqli->prepare($sql);
	$st->bind_param('s',$token);
	$st->execute();
	$res = $st->get_result();
	if($row=$res->fetch_assoc()) {
		return($row['piece_color']);
	}
	return(null);
}



*/
?>
