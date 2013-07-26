<?php

//TODO check parameters first
$fileName = $argv[1];
$offset = $argv[2];

function logMessage($message) {
    echo $message . PHP_EOL;
}

logMessage("Analyzing file {$fileName}");
logMessage("Beacon offset is {$offset}");

logMessage("Reading file contents..");
$lines = array();
$handle = fopen($fileName, "r");
if ($handle) {
    while (($line = fgets($handle, 4096)) !== false) {
        $parts = explode(' # ', $line);
        $lineNumber = $parts[0];
        $lineContent = trim($parts[1]);
        $lines[$lineNumber] = $lineContent;
    }
    if (!feof($handle)) {
        echo "Fehler: unerwarteter fgets() Fehlschlag\n";
    }
    fclose($handle);
}

logMessage("Sorting lines..");
ksort($lines);

logMessage("Extracting beacon..");
$content = implode(' ', $lines);
$words = explode(' ', $content);
$nrOfWords = count($words);
$candidates = array();
$beacons = array();
for ($i=$offset+1; $i < $nrOfWords; $i++) {
    $currentWord = $words[$i];
    $offsetWordBefore = $words[$i - $offset - 1];

    if (array_key_exists($currentWord, $candidates) == false) {
        // we have never seen this word before
        if ($offsetWordBefore == $currentWord) {
            $candidates[$currentWord] = true;
			$beacons[$currentWord] = $i - $offset - 1;
        }
        else {
            $candidates[$offsetWordBefore] = false;
			unset($beacons[$offsetWordBefore]);
        }
    }
    else  {
        // we have already seen this word before
        if ($candidates[$currentWord] == true) {
            // this is still a candidate for the beacon
            if ($offsetWordBefore != $currentWord) {
                $candidates[$currentWord] = false;
                $candidates[$offsetWordBefore] = false;
				unset($beacons[$currentWord]);
				unset($beacons[$offsetWordBefore]);
            }
        }
    }
}

$nrOfBeacons = count($beacons);
logMessage("Found $nrOfBeacons number of beacons");
foreach ($beacons as $beacon => $firstPosition) {
	$secretText = '';
	for($i = $firstPosition+1; $i < $nrOfWords; $i+=$offset+1) {
		$secretText .= " {$words[$i]}";
	}
	$secretText = trim($secretText);
	logMessage("Secret text for beacon $beacon is '$secretText'");
}
?>
