<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Block for posting to a course forum
 *
 * @package    block_forum_post
 * @copyright  Tim Butler <tim.butler@harcourts.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class block_forum_post extends block_base {

    function init() {
        $this->title = get_string('blocktitle', 'block_forum_post');
        $this->content_type = BLOCK_TYPE_TEXT;
    }

    public function specialization() {

        global $CFG, $USER, $COURSE;

        if (!empty($this->config->title)) {
            $this->title = format_string($this->config->title);
        }

    }

    function get_content() {
        global $CFG, $OUTPUT;

        if ($this->content !== null) {
            return $this->content;
        }

        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }

        $posturl = new moodle_url($CFG->wwwroot . '/blocks/forum_post/post.php');

        $this->content = new stdClass();

        $form = '<form id="forumpostform" autocomplete="off" action="'.$posturl.'" method="post" accept-charset="utf-8" class="mform" onsubmit="">';
        $form.= '<div style="display: none;">';
        $form.= '<input name="course" type="hidden" value="49">';
        $form.= '<input name="forum" type="hidden" value="57">';
        $form.= '<input name="userid" type="hidden" value="2">';
        $form.= '<input name="groupid" type="hidden" value="">';
        $form.= '<input name="sesskey" type="hidden" value="'.sesskey().'">';
        $form.= '</div>';
		$form.= '<div class="controls">';
        $form.= '<label for="forum-post-subject">Subject</label><input class="span12" name="subject" type="text" value="" id="forum-post-subject" placeholder="Type the subject…" required>';
		$form.= '<label for="forum-post-message">Message</label><textarea class="span12" id="forum-post-message" name="message" rows="5" spellcheck="true" placeholder="Type the message…" required></textarea>';
        $form.= '</div>';
		$form.= '<div class="form-submit">';
        $form.= '<input name="submitbutton" value="Post to forum" type="submit" id="submitbutton" class="btn-block">';
        $form.= '</div>';
        $form.= '<form>';

        $this->content->text   = $form;
        $this->content->footer = '';


        // user/index.php expect course context, so get one if page has module context.
        $currentcontext = $this->page->context->get_course_context(false);

        return $this->content;
    }

    // my moodle can only have SITEID and it's redundant here, so take it away
    public function applicable_formats() {
        return array('all' => false,
                     'site' => false,
                     'site-index' => false,
                     'course-view' => true, 
                     'course-view-social' => false,
                     'mod' => true, 
                     'mod-quiz' => false);
    }

    public function instance_allow_multiple() {
          return false;
    }

    function has_config() {return true;}

}
