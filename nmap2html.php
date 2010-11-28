<?php
# nmap2html
# https://github.com/tslocum/nmap2html
$xml_in = "";
$opts_showclosed = false;
$opts_showfiltered = false;
if (isset($argv)) {
	if (count($argv) > 0) {
		if (isset($argv[1])) {
			foreach ($argv as $arg) {
				if (strtolower($arg) == "--showclosed") {
					$opts_showclosed = true;
				} else if (strtolower($arg) == "--showfiltered") {
					$opts_showfiltered = true;
				}
			}
			$handle = @fopen(end($argv), "r");
			if ($handle) {
				$xml_in = fread($handle, filesize(end($argv)));
				$xml_original = end($argv);
				if ($xml_in == "") {
					echo "Error!  File was blank: " . end($argv) . "\n";
				}
			} else {
				echo "Error!  Unable to read file: " . end($argv) . "\n";
			}
		} else {
			echo "nmap2html - by tslocum <tslocum@gmail.com>\n";
			echo "Usage: php nmap2html.php [options] nmap.xml>>output.html\n";
			echo "Options: --showclosed --showfiltered\n";
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
				if (isset($_POST['showclosed'])) {
					$opts_showclosed = true;
				}
				if (isset($_POST['showfiltered'])) {
					$opts_showfiltered = true;
				}
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
					if ($port->state["state"] == "open" || ($port->state["state"] == "closed" && $opts_showclosed) || ($port->state["state"] == "filtered" && $opts_showfiltered)) {
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
	<label for="showclosed">show closed ports:</label> <input type="checkbox" name="showclosed"><br>
	<label for="showfiltered">show filtered ports:</label> <input type="checkbox" name="showfiltered"><br>
	<label for="nmap">nmap XML file:</label> <input type="file" name="nmap"> <input type="submit" value="Process">
	</legend>
	</form>
<?php
}
?>
</body>
</html>