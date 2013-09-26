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
	
	public function save_init_position() {
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
	
	public function get_solution($currentPath, $currentScore, $TGame) {
		$move = $this->get_next_move($TGame, $currentPath);
		
		if($move === false) {
			$this->save_current_solution($currentPath, $currentScore);
		} else {
			if(empty($move[0])) $move = array($move);
			
			foreach($move as $mv) {
				$this->do_move($mv, $currentScore, $TGame);
				$currentPath[] = $mv;
				/*if(count($currentPath) == 20) {
					pre($TGame);
					exit;
				}*/
				// Appel récursif pour les différents mouvements possibles
				$this->get_solution($currentPath, $currentScore, $TGame);
			}
		}
	}
	
	private function do_move(&$move, &$currentScore, &$TGame) {
		switch ($move['action']) {
			case 'up':
				// Mouvement
				$card = array_pop($move['from']);
				$move['to'][] = $card;
				$move['card'] = &$card;
				
				// Vérification du niveau des cartes montées
				$this->check_aces_level($TGame);
				
				// Calcul score
				$cardScore = ($card->rank < 10) ? ($card->rank + 1) : 10;
				$cardScore *= 10;
				$score = 110 - $cardScore;
				$currentScore += $score;
				
				// Carte découverte
				$iLast = count($move['from']) - 1;
				if(!empty($move['from'][$iLast])) {
					$move['from'][$iLast]->display = true;
					$currentScore += 20;
				}
				
				break;
				
			case 'dn':
				// Mouvement
				$card = array_pop($move['from']);
				$move['to'][] = $card;
				$move['card'] = &$card;
				
				// Calcul score
				$currentScore += 20;
				
				// Carte découverte
				$iLast = count($move['from']) - 1;
				if(!empty($move['from'][$iLast])) {
					$move['from'][$iLast]->display = true;
					$currentScore += 20;
				}
				
				break;
				
			case 'tn':
				// Mouvement
				$card = array_pop($move['from']);
				$move['to'][] = $card;
				$move['card'] = &$card;
				
				break;
				
			case 'mv':
				// Mouvement
				$TCards = array_splice($move['from'], $move['nb'] * - 1);
				$move['to'] = array_merge($move['to'], $TCards);
				$move['card'] = &$TCards[0];
				
				// Carte découverte
				$iLast = count($move['from']) - 1;
				if(!empty($move['from'][$iLast])) {
					$move['from'][$iLast]->display = true;
					$currentScore += 20;
				}

				break;
				
			case 'rs':
				// Mouvement
				$move['to'] = array_reverse($move['from']);
				$move['from'] = array();
				$move['card'] = &$move['to'][0];
				
				// Calcul score
				$currentScore -= 200;
				
				$this->round++;
				break;
			
			default:
				
				break;
		}
	}
	
	private function get_next_move(&$TGame, &$currentPath) {
		// Test si une carte peut monter
		$move = $this->can_put_a_card_up($TGame);
		if($move !== false) return $move;
		
		// Test si une carte peut être déplacée
		$move = $this->can_move_cards($TGame, $currentPath);
		//if($move !== false) return $move;
		
		// Test si la carte du deck peut descendre
		$move2 = $this->can_put_deck_card_down($TGame);
		if($move !== false && $move2 !== false) {
			array_push($move, $move2);
			return $move;
		} 
		if($move !== false) return $move;
		if($move2 !== false) return $move2;
		
		// Pioche
		$move = $this->can_turn_from_deck($TGame);
		if($this->infinite_move($move, $currentPath, $TGame)) {
			//echo 't ';
			return false;
		}
		if($move !== false) return $move;
		
		return false;
	}
	
	/**
	 * Recherche si un déplacement d'une carte du plateau ou de la défausse peut être montée
	 */
	private function can_put_a_card_up(&$TGame) {
		// Recherche sur le plateau
		foreach($TGame['TBoard'] as $iCol => $TCard) {
			$iLast = count($TCard) - 1;
			if(!empty($TCard[$iLast])) {
				$card = &$TCard[$iLast];
				if($this->can_move_card_to_aces($TGame, $card)) {
					return array(
						'action' => 'up'
						,'from' => &$TGame['TBoard'][$iCol]
						,'to' => &$TGame['TAces'][$card->suit]
					);
				}
			}
		}
		
		// Recherche pour la dernière carte de la défausse
		$iLast = count($TGame['TDiscard']) - 1;
		if(!empty($TGame['TDiscard'][$iLast])) {
			$card = &$TGame['TDiscard'][$iLast];
			if($this->can_move_card_to_aces($TGame, $card)) {
				return array(
					'action' => 'up'
					,'from' => &$TGame['TDiscard']
					,'to' => &$TGame['TAces'][$card->suit]
				);
			}
		}
		
		return false;
	}
	
	/**
	 * Recherche si un déplacement d'une carte ou d'un groupe de carte du plateau est possible
	 */
	private function can_move_cards(&$TGame, &$currentPath) {
		$TMove = array();
		// Recherche sur le plateau
		foreach($TGame['TBoard'] as $iCol => $TCard) {
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
				
				$jCol = $this->can_move_card_to_board($TGame, $card);
				if($jCol !== false && ($iLast >= 0 || $card->rank != 12) && !$this->card_has_moved($card, $currentPath)) { // Vérification pour ne pas déplacer un roi d'une colonne vide à une autre
				//if($jCol !== false && $card != $this->currentPath[count($this->currentPath) - 1]['card']) { // Amélioration : on ne déplace jamais 2 fois de suite la même carte
					$TMove[] = array(
						'action' => 'mv'
						,'from' => &$TGame['TBoard'][$iCol]
						,'to' => &$TGame['TBoard'][$jCol]
						,'nb' => $nbCards
					);
				}
			}
		}
		
		return (empty($TMove)) ? false : $TMove;
	}

	/**
	 * Recherche si la carte de la défausse peut être déscendue
	 */
	private function can_put_deck_card_down(&$TGame) {
		$iLast = count($TGame['TDiscard']) - 1;
		if(!empty($TGame['TDiscard'][$iLast])) {
			$card = &$TGame['TDiscard'][$iLast];
			$iCol = $this->can_move_card_to_board($TGame, $card);
			if($iCol !== false) {
				return array(
					'action' => 'dn'
					,'from' => &$TGame['TDiscard']
					,'to' => &$TGame['TBoard'][$iCol]
				);
			}
		}
		
		return false;
	}
	
	/**
	 * Recherche si une carte de la pioche peut être retournée
	 */
	private function can_turn_from_deck(&$TGame) {
		if(empty($TGame['TDeck']) && empty($TGame['TDiscard'])) return false; // Pioche et défausse vide => Plus de tour suivant possible
		else if(empty($TGame['TDeck'])) { // Pioche vide => Réinitialisation
			return array(
				'action' => 'rs'
				,'from' => &$TGame['TDiscard']
				,'to' => &$TGame['TDeck']
			);
		} else {
			return array(
				'action' => 'tn'
				,'from' => &$TGame['TDeck']
				,'to' => &$TGame['TDiscard']
			);
		}
	}
	
	/*
	 * Vérifie si la carte peut être montée, si son rang est supérieur au rang de la dernière carte montée
	 * et si le niveau autorisé de carte à monter correspond (pour éviter de monter trop vite les cartes) (option)
	 */
	private function can_move_card_to_aces(&$TGame, $card) {
		$iLast = count($TGame['TAces'][$card->suit]) - 1;
		$lastRank = (!empty($TGame['TAces'][$card->suit])) ? $TGame['TAces'][$card->suit][$iLast]->rank : -1;
		
		return ($lastRank + 1 == $card->rank);
		return ($lastRank + 1 == $card->rank && $card->rank <= $this->acesLevel);
	}
	
	private function can_move_card_to_board(&$TGame, $card) {
		foreach($TGame['TBoard'] as $iCol => $TCard) {
			if(empty($TCard) && $card->rank == 12) { // Colonne vide, déplacement possible si la carte est un roi
				return $iCol;
			} else if(!empty($TCard)) {
				$iLast = count($TCard) - 1;
				if($this->can_be_linked($TCard[$iLast], $card)) return $iCol;
			}
		}
		
		return false;
	}
	
	/**
	 * Calcul le niveau autorisé pour monter une carte
	 */
	private function check_aces_level(&$TGame) {
		$lowerRank = 100;
		foreach($TGame['TAces'] as $suit => $TCard) {
			$iLast = count($TCard) - 1;
			if(!empty($TCard[$iLast])) {
				$lowerRank = ($TCard[$iLast]->rank < $lowerRank) ? $TCard[$iLast]->rank : $lowerRank;
			}
		}
		
		if($lowerRank == 100) $lowerRank = 0;
		$this->acesLevel = $lowerRank + 1;
	}
	
	/*private function find_card_on_board($suit, $rank) {
		foreach($TGame['TBoard'] as $iCol => $TCard) {
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
		$card = $TGame['TBoard'][$i][$j];
		
		while($j < count($TGame['TBoard'][$i]) - 1) { // On vérifie que les cartes d'en dessous sont à la suite
			$j++;
			if(!empty($TGame['TBoard'][$i][$j])) {
				$nextCard = $TGame['TBoard'][$i][$j];
				if(!$this->can_be_linked($card, $nextCard)) {
					$ok = false;
					break;
				}
				$card = $nextCard;
			}
		}
		
		return $ok;
	}*/
	
	private function can_be_linked(&$parentCard, &$childCard) {
		return ($parentCard->rank -1 == $childCard->rank && $parentCard->color != $childCard->color && $parentCard->display);
	}
	
	/**
	 * Vérifie si une carte a déjà été déplacée
	 */
	private function card_has_moved(&$card, &$currentPath) {
		foreach($currentPath as $move) {
			if($move['card']->code == $card->code && ($move['action'] == 'mv' || $move['action'] == 'dn')) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Vérifie si on est dans une boucle infinie
	 */
	private function infinite_move(&$move, $currentPath, &$TGame) {
		if($move['action'] == 'rs') {
			$nbCards = count($TGame['TDiscard']);
			for ($i=0; $i < $nbCards; $i++) { 
				$TMove = array_slice($currentPath, -1, 1);
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
		foreach($TGame['TAces'] as $suit => $TCard) {
			if(count($TCard) < 13) $finished = false;
		}
		return $finished;
	}
	
	private function save_current_solution($currentPath, $currentScore) {
		$this->TPath[] = array('score' => $currentScore, 'path' => $currentPath);
		if($currentScore >= $this->bestScore) {
			$this->bestScore = $currentScore;
			$this->bestPath = $currentPath;
		}
	}
}