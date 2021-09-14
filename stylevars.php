<?php
// This script writes out markdown files for each stylvar group. 


// Only allow this to be run from the CLI.
if (PHP_SAPI != 'cli') { die ('Not Allowed'); } 


require_once('./includes/system.php');
require_once('./includes/database.php');
require_once('./includes/querydef.php');
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

$outDir = $sys->outputDirectory . $separator . 'stylevars' . $separator . 'stylevar_reference';

$separator=DIRECTORY_SEPARATOR;
$templateTokens=['~title~','~title_slug~','~date~','~group~','~version~','~content~','~weight~'];
$contentTokens=['~title~','~image~','~description~','~help~','~additionalinfo~','~varname~','~type~','~defaultvalue~'];
$imageTokens=['~imageurl~','~caption~'];

$queries = new QueryDefs();

$stylevarQueries = $queries->getQueries('stylevars');

//--------------------------------------------

$clean = true;
$version = $queries->getVersion($dbConnect);
$now=date('Y-m-d h:ia');

$groups = $dbConnect->run_query($stylevarQueries['groups']);

$itemReplace=[];
$currentItem='';


$pageCounter=10;
foreach ($groups as $group) {
    echo $group['stylevargroup'] . "\n\r";
    $stylevars = $dbConnect->run_query($stylevarQueries['stylevars'],[$group['stylevargroup']]);
    $content='';
    foreach ($stylevars as $stylevar) {
        
        $value_list="";
        if (!isset($stylevar['title']) or $stylevar['title'] == null or $stylevar['title'] === '') {
            $stylevar['title'] = $stylevar['stylevarid'];
        }        
        $default = $dbConnect->fetch_query($stylevarQueries['default_value'],[$stylevar['stylevarid']]);
        $values = unserialize($default['value']);
        foreach ($values as $key => $value) {
            $inherit=0;
            if (strpos($key,'stylevar_') === 0) {
                $key = str_replace('stylevar_','',$key);
            }
            if (strpos($key, 'inherit_')===0) {
                $value_list .= "  ";
            }
            if (!empty($value) || $inherit) {
                $value_list .= "- " . $key . ": " . $value; 
                // add color swatch stuff here.
                $value_list .= "\n";
            }
        }
        $value_list = "\n" . trim($value_list) . "\n";
        echo "\t". $stylevar['title'] ."\n\r";
        $itemReplace=[
            $stylevar['title'],          // title
            '',                          // image
            $stylevar['description'],    // description
            '',                          // help
            '',                          // additional info
            $stylevar['stylevarid'],     // variable name
            $stylevar['datatype'],       // type
            $value_list,                 // default values    
        ];
        $currentItem = new Template('stylevar');
        $content.=$currentItem->parse($contentTokens,$itemReplace) . "\n";
    }
    $groupDir = $outDir . $separator . $group['stylevargroup'];
    createDirectory($groupDir);
    
    $pageCounter +=10;
    if ($group['stylevargroup']==='Global') { 
        $weight = 1; 
    } elseif ($group['stylevargroup']==='GlobalPalette') {
        $weight = 2; 
    } else { 
        $weight = $pageCounter;
    }
    $templateReplace=[$group['stylevargroup'], slugify($group['stylevargroup']), $now, $group['stylevargroup'], $version, $content, $weight];

    $stylevarPage = new Template('stylevar.page');
    $page=$stylevarPage->parse($templateTokens,$templateReplace);
    file_put_contents($groupDir . $separator . 'index.md', $page);

}

