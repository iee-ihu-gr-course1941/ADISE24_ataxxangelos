$(function () {
    updatePlayerStatus();
    

    // Periodically check the player status every 5 seconds
    setInterval(updatePlayerStatus, 5000);
});

function updatePlayerStatus() {
    $.ajax({
        url: 'status.php', // Endpoint to fetch player status
        method: 'GET',
        dataType: 'json',
        success: function (response) {
            const playerCount = response.playerCount;
            const playerName = response.playerName || ''; // Optional: first player's name
            const determinePlayerDiv = $('#determine_player');

            // Clear any existing content
            determinePlayerDiv.empty();

            if (playerCount === 0) {
                // No players: Show layout selection for Player 1
                determinePlayerDiv.append(`
                    <label>Select Layout:</label><br>
                    <input type="radio" name="layout" value="cross-border" id="cross-border">
                    <label for="cross-border">Cross Border</label><br>
                    <input type="radio" name="layout" value="default" id="default">
                    <label for="default">Default</label><br>
                    <input type="radio" name="layout" value="x-border" id="x-border">
                    <label for="x-border">X Border</label><br>
                    <input type="radio" name="layout" value="inner-border" id="inner-border">
                    <label for="inner-border">Inner Border</label><br><br>
                `);
                $('#playBtn').prop('disabled', false);
            } else if (playerCount === 1) {
                // One player: Show waiting message
                determinePlayerDiv.append(`<p>${playerName} is waiting for another player to join...</p>`);
                $('#playBtn').prop('disabled', false);
            } else if (playerCount === 2) {
                // Two players: Table is full
                determinePlayerDiv.append(`<p>The game is full. Please wait for the current game to finish.</p>`);
                $('#playBtn').prop('disabled', true);
            }
        },
        error: function () {
            console.error('Error fetching player status.');
        }
    });
}
