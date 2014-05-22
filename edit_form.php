<?php

class block_forum_post_edit_form extends block_edit_form {

    protected function specific_definition($mform) {

        //block settings title
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        $mform->addElement('text', 'config_title', get_string('configtitle', 'block_forum_post'));
        $mform->setType('config_title', PARAM_TEXT);

    }
}
