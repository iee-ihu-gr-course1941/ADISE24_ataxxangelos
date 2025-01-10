-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Εξυπηρετητής: 127.0.0.1
-- Χρόνος δημιουργίας: 10 Ιαν 2025 στις 20:52:03
-- Έκδοση διακομιστή: 10.4.32-MariaDB
-- Έκδοση PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Βάση δεδομένων: `ataxx`
--

DELIMITER $$
--
-- Διαδικασίες
--
DROP PROCEDURE IF EXISTS `duplicate_piece`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `duplicate_piece` (IN `from_x` INT, IN `from_y` INT, IN `to_x` INT, IN `to_y` INT, IN `s_color` CHAR(1))   BEGIN
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

DROP PROCEDURE IF EXISTS `jump_piece`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `jump_piece` (IN `from_x` INT, IN `from_y` INT, IN `to_x` INT, IN `to_y` INT, IN `s_color` CHAR(1))   BEGIN
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

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `board`
--

DROP TABLE IF EXISTS `board`;
CREATE TABLE IF NOT EXISTS `board` (
  `x` tinyint(1) NOT NULL,
  `y` tinyint(1) NOT NULL,
  `piece_color` enum('Y','R') DEFAULT NULL,
  PRIMARY KEY (`x`,`y`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Εκκαθάριση του πίνακα πριν την εισαγωγή `board`
--

TRUNCATE TABLE `board`;
--
-- Άδειασμα δεδομένων του πίνακα `board`
--

INSERT INTO `board` (`x`, `y`, `piece_color`) VALUES
(1, 1, 'Y'),
(1, 2, NULL),
(1, 3, NULL),
(1, 4, NULL),
(1, 5, NULL),
(1, 6, NULL),
(1, 7, 'R'),
(2, 1, NULL),
(2, 2, NULL),
(2, 3, NULL),
(2, 4, NULL),
(2, 5, NULL),
(2, 6, NULL),
(2, 7, NULL),
(3, 1, NULL),
(3, 2, NULL),
(3, 3, NULL),
(3, 4, NULL),
(3, 5, NULL),
(3, 6, NULL),
(3, 7, NULL),
(4, 1, NULL),
(4, 2, NULL),
(4, 3, NULL),
(4, 4, NULL),
(4, 5, NULL),
(4, 6, NULL),
(4, 7, NULL),
(5, 1, NULL),
(5, 2, NULL),
(5, 3, NULL),
(5, 4, NULL),
(5, 5, NULL),
(5, 6, NULL),
(5, 7, NULL),
(6, 1, NULL),
(6, 2, NULL),
(6, 3, NULL),
(6, 4, NULL),
(6, 5, NULL),
(6, 6, NULL),
(6, 7, NULL),
(7, 1, 'R'),
(7, 2, NULL),
(7, 3, NULL),
(7, 4, NULL),
(7, 5, NULL),
(7, 6, NULL),
(7, 7, 'Y');

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `board_empty`
--

DROP TABLE IF EXISTS `board_empty`;
CREATE TABLE IF NOT EXISTS `board_empty` (
  `x` tinyint(1) NOT NULL,
  `y` tinyint(1) NOT NULL,
  `piece_color` enum('Y','R') DEFAULT NULL,
  PRIMARY KEY (`x`,`y`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Εκκαθάριση του πίνακα πριν την εισαγωγή `board_empty`
--

TRUNCATE TABLE `board_empty`;
--
-- Άδειασμα δεδομένων του πίνακα `board_empty`
--

INSERT INTO `board_empty` (`x`, `y`, `piece_color`) VALUES
(1, 1, 'Y'),
(1, 2, NULL),
(1, 3, NULL),
(1, 4, NULL),
(1, 5, NULL),
(1, 6, NULL),
(1, 7, 'R'),
(2, 1, NULL),
(2, 2, NULL),
(2, 3, NULL),
(2, 4, NULL),
(2, 5, NULL),
(2, 6, NULL),
(2, 7, NULL),
(3, 1, NULL),
(3, 2, NULL),
(3, 3, NULL),
(3, 4, NULL),
(3, 5, NULL),
(3, 6, NULL),
(3, 7, NULL),
(4, 1, NULL),
(4, 2, NULL),
(4, 3, NULL),
(4, 4, NULL),
(4, 5, NULL),
(4, 6, NULL),
(4, 7, NULL),
(5, 1, NULL),
(5, 2, NULL),
(5, 3, NULL),
(5, 4, NULL),
(5, 5, NULL),
(5, 6, NULL),
(5, 7, NULL),
(6, 1, NULL),
(6, 2, NULL),
(6, 3, NULL),
(6, 4, NULL),
(6, 5, NULL),
(6, 6, NULL),
(6, 7, NULL),
(7, 1, 'R'),
(7, 2, NULL),
(7, 3, NULL),
(7, 4, NULL),
(7, 5, NULL),
(7, 6, NULL),
(7, 7, 'Y');

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `game_stats`
--

DROP TABLE IF EXISTS `game_stats`;
CREATE TABLE IF NOT EXISTS `game_stats` (
  `g_status` enum('not active','initialized','started','ended','aborted') NOT NULL DEFAULT 'not active',
  `p_turn` enum('Y','R') DEFAULT NULL,
  `result` enum('R','Y','D') DEFAULT NULL,
  `last_change` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Εκκαθάριση του πίνακα πριν την εισαγωγή `game_stats`
--

TRUNCATE TABLE `game_stats`;
--
-- Άδειασμα δεδομένων του πίνακα `game_stats`
--

INSERT INTO `game_stats` (`g_status`, `p_turn`, `result`, `last_change`) VALUES
('not active', 'Y', 'Y', '2025-01-10 19:35:33');

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `players`
--

DROP TABLE IF EXISTS `players`;
CREATE TABLE IF NOT EXISTS `players` (
  `username` varchar(20) DEFAULT NULL,
  `piece_color` enum('R','Y') NOT NULL,
  `token` varchar(100) DEFAULT NULL,
  `score` int(11) DEFAULT 0,
  `last_action` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`piece_color`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Εκκαθάριση του πίνακα πριν την εισαγωγή `players`
--

TRUNCATE TABLE `players`;
--
-- Δείκτες `players`
--
DROP TRIGGER IF EXISTS `after_player_delete`;
DELIMITER $$
CREATE TRIGGER `after_player_delete` AFTER DELETE ON `players` FOR EACH ROW BEGIN
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
END
$$
DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
