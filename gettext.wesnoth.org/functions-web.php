<?php

if (!defined('IN_WESNOTH_LANGSTATS'))
{
	die(1);
}

function ui_self_link($disable_condition, $text, $href)
{
	if ($disable_condition)
	{
		echo '<b>' . $text . '</b>';
	}
	else
	{
		echo '<a href="' . htmlspecialchars($href) . '">' . $text . '</a>';
	}
}

function ui_separator()
{
	echo ' | ';
}
