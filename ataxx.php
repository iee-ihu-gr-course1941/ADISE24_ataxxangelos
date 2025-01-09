<?php

require_once "lib/board.php";
require_once "lib/dbconnect.php";
require_once "lib/game.php";
require_once "lib/players.php";


//INIT REST API
$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'],'/'));
$input = json_decode(file_get_contents('php://input'),true);
if($input==null) {
    $input=[];
}
if(isset($_SERVER['HTTP_X_TOKEN'])) {
    $input['token']=$_SERVER['HTTP_X_TOKEN'];
} else {
    $input['token']='';
}

switch ($r=array_shift($request)) {
    case 'board' : 
        switch ($b=array_shift($request)) {
            case '':
            case null: handle_board($method,$input);
                        break;
            case 'piece': handle_piece($method, $request[0],$request[1],$input);
                        break;
            }
            break;
    case 'status': 
			if(sizeof($request)==0) {handle_status($method);}
			else {header("HTTP/1.1 404 Not Found");}
			break;
	case 'players': handle_player($method, $request,$input);
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
            //show_board($input);
    } else {
        header('HTTP/1.1 405 Method Not Allowed');
    }
    
}

function handle_piece($method, $x,$y,$input) {
    if($method=='GET') {
        show_piece($x,$y);
    } else if ($method=='PUT') {
        move_piece($x,$y,$input['x'],$input['y'],  $input['token']);
    }    


}

// Ensure the response is a single JSON object
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
        $username = $input['username'];
        remove_player($username);
        echo json_encode(['status' => 'Player removed']);
    }
}





function handle_status($method){
    if ($method == 'GET'){
        $stats = get_stats();
        header('Content-Type: application/json');
        echo json_encode(['stats' => $stats]);
    }//elseif ($method == 'PUT'){    }
}




?>

