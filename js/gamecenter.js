var me={username:null, color:null, token:null};
var timerInterval=null;
var game_status={};
let isCommandCenterSpawned = false;

// INIT COMPONENTS ONLOAD DOCUMENT
$(function () {
    //HTML Spawn Functions
    HtmlSpawners.spawn_board();
    HtmlSpawners.spawn_playerform();
    HtmlSpawners.spawn_scoreboard();

    //Update Elements; Sync everything with the database
    update_everything();
    setInterval(update_everything, 3000);

    //Event Handlers
    $('#join_button').click(login_to_game);

    //$(window).on('beforeunload', player_exit);
});

$(window).on('beforeunload', function() {
    if (me!=null) player_exit();
});

function update_everything(){
    update_board();
    update_status();
    update_players();
}

function update_board() {
    $.ajax({
        url: 'ataxx.php/board',
        method: 'GET',
        success: function (response) {
            var boardData = response.board;
            renderBoard(boardData);
        },
        error: function () {
            console.log("ERROR");
        }
    });
}


function renderBoard(boardData) {
    boardData.forEach(cell => {
        const cellId = `cell_${cell.x}_${cell.y}`; // Adjust by -1 to match the correct grid
        const $cell = $(`#${cellId}`); // Get the cell element by its ID

        // Clear the cell's content first
        $cell.empty();

        // Add the piece image based on the cell's color
        if (cell.piece_color === 'R') {
            $cell.append('<img src="pieces/red.png" alt="Red Piece" class="piece">');
        } else if (cell.piece_color === 'Y') {
            $cell.append('<img src="pieces/yellow.png" alt="Yellow Piece" class="piece">');
        }
    });
}

function update_status(){
    $.ajax({
        url: 'ataxx.php/status',
        method: 'GET',
        success: function(response){
            game_status=response.stats;
            render_status(game_status);
            
        },
        error: function(){

        }
    });
}

function render_status(game_status) {
    // Extract individual fields from gameStats
    const gameStatus = game_status[0].g_status; // Game status (e.g., 'started')
    const playerTurn = game_status[0].p_turn; // Player's turn (e.g., 'R' or 'Y')
    const result = game_status[0].result; // Game result (e.g., 'R', 'Y', or 'D')
    const lastChange = game_status[0].last_change; // Timestamp of last change

    // Update the game status
    const $statusElement = $('#game_status');
    let statusText;
    switch (gameStatus) {
        case 'not active':
            statusText = 'Not Active';
            break;
        case 'initialized':
            statusText = 'Initialized';
            break;
        case 'started':
            statusText = 'Game Started';
            const $turnElement = $('#player_turn');
            if (playerTurn) {
            const turnText = playerTurn === 'R' ? 'Red\'s Turn' : 'Yellow\'s Turn';
            $turnElement.text(turnText);
            }
            break;
        case 'ended':
            statusText = 'Game Ended';
            stop_timer();
            $('#command_center').hide();
            const $resultElement = $('#game_result');
            if (result) {
                let resultText;
            switch (result) {
                case 'R':
                    resultText = 'Red Wins!';
                    break;
                case 'Y':
                    resultText = 'Yellow Wins!';
                    break;
                case 'D':
                    resultText = 'It\'s a Draw!';
                    break;
                default:
                    resultText = '';
        }
        $resultElement.text(resultText);
    }
            break;
        case 'aborted':
            statusText = 'Game Aborted';
            stop_timer();
            $('#command_center').hide();
            break;
        default:
            statusText = 'Unknown Status';
    }
    $statusElement.text(statusText);
    

    // Optionally log the last change timestamp
    console.log("Last change:", lastChange);
}

function update_players(){
    $.ajax({
        url: 'ataxx.php/players',
        method: 'GET',
        success: function(response){
            var playersQuery=response.players;
            render_players(playersQuery);
        }
    })
}

function render_players(playersQuery) {
    for (var i = 0; i <= 1; i++) {
        switch (i) {
            case 0: // Player 1
                if (playersQuery[i] == null) { // If Player 1 hasn't joined
                    $('#player1 .player_name').text('Player 1'); // Reset name
                    $('#player1 .player_score_value').text('0'); // Reset score
                } else { 
                    $('#player1 .player_name').text(playersQuery[i].username); // Update name
                    $('#player1 .player_score_value').text(playersQuery[i].score || '0'); // Update score (default 0 if null)
                    if (playersQuery[i].username=me.username){ //To update the local object
                        me.color=playersQuery[i].piece_color;
                        me.token=playersQuery[i].token;
                    }
                }
                break;
            case 1: // Player 2
                if (playersQuery[i] == null) { // If Player 2 hasn't joined
                    $('#player2 .player_name').text('Player 2'); // Reset name
                    $('#player2 .player_score_value').text('0'); // Reset score
                } else { 
                    $('#player2 .player_name').text(playersQuery[i].username); // Update name
                    $('#player2 .player_score_value').text(playersQuery[i].score || '0'); // Update score (default 0 if null)
                    if (playersQuery[i].username=me.username){ //To update the local object
                        me.color=playersQuery[i].piece_color;
                        me.token=playersQuery[i].token;
                    }
                    console.log($('#game_status').text());
                    if ($('#game_status').text() == 'Initialized') start_game();
                }
                break;
        }
    }
}

function login_to_game(){
    $.ajax({
        url: 'ataxx.php/players', 
        method: 'GET', 
        success: function(response) {
            switch(response.count){
                case 0:
                    joinPlayer();
                    initialize_game();
                break;
                case 1:
                    joinPlayer();
                break;
                case 2:
                    $('#status_msg').text('Table is full. Please try again later.');
                break;
                default:
                    $('#status_msg').text('An error occured. Please try again later.');
                break;
            }
        },
        error: function() {
            $('#status_msg').text('An error occurred. Please try again later.');
            return false;
        }
    });
}

function joinPlayer() {
    $.ajax({
        url: 'ataxx.php/players',
        method: 'POST',
        contentType: 'application/json', 
        dataType: 'json',
        data: JSON.stringify({username: $('#player_name').val()}),
        success: function(response) {
            $('#status_msg').text(response.message);
            me.username=$('#player_name').val();
            $('#player_name').hide();
            $('#join_button').hide();
        },
        error: function(xhr, status, error) {
            console.log('Error response:', xhr.responseText);  // Log the error response
            $('#status_msg').text('An error occurred. Please try again later.');
        }
    });
}

function player_exit() {
    const playerName = me.username; 

    if (playerName) {
        fetch('ataxx.php/players', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ username: playerName })
        })
        .then(response => {
            if (!response.ok) {
                console.error('Failed to remove player:', response.statusText);
            } else {
                console.log('Player removed successfully');
            }
        })
        .catch(error => {
            console.error('Error during player removal:', error);
        });
    }
}

function initialize_game(){
    $.ajax({
        url: 'ataxx.php/status',
        method: 'PUT',
        contentType: 'application/json',
        datatype: 'json',
        data: JSON.stringify({shiftgame: 'initialize'}),
        success: function(response){
            console.log(response);
        }
    });
}

function start_game() {
    // Proceed with game start actions (timer, status update, etc.)
    $.ajax({
        url: 'ataxx.php/status',
        method: 'PUT',
        contentType: 'application/json',
        dataType: 'json',
        data: JSON.stringify({ shiftgame: 'start' }),
        success: function(response) {
            console.log('Game started:', response);
            HtmlSpawners.spawn_command_center();
            let secondsElapsed = 0;
            setInterval(function() {
                secondsElapsed++;
                const minutes = Math.floor(secondsElapsed / 60);
                const seconds = secondsElapsed % 60;
                $('#game_timer p:first').text(`${minutes}:${seconds.toString().padStart(2, '0')}`);
            }, 1000);
        }
    });
}



function stop_timer(){
    if (typeof timerInterval !== 'undefined') {
        clearInterval(timerInterval); // Stops the timer
        timerInterval = null; // Reset the variable to avoid accidental reuse
        $('#game_timer p:first').text('00:00'); // Optionally reset the timer display
        $('#command_center_msg').text('Timer stopped.'); // Optional status update
    }
    
}








