QUnit.module( "Stream" );
QUnit.test( "Připojení streamu", function( assert ) {
	assert.ok($.fn.stream, "Stream.js je připojen!" );
});
QUnit.test("Test donačtení příspěvků na kliknutí", function( assert ) {
	/* načtení streamu */
	$.ajax({
		url: basePath + "/safe.tests/stream",
		async: false,
		success: function( data ) {
			$('#container').html( data );
			$("body").stream({
				addoffset: 4,
				snippetName: "snippet-userStream-posts",
				streamLoader: '#stream-loader',
				autoload: false
			});
		}
	});
	/* přiznání tu není */
	var confession = 'Moje přítelkyně je Bi. Musím říct, že je to pro mě prokletí a bezvýchodná cesta.';
	assert.ok(! window.find(confession));
	
	/* kliknutí na tlačítko */
	$('#next-data-item-btn').trigger('click');
	
	/* přiznání tu je */
	assert.ok(window.find(confession));
	
	/* uklidím po sobě */
	clear();
});