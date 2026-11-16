<?php
declare(strict_types=1);
require __DIR__.'/../src/search.php';
$cases=[['cyberpunk 2078','Cyberpunk 2077 Ultimate Edition',true],['cyberpuk','Cyberpunk 2077',true],['baldurs gate','Baldur’s Gate 3',true],['unrelatedword','Stardew Valley',false]];
foreach($cases as [$query,$title,$expected]){$matched=fuzzyScore($query,$title)>=65;if($matched!==$expected){fwrite(STDERR,"FAIL: $query vs $title\n");exit(1);}echo 'PASS: '.$query.' -> '.$title.PHP_EOL;}
