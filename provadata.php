<?php


    $date1= date_create('2013-08-14 18:49:58.606');
    $date2= date_create('2013-08-14 18:49:58.606');
  $diff = strtotime($date2->format('Y-m-d H:i:s.u'))-strtotime($date1->format('Y-m-d H:i:s.u'));
  $micro1 = $date1->format("u");
  $micro2 = $date2->format("u");
  $diffmicro = $micro2 - $micro1;
  list($sec,$micro) = array_pad(explode('.',((($diff) * 1000000) + $diffmicro )/1000000),2,'000');
  $difference = $sec . "." . str_pad($micro,3,'0');
  echo ($difference*1)+2;
