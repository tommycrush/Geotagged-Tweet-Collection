from tweepy.streaming import StreamListener
from tweepy import OAuthHandler
from tweepy import Stream
import simplejson
import cStringIO
import re
#import redis
import string
import time
import MySQLdb 
import datetime
import bounds
import random

# Twitter Auth
consumer_key = ""
consumer_secret = ""
access_token = ""
access_token_secret =""

def clean(s):
    return filter(lambda x: x in string.printable, s)

def randomword(length):
   return ''.join(random.choice(string.lowercase + string.uppercase + string.digits) for i in range(length))

class listener(StreamListener):
    """ this handles the stream!"""
    def __init__(self):
        self.tweets = []
        self.num_tweets = 0
        self.boxes = bounds.loadBoxesFromJSON()
        self.num_box_levels = 3
        self.session_id = '1-' + randomword(5)
        self.record_limit(0)
        print "starting listener" 

    def on_data(self, tweet_text):
        try: 
            self.num_tweets += 1
            tweet = simplejson.loads(tweet_text)

            # determine valid tweet. 
            if "id" not in tweet:
                if "limit" in tweet:
                    print tweet
                    self.record_limit(tweet["limit"]["track"])
                return True
            
            school = 0
            longitude = 0
            latitude = 0
            # determine if its got a point at a school
            if tweet and "coordinates" in tweet and tweet["coordinates"] is not None and "type" in tweet["coordinates"]:
                if tweet["coordinates"]["type"] == "Point":
                    longitude = float(tweet["coordinates"]["coordinates"][0])
                    latitude = float(tweet["coordinates"]["coordinates"][1])
                    school = bounds.determineSchool(self.boxes, latitude, longitude, self.num_box_levels)
            # if we didn't find one,then stop.
            if school == 0:
                return True
            #print school
            tweet_id = tweet["id_str"]
            text = clean(tweet["text"])
            retweeted = 1 if tweet["retweeted"] else 0

            user = tweet["user"]
            user_id = user["id_str"]
            user_screen_name = clean(user["screen_name"])
            user_name = clean(user["name"])
            user_description = clean(user["description"])
            followers = int(user["followers_count"])
            friends = int(user["friends_count"])
            statuses = int(user["statuses_count"])
            location = clean(user["location"])
            favorites = int(user["favourites_count"])

            hashtags = ''
            mentions = ''
            urls = ''
            if 'entities' in tweet:
                if 'hashtags' in tweet['entities']:
                    hashtags_list = []
                    for hashtag in tweet['entities']['hashtags']:
                        hashtags_list.append(hashtag['text'])
                    hashtags = ','.join(hashtags_list)
                if 'user_mentions' in tweet['entities']:
                    mentions_list = []
                    for mention in tweet['entities']['user_mentions']:
                        mentions_list.append(mention['id_str'])
                    mentions = ','.join(mentions_list)
                if 'urls' in tweet['entities']:
                    urls_list = []
                    for url in tweet['entities']['urls']:
                        urls_list.append(url['expanded_url'])
                    urls = ''.join(urls_list)
       
            # @todo: move these parameters to a class variable.
            conn = MySQLdb.connect(host="localhost",user="user",passwd="passwd",db="db_name",charset='utf8')
            x = conn.cursor()
            try:
                user_query = "REPLACE INTO `twitter`.`users` (`twitter_user_id`, `screen_name`, `description`, `location`, `name`) VALUES (%s, %s, %s, %s, %s )" 
                x.execute(user_query, (user_id, user_screen_name,user_description,location,user_name))
                
                user_snapshot = "INSERT INTO `twitter`.`user_snapshots` (`twitter_user_id`, `datetime_snapshot`, `followers_count`, `friends_count`, `favourites_count`, `statuses_count`) VALUES (%s, NOW(), %s, %s, %s, %s)" 
                x.execute(user_snapshot, (user_id, followers,friends,favorites,statuses))
                
                 
                tweets_query = "REPLACE INTO `twitter`.`tweets` (`tweet_id`, `datetime_entered`, `text`, `latitude`, `longitude`, `is_retweet`, `twitter_user_id`, `hashtags`, `mentions`, `urls`,`school_id`) VALUES (%s, NOW(), %s, %s, %s, %s, %s, %s, %s, %s, %s)"
                x.execute(tweets_query, (tweet_id,text,latitude,longitude,retweeted,user_id,hashtags,mentions,urls, school))
                
                conn.commit()
            except MySQLdb.Error, e:
                try:
                    print "MySQL Error [%d]: %s" % (e.args[0], e.args[1])
                except IndexError:
                    print "MySQL Error: %s" % str(e) 
                conn.rollback()
                """
                print "ERROR sAVING SOMETHING"
                print tweet
                """

            conn.close()

        except: # this handles wider exceptions like missing keys, etc
            pass
            #print "FAILED:"
            #print tweet_text
            #print ""
        
        # end tweet processing!
        return True


        #save batch to file
        # use this is you want to save all tweets to a file, too
        """
        if self.num_tweets >= 50:
            self.num_tweets = 0
            tweets_str = ''.join(self.tweets)
            self.tweets = []
            now = datetime.datetime.now().strftime('%Y-%m-%d')
            with open('tweet_files/tweets-' + now + '.txt', 'a') as file:
                file.write(tweets_str)
            
        #add to batch
        self.num_tweets += 1
        self.tweets.append(tweet_text)
        """

    def on_error(self, status):
        conn = MySQLdb.connect(host="localhost",user="user",passwd="passwd",db="db_name",charset='utf8')
        x = conn.cursor()
        try:
            error_query = "INSERT INTO `errors` (`code`,`datetime`) VALUES (%s, NOW() )"
            x.execute(error_query, (status,))
            conn.commit()
        except MySQLdb.Error, e:
            pass
            """
            try:
                print "MySQL Error [%d]: %s" % (e.args[0], e.args[1])
            except IndexError:
                print "MySQL Error: %s" % str(e) 
            """
            conn.rollback()
        conn.close()
        return True

    def record_limit(self, limit):
        conn = MySQLdb.connect(host="localhost",user="user",passwd="passwd",db="db_name",charset='utf8')
        x = conn.cursor()
        try:
            error_query = "INSERT INTO `messages` (`monitor_session_id`,`limit_track`,`datetime_entered`) VALUES (%s, %s, NOW() )"
            x.execute(error_query, (self.session_id, limit))
            conn.commit()
        except MySQLdb.Error, e:
            pass
            """
            try:
                print "MySQL Error [%d]: %s" % (e.args[0], e.args[1])
            except IndexError:
                print "MySQL Error: %s" % str(e) 
            """
            conn.rollback()
        conn.close()
        return True



if __name__ == '__main__':
    listener = listener()
    auth = OAuthHandler(consumer_key, consumer_secret)
    auth.set_access_token(access_token, access_token_secret)
      
    sw1 = [25.82,-124.39]
    sw2 = [25.82,-87.39]
    ne1 = [49.38,-87.39]
    ne2 = [49.38,-66.94]
    usa = [-124.39,25.82,-66.94,49.38]
    half1 = [sw1[1], sw1[0], ne1[1], ne1[0]]
    half2 = [sw2[1], sw2[0], ne2[1], ne2[0]]
   
    stream = Stream(auth, listener)
    print half2
    stream.filter(locations=half2)
