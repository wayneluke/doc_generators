<?php
require_once('./config/config.php');
require_once('./includes/database.php');
require_once('./includes/querydef.php');
require_once('./includes/template.php');
require_once('./includes/functions.php');

if (PHP_SAPI != 'cli') 
{ 
   die ('Not Allowed');
} 

// Misc. Settings. These probably do not need to be changed.
$templateTokens=['~title~','~title_slug~','~date~','~group~','~version~','~content~'];
$contentTokens=['~title~','~image~','~description~','~help~','~additionalinfo~','~varname~','~type~','~defaultvalue~'];
$imageTokens=['~imageurl~','~caption~'];

// Setup Necessary Queries.
$query =[
    'version'   => "select value from {prefix}setting where varname='templateversion'",
    'groups'    => "select distinct(stylevargroup) from {prefix}stylevardfn ORDER BY stylevargroup ASC;",
    'stylevars' => "select p1.text as 'title', p2.text as 'description', s.* from {prefix}stylevardfn AS s
        LEFT JOIN {prefix}phrase AS p1 ON (p1.varname LIKE CONCAT('stylevar_', s.stylevarid, '_name')) 
        LEFT JOIN {prefix}phrase AS p2 ON (p2.varname LIKE CONCAT('stylevar_', s.stylevarid, '_description')) 
        where stylevargroup=? ORDER BY s.stylevargroup ASC", 
    'default_value'  => "select value from {prefix}stylevar where stylevarid=? and styleid=-1",
];

$dbConnect = new Database($dbHost,$dbName,$dbUser,$dbPass,$dbPrefix);

if (!empty($dbConnect)) {
    echo "Database Connection Successful\n\r";
}

$clean = true;
$version = $dbConnect->run_query($query['version']);
$curVersion = $version->fetchColumn();
$now=date('n/d/Y h:ia');

$groups = $dbConnect->run_query($query['groups']);

$itemReplace=[];
$currentItem='';

$outDir = $outDir . $separator . '10.customizing_vbulletin'. $separator .'04.styles'. $separator .'20.stylevars'. $separator .'01.stylevar_reference';
$pageCounter=0;
foreach ($groups as $group) {
    echo $group['stylevargroup'] . "\n\r";
    $stylevars = $dbConnect->run_query($query['stylevars'],[$group['stylevargroup']]);
    $content='';
    foreach ($stylevars as $stylevar) {
        
        $value_list="";
        if (!isset($stylevar['title']) or $stylevar['title'] == null or $stylevar['title'] === '') {
            $stylevar['title'] = $stylevar['stylevarid'];
        }        
        $default = $dbConnect->fetch_query($query['default_value'],[$stylevar['stylevarid']]);
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
                $value_list .= "- " . $key . ": " . $value . "\n";
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
        $currentItem = new Template('stylevarItem.inc');
        $content.=$currentItem->parse($contentTokens,$itemReplace) . "\n";
    }
    $groupDir = $outDir . $separator . ++$pageCounter . '.' . $group['stylevargroup'];
    createDirectory($groupDir);
    $templateReplace=[$group['stylevargroup'], slugify($group['stylevargroup']), $now, $group['stylevargroup'], $curVersion, $content];

    $stylevarPage = new Template('stylevarPage.inc');
    $page=$stylevarPage->parse($templateTokens,$templateReplace);
    file_put_contents($groupDir . $separator . 'article.md', $page);
}

