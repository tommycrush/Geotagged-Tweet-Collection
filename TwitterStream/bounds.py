import json
from timeit import Timer

def whichBoxIsLatLngIn(lat, lng, median_lat, median_lng):
    if lat >= median_lat:
        if lng <= median_lng:
            return 0
        else:
            return 1
    else:
        if lng <= median_lng:
            return 2
        else:
            return 3

def median(mylist):
    sorts = sorted(mylist)
    length = len(sorts)
    if not length % 2:
        return (sorts[length / 2] + sorts[length / 2 - 1]) / 2.0
    return sorts[length / 2]

def divideSchoolsIntoBoxes(schools, x_more_levels):
    lats = []
    lngs = []
    for school in schools:
        ne = school['bounds']['northeast']
        lats.append(ne['lat'])
        lngs.append(ne['lng'])

    # we add a little bit because we don't want a school
    # to be the exactly on the boundry. So move it northeast a bit
    # [if we move it northeast from the northeast corner, we're not cutting it off]
    centroid = (sum(lats) / len(schools), sum(lngs) / len(schools))
    #todo: detemrine better than lat and lng
    #print centroid
    
    median_lat =  centroid[0]#median(lats) + 0.1
    median_lng =  centroid[1]#median(lngs) + 0.1

    boxes = {
       "median_lat": median_lat, 
       "median_lng": median_lng, 
       "boxes": [[],[],[],[]]
    }
    """
    box numbers:
        0 1
        3 2
    """
    for school in schools:
        ne = school['bounds']['northeast']
        box_num = whichBoxIsLatLngIn(ne['lat'], ne['lng'], median_lat, median_lng)
        boxes['boxes'][box_num].append(school)


    if x_more_levels > 1:
        new_boxes = []
        for schools_in_box in boxes["boxes"]:
            sub_box = divideSchoolsIntoBoxes(schools_in_box, x_more_levels -1)
            new_boxes.append(sub_box)
        boxes["boxes"] = new_boxes
    
    return boxes

def determineSchool(box_data, lat, lng, levels):
    center_lat =  box_data['median_lat']
    center_lng = box_data['median_lng']
    box = whichBoxIsLatLngIn(lat, lng, center_lat, center_lng)
    next_data = box_data["boxes"][box]
     
    #print 'center: (' + str(center_lat)+ ',' + str(center_lng) + ')'
    #print 'point: (' + str(lat)+ ',' + str(lng) + ')'
    #print 'its in ' + str(box)
    #print 'which has this data:'
    #print next_data
    if levels == 1:
        return findSchoolInFinalBox(next_data, lat, lng)
    return determineSchool(next_data, lat, lng, levels-1)

def findSchoolInFinalBox(schools_in_box, lat, lng):
    for school in schools_in_box:
        ne = school['bounds']['northeast']
        sw = school['bounds']['southwest']
        if ne['lat'] >= lat and sw['lat'] <= lat:
            if ne['lng'] >= lng and sw['lng'] <= lng:
                return school['school_id']
        #print 'not in ' + school['name']
    return 0 

def loadBoxesFromJSON():
    with open('boxes.json','r') as f:
        return json.loads(f.read())

def createAndDumpBoxes(levels):
    schools_json = ''
    with open('schools.json','r') as f:
        schools_json = f.read()
    schools = json.loads(schools_json)
    boxes = divideSchoolsIntoBoxes(schools,levels)

    with open('boxes.json','w') as f:
        f.write(json.dumps(boxes))


def printBoxes(box_data):
    i = 0

    for box_corner in boxes['boxes']:
        print "In box: " + str(i)

        subi = 0
        for sub_corner in box_corner['boxes']:
            print "  SUB BOX: " + str(subi)
            for school in sub_corner:
                ne = school['bounds']['northeast']
                box_num = whichBoxIsLatLngIn(ne['lat'], ne['lng'], median_lat, median_lng)
                print "        " + school['name'] + "     " + str(ne['lat']) + "," + str(ne['lng'])
            subi += 1
        print ""
        print ""
        i += 1


if __name__ == '__main__':
    levels = 3
    createAndDumpBoxes(levels)
    boxes = loadBoxesFromJSON()
    
    yale_lat = float('41.31478333333333')
    yale_lng = float('-72.92518333333334')
    #t = Timer(lambda: determineSchool(boxes, yale_lat, yale_lng, levels))
    print determineSchool(boxes, yale_lat, yale_lng, levels)

    stan_lat = 37.4413935
    stan_lng = -122.1502171
    print determineSchool(boxes, stan_lat, stan_lng, levels)

    whet_lat = 41.87121666666667
    whet_lng = -88.09618333333333
    print determineSchool(boxes, whet_lat, whet_lng, levels)

    #print json.dumps(boxes)

    median_lat = boxes['median_lat']
    median_lng = boxes['median_lng']

    print str(median_lat) + "," + str(median_lng)

    i = 0
    for box_corner in boxes['boxes']:
        print "In box: " + str(i)

        subi = 0
        for sub_corner in box_corner['boxes']:
            print "  SUB BOX: " + str(subi)
            if levels == 3:
                sub3 = 0
                for sub_corner2 in sub_corner['boxes']:
                    print "    SUB SUB BOX: "+ str(sub3)
                    for school in sub_corner2:
                        ne = school['bounds']['northeast']
                        print "          " + school['name'] + "     " + str(ne['lat']) + "," + str(ne['lng'])
                    sub3 += 1
            else:
                for school in sub_corner:
                    ne = school['bounds']['northeast']
                    print "        " + school['name'] + "     " + str(ne['lat']) + "," + str(ne['lng'])
            subi += 1
        print ""
        print ""
        i += 1

