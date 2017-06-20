<?php declare(strict_types=1);

namespace RestSample\Tests;

use Goutte\Client;

class AppTest extends \PHPUnit\Framework\TestCase
{
    use \RestSample\Tests\SlimControllerTestTrait;

    public function __construct()
    {
        parent::__construct();

        $this->client = new Client();

        // Sets the Content-Type header required by JSON API spec
        $this->client->setClient(new \GuzzleHttp\Client([
            'headers' => ['Content-Type' => 'application/vnd.api+json']
        ]));
    }

    // Resets the movieratings table on each test
    public function setUp()
    {
        (new PdoModels\MovieratingsModelTest)->setUp();
    }

    /**
     * @test
     */
    public function request_without_content_type_returns_400()
    {
        // Removes the Content-Type header
        $this->client->setClient(new \GuzzleHttp\Client());

        // Mocks expected response
        $expected = '{"errors":{"detail":"Bad Request"}}';

        // Sends the request
        $this->client->request('GET', 'http://localhost:8080');
        $response = $this->client->getResponse();

        // Compares page contents
        $actual = $response->getContent();
        $this->assertEquals($expected, $actual);

        // Compares Content-Type header
        $expected = 'application/vnd.api+json';
        $actual = $response->getHeaders()['Content-Type'][0];
        $this->assertEquals($expected, $actual);

        // Compares HTTP status code
        $expected = 400;
        $actual = $response->getStatus();
        $this->assertEquals($expected, $actual);

        // Resets the Content-Type header
        $this->client->setClient(new \GuzzleHttp\Client([
            'headers' => ['Content-Type' => 'application/vnd.api+json']
        ]));
    }

    /**
     * @test
     * @todo Modifies default Slim 404 to meet JSON API spec
     */
    public function request_undefined_endpoint_returns_404()
    {
        // Sends the request
        $this->client->request('GET', 'http://localhost:8080/null');
        $response = $this->client->getResponse();

        // Compares HTTP status code
        $expected = 404;
        $actual = $response->getStatus();
        $this->assertEquals($expected, $actual);
    }

    /** Tests \movies GET endpoint **/

    /**
     * @test
     */
    public function GET_invalid_movies_endpoint_returns_404()
    {
        // Mocks expected response
        $expected = '{"errors":{"detail":"No Movie for ID 9"}}';

        // Sends the request
        $this->client->request('GET', 'http://localhost:8080/movies/null');
        $response = $this->client->getResponse();

        // Compares page contents
        $actual = $response->getContent();
        $this->assertEquals($expected, $actual);

        // Compares Content-Type header
        $expected = 'application/vnd.api+json';
        $actual = $response->getHeaders()['Content-Type'][0];
        $this->assertEquals($expected, $actual);

        // Compares HTTP status code
        $expected = 404;
        $actual = $response->getStatus();
        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function GET_undefined_movies_endpoint_returns_404()
    {
        // Mocks expected response
        $expected = '{"errors":{"detail":"No Movie for ID 9"}}';

        // Sends the request
        $this->client->request('GET', 'http://localhost:8080/movies/9');
        $response = $this->client->getResponse();

        // Compares page contents
        $actual = $response->getContent();
        $this->assertEquals($expected, $actual);

        // Compares Content-Type header
        $expected = 'application/vnd.api+json';
        $actual = $response->getHeaders()['Content-Type'][0];
        $this->assertEquals($expected, $actual);

        // Compares HTTP status code
        $expected = 404;
        $actual = $response->getStatus();
        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function GET_movies_endpoint_returns_data()
    {
        // Mocks expected response
        $expected = json_encode(self::$MOVIES_GET);

        // Sends the request
        $this->client->request('GET', 'http://localhost:8080/movies/1');
        $response = $this->client->getResponse();

        // Compares page contents
        $actual = $response->getContent();
        $this->assertEquals($expected, $actual);

        // Compares Content-Type header
        $expected = 'application/vnd.api+json';
        $actual = $response->getHeaders()['Content-Type'][0];
        $this->assertEquals($expected, $actual);

        // Compares HTTP status code
        $expected = 200;
        $actual = $response->getStatus();
        $this->assertEquals($expected, $actual);
    }

    /** Tests \movieratings GET endpoint **/

    /**
     * @test
     */
    public function testGetRequestToInvalidMovieratingsEndpointReturnsError()
    {
        // Sends the request
        $this->client->request('GET', 'http://localhost:8080/movieratings/9');
        $response = $this->client->getResponse();

        // Compares page contents
        $expected = '{"errors":{"detail":"No MovieRating for Movie ID 9"}}';
        $actual = $response->getContent();
        $this->assertEquals($expected, $actual);

        // Compares Content-Type header
        $expected = 'application/vnd.api+json';
        $actual = $response->getHeaders()['Content-Type'][0];
        $this->assertEquals($expected, $actual);

        // Compares HTTP status code
        $expected = 404;
        $actual = $response->getStatus();
        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function testGetRequestToValidMovieratingsEndpointReturnsData()
    {
        // Sends the request
        $this->client->request('GET', 'http://localhost:8080/movieratings/1');
        $response = $this->client->getResponse();

        // Compares page contents
        $expected = '{"data":[{"type":"movieratings","id":"1","attributes":{"average_rating":"4","total_ratings":"3"},"relationships":{"movies":{"data":{"type":"movies","id":"1"}}}}]}';
        $actual = $response->getContent();
        $this->assertEquals($expected, $actual);

        // Compares Content-Type header
        $expected = 'application/vnd.api+json';
        $actual = $response->getHeaders()['Content-Type'][0];
        $this->assertEquals($expected, $actual);

        // Compares HTTP status code
        $expected = 200;
        $actual = $response->getStatus();
        $this->assertEquals($expected, $actual);
    }

    /** Tests \movieratings POST endpoint **/

    /**
     * @test
     */
    public function testInvalidPostRequestToMovieratingsEndpointReturnsError()
    {
        $expected = '{"errors":{"detail":"Bad Request"}}';

        // Sends the request with non-JSON POST data
        $this->client->request('POST', 'http://localhost:8080/movieratings', ['movie_id' => '2', 'average_rating' => '5', 'total_ratings' => '1']);
        $response = $this->client->getResponse();

        // Compares page contents
        $actual = $response->getContent();
        $this->assertEquals($expected, $actual);

        // @todo Verifies that post data is JSON-formatted
        // $this->client->request('POST', 'http://localhost:8080/movieratings', ["data" => ["type" => "movieratings", "attributes" => ["average_rating" => "5", "total_ratings" => "1"], "relationships" => ["movies" => ["data" => ["type" => "movies", "id" => "2"]]]]]);
        // Compares page contents
        // $actual = ($this->client->getResponse())->getContent();
        // $this->assertEquals($expected, $actual);

        // Sends the request without required root member
        $this->client->request('POST', 'http://localhost:8080/movieratings', [], [], [], '{"type":"movieratings","attributes":{"average_rating":"5","total_ratings":"1"},"relationships":{"movies":{"data":{"type":"movies","id":"2"}}}}');
        // Compares page contents
        $actual = ($this->client->getResponse())->getContent();
        $this->assertEquals($expected, $actual);

        // Sends the request as array of data items
        $this->client->request('POST', 'http://localhost:8080/movieratings', [], [], [], '{"data":[{"type":"movieratings","attributes":{"average_rating":"5","total_ratings":"1"},"relationships":{"movies":{"data":{"type":"movies","id":"2"}}}}]}');
        // Compares page contents
        $actual = ($this->client->getResponse())->getContent();
        $this->assertEquals($expected, $actual);

        // Sends the request without movie data
        $this->client->request('POST', 'http://localhost:8080/movieratings', [], [], [], '{"data":{"type":"movieratings","attributes":{"average_rating":"5","total_ratings":"1"}}}');
        // Compares page contents
        $actual = ($this->client->getResponse())->getContent();
        $this->assertEquals($expected, $actual);

        // Sends the request data without type parameter
        $this->client->request('POST', 'http://localhost:8080/movieratings', [], [], [], '{"data":{"attributes":{"average_rating":"5","total_ratings":"1"},"relationships":{"movies":{"data":{"type":"movies","id":"2"}}}}}');
        // Compares page contents
        $actual = ($this->client->getResponse())->getContent();
        $this->assertEquals($expected, $actual);

        // Sends the request data with invalid related type parameter
        $this->client->request('POST', 'http://localhost:8080/movieratings', [], [], [], '{"data":{"type":"movieratings","attributes":{"average_rating":"5","total_ratings":"1"},"relationships":{"movies":{"data":{"type":"movie","id":"2"}}}}}');
        // Compares page contents
        $actual = ($this->client->getResponse())->getContent();
        $this->assertEquals($expected, $actual);

        // Sends the request data with missing movie id
        $this->client->request('POST', 'http://localhost:8080/movieratings', [], [], [], '{"data":{"type":"movieratings","attributes":{"average_rating":"5","total_ratings":"1"},"relationships":{"movies":{"data":{"type":"movies"}}}}}');
        // Compares page contents
        $actual = ($this->client->getResponse())->getContent();
        $this->assertEquals($expected, $actual);

        // Sends the request data with invalid attribute
        $this->client->request('POST', 'http://localhost:8080/movieratings', [], [], [], '{"data":{"type":"movieratings","attributes":{"average_rating":"5 stars","total_ratings":"1"},"relationships":{"movies":{"data":{"type":"movies","id":"2"}}}}}');
        // Compares page contents
        $actual = ($this->client->getResponse())->getContent();
        $this->assertEquals($expected, $actual);

        // Sends the request data without required attribute
        $this->client->request('POST', 'http://localhost:8080/movieratings', [], [], [], '{"data":{"type":"movieratings","attributes":{"average_rating":"5"},"relationships":{"movies":{"data":{"type":"movies","id":"2"}}}}}');
        $response = $this->client->getResponse();

        // Compares page contents
        $actual = $response->getContent();
        $this->assertEquals($expected, $actual);

        // Compares Content-Type header
        $expected = 'application/vnd.api+json';
        $actual = $response->getHeaders()['Content-Type'][0];
        $this->assertEquals($expected, $actual);

        // Compares HTTP status code
        $expected = 400;
        $actual = $response->getStatus();
        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function testPostRequestToExistingMovieratingsEndpointReturnsError()
    {
        // Sends the request with JSON data in POST
        $this->client->request('POST', 'http://localhost:8080/movieratings', [], [], [], '{"data":{"type":"movieratings","attributes":{"average_rating":"5","total_ratings":"4"},"relationships":{"movies":{"data":{"type":"movies","id":"1"}}}}}');
        $response = $this->client->getResponse();

        // Compares page contents
        $expected = '{"errors":{"detail":"MovieRating already exists for Movie ID 1"}}';
        $actual = $response->getContent();
        $this->assertEquals($expected, $actual);

        // Compares Content-Type header
        $expected = 'application/vnd.api+json';
        $actual = $response->getHeaders()['Content-Type'][0];
        $this->assertEquals($expected, $actual);

        // Compares HTTP status code
        $expected = 409;
        $actual = $response->getStatus();
        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function testPostRequestToValidMovieratingsEndpointReturnsData()
    {
        // Resets auto increment on the movieratings table
        (new PdoModels\MovieratingsModelTest)->setUp();

        // Sends the request with JSON data in POST
        $this->client->request('POST', 'http://localhost:8080/movieratings', [], [], [], '{"data":{"type":"movieratings","attributes":{"average_rating":"5","total_ratings":"1"},"relationships":{"movies":{"data":{"type":"movies","id":"2"}}}}}');
        $response = $this->client->getResponse();

        // Compares page contents
        $expected = '{"data":[{"type":"movieratings","id":"2","attributes":{"average_rating":"5","total_ratings":"1"},"relationships":{"movies":{"data":{"type":"movies","id":"2"}}}}]}';
        $actual = $response->getContent();
        $this->assertEquals($expected, $actual);

        // Compares Content-Type header
        $expected = 'application/vnd.api+json';
        $actual = $response->getHeaders()['Content-Type'][0];
        $this->assertEquals($expected, $actual);

        // Compares HTTP status code
        $expected = 200;
        $actual = $response->getStatus();
        $this->assertEquals($expected, $actual);
    }

    /** Tests \movieratings PATCH endpoint **/

    /**
     * @test
     */
    public function testInvalidPatchRequestToMovieratingsEndpointReturnsError()
    {
        $expected = '{"errors":{"detail":"Bad Request"}}';

        // Sends request with non-JSON POST data
        $this->client->request('PATCH', 'http://localhost:8080/movieratings/1', ['movie_id' => '1', 'average_rating' => '5', 'total_ratings' => '4']);
        $response = $this->client->getResponse();

        // Compares page contents
        $actual = $response->getContent();
        $this->assertEquals($expected, $actual);

        // Sends request without root node
        $this->client->request('PATCH', 'http://localhost:8080/movieratings/1', ["type" => "movieratings", "attributes" => ["average_rating" => "5", "total_ratings" => "4"], "relationships" => ["movies" => ["data" => ["type" => "movies", "id" => "1"]]]]);
        // Compares page contents
        $actual = ($this->client->getResponse())->getContent();
        $this->assertEquals($expected, $actual);

        // Sends request without subroot node
        $this->client->request('PATCH', 'http://localhost:8080/movieratings/1', ["data" => ["type" => "movieratings", "attributes" => ["average_rating" => "5", "total_ratings" => "4"], "relationships" => ["movies" => []]]]);
        // Compares page contents
        $actual = ($this->client->getResponse())->getContent();
        $this->assertEquals($expected, $actual);

        // Sends request with incorrect datatype
        $this->client->request('PATCH', 'http://localhost:8080/movieratings/1', ["data" => ["type" => "movierating", "attributes" => ["average_rating" => "5", "total_ratings" => "4"], "relationships" => ["movies" => ["data" => ["type" => "movies", "id" => "1"]]]]]);
        // Compares page contents
        $actual = ($this->client->getResponse())->getContent();
        $this->assertEquals($expected, $actual);

        // Sends request with missing subdatatype
        $this->client->request('PATCH', 'http://localhost:8080/movieratings/1', ["data" => ["type" => "movieratings", "attributes" => ["average_rating" => "5", "total_ratings" => "4"], "relationships" => ["movies" => ["data" => ["id" => "1"]]]]]);
        // Compares page contents
        $actual = ($this->client->getResponse())->getContent();
        $this->assertEquals($expected, $actual);

        // Sends request with incorrect subdatatype id
        $this->client->request('PATCH', 'http://localhost:8080/movieratings/1', ["data" => ["type" => "movieratings", "attributes" => ["average_rating" => "5", "total_ratings" => "4"], "relationships" => ["movies" => ["data" => ["type" => "movies", "id" => "2"]]]]]);
        // Compares page contents
        $actual = ($this->client->getResponse())->getContent();
        $this->assertEquals($expected, $actual);

        // Sends request with invalid subdatatype id
        $this->client->request('PATCH', 'http://localhost:8080/movieratings/2spoopy', ["data" => ["type" => "movieratings", "attributes" => ["average_rating" => "5", "total_ratings" => "4"], "relationships" => ["movies" => ["data" => ["type" => "movies", "id" => "2spoopy"]]]]]);
        // Compares page contents
        $actual = ($this->client->getResponse())->getContent();
        $this->assertEquals($expected, $actual);

        // Sends request with zero-value attribute
        $this->client->request('PATCH', 'http://localhost:8080/movieratings/1', ["data" => ["type" => "movieratings", "attributes" => ["average_rating" => "5", "total_ratings" => 0], "relationships" => ["movies" => ["data" => ["type" => "movies", "id" => "1"]]]]]);
        $response = $this->client->getResponse();

        // Compares page contents
        $actual = $response->getContent();
        $this->assertEquals($expected, $actual);

        // Compares Content-Type header
        $expected = 'application/vnd.api+json';
        $actual = $response->getHeaders()['Content-Type'][0];
        $this->assertEquals($expected, $actual);

        // Compares HTTP status code
        $expected = 400;
        $actual = $response->getStatus();
        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function testPatchRequestToValidMovieratingsEndpointReturnsData()
    {
        // Sends the request with JSON data in PATCH
        $this->client->request('PATCH', 'http://localhost:8080/movieratings/1', ["data" => ["type" => "movieratings", "attributes" => ["average_rating" => "5", "total_ratings" => "4"], "relationships" => ["movies" => ["data" => ["type" => "movies", "id" => "1"]]]]]);
        $response = $this->client->getResponse();

        // Compares page contents
        $expected = '{"data":[{"type":"movieratings","id":"1","attributes":{"average_rating":"5","total_ratings":"4"},"relationships":{"movies":{"data":{"type":"movies","id":"1"}}}}]}';
        $actual = $response->getContent();
        $this->assertEquals($expected, $actual);

        // Compares Content-Type header
        $expected = 'application/vnd.api+json';
        $actual = $response->getHeaders()['Content-Type'][0];
        $this->assertEquals($expected, $actual);

        // Compares HTTP status code
        $expected = 200;
        $actual = $response->getStatus();
        $this->assertEquals($expected, $actual);
    }

    /** Tests \usermovieratings POST endpoint **/

    private const TEST_POST = [
        "data" => [
            "type" => "usermovieratings",
            "attributes" => [
                "rating" => "5"
            ],
            "relationships" => [
                "users" => [
                    "data" => [
                        "type" => "users",
                        "id" => "1"
                    ]
                ],
                "movies" => [
                    "data" => [
                        "type" => "movies",
                        "id" => "2"
                    ]
                ]
            ]
        ]
    ];

    /**
     * @test
     */
    public function POST_invalid_usermovieratings_endpoint_returns_405()
    {
        $expected = '{"errors":{"detail":"Not Allowed"}}';

        // Sends request with POST JSON data
        $this->client->request('POST', 'http://localhost:8080/usermovieratings/1/movies/2', self::TEST_POST);

        // Fetches app response
        $response = $this->client->getResponse();

        // Compares page contents
        $actual = $response->getContent();
        $this->assertEquals($expected, $actual);

        // Compares Content-Type header
        $expected = 'application/vnd.api+json';
        $actual = $response->getHeaders()['Content-Type'][0];
        $this->assertEquals($expected, $actual);

        // Compares HTTP status code
        $expected = 405;
        $actual = $response->getStatus();
        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function POST_usermovieratings_endpoint_returns_data()
    {
        // Resets the usermovieratings table
        (new PdoModels\UsermovieratingsModelTest)->setUp();

        // Mocks expected response
        $expected = '{"data":[{"type":"usermovieratings","id":"4","attributes":{"rating":"5"},"relationships":{"users":{"data":{"type":"users","id":"1"}},"movies":{"data":{"type":"movies","id":"2"}}}}]}';

        // Sends request with POST JSON data
        $this->client->request('POST', 'http://localhost:8080/usermovieratings', self::TEST_POST);

        // Fetches app response
        $response = $this->client->getResponse();

        // Compares page contents
        $actual = $response->getContent();
        $this->assertEquals($expected, $actual);

        // Compares Content-Type header
        $expected = 'application/vnd.api+json';
        $actual = $response->getHeaders()['Content-Type'][0];
        $this->assertEquals($expected, $actual);

        // Compares HTTP status code
        $expected = 200;
        $actual = $response->getStatus();
        $this->assertEquals($expected, $actual);
    }
}
