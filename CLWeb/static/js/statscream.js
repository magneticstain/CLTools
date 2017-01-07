/**
 *  CLWeb // Statscream.js
 *
 *  A JS library for handling statistics
 */

var Statscream = function(){};

// BASIC STATS
Statscream.updateListingStat = function(selector, apiUrl, returnDataType, returnFieldNum){
	/*
	    Query Calc API for requested stat and update the dataSink with the results
	 */

	var stat = '---';
	var statWrapper = selector;

	if(typeof returnFieldNum === 'undefined')
	{
		returnFieldNum = 0;
	}

	$.ajax({
		url: apiUrl,
		dataType: 'json',
		success: function(calcData){
			// check if API req was successful
			if(calcData.success)
			{
				// parse stat
				stat = DataTron.formatReturnData(returnDataType, calcData.result[0][returnFieldNum]);
			}

			// update view w/ results
			statWrapper.children('p').text(stat);
		},
		error: function(){
			var errorBot = new ErrorBot(2, 'Could not access Calc API');
			errorBot.displayError();
			errorBot.logErrorToConsole();
		}
	});
};

Statscream.loadStats = function(){
	/*
	    Load CLWeb statistics from CLWeb Calc API
	 */

	var apiBaseUrl = '/CLTools/CLData/api/v1/calc/';

	// calculate: avg rent, avg sq ft, num w/ amenities, and most popular location
	// avg rent
	Statscream.updateListingStat($('.rent'), apiBaseUrl + '?m=avg&f=price&o=desc', 'currency');
	// most popular loc
	Statscream.updateListingStat($('.popLocation'), apiBaseUrl + '?m=count&f=location&o=desc&l=1', 'text');
};

Statscream.startBasicStats = function(){
	/*
	    Update view to provide user with basic listing statistics
	 */

	// set selector for content and stats wrappers
	var contentWrapper = $('#contentWrapper');
	var statsWrapper = $('#stats');

	// get original width if set and not 100%
	var origWidth = statsWrapper.data('origWidth');
	if(typeof origWidth === 'undefined')
	{
		if(statsWrapper.css('width') !== '100%')
		{
			// set current width as original width and save for later
			origWidth = statsWrapper.css('width');
			statsWrapper.data('origWidth', origWidth);
		}
		else
		{
			// set to default
			origWidth = '232px';
		}
	}

	statsWrapper.animate({
		width: origWidth
	}, 500, function(){
		// update stats html
		statsWrapper.html('' +
			'   <div id="statsTitle"> ' +
			'       <img src="/CLTools/CLWeb/static/media/icons/stats.png" title="Detailed Listing Statistics" alt="Listing stats icon"> ' +
			'       <h2>CLWeb Stats</h2> ' +
			'   </div> ' +
			'   <div id="statsWrapper"> ' +
			'       <div class="statContainer rent"> ' +
			'           <h3>Avg. Rent</h3> ' +
			'           <p>---</p> ' +
			'       </div> ' +
			'       <div class="statContainer popLocation"> ' +
			'           <h3>Most Popular Location</h3> ' +
			'           <p>---</p> ' +
			'       </div> ' +
			'       <div class="statsButton advanced"> ' +
			'           <p>Advanced Stats</p> ' +
			'       </div> ' +
			'   </div>');

		// remove any previously-loaded maps
		$('#map').remove();

		// append new map div after stats
		contentWrapper.append('<div title="Listings Map - view all collected listings!" id="map"></div>');

		// load basic stats
		Statscream.loadStats();

		// generate map
		DataTron.generateListingMap();

		// add event handler for adv stats button
		$('.advanced').click(function(){
			Statscream.startAdvancedStats();
		});

		// set URL hash value
		window.location.hash = 'basic';
	});
};

// ADVANCED STATS
Statscream.initializeAdvancedStatsInView = function(statsContainer){
	/*
	    Initialize advanced stats headings and wrappers in preparation for graphing of data
	 */

	var html = '';
	var canvasHeight = 128;

	// add basic stats button for user to return
	html += '' +
		'<div class="statsButton basic">' +
		'   <p>Basic Stats</p>' +
		'</div>';

	// location count
	html += '' +
		'<div class="statContainer">' +
		'   <h3>Location by Popularity</h3>' +
		'   <div class="advStatsChartWrapper">' +
		'       <canvas id="locationPopularityChart" height="' + canvasHeight + '"></canvas> ' +
		'   </div>' +
		'</div>';

	// listings by post date
	html += '' +
		'<div class="statContainer">' +
		'   <h3>Listings Over Time</h3>' +
		'   <div class="advStatsChartWrapper">' +
		'       <canvas id="listingsOverTimeChart" height="' + canvasHeight + '"></canvas> ' +
		'   </div>' +
		'</div>';

	// avg price per month
	html += '' +
		'<div class="statContainer">' +
		'   <h3>Rent Prices Over Time</h3>' +
		'   <div class="advStatsChartWrapper">' +
		'       <canvas id="rentOverTimeChart" height="' + canvasHeight + '"></canvas> ' +
		'   </div>' +
		'</div>';

	// update stats container html
	statsContainer.html(html);
};

Statscream.createChart = function(ctx, type, datasetName, datasetLabels, dataSet){
	/*
	    Create chart using given canvas context obj and assorted options
	 */

	// set global graph options
	Chart.defaults.global.elements.rectangle.backgroundColor = 'rgba(60, 146, 202, 0.9)';

	// initialize graph
	var chart = new Chart(ctx, {
		type: type,
		data: {
			labels: datasetLabels,
			datasets: [{
				label: datasetName,
				data: dataSet,
				backgroundColor: [
					'rgba(60, 146, 202, 0.9)'
				],
				borderWidth: 1
			}]
		},
		options: {
			scales: {
				yAxes: [{
					ticks: {
						beginAtZero: true
					}
				}]
			}
		}
	});

	return chart;
};

Statscream.fetchChartData = function(apiUrl, dataSetName, chartWrapperSelector, chartType){
	/*
	    Query Calc and Metrics APIs to retrieve data w/ given params
	 */

	var labels = [];
	var data = [];

	// query API
	$.ajax({
		url: apiUrl,
		dataType: 'json',
		success: function(apiData){
			// check if API request was successful
			if(apiData.success)
			{
				// iterate over results
				for(i = 0;i < apiData.result.length;i++)
				{
					// split results into two arrays: one containing the keys and one containing the values
					labels.push(apiData.result[i][0].text());
					data.push(apiData.result[i][1].text());
				}

				// create chart
				var newChart = Statscream.createChart(
					chartWrapperSelector,
					chartType,
					dataSetName,
					labels,
					data
				);
			}
			else
			{
				var errorBot = new ErrorBot(2, 'Could not fetch graph data');
				errorBot.displayError();
				errorBot.logErrorToConsole();
			}
		},
		error: function(){
			var errorBot = new ErrorBot(2, 'Could not access CLData API');
			errorBot.displayError();
			errorBot.logErrorToConsole();
		}
	});
};

Statscream.loadGraphs = function(){
	/*
	    Load graphs, including their data, into view of the user
	 */

	// fetch data and update graphs
	var apiBaseUrl = '/CLTools/CLData/api/v1/';
	Statscream.fetchChartData(apiBaseUrl + 'calc/?m=count&f=location&o=desc&l=30', '# of listings', $('#locationPopularityChart'), 'bar');
	Statscream.fetchChartData(apiBaseUrl + 'metrics/?t=monthly', '# of listings', $('#listingsOverTimeChart'), 'line');
	Statscream.fetchChartData(apiBaseUrl + 'metrics/?f=price&t=daily&o=avg', 'Rent Price', $('#rentOverTimeChart'), 'line');
};

Statscream.startAdvancedStats = function(){
	/*
		Update view to provide user with advanced listing statistics
	 */

	// fade out all content within selector
	$('#contentWrapper').children(':not(#stats)').fadeOut();

	// expand stats section
	statsSection = $('#stats');
	statsWrapper = $('#statsWrapper');

	// save original width of stats wrapper for use when going back to basic stats
	statsWrapper.data('origWidth', statsWrapper.css('width'));

	statsSection.animate({
		width: '100%'
	}, 500, function(){
		statsWrapper.children().fadeOut(500, function(){
			// generate advanced stats html
			Statscream.initializeAdvancedStatsInView(statsWrapper);

			// load graph data
			Statscream.loadGraphs();

			// update URL
			window.location.hash = 'advanced';

			// add handler for basic stats button
			$('.basic').click(function(){
				Statscream.startBasicStats();
			});
		});
	});
};