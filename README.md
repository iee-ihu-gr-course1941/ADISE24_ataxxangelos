# ADISE24-ATAXX 

* Game: ATAXX 
* ID of Developer: 2019002

* # Ataxx Game

Welcome to the Ataxx game repository! This project is an implementation of the Ataxx board game, designed to be played via a web interface using REST API interactions. The game supports two players, each controlling pieces on the board with the objective of capturing as much territory as possible.

---

## Game Overview
Ataxx is a two-player strategy board game played on a 7x7 grid. Players alternate turns, moving their pieces to capture territory. Pieces can either:

- **Duplicate** by moving to an adjacent empty square.
- **Jump** by moving up to two squares away.

The game ends when:
- All squares are occupied.
- One player has no pieces left.

The winner is the player with the most pieces on the board.

---

## Features
- **Real-time two-player gameplay**.
- **REST API** for backend communication.
- AJAX-powered updates for a smooth user experience.
- Visual indicators for turn and game status.

---

## REST API Structure

The backend API is structured according to RESTful principles. Below is the API tree with supported endpoints and methods:

```
/ataxx.php
|-- /board
|   |-- GET: Get the current state of the board (pieces, positions, etc.).
|   |-- PUT: Reset or initialize the board.
|   |-- /piece
|       |-- PUT: Execute a move (duplicate or jump).
|-- /players
|   |-- GET: Retrieve the list of players (name, color, score).
|   |-- POST: Add a new player to the game.
|   |-- DELETE: Remove a player (e.g., when they leave or abort).
|-- /status
    |-- GET: Get the current game status (active, initialized, ended).
    |-- PUT: Update the game status (e.g., start, end, abort).
```

### Example API Usage

#### 1. **Move a Piece**
**Endpoint:** `/board/piece`
- **Method:** `PUT`
- **Payload:**
```json
{
  "x1": 2,
  "y1": 3,
  "x2": 3,
  "y2": 4,
  "color": "R",
  "token": "player_token"
}
```
- **Response:**
```json
{
  "success": "Move executed successfully."
}
```

#### 2. **Get Players**
**Endpoint:** `/players`
- **Method:** `GET`
- **Response:**
```json
[
  {
    "username": "Player1",
    "piece_color": "R",
    "score": 15
  },
  {
    "username": "Player2",
    "piece_color": "Y",
    "score": 10
  }
]
```

#### 3. **Update Game Status**
**Endpoint:** `/status`
- **Method:** `PUT`
- **Payload:**
```json
{
  "status": "ended"
}
```
- **Response:**
```json
{
  "success": "Game status updated."
}
```

---

## How to Play
1. Clone the repository and set up a local server (e.g., XAMPP).
2. Configure the database using the provided SQL schema.
3. Start the game by opening the `game.html` file in a browser.
4. Players can join and begin gameplay by interacting with the interface.

---

## Technologies Used
- **Frontend:** HTML, CSS, JavaScript, jQuery
- **Backend:** PHP (REST API)
- **Database:** MySQL

---

## Author
Created by Angelos Athanasiou 2019002.
Duration of development: 1 week.

---

