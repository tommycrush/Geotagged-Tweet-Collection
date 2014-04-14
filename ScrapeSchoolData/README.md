
## How to scrape schools

First, we scrape a list from forbes. Run `python get_schools.py`. Note: this script sleeps a lot to avoid rate limiting issues. After that, we should have a file called schools.json. Run `python bounds.py` to create `boxes.json`. This contains the 64 quadrants of the United States (see paper for details). Copy this file to the stream folder.

