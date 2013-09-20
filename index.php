<?php

include('./config.php');

$solitary = new Solitary(52);

if(isset($_REQUEST['random'])) {
	$solitary->create_random_game();
}

//file_put_contents('demo2.dat', serialize($solitary));
$solitary->TInit = unserialize(file_get_contents('save/2013-09-20 12:07:37.sol'));
$solitary->reset_init_position();

//$solitary->get_solution();
//$solitary->reset_init_position();

//pre($solitary->TBoard);

$board = new TTemplateTBS();
echo $board->render(
	'tpl/board.tpl.php'
	, array(
		'deck' => $solitary->TDeck
		,'discard' => $solitary->TDiscard
		,'aces_hearts' => $solitary->TAces['hearts']
		,'aces_diams' => $solitary->TAces['diams']
		,'aces_clubs' => $solitary->TAces['clubs']
		,'aces_spades' => $solitary->TAces['spades']
		,'board' => $solitary->TBoard
		,'path' => $solitary->bestPath
		,'allcards' => $solitary->TCard
	)
	, array(
		'data' => array(
			'score' => $solitary->bestScore
		)
		,'view' => array(
			'http' => HTTP
		)
	)
);
