<?php
$xml_in = "";
if (isset($argv)) {
	if (count($argv) > 0) {
		if (isset($argv[1])) {
			$handle = @fopen($argv[1], "r");
			if ($handle) {
				$xml_in = fread($handle, filesize($argv[1]));
				if ($xml_in == "") {
					echo "Error!  File was blank: " . $argv[1] . "\n";
				}
			} else {
				echo "Error!  Unable to read file: " . $argv[1] . "\n";
			}
		} else {
			echo "nmap2html - by tslocum <tslocum@gmail.com>\n";
			echo "Usage: php -f nmap2html.php nmap.xml>>output.html\n";
		}
		if ($xml_in == "") {
			die();
		}
	}
}
if ($xml_in == "" && isset($_FILES['nmap'])) {
	if ($_FILES['nmap']['name'] != "") {
		if (is_file($_FILES['nmap']['tmp_name']) && is_readable($_FILES['nmap']['tmp_name'])) {
			$handle = @fopen($_FILES['nmap']['tmp_name'], "r");
			if ($handle) {
				$xml_in = fread($handle, filesize($_FILES['nmap']['tmp_name']));
			}
		}
	}
}
?>
<!DOCTYPE HTML>
<html>
<head>
	<title>
		nmap2html
	</title>
</head>
<body>
<?php
if ($xml_in != "") {
	$xml = simplexml_load_string($xml_in);
	if (isset($xml["args"])) {
		echo '<b>' . $xml["args"] . '</b><hr size="1"><br>';
	}
} else {
?>
	<form action="?" method="post" enctype="multipart/form-data">
	<input type="file" name="nmap">
	<input type="submit" value="Process">
	</form>
<?php
}
?>
</body>
</html>