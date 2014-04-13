import time 
import urllib
import re
from bs4 import BeautifulSoup
import json
import pycurl
import cStringIO

buf = cStringIO.StringIO()

url = 'http://www.forbes.com/top-colleges/list/'
c = pycurl.Curl()
c.setopt(c.URL, url)
c.setopt(c.WRITEFUNCTION, buf.write)
c.perform()

html_doc = buf.getvalue()
buf.close()
c.close()

soup = BeautifulSoup(html_doc)
tbody = soup.find(id="listbody")
schools = []
non_decimal = re.compile(r'[^\d]+')

def getCoords(school):
    buf = cStringIO.StringIO()

    url = 'http://maps.googleapis.com/maps/api/geocode/json?address=' + urllib.quote_plus(school) + '&sensor=false'
    print url
    c2 = pycurl.Curl()
    c2.setopt(c2.URL, str(url)) # no unicode allowed
    c2.setopt(c2.WRITEFUNCTION, buf.write)
    c2.perform()
    doc = buf.getvalue()
    buf.close()
    c2.close()

    data = json.loads(doc)
    
    if data['status'] != 'OK':
        print "DATA FOR " + url + " BROKEN"
        return {}

    if 'viewport' not in data['results'][0]['geometry']:
        print "NO VIEWPORT FOR " + url
        return {}

    return data['results'][0]['geometry']['viewport']

for tr in tbody.find_all('tr'):
    tds = tr.find_all('td')
    rank = non_decimal.sub('', tds[0].string)
    a = tds[1].find('a')
    title = a.find('h3').string

    if rank == '91':
        title = 'Sewanee-University of the South'

    more_info = 'http://www.forbes.com' + a['href']
    cost = non_decimal.sub('', tds[3].string)
    enrolement = non_decimal.sub('', tds[4].string)
    school = {
        'name' : title,
        'url' : more_info,
        'cost' : cost,
        'students' : enrolement,
        'rank' : rank,
        'bounds' : getCoords(title)
    }

    schools.append(school)
    time.sleep(3)

j = json.dumps(schools)
with open('schools.json', 'w') as file:
    file.write(j)
