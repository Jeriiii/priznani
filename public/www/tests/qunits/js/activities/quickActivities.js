QUnit.module( "Quick Activities" );
QUnit.test( "Připojení streamu pro aktivity", function( assert ) {
	assert.ok($.fn.stream, "Stream.js je připojen!" );
});
QUnit.test("Test donačtení příspěvků na kliknutí", function( assert ) {
	/* načtení streamu */
	$.ajax({
		url: basePath + "/safe.tests/quickActivities",
		async: false,
		success: function( data ) {
			$('#container').html( data );
			$("body").stream({
				addoffset: 4,
				snippetName: "snippet-quickActivities-list",
				linkElement: "#next-data-item-activity-btn",
				offsetName: "quickActivities-offset",
				autoload: false,
				msgElement: '#quick-activity-message',
				msgText: "Žádné starší aktivity nebyly nalezeny"
			});
		}
	});
	/* ověření, že je tam aktivita */
	var activity = 'Test Admin nahrál nové fotky';
	assert.ok(window.find(activity));
	
	/* počet aktivit je po inicializaci 4 */
	var countActs = $('#snippet-quickActivities-list').children().length;
	console.log(countActs);
	assert.ok(countActs == 4);
	
	/* kliknutí na tlačítko */
	$('#next-data-item-activity-btn').trigger('click');
	
	/* počet aktivit je po kliknutí na tlačítko 6 nebo více */
	countActs = $('#snippet-quickActivities-list').children().length;
	console.log(countActs);
	assert.ok(countActs >= 6);
	
	/* uklidím po sobě */
	clear();
});