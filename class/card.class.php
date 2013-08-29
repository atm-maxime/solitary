<?php

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
			$this->TCard[$i] = new Card($suit, $i, ($i % $nbCardPerSuit)); 
		}
	}
}

class Card {
	var $suit; // hearts, diams, clubs, spades
	var $code; // 0 = Ace, ... K = 12
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
		if($this->rank === 0) $this->label = 'A';
		else if($this->rank < 10) $this->label = $this->rank + 1;
		else if($this->rank == 10) $this->label = 'J';
		else if($this->rank == 11) $this->label = 'Q';
		else if($this->rank == 12) $this->label = 'K';
		else $this->label = 'Unknown';
	}
	
	public function get_label($html=true) {
		//if($html) return '<div class="card '.$this->color.'" code="'.$this->code.'">'.$this->label.' &'.$this->suit.';</div>';
		if($html) return $this->label.' &'.$this->suit.';';
		else return $this->label.' of '.$this->suit;
	}
}
