<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr" dir="ltr">
<head>
	<link rel="stylesheet" type="text/css" href="http://localhost/perso/midas_solitaire/css/main.css" />
	<? /*<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script> */?>
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
			<?
			foreach($solitary->TDiscard as $card) {
				echo $card;
			}
			?>
		</div>
		<div id="aces">
			<div class="col">
			<?
			foreach($solitary->TAces['hearts'] as $card) {
				echo $card;
			}
			?>
			</div>
			<div class="col">
			<?
			foreach($solitary->TAces['diams'] as $card) {
				echo $card;
			}
			?>
			</div>
			<div class="col">
			<?
			foreach($solitary->TAces['clubs'] as $card) {
				echo $card;
			}
			?>
			</div>
			<div class="col">
			<?
			foreach($solitary->TAces['spades'] as $card) {
				echo $card;
			}
			?>
			</div>
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
			<input type="button" name="search" value="Chercher" />
			<hr>
			Score : <?= $solitary->score ?>
		</div>
	</div>
</div>
<div id="cardlist">
	<?
	foreach($solitary->TCard as $card) {
		echo
		/*echo '<hr><pre>';
		var_dump($move);
		echo '</pre><hr>';*/ $card;
	}
	?>
</div>
<div id="solution">
	<?
	foreach ($solitary->TChemin as $i => $chemin) {
		$score = $chemin['score'];
		unset($chemin['score']);
		echo '<div style="clear: left;"><hr>'.$i.' : '.$score.'<hr><div>';
		foreach($chemin as $i => $move) {
			echo '<div class="step">'.$move['action'].'</div>'.$move['card'];
		}
		echo '<hr>';
	}
	?>
</div>

</body>
</html>