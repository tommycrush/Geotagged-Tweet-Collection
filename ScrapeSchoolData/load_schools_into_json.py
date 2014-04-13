import MySQLdb 
import json

conn = MySQLdb.connect(host="localhost",user="root",passwd="91T08c!!",db="twitter",charset='utf8')
x = conn.cursor()

try:
    schools_query = "SELECT school_id, ne_lat, ne_lng, sw_lat, sw_lng, name FROM schools ORDER BY school_id ASC"
    x.execute(schools_query)
    data = x.fetchall()
    schools = []
    for row in data :
        school_id = int(row[0])
        ne_lat = float(row[1])
        ne_lng = float(row[2])
        sw_lat = float(row[3])
        sw_lng = float(row[4])
        name = str(row[5])
        
        school = {
            'bounds': {
                'northeast': {
                    'lat' : ne_lat,
                    'lng' : ne_lng
                },
                'southwest': {
                    'lat' : sw_lat,
                    'lng' : sw_lng
                }
            },
            'name': name,
            'school_id' : school_id
        }

        schools.append(school)

    with open('schools.json','w') as f:
        f.write(json.dumps(schools))

    print "wrote to schools.json"
except MySQLdb.Error, e:
    try:
        print "MySQL Error [%d]: %s" % (e.args[0], e.args[1])
    except IndexError:
        print "MySQL Error: %s" % str(e) 
conn.close()
