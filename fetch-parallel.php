<?php

require __DIR__ . '/vendor/autoload.php';

// the output file
$output = gzopen(__DIR__ . '/items.json.gz', 'w');

// the HTTP client
$client = new GuzzleHttp\Client();

// the pages as chunks
$chunks = array_chunk(range(1, 100000), 10); // reduce this if the API is fast enough

foreach ($chunks as $index => $pages) {
    printf("Fetching chunk %d\n", $index);

    // create the requests
    $requests = array_map(function($page) use ($client) {
        return $client->createRequest('GET', 'http://api.figshare.com/v1/articles', [
            'future' => true,
            'headers' => [
                'User-Agent' => 'figshare-mirror https://github.com/hubgit/figshare-mirror/',
                'Accept'     => 'application/json',
            ],
            'query' => ['page' => $page]
        ]);
    }, $pages);

    // send the requests
    \GuzzleHttp\Pool::send($client, $requests, [
        'complete' => function (\GuzzleHttp\Event\CompleteEvent $event) use ($output) {
            $data = $event->getResponse()->json();

            printf("%d %s\n", $data['count'], $event->getRequest()->getUrl());

            foreach ($data['items'] as $item) {
                // output one item per line
                gzwrite($output, json_encode($item) . "\n");
            }
        },
        'error' => function (\GuzzleHttp\Event\ErrorEvent $event) {
            print 'Request failed: ' . $event->getRequest()->getUrl() . "\n";
            print $event->getException();
        }
    ]);
}

gzclose($output);
