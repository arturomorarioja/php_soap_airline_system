<?php
/**
 * generate_wsdl.php
 *
 * Generates AirlineService.wsdl with a single operation: SearchFlights.
 * No external libraries are used. Uses DOMDocument and namespaced nodes.
 *
 * Contract:
 * - Operation: SearchFlights
 * - Request element:   tns:SearchFlightsRequest
 * - Response element:  tns:SearchFlightsResponse
 *
 * Types (tns):
 * - IataCode: pattern [A-Z]{3}
 * - CabinClass: ECONOMY | PREMIUM_ECONOMY | BUSINESS | FIRST
 * - PassengerCounts: adultCount (xs:int), childCount (xs:int, minOccurs=0), infantCount (xs:int, minOccurs=0)
 * - SearchFlightsRequest:
 *      origin (IataCode), destination (IataCode),
 *      departureDate (xs:date), returnDate (xs:date, minOccurs=0),
 *      passengers (PassengerCounts), cabin (CabinClass, minOccurs=0)
 * - Money: amount (xs:decimal), currency (xs:string)
 * - Flight:
 *      carrier (xs:string), flightNumber (xs:string),
 *      departDateTime (xs:dateTime), arriveDateTime (xs:dateTime),
 *      origin (IataCode), destination (IataCode), price (Money)
 * - SearchFlightsResponse:
 *      flights (tns:Flight) repeating list
 */

declare(strict_types=1);

$NS = [
    'wsdl' => 'http://schemas.xmlsoap.org/wsdl/',
    'soap' => 'http://schemas.xmlsoap.org/wsdl/soap/',
    'xs'   => 'http://www.w3.org/2001/XMLSchema',
    'tns'  => 'http://example.com/air/booking'
];

$serviceName   = 'AirlineService';
$portTypeName  = 'AirlinePortType';
$bindingName   = 'AirlineBinding';
$portName      = 'AirlinePort';
$soapAddress   = 'https://api.example.com/air/booking';
$soapAction    = $NS['tns'] . '/SearchFlights';
$outputFile    = __DIR__ . DIRECTORY_SEPARATOR . 'AirlineService.wsdl';

$doc = new DOMDocument('1.0', 'UTF-8');
$doc->formatOutput = true;

/* Helper: create namespaced element with optional text and attributes */
$E = function (DOMNode $parent, string $ns, string $qname, ?string $text = null, array $attrs = []) use ($doc) : DOMElement {
    $el = $doc->createElementNS($ns, $qname);
    if ($text !== null) {
        $el->appendChild($doc->createTextNode($text));
    }
    foreach ($attrs as $k => $v) {
        $el->setAttribute($k, $v);
    }
    $parent->appendChild($el);
    return $el;
};

/* <wsdl:definitions> */
$definitions = $doc->createElementNS($NS['wsdl'], 'wsdl:definitions');
$definitions->setAttribute('xmlns:wsdl', $NS['wsdl']);
$definitions->setAttribute('xmlns:soap', $NS['soap']);
$definitions->setAttribute('xmlns:xs',   $NS['xs']);
$definitions->setAttribute('xmlns:tns',  $NS['tns']);
$definitions->setAttribute('targetNamespace', $NS['tns']);
$definitions->setAttribute('name', $serviceName);
$doc->appendChild($definitions);

/* <wsdl:types> with one <xs:schema> (targetNamespace=tns) */
$types  = $E($definitions, $NS['wsdl'], 'wsdl:types');
$schema = $E($types, $NS['xs'], 'xs:schema', null, [
    'targetNamespace'    => $NS['tns'],
    'elementFormDefault' => 'qualified'
]);

/* ===== Simple types ===== */

/* IataCode: pattern [A-Z]{3} */
$stIata = $E($schema, $NS['xs'], 'xs:simpleType', null, ['name' => 'IataCode']);
$restIata = $E($stIata, $NS['xs'], 'xs:restriction', null, ['base' => 'xs:string']);
$E($restIata, $NS['xs'], 'xs:pattern', null, ['value' => '[A-Z]{3}']);

/* CabinClass enum */
$stCabin = $E($schema, $NS['xs'], 'xs:simpleType', null, ['name' => 'CabinClass']);
$restCabin = $E($stCabin, $NS['xs'], 'xs:restriction', null, ['base' => 'xs:string']);
foreach (['ECONOMY','PREMIUM_ECONOMY','BUSINESS','FIRST'] as $val) {
    $E($restCabin, $NS['xs'], 'xs:enumeration', null, ['value' => $val]);
}

/* ===== Complex types ===== */

/* PassengerCounts */
$ctPax = $E($schema, $NS['xs'], 'xs:complexType', null, ['name' => 'PassengerCounts']);
$seqPax = $E($ctPax, $NS['xs'], 'xs:sequence');
$E($seqPax, $NS['xs'], 'xs:element', null, ['name' => 'adultCount',  'type' => 'xs:int']);
$E($seqPax, $NS['xs'], 'xs:element', null, ['name' => 'childCount',  'type' => 'xs:int', 'minOccurs' => '0']);
$E($seqPax, $NS['xs'], 'xs:element', null, ['name' => 'infantCount', 'type' => 'xs:int', 'minOccurs' => '0']);

/* Money */
$ctMoney = $E($schema, $NS['xs'], 'xs:complexType', null, ['name' => 'Money']);
$seqMoney = $E($ctMoney, $NS['xs'], 'xs:sequence');
$E($seqMoney, $NS['xs'], 'xs:element', null, ['name' => 'amount',   'type' => 'xs:decimal']);
$E($seqMoney, $NS['xs'], 'xs:element', null, ['name' => 'currency', 'type' => 'xs:string']);

/* Flight */
$ctFlight = $E($schema, $NS['xs'], 'xs:complexType', null, ['name' => 'Flight']);
$seqFlight = $E($ctFlight, $NS['xs'], 'xs:sequence');
$E($seqFlight, $NS['xs'], 'xs:element', null, ['name' => 'carrier',         'type' => 'xs:string']);
$E($seqFlight, $NS['xs'], 'xs:element', null, ['name' => 'flightNumber',    'type' => 'xs:string']);
$E($seqFlight, $NS['xs'], 'xs:element', null, ['name' => 'departDateTime',  'type' => 'xs:dateTime']);
$E($seqFlight, $NS['xs'], 'xs:element', null, ['name' => 'arriveDateTime',  'type' => 'xs:dateTime']);
$E($seqFlight, $NS['xs'], 'xs:element', null, ['name' => 'origin',          'type' => 'tns:IataCode']);
$E($seqFlight, $NS['xs'], 'xs:element', null, ['name' => 'destination',     'type' => 'tns:IataCode']);
$E($seqFlight, $NS['xs'], 'xs:element', null, ['name' => 'price',           'type' => 'tns:Money']);

/* SearchFlightsRequest element (wrapped) */
$elReq = $E($schema, $NS['xs'], 'xs:element', null, ['name' => 'SearchFlightsRequest']);
$ctReq = $E($elReq, $NS['xs'], 'xs:complexType');
$seqReq = $E($ctReq, $NS['xs'], 'xs:sequence');
$E($seqReq, $NS['xs'], 'xs:element', null, ['name' => 'origin',        'type' => 'tns:IataCode']);
$E($seqReq, $NS['xs'], 'xs:element', null, ['name' => 'destination',   'type' => 'tns:IataCode']);
$E($seqReq, $NS['xs'], 'xs:element', null, ['name' => 'departureDate', 'type' => 'xs:date']);
$E($seqReq, $NS['xs'], 'xs:element', null, ['name' => 'returnDate',    'type' => 'xs:date', 'minOccurs' => '0']);
$E($seqReq, $NS['xs'], 'xs:element', null, ['name' => 'passengers',    'type' => 'tns:PassengerCounts']);
$E($seqReq, $NS['xs'], 'xs:element', null, ['name' => 'cabin',         'type' => 'tns:CabinClass', 'minOccurs' => '0']);

/* SearchFlightsResponse element (wrapped) */
$elRes = $E($schema, $NS['xs'], 'xs:element', null, ['name' => 'SearchFlightsResponse']);
$ctRes = $E($elRes, $NS['xs'], 'xs:complexType');
$seqRes = $E($ctRes, $NS['xs'], 'xs:sequence');
$E($seqRes, $NS['xs'], 'xs:element', null, [
    'name'      => 'flights',
    'type'      => 'tns:Flight',
    'minOccurs' => '0',
    'maxOccurs' => 'unbounded'
]);

/* ===== WSDL messages ===== */
$msgIn  = $E($definitions, $NS['wsdl'], 'wsdl:message', null, ['name' => 'SearchFlightsInput']);
$E($msgIn, $NS['wsdl'], 'wsdl:part', null, ['name' => 'parameters', 'element' => 'tns:SearchFlightsRequest']);

$msgOut = $E($definitions, $NS['wsdl'], 'wsdl:message', null, ['name' => 'SearchFlightsOutput']);
$E($msgOut, $NS['wsdl'], 'wsdl:part', null, ['name' => 'parameters', 'element' => 'tns:SearchFlightsResponse']);

/* ===== WSDL portType ===== */
$pt = $E($definitions, $NS['wsdl'], 'wsdl:portType', null, ['name' => $portTypeName]);
$op = $E($pt, $NS['wsdl'], 'wsdl:operation', null, ['name' => 'SearchFlights']);
$E($op, $NS['wsdl'], 'wsdl:input',  null, ['message' => 'tns:SearchFlightsInput']);
$E($op, $NS['wsdl'], 'wsdl:output', null, ['message' => 'tns:SearchFlightsOutput']);

/* ===== WSDL binding (SOAP document/literal) ===== */
$binding = $E($definitions, $NS['wsdl'], 'wsdl:binding', null, [
    'name' => $bindingName,
    'type' => 'tns:' . $portTypeName
]);
$E($binding, $NS['soap'], 'soap:binding', null, [
    'style'     => 'document',
    'transport' => 'http://schemas.xmlsoap.org/soap/http'
]);

$opb = $E($binding, $NS['wsdl'], 'wsdl:operation', null, ['name' => 'SearchFlights']);
$E($opb, $NS['soap'], 'soap:operation', null, ['soapAction' => $soapAction]);
$inb  = $E($opb, $NS['wsdl'], 'wsdl:input');
$E($inb, $NS['soap'], 'soap:body', null, ['use' => 'literal']);
$outb = $E($opb, $NS['wsdl'], 'wsdl:output');
$E($outb, $NS['soap'], 'soap:body', null, ['use' => 'literal']);

/* ===== WSDL service / port ===== */
$service = $E($definitions, $NS['wsdl'], 'wsdl:service', null, ['name' => $serviceName]);
$port    = $E($service, $NS['wsdl'], 'wsdl:port', null, [
    'name'    => $portName,
    'binding' => 'tns:' . $bindingName
]);
$E($port, $NS['soap'], 'soap:address', null, ['location' => $soapAddress]);

/* Write file */
if ($doc->save($outputFile) === false) {
    fwrite(STDERR, "Failed to write WSDL file.\n");
    exit(1);
}
echo "WSDL generated: {$outputFile}\n";
