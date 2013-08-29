<?php

include('./config.php');

$step = (!empty($_REQUEST['step']) ? $_REQUEST['step'] : 0);

$solitary = new Solitary(52);
$solitary->create_random_game();

//file_put_contents('demo2.dat', serialize($solitary));
$solitary = unserialize(file_get_contents('demo.dat'));
$solitary->nbMoveMax = $step;

$solitary->get_solution();
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
		,'path' => array(array('path' => $solitary->bestPath, 'score' => $solitary->bestScore))
		,'path' => $solitary->TPath
		,'allcards' => $solitary->TCard
	)
	, array(
		'data' => array(
			'score' => $solitary->bestScore
			,'step' => $step + 1
		)
		,'view' => array(
			'http' => HTTP
		)
	)
);

//include('tpl/board.tpl.php');
