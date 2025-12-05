<?php
declare(strict_types=1);

/**
 * AirlineService
 *
 * Implements the SearchFlights operation defined in AirlineService.wsdl.
 */
class AirlineService
{
    /**
     * SearchFlights operation.
     *
     * For document/literal wrapped, SoapServer will pass a single object
     * whose properties correspond to the child elements of SearchFlightsRequest:
     *
     *   - origin          (IataCode, string)
     *   - destination     (IataCode, string)
     *   - departureDate   (xs:date)
     *   - returnDate      (xs:date, optional)
     *   - passengers      (PassengerCounts complex type)
     *   - cabin           (CabinClass, optional)
     *
     * @param stdClass $request
     * @return array
     */
    public function SearchFlights($request): array
    {
        // Basic validation and extraction from the request object
        $origin        = isset($request->origin) ? (string)$request->origin : '';
        $destination   = isset($request->destination) ? (string)$request->destination : '';
        $departureDate = isset($request->departureDate) ? (string)$request->departureDate : '';
        $returnDate    = isset($request->returnDate) ? (string)$request->returnDate : null;
        $cabin         = isset($request->cabin) ? (string)$request->cabin : 'ECONOMY';

        $adultCount  = isset($request->passengers->adultCount) ? (int)$request->passengers->adultCount : 1;
        $childCount  = isset($request->passengers->childCount) ? (int)$request->passengers->childCount : 0;
        $infantCount = isset($request->passengers->infantCount) ? (int)$request->passengers->infantCount : 0;

        // Simple guard: if origin or destination are missing, return no flights
        if ($origin === '' || $destination === '' || $departureDate === '') {
            return [
                'flights' => [],
            ];
        }

        // For demo purposes, build a small static list of flights
        // In a real system, this would query an external GDS or database.
        $flights = [];

        // Helper to compose an ISO 8601 datetime from a date + time
        $buildDateTime = function (string $date, string $time) {
            return $date . 'T' . $time;
        };

        // Outbound flight
        $flights[] = [
            'carrier'        => 'Demo Air',
            'flightNumber'   => 'DA123',
            'departDateTime' => $buildDateTime($departureDate, '09:00:00'),
            'arriveDateTime' => $buildDateTime($departureDate, '12:30:00'),
            'origin'         => $origin,
            'destination'    => $destination,
            'price'          => [
                'amount'   => 199.99,
                'currency' => 'EUR',
            ],
        ];

        // Return flight only if returnDate is provided
        if (!empty($returnDate)) {
            $flights[] = [
                'carrier'        => 'Demo Air',
                'flightNumber'   => 'DA124',
                'departDateTime' => $buildDateTime($returnDate, '17:00:00'),
                'arriveDateTime' => $buildDateTime($returnDate, '20:30:00'),
                'origin'         => $destination,
                'destination'    => $origin,
                'price'          => [
                    'amount'   => 219.50,
                    'currency' => 'EUR',
                ],
            ];
        }

        // You might choose to adjust prices based on cabin or passenger counts here.
        // This is left simple on purpose for teaching.

        return [
            // This key must match the element name in SearchFlightsResponse:
            // <SearchFlightsResponse><flights>...</flights></SearchFlightsResponse>
            'flights' => $flights,
        ];
    }
}
