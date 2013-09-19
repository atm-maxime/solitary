<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr" dir="ltr">
<head>
	<link rel="stylesheet" type="text/css" href="[view.http; strconv=no]css/main.css" />
	<script src="[view.http; strconv=no]js/jquery-2.0.3.min.js"></script>
	<script src="[view.http; strconv=no]js/jquery-ui.min.js"></script>
	<script src="[view.http; strconv=no]js/solitary.js"></script>
</head>

<body>
<center>
	<hr>SOLITAIRE<hr>
</center>

<div id="cardlist">
	<div class="card [allcards.color; block=div]" code="[allcards.code]">[allcards.get_label; strconv=no]</div>
</div>
<div id="board">
	<div id="top">
		<div id="deck">
			<div class="card [deck.color; block=div]" code="[deck.code]">[deck.get_label; strconv=no]</div>
		</div>
		<div id="discard">
			<div class="card [discard.color; block=div]" code="[discard.code]">[discard.get_label; strconv=no]</div>
		</div>
		<div id="aces">
			<div class="col">
				<div class="card [aces_hearts.color; block=div]" code="[aces_hearts.code]">[aces_hearts.get_label; strconv=no]</div>
			</div>
			<div class="col">
				<div class="card [aces_diams.color; block=div]" code="[aces_diams.code]">[aces_diams.get_label; strconv=no]</div>
			</div>
			<div class="col">
				<div class="card [aces_clubs.color; block=div]" code="[aces_clubs.code]">[aces_clubs.get_label; strconv=no]</div>
			</div>
			<div class="col">
				<div class="card [aces_spades.color; block=div]" code="[aces_spades.code]">[aces_spades.get_label; strconv=no]</div>
			</div>
		</div>
	</div>
	<div id="solitary_board">
		<div class="col">
			[board;block=div;sub1]
			<div class="card [board_sub1.color; block=div]" code="[board_sub1.code]">[board_sub1.get_label; strconv=no]</div>
		</div>
		<div class="button">
			<input type="button" name="search" value="Chercher" /><br />
			<span class="score">[data.score]</span>
		</div>
	</div>
</div>

<div id="solution">
	<div>[path; block=div]
		<div class="step">[path.action]</div>
		<div class="card [path.card.color]" code="[path.card.code]">[path.card.get_label; strconv=no]</div>
	</div>
</div>

</body>
</html>