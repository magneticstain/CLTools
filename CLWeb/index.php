<?php
namespace CLTools\CLWeb;

	/**
	 *  CLTools
	 *  Author: Josh Carlson
	 *  Email: jcarlson(at)carlso(dot)net
	 */

	/*
	 *  index.php - main landing page for CLWeb portion of CLTools suite
	 */

	// import config(s) and libraries
	$BASE_URL = $_SERVER['DOCUMENT_ROOT'];
	require $BASE_URL.'/CLTools/lib/Autoloader.php';

	// set http headers
	// cache time is currently set to 120 seconds in order to balance caching w/ listing freshness
	Web::setHTTPHeaders(120);

	// set content template (data is loaded on-demand using CLData APIs
	$contentHTML = '
									<div id="stats">
										<div id="statsTitle">
											<img src="/CLTools/CLWeb/static/media/icons/stats.png" title="Detailed Listing Statistics" alt="Listing stats icon">
											<h2>CLWeb Stats</h2>
										</div>
										<div id="statsWrapper">
											<div class="statContainer rent">
												<h3>Avg. Rent</h3>
												<p>---</p>
											</div>
											<div class="statContainer popLocation">
												<h3>Most Popular Location</h3>
												<p>---</p>
											</div>
											<div class="statsButton advanced">
												<p>Advanced Stats</p>
											</div>
										</div>
									</div>
									<div title="Listings Map - view all collected listings!" id="map"></div>
	';

	// try to generate html
	try {
		$web = new Web(
			$contentHTML
		);
		
		echo $web;
	} catch(\Exception $e) {
		error_log('CLTools :: CLWeb :: [ SEV: FATAL ] :: could not start web engine :: [ MSG: '.$e->getMessage().' ]');
		
		// throw 503 status
		http_response_code(503);
		
		exit(1);
	}
?>
