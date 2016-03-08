#!/bin/bash

# clean
for pkg in "omerocommon" "omeromultichoice" "omerointeractive"; do
    echo "Cleaning dist of the package: '${pkg}'";
    directory="${pkg}/amd/build"
    if [[ -d ${directory} ]]; then
        if [[ "$(ls -A ${directory})" ]]; then
            rm ${directory}/*
        fi
    fi
done