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
	
	$solitary->create_perso_game($_REQUEST['TDeck'], $_REQUEST['TBoard']);
	
	// Sauvegarde pour tests
	file_put_contents('save/'.date('Y-m-d H:i:s').'.sol', serialize($solitary->TInit));
	
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
