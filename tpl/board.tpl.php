<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr" dir="ltr">
<head>
	<link rel="stylesheet" type="text/css" href="http://localhost/perso/midas_solitaire/css/main.css" />
	<?/*<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>*/?>
	<script src="http://localhost/perso/midas_solitaire/js/solitary.js"></script>
</head>
<body>
<center>
	<hr>MIDAS SOLITAIRE<hr>
</center>

<div id="board">
	<div id="top">
		<div id="deck">
			<?
			foreach($solitary->TDeck as $card) {
				echo $card;
			}
			?>
		</div>
		<div id="discard">
			DISCARD
		</div>
		<div id="aces">
			<div class="col">H</div>
			<div class="col">D</div>
			<div class="col">C</div>
			<div class="col">S</div>
		</div>
	</div>
	<div id="solitary">
		<?
		foreach($solitary->TSolitary as $i => $TCard) {
			?><div class="col"><?
			foreach($TCard as $card) {
				echo $card;
			}
			?></div><?
		}
		?>
		<div class="button">
			<input type="button" value="Chercher" />
		</div>
	</div>
</div>
<div id="cardlist">
	<?
	foreach($solitary->cardGame->TCard as $card) {
		echo $card;
	}
	?>
</div>

</body>
</html>