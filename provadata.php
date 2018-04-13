<?php

$datainizio= '2018/03/14 09:00:00';
$datafine= '2018/09/20 18:00:00';

$unixTimestamp = 1521626182780;
$dt = DateTime::createFromFormat("U.u", $unixTimestamp/1000);
echo $dt->format('Y-m-d H:i:s.u');

