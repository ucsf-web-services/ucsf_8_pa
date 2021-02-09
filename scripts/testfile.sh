#!/bin/bash
main() {
	filedir="../docroot/sites/default/files"
	echo "File deletion list is $filelist"
	filelist="extest.csv"
	echo "Changeing active directory to files directory( $filedir )"
	cd "$filedir"
	sed 's/http:\/\/ucsf\.edu\/sites\/default\/files\///g' "$filelist" > remove.txt
	input="remove.txt"
	echo "" > remove_list.csv
    while IFS= read -r line
    do
    	if test -f "$line"; then
    		echo "$line exists."
    		rm $line
    		echo "http:ucsf.edu/sites/default/files/$line,removed" >> removed_list.csv
    	else 
    		echo "http:ucsf.edu/sites/default/files/$line,DNE" >> removed_list.csv
    		
		fi
    done < "$input"

}
main "$@"