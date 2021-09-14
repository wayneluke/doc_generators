<?php
// This script writes out markdown files for each setting group. 


// Only allow this to be run from the CLI.
if (PHP_SAPI != 'cli') { die ('Not Allowed');} 

// Get some important files.
require_once('./includes/system.php');
require_once('./includes/database.php');
require_once('./includes/template.php');
require_once('./includes/functions.php');


// Setup System
$sys = new System("./config/settings.ini", __DIR__);
$dbConnect = new Database("./config/settings.ini");
if (!empty($dbConnect)) {
    echo "Database Connection Successful\n\r";
} else {
    die ('unable to connect');
}

$outDir = $sys->outputDirectory . $separator . 'settings' . $separator . 'options';

// Setup Variables for page generation.
$separator=DIRECTORY_SEPARATOR;
$templateTokens=['~title~','~title_slug~','~date~','~group~','~version~','~content~','~weight~'];
$contentTokens=['~title~','~image~','~description~','~help~','~additionalinfo~','~varname~','~type~','~defaultvalue~'];
$imageTokens=['~imageurl~','~caption~'];

// Setup Necessary Queries.
$query =[
    'version' => "select value from setting where varname='templateversion'",
    'groups' => "SELECT sg.*, p.text AS title FROM settinggroup AS sg
        LEFT JOIN phrase AS p ON (p.varname LIKE CONCAT('settinggroup_',sg.grouptitle))    
        WHERE sg.product='vbulletin' ORDER BY sg.displayorder",
    'settings' => "SELECT p.text AS 'title', p2.text AS 'description', s.varname, s.defaultvalue, s.datatype, s.displayorder FROM setting AS s 
        LEFT JOIN settinggroup AS sg ON (s.grouptitle = sg.grouptitle)
        LEFT JOIN phrase AS p ON (p.varname LIKE CONCAT('setting_', s.varname, '_title')) 
        LEFT JOIN phrase AS p2 ON (p2.varname LIKE CONCAT('setting_', s.varname, '_desc')) 
        WHERE s.grouptitle=? ORDER BY s.displayorder",
];



$clean = true;
$version = $dbConnect->run_query($query['version']);
$curVersion = $version->fetchColumn();
$now=date('Y-m-d h:ia');

$groups = $dbConnect->run_query($query['groups']);

$itemReplace=[];
$currentItem='';
$outDir = $sys->outputDirectory . $separator . 'settings' . $separator . 'options';
foreach ($groups as $group) {
    if ($group['displayorder']==0){
        continue;
    }
    echo $group['title'] . "\n\r";
    $settings = $dbConnect->run_query($query['settings'],[$group['grouptitle']]);
    $content='';
    foreach ($settings as $setting) {
        echo "\t". $setting['title'] ."\n\r";
        $itemReplace=[$setting['title'],'',$setting['description'],'','',$setting['varname'],$setting['datatype'],htmlentities($setting['defaultvalue'])];
        $currentItem = new Template('setting');
        $content.=$currentItem->parse($contentTokens,$itemReplace);
    }
    $groupDir = $outDir . $separator . $group['grouptitle'];
    createDirectory($groupDir);
    $templateReplace=[$group['title'], slugify($group['title']), $now, $group['grouptitle'], $curVersion, $content, $group['displayorder']];

    $settingPage = new Template('setting.page');
    $page=$settingPage->parse($templateTokens,$templateReplace);
    file_put_contents($groupDir . $separator . 'index.md', $page);
}

