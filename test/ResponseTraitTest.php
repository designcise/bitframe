<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2018 Daniyal Hamid (https://designcise.com)
 *
 * @license   https://github.com/designcise/bitframe/blob/master/LICENSE.md MIT License
 */

namespace BitFrame\Test;

use \PHPUnit\Framework\TestCase;

use \BitFrame\Factory\HttpMessageFactory;

/**
 * @covers \BitFrame\Message\ResponseTrait
 */
class ResponseTraitTest extends TestCase
{
	/** @var \Psr\Http\Message\ResponseInterface */
	private $response;
	
    protected function setUp()
    {
		// response created via HttpMessageFactory has ResponseTrait added already
		// so we don't need to mock the ResponseTrait specially
		$this->response = HttpMessageFactory::createResponse();
    }
	
	public function testResponseWithRedirect() 
	{
		$response = $this->response->withRedirect('http://www.google.com/', 307);
		
		$this->assertTrue($response->hasHeader('Location'));
		$this->assertSame(307, $response->getStatusCode());
	}
	
    public function testResponseWithJson()
    {
		$response = $this->response;
		
		$body = 'Hello World!';
		$response->getBody()->write($body);
		
		$data = ['test' => '123', 'data' => '456'];
		$json = json_encode(array_merge($data, ['response_txt' => $body]));
		
		$response = $response->withJson($data, 201, 'response_txt');
		$response_json = $this->readResponse($response);
		
		$this->assertJsonStringEqualsJsonString($json, $response_json);
		$this->assertSame(201, $response->getStatusCode());
    }
	
	private function readResponse($response)
	{
		$stream = $response->getBody();
		
		if ($stream->isSeekable()) {
			$stream->rewind();
		}
		
		$output = '';

		// no readable data in stream?
		if (! $stream->isReadable()) {
			$output = $stream;
		} else {
			// read data till end of stream is reached...
			while (! $stream->eof()) {
				// read 8mb (max buffer length) of binary data at a time and output it
				$output .= $stream->read(1024 * 8);
			}
		}
		
		return $output;
	}
}
?>