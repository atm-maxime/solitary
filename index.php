<?php

include('./config.php');

$solitary = new Solitary(52);

if(isset($_REQUEST['random'])) {
	$solitary->create_random_game();
}

//file_put_contents('demo2.dat', serialize($solitary));
//$solitary = unserialize(file_get_contents('demo.dat'));

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
