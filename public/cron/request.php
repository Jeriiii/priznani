<?php

/**
 * Odešle get request
 * @param string $url Url, na které se má request odeslat.
 * @return Odpověď
 */
function getRequest($url) {
	// Get cURL resource
	$curl = curl_init();
	// Set some options - we are passing in a useragent too here
	curl_setopt_array($curl, array(
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_URL => $url,
		CURLOPT_USERAGENT => 'Codular Sample cURL Request'
	));
	// Send the request & save response to $resp
	$response = curl_exec($curl);
	// Close request to clear up some resources
	curl_close($curl);

	return $response;
}
