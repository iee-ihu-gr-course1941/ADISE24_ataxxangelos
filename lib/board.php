<?php

	function get_board(){
		global $mysqli;
		$query = "SELECT * FROM board";
		$result = $mysqli->query($query);
		return $result->fetch_all(MYSQLI_ASSOC);
	}

	function move_piece($x1, $y1, $x2, $y2, $s_color, $s_token) {
		global $mysqli;
	
		$dx = abs($x2 - $x1);
		$dy = abs($y2 - $y1);
	
		// Check if it's the player's turn
		$turnquery = "SELECT p_turn FROM game_stats";
		$result = $mysqli->query($turnquery);
		$row = $result->fetch_assoc();
		if ($row['p_turn'] !== $s_color) {
			http_response_code(403);
			echo json_encode(["error" => "It's not your turn."]);
			return;
		}
	
		// Verify token and color
		if ($s_color !== get_color($s_token)) {
			http_response_code(403);
			echo json_encode(["error" => "Invalid token or unauthorized move."]);
			return;
		}
	
		// Determine the type of move
		if ($dx <= 1 && $dy <= 1) {
			// Duplication move
			$stmt = $mysqli->prepare("CALL duplicate_piece(?, ?, ?, ?, ?)");
			$stmt->bind_param('iiiis', $x1, $y1, $x2, $y2, $s_color);
		} elseif ($dx <= 2 && $dy <= 2) {
			// Jump move
			$stmt = $mysqli->prepare("CALL jump_piece(?, ?, ?, ?, ?)");
			$stmt->bind_param('iiiis', $x1, $y1, $x2, $y2, $s_color);
		} else {
			http_response_code(400);
			echo json_encode(["error" => "Invalid move distance."]);
			return;
		}
	
		// Execute the query
		if ($stmt && $stmt->execute()) {
			echo json_encode(["success" => "Move executed successfully."]);
		} else {
			http_response_code(500);
			echo json_encode(["error" => "Database error: " . $stmt->error]);
		}
	
		if ($stmt) {
			$stmt->close();
		}
	
		// Check for deadlock and switch turn
		if (!check_deadlock($s_color)) {
			switch_turn($s_token);
		} else {
			echo json_encode(["notice" => "Opponent is deadlocked. Turn not switched."]);
		}
	}
	
	
	function switch_turn($current_player_token) {
		global $mysqli;
	
		// Get the current player's color
		$stmt = $mysqli->prepare("SELECT piece_color FROM players WHERE token = ?");
		$stmt->bind_param('s', $current_player_token);
	
		if ($stmt->execute()) {
			$stmt->store_result();
			$stmt->bind_result($current_player_color);
			$stmt->fetch();
	
			// Determine the new turn
			$new_turn = ($current_player_color === 'Y') ? 'R' : 'Y';
	
			// Update the turn in the database
			$stmt = $mysqli->prepare("UPDATE game_stats SET p_turn = ?");
			$stmt->bind_param('s', $new_turn);
			$stmt->execute();
			echo json_encode(["success" => "Turn switched to $new_turn."]);
		} else {
			http_response_code(500);
			echo json_encode(["error" => "Error switching turns."]);
		}
	}
	

	function check_deadlock($player_color) {
		global $mysqli;
	
		$opponent_color = ($player_color === 'Y') ? 'R' : 'Y';
	
		// Query to check if the opponent has any valid moves
		$stmt = $mysqli->prepare("
			SELECT COUNT(*) AS valid_moves
			FROM board b
			WHERE b.piece_color = ?
			  AND EXISTS (
				  SELECT 1
				  FROM board
				  WHERE piece_color IS NULL
					AND ABS(x - b.x) <= 2
					AND ABS(y - b.y) <= 2
			  )
		");
		$stmt->bind_param('s', $opponent_color);
	
		if ($stmt->execute()) {
			$result = $stmt->get_result();
			$row = $result->fetch_assoc();
	
			// Return true if no valid moves exist
			return $row['valid_moves'] == 0;
		}
	
		return false; // Default to no deadlock
	}
	
	
	function get_color($s_token) {
		global $mysqli;
	
		$stmt = $mysqli->prepare("SELECT piece_color FROM players WHERE token = ?");
		$stmt->bind_param('s', $s_token);
	
		if ($stmt->execute()) {
			$result = $stmt->get_result();
			if ($result->num_rows > 0) {
				$row = $result->fetch_assoc();
				return $row['piece_color']; // Return the player's color
			}
		}
	
		return null; // Return null if token is invalid or not found
	}
	

	
	
 


/* function move_piece($x,$y,$x2,$y2,$token) {
	
	if($token==null || $token=='') {
		header("HTTP/1.1 400 Bad Request");
		print json_encode(['errormesg'=>"token is not set."]);
		exit;
	}
	
	$color = current_color($token);
	if($color==null ) {
		header("HTTP/1.1 400 Bad Request");
		print json_encode(['errormesg'=>"You are not a player of this game."]);
		exit;
	}
	$status = read_status();
	if($status['status']!='started') {
		header("HTTP/1.1 400 Bad Request");
		print json_encode(['errormesg'=>"Game is not in action."]);
		exit;
	}
	if($status['p_turn']!=$color) {
		header("HTTP/1.1 400 Bad Request");
		print json_encode(['errormesg'=>"It is not your turn."]);
		exit;
	}
	$orig_board=read_board();
	$board=convert_board($orig_board);
	$n = add_valid_moves_to_piece($board,$color,$x,$y);
	
	if($n==0) {
		header("HTTP/1.1 400 Bad Request");
		print json_encode(['errormesg'=>"This piece cannot move."]);
		exit;
	}
	foreach($board[$x][$y]['moves'] as $i=>$move) {
		if($x2==$move['x'] && $y2==$move['y']) {
			do_move($x,$y,$x2,$y2);
			exit;
		}
	}
	header("HTTP/1.1 400 Bad Request");
	print json_encode(['errormesg'=>"This move is illegal."]);
	exit;
}
function do_move($x,$y,$x2,$y2) {
	global $mysqli;
	$sql = 'call `move_piece`(?,?,?,?);';
	$st = $mysqli->prepare($sql);
	$st->bind_param('iiii',$x,$y,$x2,$y2 );
	$st->execute();

	header('Content-type: application/json');
	print json_encode(read_board(), JSON_PRETTY_PRINT);
}
function show_piece($x,$y) {
	global $mysqli;
	
	$sql = 'select * from board where x=? and y=?';
	$st = $mysqli->prepare($sql);
	$st->bind_param('ii',$x,$y);
	$st->execute();
	$res = $st->get_result();
	header('Content-type: application/json');
	print json_encode($res->fetch_all(MYSQLI_ASSOC), JSON_PRETTY_PRINT);
}
function show_board($input) {
	global $mysqli;
	
	$b=current_color($input['token']);
	if($b) {
		show_board_by_player($b);
	} else {
		header('Content-type: application/json');
		print json_encode(read_board(), JSON_PRETTY_PRINT);
	}
}
function show_board_by_player($b) {

	global $mysqli;

	$orig_board=read_board();
	$board=convert_board($orig_board);
	$status = read_status();
	if($status['status']=='started' && $status['p_turn']==$b && $b!=null) {
		// It my turn !!!!
		$n = add_valid_moves_to_board($board,$b);
		
		// Εάν n==0, τότε έχασα !!!!!
		// Θα πρέπει να ενημερωθεί το game_status.
	}
	header('Content-type: application/json');
	print json_encode($orig_board, JSON_PRETTY_PRINT);
}


function add_valid_moves_to_board(&$board,$b) {
	$number_of_moves=0;
	
	for($x=1;$x<9;$x++) {
		for($y=1;$y<9;$y++) {
			$number_of_moves+=add_valid_moves_to_piece($board,$b,$x,$y);
		}
	}
	return($number_of_moves);
}



function add_valid_moves_to_piece(&$board,$b,$x,$y) {
	$number_of_moves=0;
	if($board[$x][$y]['piece_color']==$b) {
		switch($board[$x][$y]['piece']){
			case 'P': $number_of_moves+=pawn_moves($board,$b,$x,$y);break;
			case 'K': $number_of_moves+=king_moves($board,$b,$x,$y);break;
			case 'Q': $number_of_moves+=queen_moves($board,$b,$x,$y);break;
			case 'R': $number_of_moves+=rook_moves($board,$b,$x,$y);break;
			case 'N': $number_of_moves+=knight_moves($board,$b,$x,$y);break;
			case 'B': $number_of_moves+=bishop_moves($board,$b,$x,$y);break;
		}
	} 
	return($number_of_moves);
}


function pawn_moves(&$board,$b,$x,$y) {
	
	$direction=($b=='W')?1:-1;
	$start_row = ($b=='W')?2:7;
	$moves=[];
	
	if($board[$x][$y+$direction]['piece_color']==null) {
		$move=['x'=>$x, 'y'=>$y+$direction];
		$moves[]=$move;
		if($y==$start_row && $board[$x][$y+2*$direction]['piece_color']==null) {
			$move=['x'=>$x, 'y'=>$y+2*$direction];
			$moves[]=$move;
		}
	}
	$j=$y+$direction;
	if($j>=1 && $j<=8) {
		for($i=$x-1;$i<=$x+1;$i+=2) {
			if($i>=1 && $i<=8 && $board[$i][$j]['piece_color']!=null && $board[$i][$j]['piece_color']!=$b) {
				$move=['x'=>$i, 'y'=>$j];
				$moves[]=$move;
			}
		}
	}

	$board[$x][$y]['moves'] = $moves;
	return(sizeof($moves));
	
}


function convert_board(&$orig_board) {
	$board=[];
	foreach($orig_board as $i=>&$row) {
		$board[$row['x']][$row['y']] = &$row;
	} 
	return($board);
}

function read_board() {
	global $mysqli;
	$sql = 'select * from board';
	$st = $mysqli->prepare($sql);
	$st->execute();
	$res = $st->get_result();
	return($res->fetch_all(MYSQLI_ASSOC));
}
function reset_board() {
	global $mysqli;
	
	$sql = 'call clean_board()';
	$mysqli->query($sql);
	//show_board();
}
*/
?>