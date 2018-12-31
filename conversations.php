<?php

define("IN_MYBB", 1);
define('THIS_SCRIPT', 'conversations.php');
require_once "./global.php";

add_breadcrumb('Conversations', "conversations.php");

if ($mybb->user['usergroup'] == 1 or $mybb->user['usergroup'] == 7)
{
    error_no_permission();
}
else
{
    $used = 0;
    $conversations_list = "";
    if (isset($_GET['action']) and !empty($_POST) and $_GET['action'] == "search" and $_POST['query'] < 25)
    {
        $q = " and title LIKE '%".$_POST['query']."%' ";
    }
    else
    {
        $q = "";
    }
    if (isset($_GET['p']))
    {
        $p = intval($_GET['p']);
        $k = $p+1;
        $interval = $p."0,".$k."0";
    }
    else
    {
        $p = 0;
        $interval = "0,10";
    }
    $query1 = $db->query("SELECT * FROM ".TABLE_PREFIX."conversations WHERE participants LIKE '%{$mybb->user['uid']}%' OR admins LIKE '%{$mybb->user['uid']}%' ".$q." ORDER BY lastpm DESC LIMIT $interval");
    while ($conversation = $db->fetch_array($query1))
    {
        $participants = unserialize($conversation['participants']);
        if (in_array($mybb->user['uid'], $participants))
        {
            $query2 = $db->query("SELECT * FROM ".TABLE_PREFIX."conversation_replies WHERE cid = {$conversation['id']}");
            while ($reply = $db->fetch_array($query2))
            {
                $used++;
                $viewers = unserialize($conversation['views']);
                if (isset($viewers[$mybb->user['uid']]) and $viewers[$mybb->user['uid']] != "Never")
                {
                    $conversation['icon'] = '<i title="No new messages" class="fa fa-dot-circle-o conversation-icon-viewed"></i>';
                }
                else
                {
                    $conversation['icon'] = '<i title="Containing new messages" class="fa fa-dot-circle-o conversation-icon"></i>';
                }
                $conversation['participants_avatars'] = "";
                foreach ($participants as $participant)
                {
                    if ($participant != $mybb->user['uid'])
                    {
                        $query3 = $db->query("SELECT * FROM ".TABLE_PREFIX."users WHERE uid = {$participant}");
                        while ($user = $db->fetch_array($query3))
                        {
                            if ($user['avatar'] == '')
                            {
                                $user['avatar'] = "images/default_avatar.png";
                            }
                            $conversation['participants_avatars'] .= "<img class='conversation-avatar' src='{$user['avatar']}' title='{$user['username']}' alt='{$user['username']}' />";
                        }
                    }
                }
                require_once MYBB_ROOT."inc/class_parser.php";
                $parser = new postParser; 
                $max_length = 30;
                if (strlen($reply['content']) > $max_length)
                {
                    $offset = ($max_length - 3) - strlen($reply['content']);
                    $reply['content'] = substr($reply['content'], 0, strrpos($reply['content'], ' ', $offset)) . '...';
                }
                $reply['content'] = $parser->parse_smilies($reply['content']);
                if ($reply['uid'] == $mybb->user['uid'])
                {
                    $conversation['last'] = "You: ".$reply['content'];
                }
                else
                {
                    $conversation['last'] = $reply['content'];
                }
                $reply_day = date('F jS', strtotime($reply['date']));
                $reply_time = date('H:i', strtotime($reply['date']));
                $reply['date'] = date('F jS H:i', strtotime($reply['date']));
                $today = date('F jS');
                $yesterday = date('F jS', strtotime('-1 days'));
                if ($reply_day == $today) { $reply['date'] = "Today $reply_time"; }
                if ($reply_day == $yesterday) { $reply['date'] = "Yesterday $reply_time"; }
                $query3 = $db->query("SELECT * FROM ".TABLE_PREFIX."users WHERE uid = {$reply['uid']}");
                while ($user = $db->fetch_array($query3))
                {
                    $reply['author'] = $user['username'] = format_name($user['username'], $user['usergroup'], $user['displaygroup']);
                }
                $conversation['last_message'] = "By: ".$reply['author']."<br><a href='conversation.php?id=".$conversation['id']."' title='Go to last post'>".$reply['date']."</a>";
            }
            $conversations_list .= '<tr>
<td class="trow1" width="10%" align="center">'.$conversation['icon'].'</td>
<td class="trow1" width="35%"><strong><a href="conversation.php?id='.$conversation['id'].'">'.$conversation['title'].'</a></strong><br>'.$conversation['last'].'</td>
<td class="trow1" width="25%">'.$conversation['participants_avatars'].'</td>
<td class="trow1" width="20%">'.$conversation['last_message'].'</td>
<td class="trow1" width="5%" align="left"><input type="checkbox" name="'.$conversation['id'].'" onchange="conversations(this, '.$conversation['id'].');" /></td>
</tr>';
        }
    }
    if (isset($_GET['querydarsider']))
    {
        $query = $db->query("SELECT * FROM ".TABLE_PREFIX."conversation_replies");
        while ($reply = $db->fetch_array($query))
        {
            $query2 = $db->query("UPDATE mybb_conversations SET lastpm = '".$reply['date']."' WHERE id = ".intval($reply['cid']));
        }
        echo "do";
        exit;
    } 
    if ($conversations_list == '')
    {
        $conversations_list = "<tr><td class='trow1' colspan='4'>There are no conversations yet.</td></tr>";
    }
    $result['count'] = 0;
    $query3 = $db->query("SELECT * FROM ".TABLE_PREFIX."conversations WHERE participants LIKE '%{$mybb->user['uid']}%'");
    while ($c = $db->fetch_array($query3))
    {
        $participants = unserialize($c['participants']);
        if (in_array($mybb->user['uid'], $participants))
        {
            $result['count'] = $result['count'] + 1;
        }
    }
    $t = 1;
    $n = $result['count']/10 + 1;
    $total_conversations = $result['count'];
    $pagination = "";
    while ($t < $n)
    {
        if (($t-1) == $p) 
        {
            $pagination .= '<a href="conversations.php?p='.($t-1).'" class="pagination__item pagination__item--current">'.$t.'</a>';
        }
        else
        {
            $pagination .= '<a href="conversations.php?p='.($t-1).'" class="pagination__item">'.$t.'</a>';
        }
        $t = $t + 1;
    }
    $total = $t - 1;

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
    if (isset($_GET['error']) and $_GET['error'] == "storage")
    {
        $inline_errors = '<div class="inline_errors">Error: Maximum storage is reached.</div>';
    }
    if (isset($_GET['error']) and $_GET['error'] == "id")
    {
        $inline_errors = '<div class="inline_errors">Error: Conversation ID doesn\'t exist.</div>';
    }
    eval("\$conversations = \"".$templates->get("conversations_page")."\";");
    output_page($conversations);
}
