<?php

function doMenu() {
    $menu = 
    "\033[34m ** Document Generators\033[39m".PHP_EOL.
    "\t1 - Settings & Options".PHP_EOL.
    "\t2 - Style Variables".PHP_EOL.
    "\t3 - Pages".PHP_EOL.
    "\t4 - Site Builder Modules".PHP_EOL.
    "\t5 - User Manual Pages".PHP_EOL.PHP_EOL.
    "\033[34m ** Tools\033[39m".PHP_EOL.
    "\tA - Wikify".PHP_EOL.
    "\tB - Frontmatter".PHP_EOL.
    "\tC - File List".PHP_EOL.
    "\tD - Image List".PHP_EOL.
    "\033[34m ** System\033[39m".PHP_EOL.
    "\tQ - Quit".PHP_EOL.
    "\033[36m-----------------------------------\033[39m".PHP_EOL.
    "\033[36mPlease select an option from above.\033[39m".PHP_EOL;

    system('clear');
    echo $menu;
    echo PHP_EOL.'Selection -> ';
    $choice = trim(fgets(STDIN));
    
    return $choice;
}

//fwrite(STDOUT, CLEAR.HOME);
//echo PHP_OS;
$selection = '';

while (strtolower($selection) !== 'q') {
    
    $selection = doMenu();

    switch (strtolower($selection)) {
        case '1': require_once('./options.php'); break;
        case '2': require_once('./stylevars.php'); break;
        case '3': require_once('./pages.php'); break;
        case '4': require_once('./modules.php'); break;
        case '5': require_once('./userhelp.php'); break;
        case 'q': break;
        default: echo "NYI"; sleep(2);
    }

}