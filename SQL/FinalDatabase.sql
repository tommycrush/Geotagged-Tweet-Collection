-- phpMyAdmin SQL Dump
-- version 3.4.10.1deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Apr 13, 2014 at 05:13 PM
-- Server version: 5.5.34
-- PHP Version: 5.3.10-1ubuntu3.9

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `twitter`
--

-- --------------------------------------------------------

--
-- Table structure for table `errors`
--

CREATE TABLE IF NOT EXISTS `errors` (
  `error_id` int(11) NOT NULL AUTO_INCREMENT,
  `code` int(11) NOT NULL,
  `datetime` datetime NOT NULL,
  PRIMARY KEY (`error_id`),
  UNIQUE KEY `error_id` (`error_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COMMENT='errors from stream' AUTO_INCREMENT=49 ;

-- --------------------------------------------------------

--
-- Table structure for table `hashtags`
--

CREATE TABLE IF NOT EXISTS `hashtags` (
  `hashtag` varchar(160) NOT NULL,
  `tweet_id` bigint(20) NOT NULL,
  UNIQUE KEY `hashtag_tweet_id_combo` (`hashtag`,`tweet_id`),
  KEY `search_by_hashtag` (`hashtag`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `hashtags_by_school`
--

CREATE TABLE IF NOT EXISTS `hashtags_by_school` (
  `hashtag` varchar(255) NOT NULL,
  `school_id` int(11) NOT NULL,
  `total` int(11) NOT NULL,
  UNIQUE KEY `hashtag_school_combo` (`hashtag`,`school_id`),
  KEY `school_index` (`school_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='counts the number of each hashtags in each school';

-- --------------------------------------------------------

--
-- Table structure for table `messages` 
-- (this was a poorly worded name for "Messages sent from
-- twitter's API to our API. These are not user-to-user messages.
-- We used this table to track 'overflow' messages.)
--

CREATE TABLE IF NOT EXISTS `messages` (
  `message_id` int(11) NOT NULL AUTO_INCREMENT,
  `monitor_session_id` varchar(10) NOT NULL,
  `message` varchar(250) NOT NULL DEFAULT '--',
  `limit_track` int(11) NOT NULL,
  `datetime_entered` datetime NOT NULL,
  PRIMARY KEY (`message_id`),
  UNIQUE KEY `message_id` (`message_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COMMENT='messages from twitters API' AUTO_INCREMENT=83863 ;

-- --------------------------------------------------------

--
-- Table structure for table `schools`
-- (data about the schools usage metrics was cached here)
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
  `num_tweets` int(11) NOT NULL,
  `num_twitter_users` int(11) NOT NULL COMMENT 'number of distinct twitter users who tweeted on campus',
  `ave_per_user` float NOT NULL COMMENT 'ave # of tweets per user at that university',
  `ave_per_student` float NOT NULL,
  PRIMARY KEY (`school_id`),
  UNIQUE KEY `school_id` (`school_id`,`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=652 ;

-- --------------------------------------------------------

--
-- Table structure for table `top_users_per_school`
--

CREATE TABLE IF NOT EXISTS `top_users_per_school` (
  `screen_name` varchar(50) NOT NULL,
  `name` varchar(50) NOT NULL,
  `twitter_user_id` bigint(20) NOT NULL,
  `school_id` int(11) NOT NULL,
  `total` int(11) NOT NULL,
  UNIQUE KEY `twitter_user_id_index` (`twitter_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='holds top X users per school.';

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
  UNIQUE KEY `tweet_id` (`tweet_id`),
  KEY `school_index` (`school_id`),
  KEY `twitter_user_id_index` (`twitter_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='table of collected tweets';

-- --------------------------------------------------------

--
-- Table structure for table `tweet_coordinates_by_school`
--

CREATE TABLE IF NOT EXISTS `tweet_coordinates_by_school` (
  `lat` float NOT NULL,
  `lng` float NOT NULL,
  `school_id` int(11) NOT NULL,
  `weight` int(11) NOT NULL,
  PRIMARY KEY (`lat`,`lng`),
  KEY `school_index` (`school_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='holds pre-computed values for heatmaps';

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
