// INIT COMPONENTS ONLOAD DOCUMENT
$(function () {
    spawn_board();
    spawn_playerform();
    spawn_scoreboard();
    update_board();

    //$(window).on('beforeunload', player_exit);
});

// Function to spawn the game board
function spawn_board() {
    const $table = $('<table>').addClass('game_board');

    for (let i = 0; i < 7; i++) {
        const $row = $('<tr>').attr('id', `line_${i}`); // Add unique ID for each row
        for (let j = 0; j < 7; j++) {
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

    const $players_online = $('<p>').attr('id', `players_online`).text(getNumberOfPlayers() + "/2 Players Online");

    var $status_text;

    switch (getNumberOfPlayers()){
        case 0: 
            $status_text='You can set the layout of the game below:';
        break;
        case 1: 
            $status_text='Player 1 is waiting for you to join.';
        break;
        case 2: 
            $status_text='Game is full. Please wait...';
        break;
    }
    var $status_msg = $('<p>').attr('id', `game_status`).text($status_text);


    $form.append($input, $button, $('<br>'), $players_online, $('<br>'), $status_msg);


    $('#player_join').empty().append($form);

    $input.on('input', function () {
        if ($(this).val().trim() !== '') {
            $button.prop('disabled', false); // Enable the button
        } else {
            $button.prop('disabled', true); // Disable the button
        }
    });
}

function getNumberOfPlayers() {
    $.ajax({
        url: 'noOfPlayers.php', // PHP file to handle the query
        type: 'GET',
        success: function(response) {
            console.log("Number of players: " + response);
        },
        error: function(xhr, status, error) {
            console.error("Error fetching number of players: " + error);
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
        .append($('<p>').text('00:00'));

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

function update_board(){

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
