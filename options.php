<?php
// This script writes out markdown files for each setting group. 


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
$outDir = $sys->outputDirectory . $separator . 'settings' . $separator . 'options';

// Setup Variables for page generation.

$templateTokens=['~title~','~title_slug~','~date~','~group~','~version~','~content~','~weight~'];
$contentTokens=['~title~','~image~','~description~','~help~','~additionalinfo~','~varname~','~type~','~defaultvalue~'];
$imageTokens=['~imageurl~','~caption~'];

$queries = new QueryDefs();

$Queries = $queries->getQueries('options');

$clean = true;
$version = $queries->getVersion($db);
$now=date('Y-m-d h:ia');

$groups = $db->run_query($Queries['groups']);

$itemReplace=[];
$currentItem='';

foreach ($groups as $group) {
    if ($group['displayorder']==0){
        continue;
    }
    echo $group['title'] . "\n\r";
    $settings = $db->run_query($Queries['settings'],[$group['grouptitle']]);
    $content='';
    foreach ($settings as $setting) {
        echo "\t". $setting['title'] ."\n\r";
        $itemReplace=[$setting['title'],'',$setting['description'],'','',$setting['varname'],$setting['datatype'],htmlentities($setting['defaultvalue'])];
        $currentItem = new Template('setting');
        $content.=$currentItem->parse($contentTokens,$itemReplace);
    }
    $groupDir = $outDir . $separator . $group['grouptitle'];
    createDirectory($groupDir);
    $templateReplace=[$group['title'], slugify($group['title']), $now, $group['grouptitle'], $version, $content, $group['displayorder']];

    $settingPage = new Template('page');
    $page=$settingPage->parse($templateTokens,$templateReplace);
    file_put_contents($groupDir . $separator . 'index.md', $page);
}

