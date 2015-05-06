#!/bin/bash

# GrepForIssues.sh
# Automated tests for some bugs in Bitsand

cd $(dirname $0)
if [ "$1" = "-h" -o "$1" = "--help" ]
then
	echo "GrepForIssues.sh uses grep to test for some issues in Bitsand"
	echo "Now also uses php -l to test for PHP syntax errors"
	echo "Use -v parameter to get verbose output"
	echo "Results are stored in the file Bitsand_issues"
	exit
fi

echo "`date` - Checking Bitsand code for errors" > Bitsand_issues
# Check for missing table prefix
echo "Checking for missing table prefixes"
echo >> Bitsand_issues
echo "Checking for missing table prefixes" >> Bitsand_issues
for TABLE in access_log ancestors bookings characters config factions faq groups guildmembers guilds locations osps ospstaken players sessions skills skillstaken
do
	if [ "$1" = "-v" ]
	then
		echo "Table $TABLE"
		echo "Table $TABLE" >> Bitsand_issues
	fi
	# grep -v is used to filter out hidden files/directories, and files in the NON_WEB directory
	grep -irn "UPDATE $TABLE " ../* | grep -v "/\." | grep -v "NON_WEB" >> Bitsand_issues
	grep -irn "SELECT .* FROM $TABLE " ../* | grep -v "/\." | grep -v "NON_WEB" >> Bitsand_issues
	grep -irn "DELETE FROM $TABLE " ../* | grep -v "/\." | grep -v "NON_WEB" >> Bitsand_issues
	grep -irn "INSERT INTO $TABLE " ../* | grep -v "/\." | grep -v "NON_WEB" >> Bitsand_issues
	grep -irn ", $TABLE[, ]" ../* | grep -v "/\." | grep -v "NON_WEB" >> Bitsand_issues
done

echo "Checking for DB function calls that do not include \$link parameter"
echo >> Bitsand_issues
echo "Checking for DB function calls that do not include \$link parameter" >> Bitsand_issues
for FUNCTION in ba_db_query ba_db_real_escape_string
do
	if [ "$1" = "-v" ]
	then
		echo "Function $FUNCTION"
		echo "Function $FUNCTION" >> Bitsand_issues
	fi
	# grep -v is used to filter out hidden files/directories, and files in the NON_WEB directory
	# search term is a bit of a hack, assumes that link will always be $link
	grep -irl "$FUNCTION *([^\$][^l][^i][^n][^k]" ../* | grep -v "/\." | grep -v "NON_WEB" >> Bitsand_issues
done

echo "Checking for <? instead of <?php"
echo >> Bitsand_issues
echo "Checking for <? instead of <?php" >> Bitsand_issues
# grep -v is used to filter out hidden files/directories, and files in the NON_WEB directory
grep -rl "^<?[^p]" ../* | grep -v "/\." | grep -v "NON_WEB" >> Bitsand_issues
grep -rl "^<?$" ../* | grep -v "/\." | grep -v "NON_WEB" >> Bitsand_issues

echo "Checking for FIXME comments"
echo >> Bitsand_issues
echo "Checking for FIXME comments" >> Bitsand_issues
# grep -v is used to filter out hidden files/directories, and files in the NON_WEB directory
grep -rl "FIXME" ../* | grep -v "/\." | grep -v "NON_WEB" >> Bitsand_issues

echo "Running php -l on files in bitsand/"
echo >> Bitsand_issues
echo "Running php -l on files in bitsand/" >> Bitsand_issues
if [ "$1" = "-v" ]
then
	ls ../*.php | sed s/"\(.*\)"/"php -l \1 >> Bitsand_issues"/ >> temp-run
	ls ../admin/*.php | sed s/"\(.*\)"/"php -l \1 >> Bitsand_issues"/ >> temp-run
	ls ../inc/*.php | sed s/"\(.*\)"/"php -l \1 >> Bitsand_issues"/ >> temp-run
	ls ../install/*.php | sed s/"\(.*\)"/"php -l \1 >> Bitsand_issues"/ >> temp-run
else
	ls ../*.php | sed s/"\(.*\)"/"php -l \1 | grep -v 'No syntax errors detected' >> Bitsand_issues"/ >> temp-run
	ls ../admin/*.php | sed s/"\(.*\)"/"php -l \1 | grep -v 'No syntax errors detected' >> Bitsand_issues"/ >> temp-run
	ls ../inc/*.php | sed s/"\(.*\)"/"php -l \1 | grep -v 'No syntax errors detected' >> Bitsand_issues"/ >> temp-run
	ls ../install/*.php | sed s/"\(.*\)"/"php -l \1 | grep -v 'No syntax errors detected' >> Bitsand_issues"/ >> temp-run
fi
bash temp-run
rm temp-run

echo
NUM_ERR=`wc -l Bitsand_issues | cut -f 1 -d " "`
if [ $NUM_ERR -ne 11 ]
then
	echo "*** Found errors. See the Bitsand_issues file for details ***"
else
	echo "No errors found"
fi
