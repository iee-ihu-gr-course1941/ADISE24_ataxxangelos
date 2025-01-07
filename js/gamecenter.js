
var currentPlayers;

// INIT COMPONENTS ONLOAD DOCUMENT
$(function () {
    //Spawn Functions in HTML
    spawn_board();
    spawn_playerform();
    spawn_scoreboard();

    //Interactive Functions
    update_everything();
    setInterval(update_everything, 3000);

    $('#join_button').click(login_to_game);

    //$(window).on('beforeunload', player_exit);
});

function update_everything(){
    update_board();
    update_status();
}

function update_board() {
    $.ajax({
        url: 'ataxx.php/board',
        method: 'GET',
        success: function (response) {
            var boardData = response.board; // Ensure the API returns { board: [...] }
            renderBoard(boardData);
        },
        error: function () {
            console.log("ERROR");
        }
    });
}


function renderBoard(boardData) {
    // `boardData` should be an array of objects with information about each cell
    // Example: [{ x: 1, y: 1, color: 'R' }, { x: 2, y: 1, color: 'Y' }, ...]

    // Loop through the board data
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
            var game_stats=response.stats;
            render_status(game_status);
            
        },
        error: function(){

        }
    });
}

function render_status(gameStats) {
    // Extract individual fields from gameStats
    const gameStatus = gameStats.g_status; // Game status (e.g., 'started')
    const playerTurn = gameStats.p_turn; // Player's turn (e.g., 'R' or 'Y')
    const result = gameStats.result; // Game result (e.g., 'R', 'Y', or 'D')
    const lastChange = gameStats.last_change; // Timestamp of last change

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
            break;
        case 'ended':
            statusText = 'Game Ended';
            break;
        case 'aborted':
            statusText = 'Game Aborted';
            break;
        default:
            statusText = 'Unknown Status';
    }
    $statusElement.text(statusText);
    
    /*
    // Update player turn
    const $turnElement = $('#player_turn'); // Make sure you have an element for player turn
    if (playerTurn) {
        const turnText = playerTurn === 'R' ? 'Red\'s Turn' : 'Yellow\'s Turn';
        $turnElement.text(turnText);
    }

    // Update game result (if any)
    
    const $resultElement = $('#game_result'); // Make sure you have an element for game result
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
        */

    // Optionally log the last change timestamp
    console.log("Last change:", lastChange);
}


// Function to spawn the game board
function spawn_board() {
    const $table = $('<table>').addClass('game_board');

    for (let i = 1; i <= 7; i++) {
        const $row = $('<tr>').attr('id', `line_${i}`); // Add unique ID for each row
        for (let j = 1; j <= 7; j++) {
            const $cell = $('<td>').attr('id', `cell_${i}_${j}`); // Optional: Add unique ID for each cell
            $cell.text(''); 
            $row.append($cell); 
        }
        $table.append($row);
    }
    // Append the table to the container with id="game_board_container"
    console.log($table[0].outerHTML); // Log the generated HTML for debugging
    $('#game_board_container').empty().append($table);
}

function spawn_playerform() {
    const $form = $('<div>').addClass('player-join-form');

    // Create the input field for the player's name
    const $input = $('<input>')
        .attr({
            type: 'text',
            id: 'player_name',
            placeholder: 'Enter your name'
        });

    // Create the join button, initially disabled
    const $button = $('<button>')
        .attr('id', 'join_button')
        .text('Join')
        .prop('disabled', true); // Button starts as disabled

    const $status_msg=$('<p>').attr('id', 'status_msg').text('Message will show up here.');


    $form.append($input, $button, $('<br>'), $status_msg);


    $('#player_join').empty().append($form);

    $input.on('input', function () {
        if ($(this).val().trim() !== '') {
            $button.prop('disabled', false); // Enable the button
        } else {
            $button.prop('disabled', true); // Disable the button
        }
    });
}

function spawn_scoreboard() {
    const $table = $('<table>').addClass('score_table');

    const $row = $('<tr>');

    const $player1Cell = $('<td>')
        .addClass('player_score')
        .attr('id', 'player1')
        .append(
            $('<p>').addClass('player_name').text('Player 1'),
            $('<p>').addClass('player_score_value').text('0')
        );

        const $timerCell = $('<td>')
        .addClass('timer')
        .attr('id', 'game_timer')
        .append($('<p>').text('00:00')) // Timer text
        .append($('<p>').attr('id', 'game_status').text('Game Status')); // Status text
    
    // Add $timerCell to the scoreboard row/table as needed
    

    const $player2Cell = $('<td>')
        .addClass('player_score')
        .attr('id', 'player2')
        .append(
            $('<p>').addClass('player_name').text('Player 2'),
            $('<p>').addClass('player_score_value').text('0')
        );

    $row.append($player1Cell, $timerCell, $player2Cell);
    $table.append($row);

    $('#game_stats').empty().append($table);
}

function login_to_game(){
    // Send an AJAX request to check the current number of players
    $.ajax({
        url: 'ataxx.php/players', 
        method: 'GET', 
        success: function(response) {
            switch(currentPlayers){
                case 0:
                case 1:
                    declare_connection();
                break;
                case 2:
                    $('status_msg').text('Table is full try again later');
                break;
                default:
                    $('status_msg').text('An error occured. Please try again later.');
                break;
            }
        },
        error: function() {
            $('#status_msg').text('An error occurred. Please try again later.');
        }
    });
}

function declare_connection(){
    joinPlayer();

}

function checkAvailability(){

}

function joinPlayer(){
    $.ajax({
        url: 'ataxx.php/players', 
        method: 'POST',
        data: {
            username: $('#player_name').val()
        },
        success: function(response){
            // Handle the success response here
            $('#status_msg').text('You have joined the game!');
        },
        error: function(){
            $('#status_msg').text('An error occurred. Please try again later.');
        }
    });
    const $game_menu=$('#game_menu')
}




// Function to handle player exit when they close the tab or browser
/*function player_exit() {
    const playerName = $('#p1inputbox').val();  // Assuming the player's name is stored here

    if (playerName) {
        $.ajax({
            url: 'remove_player.php', // Endpoint to remove the player
            method: 'POST',
            data: {
                playerName: playerName  // Send the player's name
            },
            async: false,  // Ensure it runs synchronously before the page is unloaded
            success: function (response) {
                console.log('Player removed successfully.');
            },
            error: function () {
                console.error('Error removing player.');
            }
        });
    }
}*/
