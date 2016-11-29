#!/bin/bash


# hacky script to get rid of lines before and after a found pattern
# need this to report errors in notifications while skipping false positives from ansible log
# usage: exclude.sh pattern lines_after_to exclude lines_before_to_exclude

match=$1
A=$2
B=$3

sed -ne:t -e"/\n.*$match/D" \
    -e'$!N;//D;/'"$match/{" \
            -e"s/\n/&/$A;t" \
            -e'$q;bt' -e\}  \
    -e's/\n/&/'"$B;tP"      \
    -e'$!bt' -e:P  -e'P;D'