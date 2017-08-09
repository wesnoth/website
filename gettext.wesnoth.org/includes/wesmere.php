<?php
#
# Wesnoth language statistics template for Wesmere
#
# codename Wesmere - Next-gen Wesnoth.org stylesheet
# Copyright (C) 2011 - 2017 by Ignacio R. Morelle <shadowm@wesnoth.org>
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License along
# with this program; if not, see <http://www.gnu.org/licenses/>.
#

if (!defined('IN_WESNOTH_LANGSTATS'))
{
	die(1);
}

function wesmere_emit_header()
{
	?><!DOCTYPE html>

<html class="no-js langstats" lang="en">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width,initial-scale=1" />

	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Montaga%7COpen+Sans:400,400i,700,700i" type="text/css" />
	<link rel="icon" type="image/png" href="https://www.wesnoth.org/wesmere/img/favicon-32.png" sizes="32x32" />
	<link rel="icon" type="image/png" href="https://www.wesnoth.org/wesmere/img/favicon-16.png" sizes="16x16" />
	<link rel="stylesheet" type="text/css" href="https://www.wesnoth.org/wesmere/css/wesmere-1.1.0.css" />
	<link rel="stylesheet" type="text/css" href="https://www.wesnoth.org/wesmere/css/gettext-1.1.0.css" />

	<title>Translation Statistics - The Battle for Wesnoth</title>

	<script src="https://www.wesnoth.org/wesmere/js/modernizr.js"></script>
</head>

<body>

<div id="main">

<div id="nav" role="banner">
<div class="centerbox">

	<div id="logo">
		<a href="https://www.wesnoth.org/" aria-label="Wesnoth logo"></a>
	</div>

	<ul id="navlinks">
		<li><a href="https://www.wesnoth.org/">Home</a></li>
		<li><a href="https://forums.wesnoth.org/viewforum.php?f=62">News</a></li>
		<li><a href="https://wiki.wesnoth.org/Play">Play</a></li>
		<li><a href="https://wiki.wesnoth.org/Create">Create</a></li>
		<li><a href="https://forums.wesnoth.org/">Forums</a></li>
		<li><a href="https://wiki.wesnoth.org/Project">About</a></li>
	</ul>

	<div id="sitesearch" role="search">
		<form method="get" action="https://wiki.wesnoth.org/">
			<input id="searchbox" type="search" name="search" placeholder="Search" title="Search the wiki [Alt+Shift+f]" accesskey="f" tabindex="1" />
			<span id="searchbox-controls">
				<button id="search-go" class="search-button" type="submit" title="Search" tabindex="2">
					<i class="search-icon" aria-hidden="true"></i>
					<span class="sr-label">Search the wiki</span>
				</button>
			</span>
		</form>
	</div>

	<div class="reset"></div>
</div>
</div>

<div id="content"><?php

}

function wesmere_emit_footer()
{

?></div> <!-- end content -->

</div> <!-- end main -->

<div id="footer-sep"></div>

<div id="footer"><div id="footer-content"><div>
	<a href="https://wiki.wesnoth.org/StartingPoints">Site Map</a> &#8226; <a href="http://status.wesnoth.org/">Site Status</a><br />
	Copyright &copy; 2003&ndash;2017 by <a rel="author" href="https://wiki.wesnoth.org/Project">The Battle for Wesnoth Project</a>.<br />
	Site design Copyright &copy; 2017 by Ignacio R. Morelle.
</div></div></div>

</body>
</html><?php

}
