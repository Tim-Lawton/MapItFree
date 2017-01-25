<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title></title>

<style>
  html, body  { height: 100%; margin: 0;}
	
  #map { height: 100%; margin: 0; border-top:1px #333 solid; position:relative; top:-52px;}
  .search {padding:5px;    margin-bottom: 20px; position:relative; z-index:1; float:right;}
	

</style>

</head>  
  
<body>


	   <div class="search search-form">
			<form action="/">
				<input type="text" name="search" id="location" placeholder="Enter Postcode or Location"/>
				<input type="submit" value="Search"/>
			</form>
		</div>
	
	
	    
	
<div id="map"></div>

<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script type="text/javascript" src="https://openspace.ordnancesurvey.co.uk/osmapapi/openspace.js?key=4673E3A74BBA3866E0530C6CA40A7AC2"></script> 
<script src="js/scripts.js"></script>
<script src="js/main.js"></script>
	
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-90755061-1', 'auto');
  ga('send', 'pageview');

</script>	

	<div class="products-holder"></div>

</body>
</html>