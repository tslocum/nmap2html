<?php
$xml_in = "";
if (isset($argv)) {
	if (count($argv) > 0) {
		if (isset($argv[1])) {
			$handle = @fopen($argv[1], "r");
			if ($handle) {
				$xml_in = fread($handle, filesize($argv[1]));
				$xml_original = $argv[1];
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
				$xml_original = $_FILES['nmap']['name'];
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
	echo "nmap2html - " . $xml_original. "\n";
	if (isset($xml["args"])) {
		echo "<br><b>" . $xml["args"] . "</b>\n";
	}
	if (isset($xml->runstats->hosts["total"]) && isset($xml->runstats->hosts["up"]) && isset($xml->runstats->hosts["down"])) {
		echo "<br>" . $xml->runstats->hosts["total"]. " hosts in file [" . $xml->runstats->hosts["up"] . " up/" . $xml->runstats->hosts["down"] . " down]\n";
	}
	if (isset($xml["startstr"]) && isset($xml->runstats->finished["timestr"])) {
		echo "<br>Started <u>" . $xml["startstr"] . "</u>, Finished <u>" . $xml->runstats->finished["timestr"] . "</u>\n";
	}
	echo "<br>-------<br>\n";
	foreach ($xml as $key => $value) {
		if ($key == "host") {
			if ($value->ports != "") {
				echo "<b>" . $value->address["addr"] . "</b> \"" . $value->hostnames->hostname["name"] . "\" [" . $value->address["addrtype"] . "]<br>\n";
				$ports_displayed = 0;
				foreach ($value->ports->port as $port) {
					if ($port->state["state"] == "open") {
						echo $port["protocol"] . " " . $port["portid"] . " " . $port->state["state"] . " [" . $port->state["reason"] . "] " . $port->service["name"] . " (" . $port->service["product"] . ")<br>\n";
						$ports_displayed++;
					}
				}
				if ($ports_displayed == 0) {
					echo "No interesting ports.<br>\n";
				}
				echo "-------<br>\n";
			}
		}
	}
} else {
?>
	<form action="?" method="post" enctype="multipart/form-data">
	<fieldset style="display: inline;">
	<legend>nmap2html</legend>
	<label for="nmap">nmap XML file:</label> <input type="file" name="nmap"> <input type="submit" value="Process">
	</legend>
	</form>
<?php
}
?>
</body>
</html>