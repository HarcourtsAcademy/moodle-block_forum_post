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
        global $CFG, $COURSE, $OUTPUT, $USER;

        if ($this->content !== null) {
            return $this->content;
        }

        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }

        $posturl = new moodle_url($CFG->wwwroot . '/blocks/forum_post/post.php');

        $courseid = $COURSE->id;
        $forumid = $this->config->forum;
        $userid = $USER->id;

        // Get the course module.
        $cm = get_coursemodule_from_instance('forum', $forumid, $courseid);
        $modulecontext = context_module::instance($cm->id);

        // Get the groups the user is in
        $groups = groups_get_activity_allowed_groups($cm);

        // Get the current group id.
        $currentgroupid = groups_get_activity_group($cm);

        $this->content = new stdClass();

        $form = '<form id="forumpostform" autocomplete="off" action="'.$posturl.'" method="post" accept-charset="utf-8" class="mform" onsubmit="">';
        $form.= '<div style="display: none;">';
        $form.= '<input name="course" type="hidden" value="'.$courseid.'">';
        $form.= '<input name="forum" type="hidden" value="'.$forumid.'">';
        $form.= '<input name="userid" type="hidden" value="'.$userid.'">';
        $form.= '<input name="sesskey" type="hidden" value="'.sesskey().'">';
        $form.= '</div>';
		$form.= '<div class="controls">';
        $form.= '<label for="forum-post-subject">'.get_string('subjectlabel','block_forum_post').'</label><input class="span12" name="subject" type="text" value="" id="forum-post-subject" placeholder="'.get_string('subjectplaceholder','block_forum_post').'" required>';
		$form.= '<label for="forum-post-message">'.get_string('messagelabel','block_forum_post').'</label><textarea class="span12" id="forum-post-message" name="message" rows="5" spellcheck="true" placeholder="'.get_string('messageplaceholder','block_forum_post').'" required></textarea>';
        if (count($groups) > 1 or has_capability('mod/forum:movediscussions', $modulecontext)) {
            // Ask user to select the group
            $form.= '<select name="groupid" class="span12">';

            if (has_capability('mod/forum:movediscussions', $modulecontext)) {
                // Students can't post to all participants.
                $form.= '  <option value="0">'.get_string('allparticipants').'</option>';
            }

            foreach ($groups as $group) {
                $form.= '  <option value="'.$group->id.'">'.$group->name.'</option>';
            }
            $form.= '</select>';
        } else {
           $form.= '<input name="groupid" type="hidden" value="'.$currentgroupid.'">';
        }
        $form.= '</div>';
		$form.= '<div class="form-submit">';
        $form.= '<input name="submitbutton" value="'.get_string('posttoforum','block_forum_post').'" type="submit" id="submitbutton" class="btn-block">';
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
