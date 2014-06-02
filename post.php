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
 * Save a new post to a discussion forum
 *
 * @package block-forum_post
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * TODO Development:
 * * Record student's post in the completion system
 * - Prevent multiple posts if the page is refreshed
 * * Convert output to Moodle lang strings
 * * Add ability to choose the forum to post to
 * * Ensure student posts appear in the forum immediately
 * * Show confirmation message when post was successful
 *
 */


/**
 * TODO Testing:
 * - Test new post when user not logged in
 * - Test new post when forum hidden from user
 * - Test new post when user is a guest
 * - Test new post by student to teachers only news forum
 * - Test trainer posting to all groups
 * - Test special characters in new post
 * - Test html tags in new post
 *
 */

require_once('../../config.php');
require_once('../../mod/forum/lib.php');
require_once($CFG->libdir.'/completionlib.php');

$sesskey = required_param('sesskey', PARAM_ALPHANUM);
$subject = required_param('subject', PARAM_NOTAGS);
$message = required_param('message', PARAM_NOTAGS);
$forum   = optional_param('forum', 0, PARAM_INT);
$groupid = optional_param('groupid', null, PARAM_INT);

$PAGE->set_url('/blocks/forum_post/post.php', array(
        'forum'  =>$forum,
        'groupid'=>$groupid,
        ));

//these page_params will be passed as hidden variables later in the form.
$page_params = array('forum'=>$forum);

$sitecontext = context_system::instance(); // TODO: is this right?

if (!confirm_sesskey($sesskey)) {
    print_error('invalidsesskey');
}

if (!data_submitted()) {
    print_error('invaliddata');
}

if (!isloggedin() or isguestuser()) {

    if (!isloggedin() and !get_referer()) {
        // No referer+not logged in - probably coming in via email  See MDL-9052
        require_login();
    }

    if (!empty($forum)) {      // User is starting a new discussion in a forum
        if (! $forum = $DB->get_record('forum', array('id' => $forum))) {
            print_error('invalidforumid', 'forum');
        }
    }

    if (! $course = $DB->get_record('course', array('id' => $forum->course))) {
        print_error('invalidcourseid');
    }

    if (!$cm = get_coursemodule_from_instance('forum', $forum->id, $course->id)) { // For the logs
        print_error('invalidcoursemodule');
    } else {
        $modcontext = context_module::instance($cm->id);
    }

    $PAGE->set_cm($cm, $course, $forum);
    $PAGE->set_context($modcontext);
    $PAGE->set_title($course->shortname);
    $PAGE->set_heading($course->fullname);

    echo $OUTPUT->header();
    echo $OUTPUT->confirm(get_string('noguestpost', 'forum').'<br /><br />'.get_string('liketologin'), get_login_url(), get_referer(false));
    echo $OUTPUT->footer();
    exit;
}

require_login(0, false);   // Script is useless unless they're logged in

if (empty($subject) or empty($message)) {
    $returnurl = '';
    if (get_referer()) {
        $returnurl = get_referer(FALSE);
    } else {
        $returnurl = "/course/view.php?id=$course->id";
    }

    if (!empty($forum)) {      // User is starting a new discussion in a forum
        if (! $forum = $DB->get_record('forum', array('id' => $forum))) {
            print_error('invalidforumid', 'forum');
        }
    }

    if (! $course = $DB->get_record('course', array('id' => $forum->course))) {
        print_error('invalidcourseid');
    }

    if (!$cm = get_coursemodule_from_instance('forum', $forum->id, $course->id)) { // For the logs
        print_error('invalidcoursemodule');
    } else {
        $modcontext = context_module::instance($cm->id);
    }

    $PAGE->set_cm($cm, $course, $forum);
    $PAGE->set_context($modcontext);
    $PAGE->set_title($course->shortname);
    $PAGE->set_heading($course->fullname);

    echo $OUTPUT->header();
    notice(get_string('missingpostdata', 'block_forum_post'), $returnurl, $course);
    echo $OUTPUT->footer();
}

if (!empty($forum)) {      // User is starting a new discussion in a forum
    if (! $forum = $DB->get_record("forum", array("id" => $forum))) {
        print_error('invalidforumid', 'forum');
    }
    if (! $course = $DB->get_record("course", array("id" => $forum->course))) {
        print_error('invalidcourseid');
    }
    if (! $cm = get_coursemodule_from_instance("forum", $forum->id, $course->id)) {
        print_error("invalidcoursemodule");
    }

    $coursecontext = context_course::instance($course->id);

    if (! forum_user_can_post_discussion($forum, $groupid, -1, $cm)) {
        if (!isguestuser()) {
            if (!is_enrolled($coursecontext)) {
                if (enrol_selfenrol_available($course->id)) {
                    $SESSION->wantsurl = qualified_me();
                    $SESSION->enrolcancel = $_SERVER['HTTP_REFERER'];
                    redirect($CFG->wwwroot.'/enrol/index.php?id='.$course->id, get_string('youneedtoenrol'));
                }
            }
        }
        print_error('nopostforum', 'forum');
    }

    if (!$cm->visible and !has_capability('moodle/course:viewhiddenactivities', $coursecontext)) {
        print_error("activityiscurrentlyhidden");
    }

    if (isset($_SERVER["HTTP_REFERER"])) {
        $SESSION->fromurl = $_SERVER["HTTP_REFERER"];
    } else {
        $SESSION->fromurl = '';
    }

    // Load up the $post variable.

    $post = new stdClass();
    $post->course        = $course->id;
    $post->forum         = $forum->id;
    $post->discussion    = 0;           // ie discussion # not defined yet
    $post->parent        = 0;
    $post->subject       = $subject;
    $post->userid        = $USER->id;
    $post->message       = $message;
    $post->messageformat = editors_get_preferred_format();
    $post->messagetrust  = 0;

    if (isset($groupid)) {
        $post->groupid = $groupid;
    } else {
        $post->groupid = groups_get_activity_group($cm);
    }

    // Unsetting this will allow the correct return URL to be calculated later.
    unset($SESSION->fromdiscussion);
} else {
    print_error('unknowaction');
}

if (!isset($coursecontext)) {
    // Has not yet been set by post.php.
    $coursecontext = context_course::instance($forum->course);
}

// from now on user must be logged on properly

if (!$cm = get_coursemodule_from_instance('forum', $forum->id, $course->id)) { // For the logs
    print_error('invalidcoursemodule');
}
$modcontext = context_module::instance($cm->id);
require_login($course, false, $cm);

if (isguestuser()) {
    // just in case
    print_error('noguest');
}

if (empty($SESSION->fromurl)) {
    $errordestination = "$CFG->wwwroot/mod/forum/view.php?f=$forum->id";
} else {
    $errordestination = $SESSION->fromurl;
}

$post->itemid        = $post->message['itemid'];

// Adding a new discussion.
// Before we add this we must check that the user will not exceed the blocking threshold.
$thresholdwarning = forum_check_throttling($forum, $cm);
forum_check_blocking_threshold($thresholdwarning);

if (!forum_user_can_post_discussion($forum, $post->groupid, -1, $cm, $modcontext)) {
    print_error('cannotcreatediscussion', 'forum');
}
if (empty($post->groupid)) {
    $post->groupid = -1;
}

$discussion                 = new stdClass();
$discussion->course         = $post->course;
$discussion->forum          = $post->forum;
$discussion->name           = $post->subject;
$discussion->message        = $post->message;
$discussion->messageformat  = FORMAT_PLAIN;
$discussion->messagetrust   = trusttext_trusted($modcontext);
$discussion->mailnow        = 0;
$discussion->timestart      = 0;
$discussion->timeend        = 0;

$message = '';
if ($discussion->id = forum_add_discussion($discussion)) {

    add_to_log($course->id, "forum", "add discussion",
            "discuss.php?d=$discussion->id", "$discussion->id", $cm->id);

    $timemessage = 2;
    if (!empty($message)) { // if we're printing stuff about the file upload
        $timemessage = 4;
    }

    $message .= '<p>'.get_string("postaddedsuccess", "forum") . '</p>';
    $message .= '<p>'.get_string("postaddedtimeleft", "forum", format_time($CFG->maxeditingtime)) . '</p>';

    if ($subscribemessage = forum_post_subscription($discussion, $forum)) {
        $timemessage = 6;
    }

    // Update completion status
    $completion=new completion_info($course);
    if($completion->is_enabled($cm) &&
        ($forum->completiondiscussions || $forum->completionposts)) {
        $completion->update_state($cm,COMPLETION_COMPLETE);
    }

    if (get_referer()) {
        redirect(get_referer(FALSE), $message.$subscribemessage, $timemessage);
    } else {
        redirect(forum_go_back_to("/mod/forum/view.php?f=$post->forum"), $message.$subscribemessage, $timemessage);
    }

    

} else {
    print_error("couldnotadd", "forum", $errordestination);
}

exit;