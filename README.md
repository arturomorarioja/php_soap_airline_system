# Airline System
SOAP example. It simulates the search flights operation for an airline system. Functionalities
- Creation of the WSDL document `AirlineService.wsdl`
- Implementation of a SOAP server and client that handle the operation `SearchFlights`

## Usage

### With Docker
1. Build the image: `docker compose build`
2. Generate the WSDL file: `docker compose run --rm cli generate_wsdl.php`. It will generate `AirlineService.wsdl`
3. Start the SOAP server: `docker compose up server`. It will be reachable at `http://localhost:8080`
4. In a different terminal, run the client: `docker compose run --rm cli client.php`. It will show the hardcoded flights, the XML request, and the XML response
    
### As a PHP project
Preconditions:
- Make sure that the line `extension=soap` is uncommented in the configuration file `php.ini`

WSDL generation:
- Run `php generate_wsdl.php`. It will generate `AirlineService.wsdl`

Starting the SOAP server:
- Run `php -S 127.0.0.1:8080`. If a different IP or port is desired, edit it in `classes/Config.php`

Running the SOAP client:
1. In a different terminal, execute the client request `SearchFlights`: `php client.php`. It will show the hardcoded flights, the XML request, and the XML response

### Postman
The folder `postman` contains a Postman collection and environment to test both operations. Notice the following:
- The transport media defined in the WSDL is HTTP, thus they are HTTP requests
- The HTTP verb is POST, as usual in SOAP requests over HTTP
- A `SOAPAction` header is necessary:
    - `SOAPAction:"http://api.example.com/air/booking"`
- The body of the request must include the expected raw XML:
    ```XML
    <?xml version="1.0" encoding="UTF-8"?>
    <SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="http://example.com/air/booking">
    <SOAP-ENV:Body>
        <ns1:SearchFlightsRequest>
        <ns1:origin>CPH</ns1:origin>
        <ns1:destination>MAD</ns1:destination>
        <ns1:departureDate>2025-03-15</ns1:departureDate>
        <ns1:returnDate>2025-03-22</ns1:returnDate>
        <ns1:passengers>
            <ns1:adultCount>1</ns1:adultCount>
            <ns1:childCount>0</ns1:childCount>
            <ns1:infantCount>0</ns1:infantCount>
        </ns1:passengers>
        <ns1:cabin>ECONOMY</ns1:cabin>
        </ns1:SearchFlightsRequest>
    </SOAP-ENV:Body>
    </SOAP-ENV:Envelope>
    ```

## Tools
PHP8

## Author
ChatGPT 5.1, prompted by Arturo Mora-Rioja