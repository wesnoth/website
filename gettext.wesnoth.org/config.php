<?php

////////////////////////////////////////////////////////////////////////////
//
// Description: Configuration file for GUI statistics grabbing program
//
////////////////////////////////////////////////////////////////////////////

error_reporting(E_ALL);

// Path to gettext's msgfmt.
$msgfmt = '/usr/bin/msgfmt';

// Name of the current stable branch (dir name of the Git clone).
$branch = '1.12';

// Name of the current stable branch for WesCamp.
$wescamp_version_dev = '1.12';

// Name of the current development branch for WesCamp.
$wescamp_version_branch = '1.12';

// Prefix containing a checkout of Wesnoth's development branch.
$master_basedir = '/usr/src/wesnoth/master/';

// Prefix containing a checkout of Wesnoth's stable branch.
$branch_basedir = '/usr/src/wesnoth/' . $branch . '/';

// Prefix containing checkouts of WesCamp development branch repositories.
$addons_dev_basedir = '/home/wesnoth/wescamp-i18n/branch-' . $wescamp_version_dev . '/';

// Prefix containing checkouts of WesCamp stable branch repositories.
$addons_branch_basedir = '/home/wesnoth/wescamp-i18n/branch-' . $wescamp_version_branch . '/';

// Mainline core textdomains (excludes campaigns, unit tests, and ancillary
// external content like man pages and such).
$core_textdomains = [
	'wesnoth',
	'wesnoth-lib',
	'wesnoth-editor',
	'wesnoth-help',
	'wesnoth-ai',
	'wesnoth-units',
	'wesnoth-multiplayer',
	'wesnoth-tutorial',
];

// Additional mainline textdomains
$mainline_textdomains = array_merge($core_textdomains, [
	'wesnoth-test',
	'wesnoth-anl', // FIXME: why does this count as non-core?
	'wesnoth-manpages',
	'wesnoth-manual',
	// Campaigns
	'wesnoth-aoi',
	'wesnoth-did',
	'wesnoth-dm',
	'wesnoth-dw',
	'wesnoth-ei',
	'wesnoth-httt',
	'wesnoth-l',
	'wesnoth-low',
	'wesnoth-nr',
	'wesnoth-sof',
	'wesnoth-sota',
	'wesnoth-sotbe',
	'wesnoth-tb',
	'wesnoth-thot',
	'wesnoth-trow',
	'wesnoth-tsg',
	'wesnoth-utbs',
]);

// URL prefix used in links to the contents of individual mainline catalog files.
$mainline_file_url_prefix = 'https://raw.github.com/wesnoth/wesnoth';

// Skip processing statistics for catalogs pertaining to these locales.
$ignore_langs = [
	'sr@ijekavianlatin',
	'sr@latin',
	'sr@ijekavian',
];

/****************************************************************************
 *                         Configuration ends here                          *
 ****************************************************************************/

//
// Get unofficial packages
//

function enumerate_addon_packages($base_dir)
{
	$packs_ary = [];

	if ($dir = opendir($base_dir))
	{
		// PHP manual says readdir returns false on failure and that
		// false-evaluating non-booleans may be returned on successful execution.
		// Testing shows that readdir may return NULL on failure, causing infinite loops.
		// For our purposes, an empty string is equivalent to a failure anyway.

		while (($file = readdir($dir)) != false)
		{
			if ($file[0] != '.')
			{
				$packs_ary[] = $file;
			}
		}

		closedir($dir);
		sort($packs_ary);
	}

	return $packs_ary;
}

$addon_packages_dev = enumerate_addon_packages($addons_dev_basedir);
$addon_packages_branch = enumerate_addon_packages($addons_branch_basedir);
