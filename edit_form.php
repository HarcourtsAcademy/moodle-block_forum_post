<?php

class block_forum_post_edit_form extends block_edit_form {

    protected function specific_definition($mform) {

        //block settings title
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        $mform->addElement('text', 'config_title', get_string('configtitle', 'block_forum_post'));
        $mform->setType('config_title', PARAM_TEXT);

        $forumoptions = array();

        $forums = $this->get_course_forums();

        if (!empty($forums)) {

            foreach ($forums as $key => $value) {
                $forumoptions[$value->id] = $value->name;
            }

            $mform->addElement('select', 'config_forum', get_string('configselectforum', 'block_forum_post'), $forumoptions);

        } else {

            $mform->addElement('static', 'config_forum', get_string('configselectforum', 'block_forum_post'),
                    get_string('confignoforums', 'block_forum_post'));

        }

    }

    /**
     * Get all the forums in the course.
     *
     * @return array of forum objects indexed by first column
     */
    private function get_course_forums() {

        global $DB, $CFG, $COURSE, $USER;

        if ($forums = $DB->get_records_select("forum", "course = '$COURSE->id'")) {
            return $forums;
        }

    }
}
