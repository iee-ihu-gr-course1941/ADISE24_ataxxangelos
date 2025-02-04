<?php

require_once "lib/board.php";
require_once "lib/dbconnect.php";
require_once "lib/game.php";
require_once "lib/players.php";


//INIT REST API
$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'],'/'));
$input = json_decode(file_get_contents('php://input'),true);
/*if($input==null) {
    $input=[];
}
if(isset($_SERVER['HTTP_X_TOKEN'])) {
    $input['token']=$_SERVER['HTTP_X_TOKEN'];
} else {
    $input['token']='';
}
*/
switch ($r=array_shift($request)) {
    case 'board' : 
        switch ($b=array_shift($request)) {
            case '':
            case null: handle_board($method,$input);
                        break;
            case 'piece': handle_piece($method, $input);
                        break;
            }
            break;
    case 'status': 
			if(sizeof($request)==0) {handle_status($method, $input);}
			else {header("HTTP/1.1 404 Not Found");}
			break;
	case 'players': handle_player($method, $request, $input);
			    break;
	default:  header("HTTP/1.1 404 Not Found");
                        exit;
}

function handle_board($method,$input) {
    if($method=='GET') {
            $board=get_board();
            header('Content-Type: application/json');
            echo json_encode(['board' => $board]);
    } else if ($method=='PUT') {
            //reset_board();
    } else {
        header('HTTP/1.1 405 Method Not Allowed');
    }
    
}

function handle_piece($method, $input) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if ($method == 'PUT') {
        if (isset($input['movedata'])) { // Extract the 'movedata' object
            $moveData = $input['movedata'];
            move_piece($moveData['x1'], $moveData['y1'], $moveData['x2'], $moveData['y2'], $moveData['color'], $moveData['token']);
        } else {
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(["error" => "Invalid request payload."]);
        }
    } else {
        header('HTTP/1.1 405 Method Not Allowed');
    }
}


function handle_player($method, $input) {
    if ($method == 'GET') {
        $players = get_players();
        header('Content-Type: application/json');
        echo json_encode([
            'players' => $players,
            'count' => count($players)
        ]); 
    } elseif ($method == 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $username = $input['username'];  // Get the username from input
        add_player($username);  // The add_player function will handle the token and everything else
        header('Content-Type: application/json');
        echo json_encode(['message' => 'You jave joined the game!']);
    } elseif ($method == 'DELETE') {
        $input = json_decode(file_get_contents('php://input'), true);
        $username = $input['username'];
        remove_player($username);
        echo json_encode(['message' => 'Player removed successfully']);
    } else {
        header('HTTP/1.1 405 Method Not Allowed');
    }
}





function handle_status($method, $input){
    $input = json_decode(file_get_contents('php://input'),true);
    if ($method == 'GET'){
        $stats = get_stats();
        header('Content-Type: application/json');
        echo json_encode(['stats' => $stats]);
    }elseif ($method == 'PUT'){
        $shiftgame=$input['shiftgame'];
        switch ($shiftgame){
            case 'initialize': initialize_game();
            break;
            case 'start': begin_round();
            break;
            case 'ended': game_over($input['victory']);
            break;
        }
        echo json_encode(['message' => 'Game Status changed.']);
    }else {
        header('HTTP/1.1 405 Method Not Allowed');
    }
}




?>

