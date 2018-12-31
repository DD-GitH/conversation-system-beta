<?php

function conversations_add_tables() 
{
    global $db, $mybb;
    require_once MYBB_ROOT."/inc/adminfunctions_templates.php";
    $db->write_query("CREATE TABLE IF NOT EXISTS`".TABLE_PREFIX."conversations` (
  `id` int(10) NOT NULL AUTO_INCREMENT PRIMARY KEY, 
  `title` varchar(255) NOT NULL,
  `participants` TEXT NOT NULL,
  `admins` TEXT NOT NULL,
  `views` TEXT NOT NULL,
  `lastpm` TEXT NOT NULL,
  `creator` int(10) NOT NULL
)
  "); 
    
    $db->write_query("CREATE TABLE IF NOT EXISTS`".TABLE_PREFIX."conversation_replies` (
  `id` int(10) NOT NULL AUTO_INCREMENT PRIMARY KEY, 
  `content` TEXT NOT NULL,
  `cid` int(10) NOT NULL, 
  `uid` int(10) NOT NULL,
  `viewedby` TEXT NOT NULL,
  `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP 
)
  "); 
    
    find_replace_templatesets('header', '#'.preg_quote('{$pm_notice}').'#i', '{$conversations_notice}'); 
    
    $template = '<html>

<head>
    <title>{$conversation_title} - {$mybb->settings[\'bbname\']}</title>
    {$headerinclude}
    <style>
        .inline_errors {
            padding: 10px;
			margin-top: 5px;
			color: rgb(191, 24, 24);
        }
        
        .conversation-icon {
            font-size: 20px;
            padding: 15px;
            color: rgba(0, 0, 0, 0.4);
        }
        
        .conversation-icon-viewed {
            font-size: 20px;
            padding: 15px;
        }
        
        .conversation-space {
            background: rgba(255, 255, 255, 0.1);
            text-align: left;
            padding: 5px;
        }
        
        .conversation-space-sub {
            display: inline-block;
            background: red;
            width: 0%;
        }
        
        .conversation-button {
            text-align: center;
            width: 95%;
            padding: 10px !important;
        }
        
        .conversation-avatar {
            border-radius: 100%;
            width: 35px;
            height: 35px;
        }
        
        .scrolltable {
            overflow-x: hidden;
            height: 350px;
        }
        
        .scrolltable table {
            width: 100%;
        }
        
        .message-avatar {
            border-radius: 100%;
            width: 50px;
            height: 50px;
            vertical-align: middle;
            display: table-cell;
        }
        
        .message-infos {
            vertical-align: middle;
            display: table-cell;
            padding: 0px 5px;
        }
        
        .participant-message {
            padding: 10px;
            background: #1e8bc3;
            border-radius: 2px;
            color: white;
        }
        
        .user-message {
            padding: 10px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 2px;
        }
        
        .inline_ok {
            padding: 10px;
			margin-top: 5px;
			color: rgb(24, 191, 114);
        }

    </style>
</head>

<body>
    {$header}
    <div id="sent-modal" style="display:none;width: inherit;padding:40px 60px;font-size:20px;">
        <center>Message sent!</center>
    </div>
    <br>
    <table width="100%" border="0" align="center">

        <td valign="top" width="25%">
			<table width="100%" border="0" align="center">
                <tr>
                    <td valign="top" width="25%">
                        <table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
                            <tr>
                                <th class="thead">Last Seen</th>
                            </tr>
                            <tr>
                                <td class="trow1">
                                    {$views}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
			<table width="100%" border="0" align="center">
                <tr>
                    <td valign="top" width="25%">
                        <table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
                            <tr>
                                <th class="thead">Invite Participants</th>
                            </tr>
                            <tr id="admin">
                                <td style="text-align: center;" class="trow1">
                                    <input class="textbox" style="padding:12px;" placeholder="Separated by comma..." type="text" id="users1" /> <button id="add" class="button">Add</button>
                                    <span id="result1"></span>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            <table width="100%" border="0" align="center">
                <tr>
                    <td valign="top" width="25%">
                        <table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
                            <tr>
                                <th class="thead">Kick Participants</th>
                            </tr>
                            <tr id="admin">
                                <td style="text-align: center;" class="trow1">
                                    <input class="textbox" style="padding:12px;" placeholder="Separated by comma..." type="text" id="users2" /> <button id="kick" class="button">Kick</button>
                                    <span id="result2"></span>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            <table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
                <tr>
                    <td class="thead"><strong>Menu</strong></td>
                </tr>
                <tr>
                    <td class="trow1" align="center"><input type="button" onclick="window.location = \'conversations.php\';" class="button conversation-button" value=\'Inbox\' /></td>
                </tr>
                <tr>
                    <td class="trow1" align="center"><input type="button" onclick="window.location = \'newconversation.php\';" class="button conversation-button" value=\'New Conversation\' /></td>
                </tr>
                <tr>
                    <td class="trow1">Total Conversations <span style=\'float:right;\'>{$total_conversations}</span></td>
                </tr>
                <tr>
                    <td class="trow1">Total Storage <span style=\'float:right;\'>{$total_storage}</span></td>
                </tr>
                <tr>
                    <td class="trow1" align="center">
                        <div class="conversation-space">
                            <div class="conversation-space-sub" style="width:{$used_percent};">{$used_percent}</div>
                        </div>Space Used</td>
                </tr>
            </table>
        </td>

        <td valign="top">
            {$inline_errors}
            <table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
                <thead>
                    <tr>
                        <td class="thead" colspan="4"><strong><a href="{$mybb->settings[\'bburl\']}/conversation.php?id={$id}">{$conversation_title}</a></strong></td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="trow1">
                            <div class="scrolltable">
                                {$messages_list}
                            </div>
                            <small style="margin:10px;margin-top:15px">{$viewedby}</small>
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td style="text-align: center;" class="trow1">
                            <textarea cols="90" rows="2" id="message"></textarea><button id="send" style="padding: 21px; vertical-align: top; margin: 3px;"><i class="fa fa-paper-plane"></i></button>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </td>
        </tr>
    </table>
    {$footer}
    <script>
        $(\'#send\').click(function(event) {
            conversation_message(document.getElementById(\'message\').value, $id);
            document.getElementById(\'message\').value = "";
        });

        $(\'#add\').click(function(event) {
            conversation_add(document.getElementById(\'users1\').value, $id);
            document.getElementById(\'users1\').value = "";
        });


        $(\'#kick\').click(function(event) {
            conversation_kick(document.getElementById(\'users2\').value, $id);
            document.getElementById(\'users2\').value = "";
        });

        function conversation_message(message, conversation_id) {
            if (message != "") {
                jQuery.ajax({
                    type: \'GET\',
                    url: \'conversation.php\',
                    data: \'id=\' + conversation_id + \'&action=send&message=\' + message,
                    cache: false,
                    success: function(result) {
                        var modalSelector = "#sent-modal";
                        $(modalSelector).modal({
                            fadeDuration: 250,
                            keepelement: true
                        });
                    },
                });
            }
        }

        function conversation_delete(message) {
            jQuery.ajax({
                type: \'GET\',
                url: \'conversation.php\',
                data: \'id={$id}&action=delete&message=\' + message,
                cache: false,
                success: function(result) {
                    document.location.reload();
                },
            });
        }

        function conversation_add(users, conversation_id) {
            if (users != "") {
                jQuery.ajax({
                    type: \'GET\',
                    url: \'conversation.php\',
                    data: \'id=\' + conversation_id + \'&action=add&users=\' + users,
                    cache: false,
                    success: function(result) {
                        document.getElementById(\'result1\').innerHTML = result;
                    },
                });
            }
        }

        function conversation_kick(users, conversation_id) {
            if (users != "") {
                jQuery.ajax({
                    type: \'GET\',
                    url: \'conversation.php\',
                    data: \'id=\' + conversation_id + \'&action=kick&users=\' + users,
                    cache: false,
                    success: function(result) {
                        document.getElementById(\'result2\').innerHTML = result;
                    },
                });
            }
        }
        
        function conversation_refresh()
        {
            jQuery.ajax({
                    type: \'GET\',
                    url: \'conversation.php\',
                    data: \'id={$id}&action=refresh\',
                    cache: false,
                    success: function(result) {
                        document.getElementsByClassName(\'scrolltable\')[0].innerHTML = result;
                    },
            });

            var scrollbar = $(".scrolltable");
            scrollbar.scrollTop(scrollbar.prop("scrollHeight"));
        }

        setInterval(function(){
            conversation_refresh();
        }, 1000);


		{$admin_functions}


    </script>
</body>

</html>';
    $insert_array = array(
        'title' => 'conversation_page',
        'template' => $db->escape_string($template),
        'sid' => '-1',
        'version' => '',
        'dateline' => time()
    );
    $db->insert_query('templates', $insert_array);
    
    $template = '<html>
<head>
<title>Conversations - {$mybb->settings[\'bbname\']}</title>
{$headerinclude}
<style>
.inline_errors
{
	background: rgb(191, 24, 24); padding: 20px; margin: 5px; color: white;
}
.conversation-icon
{
	font-size: 20px; padding: 15px;
}
.conversation-icon-viewed
{
	font-size: 20px; padding: 15px; color: rgba(0, 0, 0, 0.2);
}
.conversation-space
{
	background: rgba(255, 255, 255, 0.1); text-align: left;padding: 5px;
}
.conversation-space-sub
{
	display:inline-block;background:red;width: 0%;
}
.conversation-button
{
	text-align: center; width:95%;padding:10px !important;
}
.conversation-avatar
{
	border-radius:100%; width:35px; height:35px;
}
</style>
</head>
<body>
<div id="ok-modal" style="display:none;width: inherit;padding:40px 60px;font-size:20px;">
	<center>Success! <a href=\'conversation.php\'>Refresh</a></center>
</div>
<div id="error-modal" style="display:none;width: inherit;padding:40px 60px;font-size:20px;">
    <center>Error: No permission</center>
</div>
{$header}<br>
<table width="100%" border="0" align="center">
<tr>
<td valign="top" width="25%">
<form method="post" action="?action=search">
<input style="width:91%;padding: 12px;margin-bottom: 2px;" name="query" class="textbox" placeholder="Search..." type="text"><br>
</form>
<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
<tr>
<td class="thead"><strong>Menu</strong></td>
</tr>
<tr>
<td class="trow1" align="center"><input type="button" onclick="window.location = \'conversations.php\';" class="button conversation-button" value=\'Inbox\' /></td>
</tr>
<tr>
<td class="trow1" align="center"><input type="button" onclick="window.location = \'newconversation.php\';" class="button conversation-button" value=\'New Conversation\' /></td>
</tr>
<tr>
<td class="trow1">Total Conversations <span style=\'float:right;\'>{$total_conversations}</span></td>	
</tr>
<tr>
<td class="trow1">Total Storage <span style=\'float:right;\'>{$total_storage}</span></td>	
</tr>
<tr>
<td class="trow1" align="center"><div class="conversation-space"><div class="conversation-space-sub" style="width:{$used_percent};">{$used_percent}</div></div>Space Used</td>	
</tr>
</table>
</td>
<td valign="top">
{$inline_errors}
<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
<tr>
<td class="thead" colspan="5"><strong><a href="{$mybb->settings[\'bburl\']}/conversations.php">Conversations</a></strong></td>
</tr>
<tr>
	<td class="tcat"></td>
	<td class="tcat">Topic</td>
	<td class="tcat">Participants</td>
	<td class="tcat">Last Message</td>
	<td class="tcat"></td>
</tr>
{$conversations_list}
</table>
</td>
</tr>
</table>
	
<div class="float_right" style="text-align: right;">	
<span class="smalltext"><strong>Actions:</strong></span>
<select  id="conversation_options">
<option value="markasread">Mark as read</option>
<option value="markasunread">Mark as unread</option>
<option value="delete_conversation">Delete</option>
</select>
<input onclick="conversation_options();" id="conversation_options" class="button" value="Go" type="submit">
</div>
<script>
	var convs = [];
	function conversations(checkbox, id) {
    if(checkbox.checked == true){
        convs.push(id);
    }else{
        var index = convs.indexOf(id);
		convs.splice(index, 1);
   }
}
	
	function conversation_options()
	{
		var option = document.getElementById("conversation_options").value;
		convs.forEach(function(conv) {
			jQuery.ajax({
                    type: \'GET\',
                    url: \'conversation.php\',
                    data: \'id=\' + conv + \'&action=\'+ option,
                    cache: false,
                    success: function(result) {
                        if (result == \'ok\')
						{
							var modalSelector = "#ok-modal";
							$(modalSelector).modal({
								fadeDuration: 250,
								keepelement: true
							});
						}
						else 
						{
							var modalSelector = "#error-modal";
							document.getElementById(\'error-modal\').innerHTML = result;
							$(modalSelector).modal({
								fadeDuration: 250,
								keepelement: true
							});
						}
                    },
                });
		});
	}
</script>
{$footer}
</body>
</html>';
    $insert_array = array(
        'title' => 'conversations_page',
        'template' => $db->escape_string($template),
        'sid' => '-1',
        'version' => '',
        'dateline' => time()
    );
    $db->insert_query('templates', $insert_array);
    
    $template = '<div class="pm_alert" id="pm_notice">
	<div>You have <a href="conversations.php"><strong>{$conversations_count}</strong></a> unread conversation messages</div>
</div>';
    $insert_array = array(
        'title' => 'conversations_notify',
        'template' => $db->escape_string($template),
        'sid' => '-1',
        'version' => '',
        'dateline' => time()
    );
    $db->insert_query('templates', $insert_array);
    
    $template = '<html>
<head>
<title>Conversations - {$mybb->settings[\'bbname\']}</title>
	<script type="text/javascript" src="{$mybb->asset_url}/jscripts/usercp.js?ver=1808"></script>
{$headerinclude}
<style>
.inline_errors
{
	background: rgb(191, 24, 24); padding: 20px; margin: 5px; color: white;
}
.conversation-icon
{
	font-size: 20px; padding: 15px; color: rgba(0, 0, 0, 0.4);
}
.conversation-icon-viewed
{
	font-size: 20px; padding: 15px;
}
.conversation-space
{
	background: rgba(255, 255, 255, 0.1); text-align: left;padding: 5px;
}
.conversation-space-sub
{
	display:inline-block;background:red;width: 0%;
}
.conversation-button
{
	text-align: center; width:90%;padding:10px !important;
}
.conversation-avatar
{
	border-radius:100%; width:35px; height:35px;
}
</style>
</head>
<body>
{$header}<br>
<table width="100%" border="0" align="center">
<tr>
<td valign="top" width="25%">
<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
<tr>
<td class="thead"><strong>Menu</strong></td>
</tr>
<tr>
<td class="trow1" align="center"><input type="button" onclick="window.location = \'conversations.php\';" class="button conversation-button" value=\'Inbox\' /></td>
</tr>
<tr>
<td class="trow1" align="center"><input type="button" onclick="window.location = \'newconversation.php\';" class="button conversation-button" value=\'New Conversation\' /></td>
</tr>
<tr>
<td class="trow1">Total Conversations <span style=\'float:right;\'>{$total_conversations}</span></td>	
</tr>
<tr>
<td class="trow1">Total Storage <span style=\'float:right;\'>{$total_storage}</span></td>	
</tr>
<tr>
<td class="trow1" align="center"><div class="conversation-space"><div class="conversation-space-sub" style="width:{$used_percent};">{$used_percent}</div></div>Space Used</td>
</tr>
</table>
</td>
<td valign="top">
{$inline_errors}
<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
<tr>
<td class="thead" colspan="4"><strong><a href="{$mybb->settings[\'bburl\']}/newconversation.php">Start a conversaion</a></strong></td>
</tr>
<form method="post" action="newconversation.php">
<tr>
	<td>Title:</td><td colspan="3"><input name="title" class="textbox" type="text" /></td>
</tr>
<tr>
	<td>With :<br><small>(Users separated by comma)</small></td><td colspan="3"><input id="to1" name="participants" value="{$usernames_box}" class="textbox" type="text" />  </td>
</tr>
<tr>
	<td>Admins<br><small>(Users separated by comma)</small></td><td colspan="3"><input id="to2" name="admins" value="{$usernames_box}" class="textbox" type="text" /></td>
</tr>
<tr>
	<td>Message:</td><td colspan="3"><textarea id="message" name="message" rows="10" cols="60"></textarea>{$codebuttons}</td>
</tr>
<tr>
	<td colspan="4" align="center";><input class="button" type="submit" value="Submit" /></td>
</tr>
</form>
</table>
</td>
</tr>
</table>
	
{$footer}
<link rel="stylesheet" href="https://community.mybb.com/jscripts/select2/select2.css?ver=1807">
	<script type="text/javascript" src="https://community.mybb.com/jscripts/select2/select2.min.js?ver=1806"></script>
<script type="text/javascript">
<!--
if(use_xmlhttprequest == "1")
{
	MyBB.select2();
	$("#to1").select2({
		placeholder: "Search for a user",
		minimumInputLength: 2,
		maximumSelectionSize: 5,
		multiple: true,
		ajax: { // instead of writing the function to execute the request we use Select2\'s convenient helper
			url: "xmlhttp.php?action=get_users",
			dataType: \'json\',
			data: function (term, page) {
				return {
					query: term, // search term
				};
			},
			results: function (data, page) { // parse the results into the format expected by Select2.
				// since we are using custom formatting functions we do not need to alter remote JSON data
				return {results: data};
			}
		},
		initSelection: function(element, callback) {
			var query = $(element).val();
			if (query !== "") {
				var newqueries = [];
				exp_queries = query.split(",");
				$.each(exp_queries, function(index, value ){
					if(value.replace(/\s/g, \'\') != "")
					{
						var newquery = {
							id: value.replace(/,\s?/g, ", "),
							text: value.replace(/,\s?/g, ", ")
						};
						newqueries.push(newquery);
					}
				});
				callback(newqueries);
			}
		}
	});

  $("#to2").select2({
		placeholder: "Search for a user",
		minimumInputLength: 2,
		maximumSelectionSize: 5,
		multiple: true,
		ajax: { // instead of writing the function to execute the request we use Select2\'s convenient helper
			url: "xmlhttp.php?action=get_users",
			dataType: \'json\',
			data: function (term, page) {
				return {
					query: term, // search term
				};
			},
			results: function (data, page) { // parse the results into the format expected by Select2.
				// since we are using custom formatting functions we do not need to alter remote JSON data
				return {results: data};
			}
		},
		initSelection: function(element, callback) {
			var query = $(element).val();
			if (query !== "") {
				var newqueries = [];
				exp_queries = query.split(",");
				$.each(exp_queries, function(index, value ){
					if(value.replace(/\s/g, \'\') != "")
					{
						var newquery = {
							id: value.replace(/,\s?/g, ", "),
							text: value.replace(/,\s?/g, ", ")
						};
						newqueries.push(newquery);
					}
				});
				callback(newqueries);
			}
		}
	});
}
// -->
</script>
</body>
</html>';
    $insert_array = array(
        'title' => 'newconversation_page',
        'template' => $db->escape_string($template),
        'sid' => '-1',
        'version' => '',
        'dateline' => time()
    );
    $db->insert_query('templates', $insert_array);
    
    $template = '<table>
<tr>
	<td style="width:75%">
		<div  style="max-width: 600px;word-wrap: break-word;" class="user-message">{$reply[\'content\']}</div>
	</td>
	<td style="display:table;padding:15px;">
		  <img src="{$user[\'avatar\']}" class="message-avatar">
		  <span class="message-infos"><a href="member.php?action=profile?id={$user[\'uid\']}">{$user[\'username\']}</a><br>{$reply[\'date\']} {$message_delete}</span>
	</td>
</tr>
</table>';
    $insert_array = array(
        'title' => 'conversations_message_user',
        'template' => $db->escape_string($template),
        'sid' => '-1',
        'version' => '',
        'dateline' => time()
    );
    $db->insert_query('templates', $insert_array);
    
    $template = '<table>
<tr>
	<td style="display: table;">
		  <img src="{$user[\'avatar\']}" class="message-avatar" alt="">
		  <span class="message-infos"><a href="member.php?action=profile&uid={$user[\'uid\']}">{$user[\'username\']}</a><br>{$reply[\'date\']} {$message_delete}</span>
	</td>
	<td style="width:75%;">
		<div class="participant-message" style="max-width: 600px;word-wrap: break-word;">{$reply[\'content\']}</div>
	</td>
</tr>
</table>';
    $insert_array = array(
        'title' => 'conversations_message_participants',
        'template' => $db->escape_string($template),
        'sid' => '-1',
        'version' => '',
        'dateline' => time()
    );
    $db->insert_query('templates', $insert_array);
}

function conversations_remove_tables()
{
    global $db, $mybb;
    require_once MYBB_ROOT."/inc/adminfunctions_templates.php";
    $query1 = "DROP TABLE `".TABLE_PREFIX."conversations`"; 
    $db->query($query1); 
    
    $query2 = "DROP TABLE `".TABLE_PREFIX."conversation_replies`";
    $db->query($query2);
    
    find_replace_templatesets('header', '#'.preg_quote('{$conversations_notice}').'#i', '{$pm_notice}');
    $db->delete_query("templates", "title = 'conversations_page'");
    $db->delete_query("templates", "title = 'conversation_page'");
    $db->delete_query("templates", "title = 'conversations_notify'");
    $db->delete_query("templates", "title = 'newconversation_page'");
}

function conversations_redirect()
{
    if (isset($_GET['action']) and $_GET['action'] == "send" and isset($_GET['uid']))
    {
        header("Location: newconversation.php?uid={$_GET['uid']}");
    }
    else
    {
        header("Location: conversations.php");
    }
}

function conversations_notice()
{
    global $conversations_notice, $conversations_count, $mybb, $db, $templates;
    require_once MYBB_ROOT."/inc/adminfunctions_templates.php";
    $conversations_count = 0;
    $unviewed = 0;
    $query1 = $db->query("SELECT * FROM ".TABLE_PREFIX."conversations");
    while ($conversation = $db->fetch_array($query1))
    {
        $participants = unserialize($conversation['participants']);
        if (in_array($mybb->user['uid'], $participants))
        {
            $viewers = unserialize($conversation['views']);
            if ($viewers[$mybb->user['uid']] == "Never")
            {
                $unviewed = 1;
            }
            
            if ($unviewed != 0)
            {
                $conversations_count++;
                $unviewed = 0;
            }
        }
    }
    if ($conversations_count != 0)
    {
        eval("\$conversations_notice = \"".$templates->get("conversations_notify")."\";");
    }
}

?>
