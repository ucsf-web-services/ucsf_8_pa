#!/bin/bash

#
# UCSF Site PDF Removal Project.
# Provisions a new site in Acquia's hosting environments using it's Cloud API.
#
# Authors:
#   Austen Tong <austen.tong@ucsf.edu>
#   Need to fix this ...
# Note:
# You *must* have your Acquia Cloud API credentials set as environment variables AQ_CLOUD_USER and AQ_CLOUD_PASS.
# Otherwise the script will not work correctly
#.
main() {
    # Capture the location of this script from the execution context.
    local -r script_dir="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
    local -r docroot="${script_dir}/../docroot"

    local ok=0

    local domain
    local db_name
    
    filedir="${script_dir}/../docroot/sites/default/files"
	  filelist="pdflist.csv"
	  echo "File deletion list is $filelist"
	  echo "Changeing active directory to files directory( $filedir )"
	  cd "$filedir"
	  sed 's/https\?:\/\/\(www\.\)\?ucsf\.edu\/sites\/default\/files\///g' "$filelist" > remove.txt
      sleep 10
      sed 's/%20/ /g' "remove.txt" > removed.txt
	  input="removed.txt"
	  echo "" > removed_list.csv
    while IFS= read -r line
    do
    	if test -f "$line"; then
    		echo "$line exists."
    		rm $line
    		echo "$line,removed" >> removed_list.csv
    	else 
    		echo "$line,DNE" >> removed_list.csv
		fi
    done < "$input"
   echo
   echo "Done removing files"
}

main "$@"
