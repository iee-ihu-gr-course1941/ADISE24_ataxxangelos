-- Table to store the current state of the board
CREATE TABLE board (
  x TINYINT(1) NOT NULL, -- X-coordinate
  y TINYINT(1) NOT NULL, -- Y-coordinate
  piece_color ENUM('Y', 'R') DEFAULT NULL, -- Indicates if a piece is on the square
  PRIMARY KEY (x, y)
);

INSERT INTO board (x, y, piece_color)
VALUES 
    -- Add the special cases for 'R' and 'Y' pieces first
    (1, 1, 'Y'),
    (7, 7, 'Y'),
    (1, 7, 'R'),
    (7, 1, 'R'),

    -- Add the remaining cells with NULL for piece_color
    (1, 2, NULL), (1, 3, NULL), (1, 4, NULL), (1, 5, NULL), (1, 6, NULL),
    (2, 1, NULL), (2, 2, NULL), (2, 3, NULL), (2, 4, NULL), (2, 5, NULL), (2, 6, NULL), (2, 7, NULL),
    (3, 1, NULL), (3, 2, NULL), (3, 3, NULL), (3, 4, NULL), (3, 5, NULL), (3, 6, NULL), (3, 7, NULL),
    (4, 1, NULL), (4, 2, NULL), (4, 3, NULL), (4, 4, NULL), (4, 5, NULL), (4, 6, NULL), (4, 7, NULL),
    (5, 1, NULL), (5, 2, NULL), (5, 3, NULL), (5, 4, NULL), (5, 5, NULL), (5, 6, NULL), (5, 7, NULL),
    (6, 1, NULL), (6, 2, NULL), (6, 3, NULL), (6, 4, NULL), (6, 5, NULL), (6, 6, NULL), (6, 7, NULL),
    (7, 2, NULL), (7, 3, NULL), (7, 4, NULL), (7, 5, NULL), (7, 6, NULL);

-- Table to store empty board layout (used for resetting or initializing games)
CREATE TABLE board_empty (
  x TINYINT(1) NOT NULL, -- X-coordinate
  y TINYINT(1) NOT NULL, -- Y-coordinate
  piece_color ENUM('Y', 'R') DEFAULT NULL, -- Indicates if a piece is on the square
  PRIMARY KEY (x, y)
);

INSERT INTO board_empty (x, y, piece_color)
VALUES 
    -- Add the special cases for 'R' and 'Y' pieces first
    (1, 1, 'Y'),
    (7, 7, 'Y'),
    (1, 7, 'R'),
    (7, 1, 'R'),

    -- Add the remaining cells with NULL for piece_color
    (1, 2, NULL), (1, 3, NULL), (1, 4, NULL), (1, 5, NULL), (1, 6, NULL),
    (2, 1, NULL), (2, 2, NULL), (2, 3, NULL), (2, 4, NULL), (2, 5, NULL), (2, 6, NULL), (2, 7, NULL),
    (3, 1, NULL), (3, 2, NULL), (3, 3, NULL), (3, 4, NULL), (3, 5, NULL), (3, 6, NULL), (3, 7, NULL),
    (4, 1, NULL), (4, 2, NULL), (4, 3, NULL), (4, 4, NULL), (4, 5, NULL), (4, 6, NULL), (4, 7, NULL),
    (5, 1, NULL), (5, 2, NULL), (5, 3, NULL), (5, 4, NULL), (5, 5, NULL), (5, 6, NULL), (5, 7, NULL),
    (6, 1, NULL), (6, 2, NULL), (6, 3, NULL), (6, 4, NULL), (6, 5, NULL), (6, 6, NULL), (6, 7, NULL),
    (7, 2, NULL), (7, 3, NULL), (7, 4, NULL), (7, 5, NULL), (7, 6, NULL);

-- Table where the game stats are stored
CREATE TABLE game_stats (
  g_status ENUM('not active', 'initialized', 'started', 'ended', 'aborted') NOT NULL DEFAULT 'not active',
  p_turn ENUM('Y', 'R') DEFAULT NULL, -- Indicates whose turn it is
  result ENUM('R', 'Y', 'D') DEFAULT NULL, -- 'D' for draw
  last_change TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO game_stats(g_status, p_turn, result, last_change) VALUES ('not active', 'Y', NULL, CURRENT_TIMESTAMP);

-- Table to store information about players and moves
CREATE TABLE players (
  username VARCHAR(20) DEFAULT NULL,
  piece_color ENUM('R', 'Y') NOT NULL,
  token VARCHAR(100) DEFAULT NULL,
  score INT DEFAULT 0, -- Corrected the syntax here
  last_action TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (piece_color) -- Ensures each color is unique
);

DELIMITER $$

CREATE TRIGGER after_player_delete
AFTER DELETE ON players
FOR EACH ROW
BEGIN
    DECLARE remaining_players INT;

    -- Count the remaining players in the table
    SELECT COUNT(*) INTO remaining_players FROM players;

    -- Update the game status based on the number of players left
    IF remaining_players = 1 THEN
        UPDATE game_stats
        SET g_status = 'aborted';
    ELSEIF remaining_players = 0 THEN
        UPDATE game_stats
        SET g_status = 'not active', p_turn = 'Y';
        REPLACE INTO board SELECT * FROM board_empty;
    END IF;
END$$

DELIMITER ;

DELIMITER $$

CREATE PROCEDURE duplicate_piece(
    IN from_x INT,
    IN from_y INT,
    IN to_x INT,
    IN to_y INT,
    IN s_color CHAR(1) -- 'Y' for yellow, 'R' for red
)
BEGIN
    -- Check if the destination is empty (NULL)
    IF EXISTS (
        SELECT 1 FROM board WHERE x = to_x AND y = to_y AND piece_color IS NULL
    ) AND EXISTS (
        SELECT 1 FROM board WHERE x = from_x AND y = from_y AND piece_color = s_color
    )THEN
        -- Duplicate the piece to the new position
        UPDATE board
        SET piece_color = s_color
        WHERE x = to_x AND y = to_y;

        -- Infect adjacent opponent places
        UPDATE board
        SET piece_color = s_color
        WHERE ABS(x - to_x) <= 1 AND ABS(y - to_y) <= 1 AND piece_color IS NOT NULL AND piece_color != s_color;

        -- Update the players' score
        UPDATE players
        SET score = (
            SELECT COUNT(*) FROM board WHERE piece_color = 'Y'
        )
        WHERE piece_color = 'Y';

        UPDATE players
        SET score = (
            SELECT COUNT(*) FROM board WHERE piece_color = 'R'
        )
        WHERE piece_color = 'R';

    ELSE
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Destination is not empty';
    END IF;
END$$

DELIMITER ;

DELIMITER $$

CREATE PROCEDURE jump_piece(
    IN from_x INT,
    IN from_y INT,
    IN to_x INT,
    IN to_y INT,
    IN s_color CHAR(1) -- 'Y' for yellow, 'R' for red
)
BEGIN
    -- Check if the destination is empty (NULL) and the source has the correct piece
    IF EXISTS (
        SELECT 1 FROM board WHERE x = to_x AND y = to_y AND piece_color IS NULL
    ) AND EXISTS (
        SELECT 1 FROM board WHERE x = from_x AND y = from_y AND piece_color = s_color
    ) THEN
        -- Move the piece to the new position
        UPDATE board
        SET piece_color = s_color
        WHERE x = to_x AND y = to_y;

        -- Clear the original position
        UPDATE board
        SET piece_color = NULL
        WHERE x = from_x AND y = from_y;

        -- Infect adjacent opponent pieces near the destination
        UPDATE board
        SET piece_color = s_color
        WHERE ABS(x - to_x) <= 1 AND ABS(y - to_y) <= 1 
              AND piece_color IS NOT NULL 
              AND piece_color != s_color;

        -- Update the players' score
        UPDATE players
        SET score = (
            SELECT COUNT(*) FROM board WHERE piece_color = 'Y'
        )
        WHERE piece_color = 'Y';

        UPDATE players
        SET score = (
            SELECT COUNT(*) FROM board WHERE piece_color = 'R'
        )
        WHERE piece_color = 'R';

    ELSE
        -- Signal an error for invalid move
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Invalid jump: Either destination is not empty or source is incorrect';
    END IF;
END$$

DELIMITER ;




