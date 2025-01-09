//All HTML spawnable content through jQuery is coded here. Front-End Only

const HtmlSpawners = {
    // Function to spawn the game board
    spawn_board: function() {
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
        $('#game_board_container').empty().append($table);
    },

    spawn_playerform: function() {
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
    },

    spawn_scoreboard: function() {
        const $table = $('<table>').addClass('score_table');

        const $row = $('<tr>');

        const $player1Cell = $('<td>')
            .addClass('player_score')
            .attr('id', 'player1')
            .append(
                $('<p>').addClass('player_name').text('Player 1'),
                $('<p>').addClass('player_color').text('Yellow'),
                $('<p>').addClass('player_score_value').text('0'),
            );

            const $timerCell = $('<td>')
            .addClass('timer')
            .attr('id', 'game_timer')
            .append($('<p>').text('00:00')) // Timer text
            .append($('<p>').attr('id', 'game_status').text('Game Status'))
            .append($('<p>').attr('id', 'player_turn').text('Player turns will be mentioned here'))
            .append($('<p>').attr('id', 'game_result').text('Winner will be announced here')); // Status text
        
        // Add $timerCell to the scoreboard row/table as needed
        

        const $player2Cell = $('<td>')
            .addClass('player_score')
            .attr('id', 'player2')
            .append(
                $('<p>').addClass('player_name').text('Player 2'),
                $('<p>').addClass('player_color').text('Red'),
                $('<p>').addClass('player_score_value').text('0')
            );

        $row.append($player1Cell, $timerCell, $player2Cell);
        $table.append($row);

        $('#game_stats').empty().append($table);
    },

    spawn_command_center: function () {
        // Ensure only one command center exists per tab
        if ($('#command_center').length > 0) {
            console.log("Command center already exists in this tab.");
            return;
        }
    
        // Create a container for the command center
        const $commandCenter = $('<div>')
            .attr('id', 'command_center')
            .addClass('command_center'); // Add a class for styling
    
        // Create a label for the input
        const $label = $('<label>')
            .attr('for', 'command_input')
            .text('Enter Command:');
    
        // Create the input textbox
        const $input = $('<input>')
            .attr('type', 'text')
            .attr('id', 'command_input')
            .addClass('command_input')
            .attr('placeholder', 'e.g., move x1 y1 x2 y2');
    
        // Create the GO button
        const $button = $('<button>')
            .attr('id', 'go_button')
            .addClass('go_button')
            .text('GO');
    
        // Append the elements to the command center
        $commandCenter.append($label, '<br>', $input, '<br>', $button);
    
        // Append the command center to the right of the game board
        $('#game_board_container').after($commandCenter);
    }
        
};