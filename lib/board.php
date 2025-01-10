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
	
?>