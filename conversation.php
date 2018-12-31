<?php

define("IN_MYBB", 1);
define('THIS_SCRIPT', 'conversation.php');
require_once "./global.php";

add_breadcrumb('Conversations', "conversation.php");

if ($mybb->user['usergroup'] == 1 or $mybb->user['usergroup'] == 7)
{
    error_no_permission();
}
else
{
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
    if (isset($_GET['id']))
    {
        $id = intval($_GET['id']);
        $exist = 0;
        $query1 = $db->query("SELECT * FROM ".TABLE_PREFIX."conversations WHERE id = {$id}");
        while ($conversation = $db->fetch_array($query1))
        {
            $admins = unserialize($conversation['admins']);
            if (in_array($mybb->user['uid'], $admins))
            {
                $admin_functions = "";
            }
            else
            {
                $admin_functions = "jQuery('#admin').remove();";
            }
            if ($mybb->user['avatar'] == '') { $mybb->user['avatar'] = 'images/default_avatar.png'; }
            $formatted_user = format_name($mybb->user['username'], $mybb->user['usergroup'], $mybb->user['displaygroup']);
            $conversation_title = $conversation['title'];
            $exist = 1;
            $participants = unserialize($conversation['participants']);
            if (in_array($mybb->user['uid'], $participants))
            {
                $viewers = unserialize($conversation['views']);
                
                $reply_day = date('F jS, Y', strtotime('now'));
                $reply_time = date('H:i A', strtotime('now'));
                $view['date'] = date('F jS, Y H:i A', strtotime('now'));
                $today = date('F jS, Y');
                $yesterday = date('F jS, Y', strtotime('-1 days'));
                if ($reply_day == $today) { $view['date'] = "Today $reply_time"; }
                if ($reply_day == $yesterday) { $view['date'] = "Yesterday $reply_time"; }
                $viewers[$mybb->user['uid']] = $view['date'];
                $viewers = serialize($viewers);
                $db->query("UPDATE ".TABLE_PREFIX."conversations SET views = '{$viewers}' WHERE id = {$id}");
                $viewers = unserialize($viewers);
                $views = "";
                foreach($viewers as $key => $date)
                {
                    $query4 = $db->query("SELECT * FROM ".TABLE_PREFIX."users WHERE uid = {$key}");
                    while ($viewer = $db->fetch_array($query4))
                    {
                        if ($viewer['avatar'] == '') { $viewer['avatar'] = 'images/default_avatar.png'; }
                        $viewer['username'] = format_name($viewer['username'], $viewer['usergroup'], $viewer['displaygroup']);
                        $views .= "<img src='".$viewer['avatar']."' style='width:40px;height:40px;margin-right:10px;float:left' />".$viewer['username']."<br>Read: ".$date."<br><br>";
                    }
                }
                $messages_list = "";
                $query2 = $db->query("SELECT * FROM ".TABLE_PREFIX."conversation_replies WHERE cid = {$id} ORDER BY id");
                while ($reply = $db->fetch_array($query2))
                {
                    $reply_day = date('F jS, Y', strtotime($reply['date']));
                    $reply_time = date('H:i A', strtotime($reply['date']));
                    $reply['date'] = date('F jS, Y H:i A', strtotime($reply['date']));
                    $today = date('F jS, Y');
                    $yesterday = date('F jS, Y', strtotime('-1 days'));
                    if ($reply_day == $today) { $reply['date'] = "Today $reply_time"; }
                    if ($reply_day == $yesterday) { $reply['date'] = "Yesterday $reply_time"; }
                    require_once MYBB_ROOT."inc/class_parser.php";
                    $parser = new postParser; 
                    $parser_options = array(
                        'allow_html' => 'no',
                        'allow_mycode' => 'yes',
                        'allow_smilies' => 'yes',
                        'allow_imgcode' => 'yes',
                        'filter_badwords' => 'yes',
                        'nl2br' => 'yes'
                    );
                    $reply['content'] = $parser->parse_message($reply['content'], $parser_options);
                    if ($reply['uid'] == $mybb->user['uid'])
                    {
                        $user = $mybb->user;
                        $admins = unserialize($conversation['admins']);
                        if (in_array($mybb->user['uid'], $admins))
                        {
                            $message_delete = "<a href='#' onclick='conversation_delete(\"".$reply['id']."\");'><i class='fa fa-trash'></i></a>";
                        }
                        else
                        {
                            $message_delete = "";
                        }
                        if (empty($user['avatar']))
                        {
                            $user['avatar'] = "images/default_avatar.png";
                        }
                        $user['username'] = format_name($mybb->user['username'], $mybb->user['usergroup'], $mybb->user['displaygroup']);
                        eval("\$messages_list .= \"".$templates->get("conversations_message_user")."\";");
                    }
                    else
                    {
                        $query3 = $db->query("SELECT * FROM ".TABLE_PREFIX."users WHERE uid = {$reply['uid']}");
                        while ($user = $db->fetch_array($query3))
                        {
                            if ($user['avatar'] == "")
                            {
                                $user['avatar'] = "images/default_avatar.png";
                            }
                            $admins = unserialize($conversation['admins']);
                            if (in_array($mybb->user['uid'], $admins))
                            {
                                $message_delete = "<a href='#' onclick='conversation_delete(\"".$reply['id']."\");'><i class='fa fa-trash'></i></a>";
                            }
                            else
                            {
                                $message_delete = "";
                            }
                            $user['username'] = format_name($user['username'], $user['usergroup'], $user['displaygroup']);
                            eval("\$messages_list .= \"".$templates->get("conversations_message_participants")."\";");
                        }
                    }
                }
                if (isset($_GET['action']) and $_GET['action'] == "refresh")
                {
                    echo $messages_list;
                    if ($used >= $total_storage and $total_storage != "Unlimited")
                    {
                        echo "<div class=\"inline_errors\">Error: Maximum storage is reached.</div>";
                    }  
                    exit;
                }
                elseif (isset($_GET['action']) and $_GET['action'] == "send" and isset($_GET['message']))
                {
                    $_GET['message'] = trim($_GET['message']);
                    if ($used >= $total_storage and $total_storage != "Unlimited" or empty($_GET['message']))
                    {
                        exit;
                    }  
                    $db->insert_query("conversation_replies", array(
                        "cid" => $id,
                        "viewedby" => serialize(array($mybb->user['uid'])),
                        "uid" => $mybb->user['uid'],
                        "content" => addslashes($_GET['message'])
                    ));
                    exit;
                }
                elseif (isset($_GET['action']) and $_GET['action'] == "markasread" and isset($_GET['id']))
                {
                    $id = intval($_GET['id']);
                    $query2 = $db->query("SELECT * FROM ".TABLE_PREFIX."conversations WHERE id = {$id}");
                    while ($reply = $db->fetch_array($query2))
                    {
                        $viewers = unserialize($reply['views']);
                        if (empty($viewers))
                        {
                            $viewers = array();
                        }
                        if (!isset($viewers[$mybb->user['uid']]) or $viewers[$mybb->user['uid']] == "Never")
                        {
                            $reply_day = date('F jS, Y', strtotime('now'));
                            $reply_time = date('H:i A', strtotime('now'));
                            $reply['date'] = date('F jS, Y H:i A', strtotime('now'));
                            $today = date('F jS, Y');
                            $yesterday = date('F jS, Y', strtotime('-1 days'));
                            if ($reply_day == $today) { $reply['date'] = "Today $reply_time"; }
                            if ($reply_day == $yesterday) { $reply['date'] = "Yesterday $reply_time"; }
                            $viewers[$mybb->user['uid']] = $reply['date'];
                            $viewers = serialize($viewers);
                            $db->query("UPDATE ".TABLE_PREFIX."conversations SET views = '{$viewers}' WHERE id = {$id}");
                        }
                    }
                    echo "ok";
                    exit;
                }
                elseif (isset($_GET['action']) and $_GET['action'] == "markasunread" and isset($_GET['id']))
                {
                    $id = intval($_GET['id']);
                    $query2 = $db->query("SELECT * FROM ".TABLE_PREFIX."conversations WHERE id = {$id}");
                    while ($reply = $db->fetch_array($query2))
                    {
                        $viewers = unserialize($reply['views']);
                        if (empty($viewers))
                        {
                            $viewers = array();
                        }
                        if (isset($viewers[$mybb->user['uid']]))
                        {
                            $viewers[$mybb->user['uid']] = 'Never';
                            $viewers = serialize($viewers);
                            $db->query("UPDATE ".TABLE_PREFIX."conversations SET views = '{$viewers}' WHERE id = {$id}");
                        }
                    }
                    echo "ok";
                    exit;
                }
                elseif (isset($_GET['action']) and $_GET['action'] == "delete_conversation" and isset($_GET['id']))
                {
                    if (in_array($mybb->user['uid'], $admins))
                    {
                        $id = intval($_GET['id']);
                        $db->query("DELETE FROM ".TABLE_PREFIX."conversations WHERE id = {$id}");
                        echo "ok";
                        exit;
                    }
                    else
                    {
                        echo "<center>Error: You must be admin to delete '".$conversation['title']."' conversation</center>";
                        exit;
                    }
                }
                elseif (isset($_GET['action']) and $_GET['action'] == "delete" and isset($_GET['message']))
                {
                    $_GET['message'] = intval($_GET['message']);
                    $db->query("DELETE FROM ".TABLE_PREFIX."conversation_replies WHERE id = ".$_GET['message']);
                    exit;
                }
                elseif (isset($_GET['action']) and $_GET['action'] == "add" and isset($_GET['users']) and !empty($_GET['users']))
                {
                    unset($exist);
                    $nparticipants = $participants;
                    $added_participants = explode(',', $_GET['users']);
                    foreach ($added_participants as $participant)
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
                                echo '<div class="inline_errors">User "'.$participant.'" doesn\'t exist.</div>';
                                exit;
                            }
                            else
                            {
                                unset($exist);
                                if (!in_array($participant, $participants))
                                {
                                    array_push($nparticipants, $participant);
                                    $viewers[$participant] = "Never";
                                }
                            }
                        }
                    }
                    if ($nparticipants != $participants)
                    {
                        $db->query("UPDATE ".TABLE_PREFIX."conversations SET participants='".serialize($nparticipants)."', views='".serialize($viewers)."' WHERE id=".$id); 
                        echo '<div class="inline_ok">Users successfuly added.</div>';
                    }
                    if ($nparticipants == $participants)
                    {
                        echo '<div class="inline_errors">Users already added in conversation.</div>';
                    }
                    exit;
                }
                elseif (isset($_GET['action']) and $_GET['action'] == "kick" and isset($_GET['users']) and !empty($_GET['users']))
                {
                    unset($exist);
                    $nparticipants = $participants;
                    $added_participants = explode(',', $_GET['users']);
                    foreach ($added_participants as $participant)
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
                                echo '<div class="inline_errors">User "'.$participant.'" doesn\'t exist.</div>';
                                exit;
                            }
                            else
                            {
                                unset($exist);
                                if (in_array($participant, $participants))
                                {
                                    $nparticipants = array_diff($nparticipants, [$participant]);
                                    unset($viewers[$participant]);
                                }
                            }
                        }
                    }
                    if ($nparticipants != $participants)
                    {
                        $db->query("UPDATE ".TABLE_PREFIX."conversations SET participants='".serialize($nparticipants)."', views='".serialize($viewers)."' WHERE id=".$id); 
                        echo '<div class="inline_ok">Users successfuly kicked.</div>';
                    }
                    if ($nparticipants == $participants)
                    {
                        echo '<div class="inline_errors">Users are not members of this conversation.</div>';
                    }
                    exit;
                }
            }
            else
            {
                error_no_permission();
            }
        }
        if ($exist == 0)
        {
            header("Location: conversations.php?error=id");
            exit;
        }
    }
    else
    {
        header("Location: conversations.php");
        exit;
    }
    eval("\$conversation = \"".$templates->get("conversation_page")."\";");
    output_page($conversation);
}
