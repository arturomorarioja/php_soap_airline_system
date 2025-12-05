<?php
declare(strict_types=1);

/**
 * Pretty print an XML string.
 */
function pretty_print_xml(string $xml): string
{
    $dom = new DOMDocument('1.0', 'UTF-8');
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = true;

    // Suppress warnings in case the XML is not well-formed
    if (@$dom->loadXML($xml) === false) {
        return $xml;
    }

    return $dom->saveXML();
}

// Resolve SOAP host/port from environment (used in Docker)
$host = getenv('SOAP_SERVER_HOST') ?: '127.0.0.1';
$port = getenv('SOAP_SERVER_PORT') ?: '8080';

$baseUrl = 'http://' . $host . ':' . $port;

// WSDL served by the built-in PHP server
$wsdlUrl     = $baseUrl . '/AirlineService.wsdl';
$endpointUrl = $baseUrl . '/server.php';

$options = [
    'trace'      => true,
    'exceptions' => true,
    'cache_wsdl' => WSDL_CACHE_NONE,
    'location'   => $endpointUrl,
];

try {
    $client = new SoapClient($wsdlUrl, $options);

    $requestPayload = [
        'origin'        => 'CPH',
        'destination'   => 'MAD',
        'departureDate' => '2025-03-15',
        'returnDate'    => '2025-03-22',
        'passengers'    => [
            'adultCount'  => 1,
            'childCount'  => 0,
            'infantCount' => 0,
        ],
        'cabin'         => 'ECONOMY',
    ];

    // For document/literal-wrapped with part name "parameters",
    // pass the contents of SearchFlightsRequest directly.
    $result = $client->__soapCall('SearchFlights', [
        $requestPayload,
    ]);
    // Alternatively:
    // $result = $client->SearchFlights($requestPayload);


    echo "SearchFlights result\n";
    echo "====================\n\n";

    if (isset($result->flights)) {
        $flights = is_array($result->flights) ? $result->flights : [$result->flights];

        if (count($flights) === 0) {
            echo "No flights returned.\n\n";
        } else {
            foreach ($flights as $index => $flight) {
                echo 'Flight ' . ($index + 1) . ":\n";
                echo '  Carrier       : ' . ($flight->carrier ?? '') . "\n";
                echo '  Flight number : ' . ($flight->flightNumber ?? '') . "\n";
                echo '  Departure     : ' . ($flight->departDateTime ?? '') .
                     ' from ' . ($flight->origin ?? '') . "\n";
                echo '  Arrival       : ' . ($flight->arriveDateTime ?? '') .
                     ' at ' . ($flight->destination ?? '') . "\n";

                if (isset($flight->price)) {
                    echo '  Price         : ' .
                         ($flight->price->amount ?? '') . ' ' .
                         ($flight->price->currency ?? '') . "\n";
                }

                echo "\n";
            }
        }
    } else {
        echo "No flights element found in response.\n\n";
    }

    $lastRequest  = $client->__getLastRequest();
    $lastResponse = $client->__getLastResponse();

    echo "=== LAST REQUEST ===\n";
    echo pretty_print_xml($lastRequest) . "\n";

    echo "=== LAST RESPONSE ===\n";
    echo pretty_print_xml($lastResponse) . "\n";

} catch (SoapFault $e) {
    echo 'ERROR: ' . $e->getMessage() . "\n\n";

    if (isset($client)) {
        $lastRequest  = $client->__getLastRequest();
        $lastResponse = $client->__getLastResponse();

        echo "=== LAST REQUEST ===\n";
        echo $lastRequest !== null && $lastRequest !== ''
            ? pretty_print_xml($lastRequest) . "\n"
            : "(none)\n";

        echo "=== LAST RESPONSE ===\n";
        echo $lastResponse !== null && $lastResponse !== ''
            ? pretty_print_xml($lastResponse) . "\n"
            : "(none)\n";
    }
}
