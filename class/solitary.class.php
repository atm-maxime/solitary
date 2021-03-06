<?php

include('card.class.php');

class Solitary extends CardGame {
	var $TDeck = array();
	var $TDiscard = array();
	var $TAces = array();
	var $TBoard = array();
	
	var $acesLevel = 1;
	var $round = 1;
	var $blocked = false;
	var $finished = false;
	var $currentScore = 0;
	var $currentPath = array();
	
	var $nbMoveMax = 200;
	
	var $bestScore = 0;
	var $bestPath = array();
	var $TPath = array();
	var $TInit = array();
	
	function __construct($nbCartes=52) {
		parent::__construct($nbCartes);
		$this->init();
		
		foreach($this->TSuit as $suit) {
			$this->TAces[$suit] = array();
		}
		
		for ($i=0; $i < 7; $i++) { 
			$this->TBoard[$i] = array();
		}
	}
	
	public function create_random_game() {
		shuffle($this->TCard);
		$i = 0;
		foreach($this->TCard as $code => $card) {
			if(count($this->TDeck) < 24) { // Fill the deck with the 24 first cards
				$this->TDeck[] = $card;
			} else { // Fill the board with the others
				if(empty($this->TBoard[$i])) {
					$this->TBoard[$i] = array();
				}
				
				$this->TBoard[$i][] = $card;
				
				if(count($this->TBoard[$i]) > $i) {
					$iLast = count($this->TBoard[$i]) - 1;
					$this->TBoard[$i][$iLast]->display = true;
					$i++;
				}
			}
			
			$this->TDeck[count($this->TDeck) - 1]->display = true;
			
			unset($this->TCard[$code]);
		}
		
		$this->save_init_position();
	}
	
	public function create_perso_game($TDeck, $TBoard) {
		foreach ($TDeck as $code) {
			$this->TDeck[] = $this->TCard[$code];
		}
		
		$this->TDeck[count($this->TDeck) - 1]->display = true;
		
		foreach ($TBoard as $col => $TCards) {
			foreach ($TCards as $code) {
				$this->TBoard[$col][] = $this->TCard[$code];
			}
			
			$iLast = count($this->TBoard[$col]) - 1;
			$this->TBoard[$col][$iLast]->display = true;
		}
		
		$this->save_init_position();
	}
	
	private function save_init_position() {
		$this->TInit = array(
			'TDeck' => $this->TDeck
			,'TDiscard' => $this->TDiscard
			,'TAces' => $this->TAces
			,'TBoard' => $this->TBoard
		);
	}
	
	public function reset_init_position() {
		foreach($this->TInit as $area => $TCards) {
			$this->{$area} = $TCards;
		}
	}
	
	/**
	 * TODO : détecter un blocage et dans ce cas, permettre de déplacer des groupes de carte sans partir de la + haute
	 */
	public function get_solution2() {
		if(count($this->currentPath) >= $this->nbMoveMax || $this->is_game_finished() || $this->blocked == true) { // Blockage à N coups pour éviter la boucle infinie
			$this->save_current_solution();
			$this->finished = true;
		} else {
			$this->finished = false;
		}
		
		while(!$this->finished && !$this->blocked) {
			if($this->is_game_finished()) {
				$this->finished = true;
				break;
			}
			
			$move = $this->get_next_move();
			
			if($move === false) {
				$this->blocked = true;
			} else {
				$this->blocked = false;
				if(empty($move[0])) $move = array($move);
				
				foreach($move as $mv) {
					$this->do_move($mv);
					$this->calculate_score($mv);
					$this->currentPath[] = $mv;
					
					// Appel récursif pour les différents mouvements possibles
					$this->get_solution();
					
					array_pop($this->currentPath);
					
					$this->do_move($mv, 'reverse');
					$this->calculate_score($mv, 'remove');
				}
			}
		}
	}
	
	public function get_solution() {
		$move = $this->get_next_move();
		
		if($move === false) {
			$this->save_current_solution();
		} else {
			if(empty($move[0])) $move = array($move);
			
			foreach($move as $mv) {
				$this->do_move($mv);
				$this->calculate_score($mv);
				$this->currentPath[] = $mv;
				
				// Appel récursif pour les différents mouvements possibles
				$this->get_solution();
				
				array_pop($this->currentPath);
				
				$this->do_move($mv, 'reverse');
				$this->calculate_score($mv, 'remove');
			}
		}
	}
	
	private function do_move(&$move, $mode='straight') {
		if($mode == 'reverse') { // Inversion from et to
			$tmp = &$move['from'];
			$move['from'] = &$move['to'];
			$move['to'] = &$tmp;
		}
		
		switch ($move['action']) {
			case 'up':
			case 'dn':
			case 'tn':
				$card = array_pop($move['from']);
				$move['to'][] = $card;
				$move['card'] = &$card;
				if($move['action'] == 'up') $this->check_aces_level();
				break;
				
			case 'mv':
			case 'mt':
				$TCards = array_splice($move['from'], $move['nb'] * - 1);
				$move['to'] = array_merge($move['to'], $TCards);
				$move['card'] = &$TCards[0];
				
				break;
			
			
			/*	$card = array_pop($move['from']);
				$move['to'][] = $card;
				$move['card'] = &$card;
				break;*/
				
			case 'rs':
				$move['to'] = array_reverse($move['from']);
				$move['from'] = array();
				$move['card'] = &$move['to'][0];
				$this->round++;
				break;
			
			default:
				
				break;
		}
		
		$this->set_card_visibility($move, $mode);
		
		//echo $move['action'].' '.$move['card'].'<br />';
	}
	
	private function calculate_score(&$move, $mode='add') {
		$score = 0;
		
		switch ($move['action']) {
			case 'up':
				$cardScore = ($move['card']->rank < 10) ? ($move['card']->rank + 1) : 10;
				$cardScore *= 10;
				$score = 110 - $cardScore;
				
				break;
			case 'dn': $score = 20;
				break;
			case 'mv':
			case 'mt':
				break;
			
			case 'tn':
				
				break;
			case 'rs': $score = -200;
				break;
			
			default:
				break;
		}
		
		if($mode == 'add') $this->currentScore += $score;
		else $this->currentScore -= $score;
	}
	
	private function set_card_visibility(&$move, $mode) {
		// La carte suivante de l'emplacement d'origine est maintenant visible
		$iLastFrom = count($move['from']) - 1;
		if($iLastFrom >= 0) {
			$move['from'][$iLastFrom]->display = true;
			$this->currentScore += 20;
		}
		
		// La carte parente de l'emplacement de destination n'est plus visible si mouvement inversé
		$iLastTo = count($move['to']) - 2;
		if($iLastTo >= 0 && $mode == 'reverse') {
			$move['to'][$iLastTo]->display = false;
			$this->currentScore -= 20;
		}
	}
	
	private function get_next_move() {
		// Test si une carte peut monter
		$move = $this->can_put_a_card_up();
		if($move !== false) return $move;
		
		// Test si une carte peut être déplacée
		$move = $this->can_move_cards();
		//if($move !== false) return $move;
		
		// Test si la carte du deck peut descendre
		$move2 = $this->can_put_deck_card_down();
		if($move !== false && $move2 !== false) {
			array_push($move, $move2);
			return $move;
		} 
		if($move !== false) return $move;
		if($move2 !== false) return $move2;
		
		// Test si une carte peut être montée suite à un déplacement spécial
		//$move = $this->can_put_a_card_up_after_move();
		//if($move !== false) return $move;
		
		// Pioche
		$move = $this->can_turn_from_deck();
		if($this->infinite_move($move)) return false;
		if($move !== false) return $move;
		
		return false;
	}
	
	/**
	 * Recherche si un déplacement d'une carte du plateau ou de la défausse peut être montée
	 */
	private function can_put_a_card_up() {
		// Recherche sur le plateau
		foreach($this->TBoard as $iCol => $TCard) {
			$iLast = count($TCard) - 1;
			if(!empty($TCard[$iLast])) {
				$card = &$TCard[$iLast];
				if($this->can_move_card_to_aces($card)) {
					return array(
						'action' => 'up'
						,'from' => &$this->TBoard[$iCol]
						,'to' => &$this->TAces[$card->suit]
					);
				}
			}
		}
		
		// Recherche pour la dernière carte de la défausse
		$iLast = count($this->TDiscard) - 1;
		if(!empty($this->TDiscard[$iLast])) {
			$card = &$this->TDiscard[$iLast];
			if($this->can_move_card_to_aces($card)) {
				return array(
					'action' => 'up'
					,'from' => &$this->TDiscard
					,'to' => &$this->TAces[$card->suit]
				);
			}
		}
		
		return false;
	}
	
	/**
	 * Recherche si un déplacement d'une carte ou d'un groupe de carte du plateau est possible
	 */
	private function can_move_cards() {
		$TMove = array();
		// Recherche sur le plateau
		foreach($this->TBoard as $iCol => $TCard) {
			$iLast = count($TCard) - 1;
			if(!empty($TCard[$iLast])) {
				$card = $TCard[$iLast];
				$nbCards = 1;
				
				// Vérification si la carte fait parti d'un groupe de carte
				$cardOk = false;
				
				while (!$cardOk) {
					$iLast--;
					if(!empty($TCard[$iLast])) {
						$parentCard = $TCard[$iLast];
						if($this->can_be_linked($parentCard, $card)) {
							$card = $parentCard;
							$nbCards++;
						} else {
							$cardOk = true;
						}
					} else {
						$cardOk = true;
					}
				}
				
				$jCol = $this->can_move_card_to_board($card);
				if($jCol !== false && ($iLast >= 0 || $card->rank != 12) && !$this->card_has_moved($card)) { // Vérification pour ne pas déplacer un roi d'une colonne vide à une autre
				//if($jCol !== false && $card != $this->currentPath[count($this->currentPath) - 1]['card']) { // Amélioration : on ne déplace jamais 2 fois de suite la même carte
					$TMove[] = array(
						'action' => 'mv'
						,'from' => &$this->TBoard[$iCol]
						,'to' => &$this->TBoard[$jCol]
						,'nb' => $nbCards
					);
				}
			}
		}
		
		return (empty($TMove)) ? false : $TMove;
	}

	/**
	 * Recherche si une carte peut être montée après un mouvement spécial
	 */
	private function can_put_a_card_up_after_move() {
		foreach($this->TAces as $suit => $TCard) {
			$iLast = count($TCard) - 1;
			if(!empty($TCard[$iLast])) {
				$rank = $TCard[$iLast]->rank + 1;
				$pos = $this->find_card_on_board($suit, $rank); // On cherche si la carte est sur le plateau
				if($pos !== false) {
					if($this->can_move_cards_below($pos)) {
						list($i, $j) = explode(':', $pos);
						if(!empty($this->TBoard[$i][($j + 1)])) {
							$card = &$this->TBoard[$i][($j + 1)];
							$jCol = $this->can_move_card_to_board($card);
							if($jCol !== false) {
								$nbCard = count($this->TBoard[$i]) - 1 - $j;
								return array(
									'action' => 'mt'
									,'from' => &$this->TBoard[$i]
									,'to' => &$this->TBoard[$jCol]
									,'nb' => $nbCard
								);
							}
						}
					}
				}
			}
		}
		
		return false;
	}
	
	/**
	 * Recherche si la carte de la défausse peut être déscendue
	 */
	private function can_put_deck_card_down() {
		$iLast = count($this->TDiscard) - 1;
		if(!empty($this->TDiscard[$iLast])) {
			$card = &$this->TDiscard[$iLast];
			$iCol = $this->can_move_card_to_board($card);
			if($iCol !== false) {
				return array(
					'action' => 'dn'
					,'from' => &$this->TDiscard
					,'to' => &$this->TBoard[$iCol]
				);
			}
		}
		
		return false;
	}
	
	/**
	 * Recherche si une carte de la pioche peut être retournée
	 */
	private function can_turn_from_deck() {
		if(empty($this->TDeck) && empty($this->TDiscard)) return false; // Pioche et défausse vide => Plus de tour suivant possible
		else if(empty($this->TDeck)) { // Pioche vide => Réinitialisation
			return array(
				'action' => 'rs'
				,'from' => &$this->TDiscard
				,'to' => &$this->TDeck
			);
		} else {
			return array(
				'action' => 'tn'
				,'from' => &$this->TDeck
				,'to' => &$this->TDiscard
			);
		}
	}
	
	/*
	 * Vérifie si la carte peut être montée, si son rang est supérieur au rang de la dernière carte montée
	 * et si le niveau autorisé de carte à monter correspond (pour éviter de monter trop vite les cartes)
	 */
	private function can_move_card_to_aces(&$card) {
		$iLast = count($this->TAces[$card->suit]) - 1;
		$lastRank = (!empty($this->TAces[$card->suit])) ? $this->TAces[$card->suit][$iLast]->rank : -1;
		
		return ($lastRank + 1 == $card->rank);
		return ($lastRank + 1 == $card->rank && $card->rank <= $this->acesLevel);
	}
	
	private function can_move_card_to_board(&$card) {
		foreach($this->TBoard as $iCol => $TCard) {
			if(empty($TCard) && $card->rank == 12) { // Colonne vide, déplacement possible si la carte est un roi
				return $iCol;
			} else {
				$iLast = count($TCard) - 1;
				$lastRank = (!empty($TCard[$iLast])) ? $TCard[$iLast]->rank : -1;
				$lastColor = (!empty($TCard[$iLast])) ? $TCard[$iLast]->color : '';
				
				if($lastRank - 1 == $card->rank && $lastColor != $card->color) {
					return $iCol;
				}
			}
		}
		
		return false;
	}
	
	/**
	 * Calcul le niveau autorisé pour monter une carte
	 */
	private function check_aces_level() {
		$lowerRank = 100;
		foreach($this->TAces as $suit => $TCard) {
			$iLast = count($TCard) - 1;
			if(!empty($TCard[$iLast])) {
				$lowerRank = ($TCard[$iLast]->rank < $lowerRank) ? $TCard[$iLast]->rank : $lowerRank;
			}
		}
		
		if($lowerRank == 100) $lowerRank = 0;
		$this->acesLevel = $lowerRank + 1;
	}
	
	private function find_card_on_board($suit, $rank) {
		foreach($this->TBoard as $iCol => $TCard) {
			foreach($TCard as $jCol => $card) {
				if($card->suit == $suit && $card->rank == $rank) {
					return $iCol.':'.$jCol;
				}
			}
		}
		
		return false;
	}
	
	private function can_move_cards_below($pos) {
		list($i, $j) = explode(':', $pos);
		$ok = true;
		$card = $this->TBoard[$i][$j];
		
		while($j < count($this->TBoard[$i]) - 1) { // On vérifie que les cartes d'en dessous sont à la suite
			$j++;
			if(!empty($this->TBoard[$i][$j])) {
				$nextCard = $this->TBoard[$i][$j];
				if(!$this->can_be_linked($card, $nextCard)) {
					$ok = false;
					break;
				}
				$card = $nextCard;
			}
		}
		
		return $ok;
	}
	
	private function can_be_linked(&$parentCard, &$childCard) {
		return ($parentCard->rank -1 == $childCard->rank && $parentCard->color != $childCard->color && $parentCard->display);
	}
	
	/**
	 * Vérifie si une carte a déjà été déplacée
	 */
	private function card_has_moved(&$card) {
		foreach($this->currentPath as $move) {
			if(($move['action'] == 'mv' || $move['action'] == 'dn') && $move['card']->code == $card->code) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Vérifie si on est dans une boucle infinie
	 */
	private function infinite_move(&$move) {
		if($move['action'] == 'rs') {
			$nbCards = count($this->TDiscard);
			for ($i=0; $i < $nbCards; $i++) { 
				$TMove = array_slice($this->currentPath, -1, 1);
				if($TMove[0]['action'] != 'tn') {
					return false;
				}
			}
			
			return true;
		}
		
		return false;
	}
	
	public function is_game_finished() {
		$finished = true;
		foreach($this->TAces as $suit => $TCard) {
			if(count($TCard) < 13) $finished = false;
		}
		return $finished;
	}
	
	private function save_current_solution() {
		$this->TPath[] = array('score' => $this->currentScore, 'path' => $this->currentPath);
		if($this->currentScore >= $this->bestScore) {
			$this->bestScore = $this->currentScore;
			$this->bestPath = $this->currentPath;
		}
	}
}