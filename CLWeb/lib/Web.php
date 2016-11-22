<?php
namespace CLTools\CLWeb;

	/**
	 *  CLTools
	 *  Author: Josh Carlson
	 *  Email: jcarlson(at)carlso(dot)net
	 */
	
	/*
	 *  Web.php - Primary web environment for CLWeb service
	 */
	
	class Web
	{
		public $subTitle = 'Home';
		public $content = '';
		
		public function __construct($content, $subTitle = 'Home')
		{
			// no current constraints
			$this->subTitle = $subTitle;
			$this->content = $content;
		}
		
		// OTHER FUNCTIONS
		public static function setHTTPHeaders($cacheTime = 3600, $contentType = '')
		{
			/*
			 *  Purpose: set necessary HTTP headers; usually includes security and cache headers
			 *
			 *  Params:
			 * 		* $cacheTime :: int :: amount of time to store data for, in seconds
			 * 		* $contentType :: string :: content-type header value to be used to specify what type of content is being served
			 *
			 *  Returns: NONE
			 *
			 * 	Addl. Info:
			 * 		* https://www.owasp.org/index.php/OWASP_Secure_Headers_Project#tab=Headers
			 */
			
			// normalize and validate cache time
			$cacheTime = (int) $cacheTime;
			if($cacheTime < 0)
			{
				throw new \Exception('invalid cache time supplied');
			}
			
			// SECURITY
			// HSTS
			header('strict-transport-security: max-age=86400');
			
			// X-Frame-Options
			header('X-Frame-Options: sameorigin');
			
			// Browser XSS Protection
			header('X-XSS-Protection: 1');
			
			// Disable Content Sniffing (why IE...)
			header('X-Content-Type-Options: nosniff');
			
			// CSP
			header('Content-Security-Policy: default-src https:; script-src https: \'unsafe-inline\' \'self\' https://maps.googleapis.com https://fonts.googleapis.com https://*.gstatic.com; style-src https: \'unsafe-inline\'');
			
			// Cross-Domain Policies
			header('X-Permitted-Cross-Domain-Policies: none');
			
			// CACHING
			// Cache-Control
			header('Cache-Control: max-age='.$cacheTime);
			
			// OTHER
			// Content-Type
			if(!empty($contentType))
			{
				header('Content-Type: application/json');
			}
		}
		
		public function generateHTML()
		{
			/*
			 *  Purpose: generate string of HTML to be used w/ view and displayed to the user
			 *
			 *  Params: NONE
			 *
			 *  Returns: string
			 */
			
			return '
				<!DOCTYPE html>
				<html lang="en">
				<head>
					<meta charset="UTF-8">
					<!-- <meta name=viewport content="width=640, initial-scale=1"> -->
					<meta name=viewport content="width=device-width, initial-scale=1">
				
					<title>CLWeb /:/ CLTools /:/ '.$this->subTitle.'</title>
				
					<link href="/CLTools/CLWeb/static/media/icons/favicon.ico" rel="icon" type="image/x-icon">
				
					<!-- css -->
					<link href="/CLTools/CLWeb/static/css/main.css" rel="stylesheet" media="all">
				</head>
				<body id="clweb">
					<div id="errorModal"></div>
					<header>
						<div>
							<a href="/CLTools/CLWeb/" title="CLWeb Home">
								<img src="/CLTools/CLWeb/static/media/icons/buildings.png" title="Welcome to CLWeb!" alt="Logo for CLWeb">
								<h1>CLTools <span class="accent">::</span> CLWeb</h1>
							</a>
						</div>
					</header>
					<main>
						<article>
							<section>
								<div id="contentWrapper">
									'.$this->content.'
								</div>
							</section>
						</article>
					</main>
					<footer>
						<div class="sectionWrapper">
							<div class="linkWrapper">
								<a href="https://github.com/magneticstain/CLTools" target="_blank" title="CLTools GitHub Project">Project Home</a>//<a href="https://opensource.org/licenses/MIT" target="_blank" title="MIT License Information">MIT License</a>
							</div>
						</div>
					</footer>
				
					<!-- js -->
					<!-- frameworks -->
					<script src="/CLTools/CLWeb/static/js/jquery-3.1.1.min.js" rel="script"></script>
					<!-- plugins -->
					<script async src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBM_drWAJQL9PqXD_IGMrjv-zKg4Yu12oY" rel="script"></script>
					<script async src="/CLTools/CLWeb/static/js/Chart.bundle.min.js" rel="script"></script>
					<!-- custom -->
					<script async src="/CLTools/CLWeb/static/js/errorbot.js" rel="script"></script>
					<script async src="/CLTools/CLWeb/static/js/datatron.js" rel="script"></script>
					<script async src="/CLTools/CLWeb/static/js/statscream.js" rel="script"></script>
					<script async src="/CLTools/CLWeb/static/js/main.js" rel="script"></script>
				</body>
				</html>
			';
		}
		
		public function __toString()
		{
			// Overload of toString function in order to generate HTML when treated as a string
			
			return $this->generateHTML();
		}
	}

?>