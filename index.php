<?php



include('./config.php');

$solitary = new Solitary(52);
$solitary->create_random_game();

//file_put_contents('demo2.dat', serialize($solitary));
$solitary = unserialize(file_get_contents('demo2.dat'));

$chemin = array();
$solitary->get_solution($chemin);

/*echo '<pre>';
print_r($chemin);
echo '</pre>';*/

include('tpl/board.tpl.php');
