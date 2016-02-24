#!/bin/bash

# clean
for pkg in "omerocommon" "omeromultichoice" "omerointeractive"; do
    echo "Cleaning dist of the package: '${pkg}'";
    rm ${pkg}/amd/build/*
done