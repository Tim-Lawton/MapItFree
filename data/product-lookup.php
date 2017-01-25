<?php
$servername = "localhost";
$username = "";
$password = "";

// Create connection
$conn = mysqli_connect($servername, $username, $password, 'maps4fre_products');

// Check connection
if (!$conn) {
	die("Connection failed: " . mysqli_connect_error());
}


$local = $_REQUEST['local'];
$town = $_REQUEST['town'];
$county = $_REQUEST['county'];


$local = str_replace('"', "", $local);
$town = str_replace('"', "", $town);
$county = str_replace('"', "", $county);


if($local){

	$result = mysqli_query($conn, "SELECT * FROM products WHERE (`name` LIKE '%".$local."%')");

	if($result->num_rows == 0){


		$result = mysqli_query($conn, "SELECT * FROM products WHERE (`name` LIKE '%".$town."%')");


		if($result->num_rows == 0){

			$result = mysqli_query($conn, "SELECT * FROM products WHERE (`name` LIKE '%".$county."%')");
		}
	}
}



if($result){

	while($row = $result->fetch_assoc()){

        ?>

	<article>
		<figure>
			<a href="https://dash4it.co.uk/<?=$row['url_path'];?>?utm_source=Maps4Free&utm_medium=Product%20Bar&utm_campaign=Products"><img src="https://dash4it.co.uk/media/catalog/product/<?=$row['thumbnail'];?>" /></a>
		</figure>
		<h3><a href="https://dash4it.co.uk/<?=$row['url_path'];?>"><?=$row['name'];?></a></h3>
		<span class="price">&pound;<?=number_format($row['price'], 2);?></span>
		<a class="buy-now" href="https://dash4it.co.uk/<?=$row['url_path'];?>">Buy Now</a>
	</article>

	<?php
}

$result->free();
}

$conn->close();