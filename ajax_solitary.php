<?php

include('./config.php');

switch ($_REQUEST['action']) {
	case 'search':
		search_solution();
		break;
		
	case 'random_game':
		random_game();
		break;
	
	default:
		
		break;
}


function search_solution() {
	$solitary = new Solitary(52);
	
	$TDeck = $_REQUEST['TDeck'];
	foreach ($TDeck as $code) {
		$solitary->TDeck[] = $solitary->TCard[$code];
	}
	
	$TBoard = $_REQUEST['TBoard'];
	foreach ($TBoard as $col => $TCards) {
		foreach ($TCards as $code) {
			$solitary->TBoard[$col][] = $solitary->TCard[$code];
		}
	}
	
	$solitary->get_solution();
	
	$sol = new TTemplateTBS();
	$pathHTML = $sol->render(
		'tpl/solution.tpl.php'
		, array(
			'path' => $solitary->bestPath
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
	
	echo json_encode(array(
		'score' => $solitary->bestScore
		//,'path' => $solitary->bestPath
		,'pathHTML' => $pathHTML
	));
}

function random_game() {
	$solitary = new Solitary(52);
	$solitary->create_random_game();
}
