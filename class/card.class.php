<?php

class Solitary {
	var $cardGame;
	var $TDeck = array();
	var $TDiscard = array();
	var $TAces = array();
	var $TSolitary = array();
	var $end = false;
	
	function __construct() {
		$this->cardGame = new CardGame();
		$this->cardGame->init();
	}
	
	public function create_random_game() {
		shuffle($this->cardGame->TCard);
		$i = 0;
		foreach($this->cardGame->TCard as $card) {
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
	
	public function get_solution(&$chemin) {
		if(count($chemin) == 5) $this->end = true;
		while(!$this->end) {
			$next = $this->get_next_move();
			
			$chemin[] = $next;
			
			$this->get_solution($chemin);
		}
	}
	
	private function get_next_move() {
		
	}
}

class CardGame {
	var $TCard = array();
	var $TSuit = array('clubs', 'diams', 'hearts', 'spades');
	var $nbCards = 0;

	function __construct($nbCards=52) {
		$this->nbCards = $nbCards;
	}
	
	public function __toString() {
		foreach($this->TCard as $card) {
			echo $card;
		}
		return '';
	}
	
	public function init() {
		$nbCardPerSuit = $this->nbCards / count($this->TSuit);
		for($i=0; $i < $this->nbCards; $i++) {
			$suit = $this->TSuit[floor($i / $nbCardPerSuit)];
			$this->TCard[] = new Card($suit, ($i % $nbCardPerSuit), ($i % $nbCardPerSuit)); 
		}
	}
}

class Card {
	var $suit; // hearts, diams, clubs, spades
	var $code; // 1 = Ace, ... K = 13
	var $rank;
	var $value;
	var $label;
	var $color;
	
	function __construct($suit, $code, $rank, $value=0) {
		$this->suit = $suit;
		$this->code = $code;
		$this->rank = $rank;
		$this->value = $value;
		
		$this->color = ($suit == 'hearts' || $suit == 'diams') ? 'red' : 'black';
		$this->set_label();
	}
	
	public function __toString() {
		return $this->get_label();
	}
	
	private function set_label() {
		if($this->code === 0) $this->label = 'A';
		else if($this->code < 10) $this->label = $this->code + 1;
		else if($this->code == 10) $this->label = 'J';
		else if($this->code == 11) $this->label = 'Q';
		else if($this->code == 12) $this->label = 'K';
		else $this->label = 'Unknown';
	}
	
	public function get_label($html=true) {
		if($html) return '<div class="card '.$this->color.'">'.$this->label.' &'.$this->suit.';</div>';
		else return $this->label.' of '.$this->suit;
	}
}
