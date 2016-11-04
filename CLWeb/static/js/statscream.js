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

Statscream.loadStats = function()
{
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

// ADVANCED STATS
Statscream.initializeAdvancedStatsInView = function(statsContainer){
	/*
	    Initialize advanced stats headings and wrappers in preparation for graphing of data
	 */

	// the new stats will be appended to the html currently within the container
	var html = '';
	var canvasHeight = 128;

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

	$.ajax({
		url: apiUrl,
		dataType: 'json',
		success: function(apiData){
			// check if API req was successful
			if(apiData.success)
			{
				// iterate over results
				for(i = 0;i < apiData.result.length;i++)
				{
					// split results into two arrays: one containing the keys and one containing the values
					labels.push(apiData.result[i][0]);
					data.push(apiData.result[i][1]);
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

	// initialize each graph
	// var locPopChart = Statscream.createGraph(, 'bar');
	// var listingsOverTimeChart = Statscream.createGraph($('#listingsOverTimeChart'), 'line');
	// var rentOverTimeChart = Statscream.createGraph($('#rentOverTimeChart'), 'line');

	// fetch data and update graphs
	var apiBaseUrl = '/CLTools/CLData/api/v1/';
	Statscream.fetchChartData(apiBaseUrl + 'calc/?m=count&f=location&o=desc&l=30', '# of listings', $('#locationPopularityChart'), 'bar');
	Statscream.fetchChartData(apiBaseUrl + 'metrics/?t=monthly', '# of listings', $('#listingsOverTimeChart'), 'line');
	Statscream.fetchChartData(apiBaseUrl + 'metrics/?f=price&t=daily&o=avg', 'Rent Price', $('#rentOverTimeChart'), 'line');
};

Statscream.startAdvancedStats = function(contentSelector){
	/*
		Update view to provide user with advanced listing statistics
	 */

	// fade out all content within selector
	contentSelector.children(':not(#stats)').fadeOut();

	// expand stats section
	statsSection = $('#stats');
	statsWrapper = $('#statsWrapper');
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
		});
	});
};