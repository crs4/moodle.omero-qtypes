#!/bin/bash

# clean
./dist-clean.sh

# update
for pkg in "omerocommon" "omeromultichoice" "omerointeractive"; do
    echo "Uptading dist of the package: '${pkg}'";
    cd ${pkg}/amd && grunt "$@" && cd -
done