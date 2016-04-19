#!/bin/bash

if [[ -z ${MOODLE_WWW} ]]; then
	echo -e "ERROR: MOODLE_WWW not found in your environment !!!"
	exit -1
fi

if grep -o 'MoodleQuickForm_omeroquestiontags' ${MOODLE_WWW}/lib/formslib.php ; then
	echo -e "\n NOTICE: 'omeroquestiontags' form already registered."
else	
	echo -e "# OmeroQuestionTags \nMoodleQuickForm::registerElementType('omeroquestiontags', \"\$CFG->dirroot/question/type/omerocommon/omeroquestiontags.php\", 'MoodleQuickForm_omeroquestiontags');" >> ${MOODLE_WWW}/lib/formslib.php
	echo -e "Registering the 'omeroquestiontags' form.... done."
fi

# update JS dist
cd ${MOODLE_WWW}/question/type && ./dist-update.sh