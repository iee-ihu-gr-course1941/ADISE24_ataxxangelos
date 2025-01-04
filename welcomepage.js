// This function is called every time one of the textboxes changes
// It checks whether both of the player textboxes have text.
function textboxChanged() {
    var player1 = document.getElementById("p1inputbox").value.trim();
    var player2 = document.getElementById("p2inputbox").value.trim();

    var playBtn = document.getElementById("playBtn");

    if (player1 !== "" && player2 !== "") {
        playBtn.disabled = false;
    } else {
        playBtn.disabled = true;
    }
}
