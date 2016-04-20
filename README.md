# omero_moodle.qtypes

A *question plugin* which supports multichoice questions extended with an embedded OMERO image and ROI based answers.

It allows the teacher to define multiple choice question with

* an embedded OMERO image selected by browsing the OMERO repository;
* a single correct answer or multiple correct answers;
* plaintext answers or ROI based answers.


## How to install

* copy this repository to the folder `<MOODLE-ROOT-DIR>/question/type/`;
* run the `register.sh` script (you need to properly set the `MOODLE_WWW` variable of your environment to point to the root folder of your Moodle installation);
* go to *Site Administrations* ---> *Notifications* and follow the Moodle instructions to complete the plugin installation.

## Requirements

* Moodle 2.9 or later (available on the [Moodle site](https://download.moodle.org/releases/supported/))
* NodeJS and Grunt (*grunt-cli* and the following plugins: *uglify*, *jshint*, *less*, *watch*)
* Omero Repository for Moodle (available on [Github](https://github.com/crs4/moodle.omero-repository))
* Omero FilePicker form for Moodle (available on [Github](https://github.com/crs4/moodle.omero-filepicker))
* (optionally) Omero QuestionBank Tag Filter for Moodle (available on [Github](https://github.com/crs4/moodle.qbank-tag-filter))


## Copyright and license
Code and documentation Copyright © 2015-2016, [CRS4](http://www.crs4.it). 
Code released under the [MIT license](https://opensource.org/licenses/mit-license.php). 