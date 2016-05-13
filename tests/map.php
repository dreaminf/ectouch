<?php

$conn = mysql_connect('localhost', 'root', 'root') OR die('error.');
mysql_select_db('test_map');


//$sql = 'SELECT *, SQRT(POW(111.2 * (lat - 40.0844020000), 2) + POW(111.2 * (116.3483150000 - lng) * COS(lat / 57.3), 2)) AS distance FROM map HAVING distance < 25 ORDER BY distance;';
$sql = 'SELECT *, SQRT(POW(111.2 * (lat - 40.0844020000), 2) + POW(111.2 * (116.3483150000 - lng) * COS(lat / 57.3), 2)) AS distance FROM map ORDER BY distance;';

$result = mysql_query($sql, $conn);

while ($row = mysql_fetch_array($result)) {
	echo '<pre>';
	var_dump($row);
	echo '</pre>';
}


