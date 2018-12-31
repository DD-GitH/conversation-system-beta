<?php

if(!defined("IN_MYBB"))
{
    die("Direct initialization of this file is not allowed.");
}

$plugins->add_hook("private_start", "conversations_redirect");
$plugins->add_hook("global_start", "conversations_notice");

function conversations_info()
{
    return array(
        "name"          => "Conversations system",
        "description"   => "Replace default MP system with conversations system",
        "website"       => "http://www.elegantdesigning.com",
        "author"        => "DarSider",
        "authorsite"    => "http://www.elegantdesigning.com",
        "version"       => "1.0",
        "guid"          => "",
        "codename"      => "conversations",
        "compatibility" => "*"
    );
}

function conversations_install()
{
    conversations_add_tables();
}

function conversations_uninstall()
{
    conversations_remove_tables();
}

function conversations_is_installed()
{
    global $db;
    if($db->table_exists('conversations')){
        return true;
    } 
    else
    {
        return false;
    }
}

function conversations_activate()
{

}

function conversations_desactivate()
{

}

include('conversations/functions.php');