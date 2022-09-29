<?php

// Sample config file for creating checksum config.
// Run  php vB5Checksum.phar --help  for sample usage.


//    ****** REQUIRED ******

$forumroot = '/volumes/secondary/sites/vbulletin/docs';
$productid = 'docgenerators';
/*
	Contents of these directories will be added to the checksum file.
	These paths must be relative to $forumroot above and must be
	inside $forumroot.

	Do not use directory traversals (/../) in these paths as they may
	not work as expected and specified files outside of $forumroot
	will not be scanned by the diagnostics tool even if listed in the
	manifest.

	These paths may be directories or files.
 */
$scanpaths = [
	# directories
	'generators',

	# single files

	# don't do this. It may resolve & add entries to the manifest, but will NOT be scanned
	# '../externalfolder',
];


//    ****** OPTIONAL ******

/*
If any of your custom files have a version header like "vBulletin 5.6.0"
you must specify that version here. If missing, the diagnostic tool may
show erroneous warnings like
"File version mismatch: found 5.6.0, expected "
If your files do not have a version header, you can skip this.
 */
//$version = '5.7.0';