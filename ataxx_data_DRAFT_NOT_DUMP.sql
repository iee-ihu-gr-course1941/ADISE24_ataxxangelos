-- Table of the board
CREATE TABLE board (
  x tinyint(1) NOT NULL,
  y tinyint(1) NOT NULL,
  b_color enum('S','O') NOT NULL,
  piece_color enum('Y','R') DEFAULT NULL,
  PRIMARY KEY (x, y)
);

-- Insert initial values into the board
INSERT INTO board VALUES 
(1,1,'S','Y'),(2,1,'S',NULL),(3,1,'S',NULL),(4,1,'S',NULL),(5,1,'S',NULL),(6,1,'S',NULL),(7,1,'S','R'),(8,1,'S',NULL),
(1,2,'S',NULL),(2,2,'S',NULL),(3,2,'S',NULL),(4,2,'S',NULL),(5,2,'S',NULL),(6,2,'S',NULL),(7,2,'S',NULL),(8,2,'S',NULL),
(1,3,'S',NULL),(2,3,'S',NULL),(3,3,'S',NULL),(4,3,'S',NULL),(5,3,'S',NULL),(6,3,'S',NULL),(7,3,'S',NULL),(8,3,'S',NULL),
(1,4,'S',NULL),(2,4,'S',NULL),(3,4,'S',NULL),(4,4,'S',NULL),(5,4,'S',NULL),(6,4,'S',NULL),(7,4,'S',NULL),(8,4,'S',NULL),
(1,5,'S',NULL),(2,5,'S',NULL),(3,5,'S',NULL),(4,5,'S',NULL),(5,5,'S',NULL),(6,5,'S',NULL),(7,5,'S',NULL),(8,5,'S',NULL),
(1,6,'S',NULL),(2,6,'S',NULL),(3,6,'S',NULL),(4,6,'S',NULL),(5,6,'S',NULL),(6,6,'S',NULL),(7,6,'S',NULL),(8,6,'S',NULL),
(1,7,'S','R'),(2,7,'S',NULL),(3,7,'S',NULL),(4,7,'S',NULL),(5,7,'S',NULL),(6,7,'S',NULL),(7,7,'S','Y');

-- Duplicate of empty board to clean it once the game restarts
CREATE TABLE board_empty (
  x tinyint(1) NOT NULL,
  y tinyint(1) NOT NULL,
  b_color enum('S','O') NOT NULL,
  piece_color enum('Y','R') DEFAULT NULL,
  PRIMARY KEY (x, y)
);

-- Insert initial values into the empty board
INSERT INTO board_empty VALUES 
(1,1,'S','Y'),(2,1,'S',NULL),(3,1,'S',NULL),(4,1,'S',NULL),(5,1,'S',NULL),(6,1,'S',NULL),(7,1,'S','R'),(8,1,'S',NULL),
(1,2,'S',NULL),(2,2,'S',NULL),(3,2,'S',NULL),(4,2,'S',NULL),(5,2,'S',NULL),(6,2,'S',NULL),(7,2,'S',NULL),(8,2,'S',NULL),
(1,3,'S',NULL),(2,3,'S',NULL),(3,3,'S',NULL),(4,3,'S',NULL),(5,3,'S',NULL),(6,3,'S',NULL),(7,3,'S',NULL),(8,3,'S',NULL),
(1,4,'S',NULL),(2,4,'S',NULL),(3,4,'S',NULL),(4,4,'S',NULL),(5,4,'S',NULL),(6,4,'S',NULL),(7,4,'S',NULL),(8,4,'S',NULL),
(1,5,'S',NULL),(2,5,'S',NULL),(3,5,'S',NULL),(4,5,'S',NULL),(5,5,'S',NULL),(6,5,'S',NULL),(7,5,'S',NULL),(8,5,'S',NULL),
(1,6,'S',NULL),(2,6,'S',NULL),(3,6,'S',NULL),(4,6,'S',NULL),(5,6,'S',NULL),(6,6,'S',NULL),(7,6,'S',NULL),(8,6,'S',NULL),
(1,7,'S','R'),(2,7,'S',NULL),(3,7,'S',NULL),(4,7,'S',NULL),(5,7,'S',NULL),(6,7,'S',NULL),(7,7,'S','Y');

-- Table where the game stats are stored
CREATE TABLE game_stats (
  `status` enum('not active','initialized','started','ended','aborted') NOT NULL DEFAULT 'not active',
  p_turn enum('Y','R') DEFAULT NULL,
  result enum('R','Y','D') DEFAULT NULL,
  last_change timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
);

-- Insert initial value into game_stats
INSERT INTO game_stats VALUES ('started','Y','D',current_timestamp());

-- Table to store information about players and moves
CREATE TABLE players (
  username varchar(20) DEFAULT NULL,
  piece_color enum('R','Y') NOT NULL,
  token varchar(100) DEFAULT NULL,
  last_action timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (piece_color)
);

INSERT INTO players VALUES ('Player 1', 'Y', '8599a2efe05697622caeddae84507ee3', '2022-11-28 18:16:51'),
('Player 2', 'R', '05da4297eecc648e840b6d3bfa772adc', '2022-11-28 18:16:55');

-- Procedure to clean the board every time the game restarts
DELIMITER $$

CREATE PROCEDURE clean_board()
BEGIN
  REPLACE INTO board SELECT * FROM board_empty;
END $$

-- Procedure to duplicate a piece on the board
CREATE PROCEDURE duplicate(IN p_color ENUM('Y','R'), IN x TINYINT, IN y TINYINT)
BEGIN
    -- Ensure the target square is valid (S for space and not occupied)
    IF EXISTS (
        SELECT 1 FROM board
        WHERE x = x AND y = y AND b_color = 'S' AND piece_color IS NULL
    ) THEN
        -- Update the target square with the player's piece
        UPDATE board
        SET piece_color = p_color
        WHERE x = x AND y = y;

        -- Change any adjacent opponent pieces to the player's color
        UPDATE board
        SET piece_color = p_color
        WHERE
            ABS(x - x) <= 1 AND ABS(y - y) <= 1 -- Within a 1-step radius
            AND piece_color IS NOT NULL
            AND piece_color != p_color;
    END IF;
END $$

-- Procedure to perform a jump move
CREATE PROCEDURE jump(IN p_color ENUM('Y','R'), IN start_x TINYINT, IN start_y TINYINT, IN end_x TINYINT, IN end_y TINYINT)
BEGIN
    -- Ensure the move is valid
    IF EXISTS (
        SELECT 1 FROM board
        WHERE x = end_x AND y = end_y AND b_color = 'S' AND piece_color IS NULL
    ) THEN
        -- Update the target square with the player's piece
        UPDATE board
        SET piece_color = p_color
        WHERE x = end_x AND y = end_y;

        -- Remove the player's piece from the starting square
        UPDATE board
        SET piece_color = NULL
        WHERE x = start_x AND y = start_y;

        -- Change any adjacent opponent pieces to the player's color
        UPDATE board
        SET piece_color = p_color
        WHERE
            ABS(x - end_x) <= 1 AND ABS(y - end_y) <= 1 -- Within a 1-step radius
            AND piece_color IS NOT NULL
            AND piece_color != p_color;
    END IF;
END $$

--Set the board layout
DELIMITER $$

CREATE PROCEDURE update_obstacles(IN layout JSON)
BEGIN
    DECLARE i INT DEFAULT 0;
    DECLARE coord_x INT;
    DECLARE coord_y INT;

    -- Reset all existing obstacles (set 'O' to 'S')
    UPDATE board
    SET b_color = 'S'
    WHERE b_color = 'O';

    UPDATE board_empty
    SET b_color = 'S'
    WHERE b_color = 'O';

    -- Get the number of elements in the layout
    SET @layout_size = JSON_LENGTH(layout);

    -- Loop through each coordinate in the layout
    WHILE i < @layout_size DO
        -- Extract the x and y coordinates from the JSON
        SET coord_x = JSON_UNQUOTE(JSON_EXTRACT(layout, CONCAT('$[', i, '].x')));
        SET coord_y = JSON_UNQUOTE(JSON_EXTRACT(layout, CONCAT('$[', i, '].y')));

        -- Update the board table: set 'S' to 'O' at the specified coordinates
        UPDATE board
        SET b_color = 'O'
        WHERE x = coord_x AND y = coord_y AND b_color = 'S';

        -- Update the board_empty table: set 'S' to 'O' at the specified coordinates
        UPDATE board_empty
        SET b_color = 'O'
        WHERE x = coord_x AND y = coord_y AND b_color = 'S';

        -- Increment the loop counter
        SET i = i + 1;
    END WHILE;
END $$

DELIMITER ;



