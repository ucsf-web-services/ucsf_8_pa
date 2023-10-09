#!/bin/bash

# Check if a file is provided
if [ -z "$1" ]; then
  echo "Usage: $0 <filename>"
  exit 1
fi

filename="$1"

# Check if the file exists
if [ ! -e "$filename" ]; then
  echo "File not found: $filename"
  exit 1
fi

# Read the file line by line and execute each line as a bash command
while IFS= read -r line; do
  eval "$line"
done < "$filename"
