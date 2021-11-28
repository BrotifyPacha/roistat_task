<?php

if (count($argv) < 2) {
    exit("Usage: php {$argv[0]} <data-file>");
}

$dataFile = $argv[1];
if (!file_exists($dataFile)) {
    exit("File \"{$dataFile}\" doesn't exist");
}


$dataFileObject = new SplFileObject($dataFile);

$crawlers = [
    'Google' => 'Googlebot',
    'Bing' => 'bingbot',
    'Baidu' => 'baidu',
    'Yandex' => 'YandexBot',
];

$report = [
    'views' => 0,
    'urls' => 0,
    'traffic' => 0,
    'crawlers' => [],
    'statusCodes' => [],
];

$uniqueUrls = [];
$httpStatusesCount = [];
$crawlersCount = [];
foreach ($crawlers as $name => $value) {
    $crawlersCount[$name] = 0;
}


$lineCounter = 0;
while (!$dataFileObject->eof()) {
    $line = $dataFileObject->fgetcsv(' ');
    $lineCounter++;

    if (count($line) > 1) $report['views']++;
    else continue;

    $request = explode(' ', $line[5]);
    $requestMethod = $request[0];
    $requestUri = $request[1];

    $httpStatus = $line[6];
    $transferedBytes = $line[7];
    $url = str_replace('/index.php', '', $line[8]) . $requestUri;
    $userAgentInfo = $line[9];


    if ($requestMethod != 'GET')
        $report['traffic'] += $transferedBytes;


    if (!in_array($url, $uniqueUrls)) $uniqueUrls[] = $url;


    if (!array_key_exists($httpStatus, $httpStatusesCount)) {
        $httpStatusesCount[$httpStatus] = 1;
    } else {
        $httpStatusesCount[$httpStatus]++;
    }


    foreach ($crawlers as $name => $userAgent) {
        if (str_contains($userAgentInfo, $userAgent)) {
            $crawlersCount[$name]++;
        }
    }

}

$report['urls'] = count($uniqueUrls);
$report['statusCodes'] = $httpStatusesCount;
$report['crawlers'] = $crawlersCount;

$jsonReport = json_encode($report, JSON_PRETTY_PRINT);

echo $jsonReport . PHP_EOL;

