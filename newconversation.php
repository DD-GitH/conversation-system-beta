<?php

define("IN_MYBB", 1);
define('THIS_SCRIPT', 'newconversation.php');
require_once "./global.php";

add_breadcrumb('New Conversation', "newconversation.php");

if ($mybb->user['usergroup'] == 1 or $mybb->user['usergroup'] == 7)
{
    error_no_permission();
}
else
{
    $codebuttons = build_mycode_inserter();

    if($forum['allowsmilies'] != 0)
    {
        $clickablesmilies = build_clickable_smilies();
    }
    $messages_list = "";
    $total_conversations = 0;
    $query1 = $db->query("SELECT * FROM ".TABLE_PREFIX."conversations");
    while ($conversation = $db->fetch_array($query1))
    {
        $participants = unserialize($conversation['participants']);
        if (in_array($mybb->user['uid'], $participants))
        {
            $total_conversations++;
            $query2 = $db->query("SELECT * FROM ".TABLE_PREFIX."conversation_replies WHERE cid = {$conversation['id']}");
            while ($reply = $db->fetch_array($query2))
            {
                $used++;
            }
        }
    }
    $query = $db->query("SELECT * FROM ".TABLE_PREFIX."usergroups WHERE gid = {$mybb->user['usergroup']}");
    while ($usergroup = $db->fetch_array($query))
    {
        if ($usergroup['pmquote'] == 0)
        {
            $total_storage = "Unlimited";
            $used_percent = "0%";
        }
        else
        {
            $total_storage = $usergroup['pmquota'];
            $used_percent .= $used * 100 / $total_storage;
            $used_percent = round($used_percent, 2);
            $used_percent .= "%";
        }
    }
    if ($used >= $total_storage and $total_storage != "Unlimited")
    {
        header("Location: conversations.php?error=storage");
        exit;
    }
    if (!empty($_POST))
    {
        $title = $_POST['title'];
        $participants = explode(',', $_POST['participants']);
        $admins = explode(',', $_POST['admins']);
        $nparticipants = array($mybb->user['uid']);
        $nadmins = array($mybb->user['uid']);
        $message = $_POST['message'];
        foreach ($participants as $participant)
        {
            $participant = trim($participant);
            if ($participant != "" or $participant == $mybb->user['username'])
            {
                $query = $db->simple_select("users", "*", "username='{$participant}'");
                while ($user = $db->fetch_array($query))
                {
                    $participant = $user['uid'];
                    $exist = 1;
                }
                if (!isset($exist))
                {
                    $inline_errors = '<div class="inline_errors">User "'.$participant.'" doesn\'t exist.</div>';
                }
                else
                {
                    unset($exist);
                    array_push($nparticipants, $participant);
                }
            }
        }
        foreach ($admins as $admin)
        {
            $admin = trim($admin);
            if ($admin != "" or $admin == $mybb->user['username'])
            {
                $query = $db->simple_select("users", "*", "username='{$admin}'");
                while ($user = $db->fetch_array($query))
                {
                    $admin = $user['uid'];
                    $exist = 1;
                }
                if (!isset($exist))
                {
                    $inline_errors = '<div class="inline_errors">User "'.$admin.'" doesn\'t exist.</div>';
                }
                else
                {
                    unset($exist);
                    array_push($nadmins, $admin);
                }
            }
        }
        $message = trim($message);
        if (empty($nparticipants) or empty($message) or empty($nadmins) or empty($title))
        {
            $inline_errors = '<div class="inline_errors">Please check your entries.</div>';   
        }
        if (!isset($inline_errors))
        {
            foreach ($nparticipants as $participant)
            {
                $viewers[$participant] = "Never";
            }
            $reply_day = date('F jS, Y', strtotime('now'));
            $reply_time = date('H:i A', strtotime('now'));
            $reply['date'] = date('F jS, Y H:i A', strtotime('now'));
            $today = date('F jS, Y');
            $yesterday = date('F jS, Y', strtotime('-1 days'));
            if ($reply_day == $today) { $reply['date'] = "Today $reply_time"; }
            if ($reply_day == $yesterday) { $reply['date'] = "Yesterday $reply_time"; }
            $viewers[$mybb->user['uid']] = $reply['date'];
            $db->insert_query("conversations", array(
                "title" => addslashes($title),
                "participants" => serialize($nparticipants),
                "admins" => serialize($nadmins),
                "views" => serialize($viewers),
                "lastpm" => date('Y-m-d H:i:s'),
                "creator" => $mybb->user['uid']
            ));
            
            $db->insert_query("conversation_replies", array(
                "cid" => $db->insert_id(),
                "viewedby" => serialize(array($mybb->user['uid'])),
                "uid" => $mybb->user['uid'],
                "content" => addslashes($message)
            ));
            header("Location: conversations.php");
        }
    }
    $usernames_box = "";
    if (isset($_GET['uid']))
    {
        $uid = intval($_GET['uid']);
        $user = get_user($uid);
        $usernames_box = $user['username'].",";
    }
    eval("\$newconversation = \"".$templates->get("newconversation_page")."\";");
    output_page($newconversation);
}
