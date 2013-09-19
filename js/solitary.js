$(document).ready(function() {
	// Possibilité de déplacer les cartes
	$('#cardlist').addClass('connectedSortable');
	$('#deck').addClass('connectedSortable');
	$('#solitary_board div.col').addClass('connectedSortable');
	$('#cardlist, #deck, #solitary_board div.col').sortable({
		connectWith: ".connectedSortable"
	});
	//$('#cardlist div.card').draggable();
	//$('#deck').droppable();
	//$('#solitary_board div.col').droppable();
	
	
	// Lancement de la recherche de solution
	$('input[name="search"]').click(function() {
		var TDeck = new Array();
		var TBoard = new Array();
		
		$('#deck div.card').each(function() {
			TDeck.push($(this).attr('code'));
		});
		
		$('#solitary_board div.col').each(function() {
			var TCol = new Array();
			$(this).find('div.card').each(function() {
				TCol.push($(this).attr('code'));
			});
			TBoard.push(TCol);
		});
		
		if(TDeck.length == 24 && $('#cardlist dic.card').length == 0) {
			$.ajax({
				type: 'POST',
				url: 'ajax_solitary.php',
				data: { action: 'search', TDeck: TDeck, TBoard: TBoard },
				success: function(sol) {
					$('span.score').html(sol.score);
					$('#solution').html(sol.pathHTML);
				},
				dataType: 'json'
			});
		}
	});
});
