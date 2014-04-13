GeoTagged Tweets Collection 
=====================


This project was built to collect, process, and store all geotagged tweets on all college campuses in the United States. It was built as a case study project at the Univeristy of Kentucky, in the Gatton College of Business. It was built by [Tommy Crush](https://www.linkedin.com/profile/view?trk=nav_responsive_tab_profile_pic&id=100094894), with [De Liu](https://www.linkedin.com/profile/view?id=38379930) as the faculty advisor.



## TwitterStream
This is the main workhorse of the application. It's the part that plugs into the Twitter Stream API, retrieves tweets, determines what is from a college campus, and stores it.

## WebInterface
This folder is a PHP site that exposes the data in the MySQL database. It displays a heatmap of each campus's tweet activity, as well as trends related to hashtags, etc.  

## SQL
This folder provides SQL to build the structure of the database, as well as procedures I used to denormalize data.


## ScrapeSchoolData
The list of schools, as well as their respective geo-boxes, is clearly an important piece of the puzzle. Make sure `boxes.json` gets copied to `TwitterStream/` after it's created. Also, `bounds.py` is copied to `TwitterStream` as well.