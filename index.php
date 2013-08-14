<?php

ini_set('display_errors', true);
include('class/card.class.php');

/*$solitary = new Solitary();
$solitary->create_random_game();

file_put_contents('demo.dat', serialize($solitary));*/

$solitary = unserialize(file_get_contents('demo.dat'));

include('tpl/board.tpl.php');
