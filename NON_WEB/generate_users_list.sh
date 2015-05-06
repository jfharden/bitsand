#!/bin/bash

# Very simple script to generate a list of users for the front page of
# the Google Code site

cd `dirname $0`
grep -v "^#" systems | sed s/"\(.*\)\t.*\t\(.*\)export.php"/"  \* \[\2 \1\]"/g
