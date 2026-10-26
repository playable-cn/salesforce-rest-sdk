# CHANGELOG
##v2.2.0 (2026-10-26)
[CHANGE] require PHP ^8.2, Guzzle 7 and psr/http-message ^1.1|^2.0; widen symfony / doctrine / psr/log / ramsey/uuid constraints
[CHANGE] CsvStream no longer implements StreamInterface (its read() / getContents() return CSV rows, which psr/http-message 2.0 does not allow); Bulk\Client::getResult() now returns StreamInterface|CsvStream
[FIX] use GuzzleHttp\Utils::chooseHandler() instead of the removed Guzzle 6 function
[FIX] invalid "\T" escape in the SObject date regex
[FIX] explicit nullable parameter types (implicit nullable is deprecated since PHP 8.4)
[FIX] add return types to CountResult ArrayAccess / Iterator / Countable methods
[CHANGE] update tests for PHPUnit 10/11

##v2.0.5 (2026-06-25)
[FIX] add grant type "client_credentials" for TokenOAuthProvider

##v2.0.4 (2026-06-25)
[FIX] add grant type "client_credentials" for OAuthProvider

##v2.0.3 (2021-02-09)
[FIX] fix callback call

##v2.0.2 (2021-02-07)
[NEW] add token reauth callback for fixed-token grant type

##v2.0.1 (2019-04-26)
[NEW] add fixed-token support with AuthProvider

##init (2020-04-08)
[INIT] fork from https://github.com/advisors-excel-llc/salesforce-rest-sdk
