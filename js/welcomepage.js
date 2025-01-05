$(function () {
    $('#playBtn').prop('disabled', true); //Disable button initially
    $('#p1inputbox, #p2inputbox').on("input", textboxChanged); // Attach the input event listener to both textboxes
});

function textboxChanged() {
    var player1 = $('#p1inputbox').val().trim(); // Get trimmed value of player1
    var player2 = $('#p2inputbox').val().trim(); // Get trimmed value of player2
    $('#playBtn').prop('disabled', !(player1 && player2)); // Button disabled if either field is empty
}
