<?php

class Solitary {
	var $cardGame;
	var $TDeck = array();
	var $TDiscard = array();
	var $TAces = array();
	var $TSolitary = array();
	
	function __construct() {
		$this->cardGame = new CardGame();
		$this->cardGame->init();
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
	}
	
	public function init() {
		for($i=0; $i < $this->nbCards; $i++) {
			$suit = $this->TSuit[($i % 13)];
			$this->TCard[] = new Card($suit, ($i % 13), ($i % 13)); 
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
		return $this->get_label().'<br />';
	}
	
	private function set_label() {
		if($this->code == 1) $this->label = 'Ace';
		else if($this->code < 11) $this->label = $this->code;
		else if($this->code == 11) $this->label = 'J';
		else if($this->code == 12) $this->label = 'Q';
		else if($this->code == 13) $this->label = 'K';
		else $this->label = 'Unknown';
	}
	
	public function get_label($html=true) {
		if($html) return $this->label.' &'.$this->suit.';';
		else return $this->label.' of '.$this->suit;
	}
}
