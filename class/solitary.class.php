<?php

include('card.class.php');

class Solitary extends CardGame {
	var $TDeck = array();
	var $TDiscard = array();
	var $TAces = array();
	var $TSolitary = array();
	
	var $acesLevel = 1;
	var $round = 1;
	var $blocked = false;
	var $end = false;
	var $score = 0;
	
	var $nbMoveMax = 200;
	
	var $bestScore = 0;
	var $bestChemin = array();
	var $TChemin = array();
	
	function __construct($nbCartes=52) {
		parent::__construct($nbCartes);
		$this->init();
		
		foreach($this->TSuit as $suit) {
			$this->TAces[$suit] = array();
		}
	}
	
	public function create_random_game() {
		shuffle($this->TCard);
		$i = 0;
		foreach($this->TCard as $card) {
			if(count($this->TDeck) < 24) { // Fill the deck with the 24 first cards
				$this->TDeck[] = $card;
			} else { // Fill the board with the others
				if(empty($this->TSolitary[$i])) {
					$this->TSolitary[$i] = array();
				}
				
				$this->TSolitary[$i][] = $card;
				
				if(count($this->TSolitary[$i]) > $i) {
					$i++;
				}
			}
		}
	}
	
	/**
	 * TODO : détecter un blocage et dans ce cas, permettre de déplacer des groupes de carte sans partir de la + haute
	 */
	public function get_solution(&$chemin) {
		if($this->game_finished()) {
			$this->end = true;
		}
		
		while(!$this->end && !$this->blocked) {
			if(count($chemin) >= $this->nbMoveMax) $this->end = true; // Blockage à 200 coups pour éviter la boucle infinie
			
			$lastMove = (!empty($chemin)) ? array_slice($chemin, -1) : array();
			
			$move = $this->get_next_move();
			if($move === false) {
				$this->blocked = true;
			} else if(!empty($move[0])) {
				foreach($move as $mv) {
					$this->do_move($mv);
					$this->calculate_score($mv);
					$chemin[] = $mv;
					
					// A revoir pour le récursif et trouver la meilleure solution
					$this->get_solution($chemin);
					
					array_pop($chemin);
					$tmp = $mv['from'];
					$mv['from'] = $mv['to'];
					$mv['to'] = $tmp;
					$this->do_move($mv);
					$this->calculate_score($mv, 'remove');
				}
				
			} else if($this->infinite_move($chemin, $move)) {
				$this->blocked = true;
			} else {
				$this->blocked = false;
				
				$this->do_move($move);
				$this->calculate_score($move);
				$chemin[] = $move;
			}
		}
		
		$this->TChemin[] = array_merge(array('score' => $this->score), $chemin);
		if($this->score > $this->bestScore) {
			$this->bestScore = $this->score;
			$this->bestChemin = $chemin;
		}
	}
	
	private function do_move(&$move) {
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
				$TCards = array_splice($move['from'], $move['nb'] * -1);
				$move['to'] = array_merge($move['to'], $TCards);
				$move['card'] = &$TCards[0];
				break;
			
			
			/*	$card = array_pop($move['from']);
				$move['to'][] = $card;
				$move['card'] = &$card;
				break;*/
				
			case 'rs':
				$move['to'] = $move['from'];
				$move['from'] = array();
				$move['card'] = &$move['to'][0];
				$this->round++;
				break;
			
			default:
				
				break;
		}
		
		//echo $move['action'].' '.$move['card'].'<br />';
	}
	
	private function calculate_score(&$move, $mode='add') {
		$score = 0;
		
		switch ($move['action']) {
			case 'up': $score = 50;
				break;
			case 'dn': $score = 20;
				break;
			case 'mv':
			case 'mt':
				if(!empty($move['from'])) {
					$score = 20;
				}
				break;
			
			case 'tn':
				
				break;
			case 'rs': $score = -200;
				break;
			
			default:
				break;
		}
		
		if($mode == 'add') $this->score += $score;
		else $this->score -= $score;
	}
	
	private function get_next_move() {
		// Test si une carte peut monter
		$move = $this->can_put_a_card_up();
		if($move !== false) return $move;
		
		// Test si une carte peut être déplacée
		$move = $this->can_move_cards();
		if($move !== false) return $move;
		
		// Test si une carte peut être montée suite à un déplacement spécial
		//$move = $this->can_put_a_card_up_after_move();
		if($move !== false) return $move;
		
		// Test si la carte du deck peut descendre
		$move = $this->can_put_deck_card_down();
		if($move !== false) return $move;
		
		// Pioche
		$move = $this->can_turn_from_deck();
		if($move !== false) return $move;
		
		return $move;
	}
	
	/**
	 * Recherche si un déplacement d'une carte du plateau ou de la défausse peut être montée
	 */
	private function can_put_a_card_up() {
		// Recherche sur le plateau
		foreach($this->TSolitary as $iCol => $TCard) {
			$iLast = count($TCard) - 1;
			if(!empty($TCard[$iLast])) {
				$card = &$TCard[$iLast];
				if($this->can_move_card_to_aces($card)) {
					return array(
						'action' => 'up'
						,'from' => &$this->TSolitary[$iCol]
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
		foreach($this->TSolitary as $iCol => $TCard) {
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
				if($jCol !== false && ($iLast >= 0 || $card->rank != 12)) { // Vérification pour ne pas déplacer un roi d'une colonne vide à une autre
					$TMove[] = array(
						'action' => 'mv'
						,'from' => &$this->TSolitary[$iCol]
						,'to' => &$this->TSolitary[$jCol]
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
						if(!empty($this->TSolitary[$i][($j + 1)])) {
							$card = &$this->TSolitary[$i][($j + 1)];
							$jCol = $this->can_move_card_to_board($card);
							if($jCol !== false) {
								$nbCard = count($this->TSolitary[$i]) - 1 - $j;
								return array(
									'action' => 'mt'
									,'from' => &$this->TSolitary[$i]
									,'to' => &$this->TSolitary[$jCol]
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
					,'to' => &$this->TSolitary[$iCol]
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
		foreach($this->TSolitary as $iCol => $TCard) {
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
		foreach($this->TSolitary as $iCol => $TCard) {
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
		$card = $this->TSolitary[$i][$j];
		
		while($j < count($this->TSolitary[$i]) - 1) { // On vérifie que les cartes d'en dessous sont à la suite
			$j++;
			if(!empty($this->TSolitary[$i][$j])) {
				$nextCard = $this->TSolitary[$i][$j];
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
		return ($parentCard->rank -1 == $childCard->rank && $parentCard->color != $childCard->color);
	}
	
	/**
	 * Vérifie si on est dans une boucle infinie
	 */
	private function infinite_move(&$chemin, &$move) {
		if($move['action'] == 'rs') {
			$nbCards = count($this->TDiscard);
			for ($i=0; $i < $nbCards; $i++) { 
				$TMove = array_slice($chemin, -1, 1);
				if($TMove[0]['action'] != 'tn') {
					return false;
				}
			}
			
			return true;
		}
		
		return false;
	}
	
	private function game_finished() {
		$finished = true;
		foreach($this->TAces as $suit => $TCard) {
			if(count($TCard) < 13) $finished = false;
		}
		return $finished;
	}
}