#!/bin/bash

# updates
for pkg in "omerocommon" "omeromultichoice" "omerointeractive"; do
    echo "Uptading dist of the package: '${pkg}'";
    cd ${pkg}/amd && grunt --force && cd -
done