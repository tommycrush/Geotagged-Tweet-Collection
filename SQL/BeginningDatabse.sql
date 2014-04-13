--
-- This is the barebones needed for the Twitter Stream
-- to store its data. A dump of our final DB structure
-- is in FinalDatabase.sql
--


SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `twitter`
--

--
-- Table structure for table `schools`
--

CREATE TABLE IF NOT EXISTS `schools` (
  `school_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'internal measure',
  `ne_lat` float NOT NULL,
  `ne_lng` float NOT NULL,
  `sw_lat` float NOT NULL,
  `sw_lng` float NOT NULL,
  `name` varchar(50) NOT NULL,
  `forbes_rank` int(11) NOT NULL,
  `cost` int(11) NOT NULL,
  `students` int(11) NOT NULL,
  `forbes_url` varchar(250) NOT NULL,
  PRIMARY KEY (`school_id`),
  UNIQUE KEY `school_id` (`school_id`,`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=652 ;

-- --------------------------------------------------------

--
-- Table structure for table `tweets`
--

CREATE TABLE IF NOT EXISTS `tweets` (
  `tweet_id` bigint(20) NOT NULL,
  `datetime_entered` datetime NOT NULL,
  `text` varchar(160) CHARACTER SET utf8 DEFAULT NULL,
  `latitude` float NOT NULL,
  `longitude` float NOT NULL,
  `is_retweet` tinyint(1) NOT NULL,
  `twitter_user_id` bigint(20) NOT NULL,
  `hashtags` varchar(160) CHARACTER SET latin1 NOT NULL,
  `mentions` varchar(160) CHARACTER SET latin1 NOT NULL,
  `urls` varchar(500) CHARACTER SET latin1 NOT NULL,
  `school_id` int(11) NOT NULL,
  UNIQUE KEY `tweet_id` (`tweet_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='table of collected tweets';

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `twitter_user_id` bigint(20) NOT NULL,
  `screen_name` varchar(30) CHARACTER SET utf8 DEFAULT NULL,
  `description` varchar(250) CHARACTER SET utf8 DEFAULT NULL,
  `location` varchar(30) CHARACTER SET utf8 DEFAULT NULL,
  `name` varchar(150) CHARACTER SET utf8 DEFAULT NULL,
  UNIQUE KEY `twitter_user_id` (`twitter_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `user_snapshots`
--

CREATE TABLE IF NOT EXISTS `user_snapshots` (
  `twitter_user_id` bigint(20) NOT NULL,
  `datetime_snapshot` datetime NOT NULL,
  `followers_count` int(11) NOT NULL,
  `friends_count` int(11) NOT NULL,
  `favourites_count` int(11) NOT NULL,
  `statuses_count` int(11) NOT NULL,
  UNIQUE KEY `PK_user_datetime` (`twitter_user_id`,`datetime_snapshot`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;