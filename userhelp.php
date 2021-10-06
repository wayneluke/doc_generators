<?php
// This script writes out markdown files for each setting section. 


// Only allow this to be run from the CLI.
if (PHP_SAPI != 'cli') { die ('Not Allowed');} 

// Get some important files.
// Note: I may be able to do this with an Autoload function. Who knows?
require_once('./includes/system.php');
require_once('./includes/database.php');
require_once('./includes/querydef.php');
require_once('./includes/template.php');
require_once('./includes/functions.php');

// Setup System
$sys = new System("./config/settings.ini", __DIR__);
$db = new Database("./config/settings.ini");
if (!empty($db)) {
    echo "Database Connection Successful\n\r";
} else {
    die ('unable to connect');
}

//--------------------------------------------

$separator=DIRECTORY_SEPARATOR;
$outDir = $sys->outputDirectory . $separator . 'userhelp';

// Setup Variables for page generation.

$templateTokens=['~title~','~title_slug~','~date~','~version~','~content~','~weight~'];
$contentTokens=['~title~','~image~','~description~','~help~','~additionalinfo~','~varname~','~type~','~defaultvalue~'];
$imageTokens=['~imageurl~','~caption~'];

$queries = new QueryDefs();

$Queries = $queries->getQueries('userhelp');

$clean = true;
$version = $queries->getVersion($db);
$now=date('Y-m-d h:ia');

$sections = $db->run_query($Queries['sections']);

$itemReplace=[];
$currentItem='';

foreach ($sections as $section) {
    echo $section['title'] . "\n\r";
    $pages = $db->run_query($Queries['pages'],[$section['faqname']]);
    $sectionDir = $outDir . $separator . slugify($section['faqname']);
    createDirectory($sectionDir);    
    foreach ($pages as $page) {
        echo "\t". $page['title'] ."\n\r";
        $templateReplace=[$page['title'], slugify($page['title']), $now, $version, $page['text'], $page['displayorder']];

        $userHelpPage = new Template('page');
        $fileOut=$userHelpPage->parse($templateTokens,$templateReplace);
        file_put_contents($sectionDir . $separator . slugify($page['faqname']) . '.md', $fileOut);        
    }
}

