// INIT COMPONENTS ONLOAD DOCUMENT
$(function () {
    spawn_board();
});

function spawn_board() {
    const $table = $('<table>').addClass('gameBoard');

    for (let i = 0; i < 7; i++) {
        const $row = $('<tr>'); 
        for (let j = 0; j < 7; j++) {
            const $cell = $('<td>'); 
            $cell.text(''); 
            $row.append($cell); 
        }
        $table.append($row);
    }
    // Append the table to the container with id="gameBoardContainer"
    console.log($table[0].outerHTML);
    $('#game_board_container').empty().append($table);
}
