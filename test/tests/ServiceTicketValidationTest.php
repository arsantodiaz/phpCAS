<?php
require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__).'/../harness/DummyRequest.php';
require_once dirname(__FILE__).'/../harness/BasicResponse.php';

/**
 * Test class for verifying the operation of service tickets.
 *
 *
 * Generated by PHPUnit on 2010-09-07 at 13:33:53.
 */
class ServiceTicketValidationTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var CAS_Client
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
		$_SERVER['SERVER_NAME'] = 'www.service.com';
		$_SERVER['SERVER_PORT'] = '80';
		$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
		$_SERVER['SERVER_ADMIN'] = 'root@localhost';
		$_SERVER['REQUEST_URI'] = '/';
		$_SERVER['SCRIPT_NAME'] = '/index.php';
		$_SERVER['PHP_SELF'] = '/index.php';
		$_SESSION = array();

// 		$_GET['ticket'] = 'ST-123456-asdfasdfasgww2323radf3';

		$this->object = new CAS_Client(
			CAS_VERSION_2_0, 	// Server Version
			false, 				// Proxy
			'cas.example.edu',	// Server Hostname
			443,				// Server port
			'/cas/',			// Server URI
			false				// Start Session
		);
		
		$this->object->setRequestImplementation('CAS_TestHarness_DummyRequest');
		$this->object->setCasServerCACert('/path/to/ca_cert.crt');

		/*********************************************************
		 * Enumerate our responses
		 *********************************************************/
		// Valid ticket response
		$response = new CAS_TestHarness_BasicResponse('https', 'cas.example.edu', '/cas/serviceValidate');
		$response->matchQueryParameters(array(
			'service' => 'http://www.service.com/',
			'ticket' => 'ST-123456-asdfasdfasgww2323radf3',
		));
		$response->setResponseHeaders(array(
			'HTTP/1.1 200 OK',
			'Date: Wed, 29 Sep 2010 19:20:57 GMT',
			'Server: Apache-Coyote/1.1',
			'Pragma: no-cache',
			'Expires: Thu, 01 Jan 1970 00:00:00 GMT',
			'Cache-Control: no-cache, no-store',
			'Content-Type: text/html;charset=UTF-8',
			'Content-Language: en-US',
			'Via: 1.1 cas.example.edu',
			'Connection: close',
			'Transfer-Encoding: chunked',
		));
		$response->setResponseBody(
"<cas:serviceResponse xmlns:cas='http://www.yale.edu/tp/cas'>
    <cas:authenticationSuccess>
        <cas:user>jsmith</cas:user>
            <cas:proxyGrantingTicket>PGTIOU-84678-8a9d2sfa23casd</cas:proxyGrantingTicket>
    </cas:authenticationSuccess>
</cas:serviceResponse>
");
		$response->ensureCaCertPathEquals('/path/to/ca_cert.crt');
		CAS_TestHarness_DummyRequest::addResponse($response);

		// Invalid ticket response
		$response = new CAS_TestHarness_BasicResponse('https', 'cas.example.edu', '/cas/serviceValidate');
		$response->matchQueryParameters(array(
			'service' => 'http://www.service.com/',
		));
		$response->setResponseHeaders(array(
			'HTTP/1.1 200 OK',
			'Date: Wed, 29 Sep 2010 19:20:57 GMT',
			'Server: Apache-Coyote/1.1',
			'Pragma: no-cache',
			'Expires: Thu, 01 Jan 1970 00:00:00 GMT',
			'Cache-Control: no-cache, no-store',
			'Content-Type: text/html;charset=UTF-8',
			'Content-Language: en-US',
			'Via: 1.1 cas.example.edu',
			'Connection: close',
			'Transfer-Encoding: chunked',
		));
		$response->setResponseBody(
"<cas:serviceResponse xmlns:cas='http://www.yale.edu/tp/cas'>
    <cas:authenticationFailure code='INVALID_TICKET'>
        Ticket ST-1856339-aA5Yuvrxzpv8Tau1cYQ7 not recognized
    </cas:authenticationFailure>
</cas:serviceResponse>

");
		$response->ensureCaCertPathEquals('/path/to/ca_cert.crt');
		CAS_TestHarness_DummyRequest::addResponse($response);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
		CAS_TestHarness_DummyRequest::clearResponses();
    }

    /**
     * Test that a service ticket can be successfully validated.
     */
    public function test_validation_success() {
		$this->object->setTicket('ST-123456-asdfasdfasgww2323radf3');
		$result = $this->object->validateCAS20($url, $text_response, $tree_response);
		$this->assertTrue($result);
		$this->assertEquals(
"<cas:serviceResponse xmlns:cas='http://www.yale.edu/tp/cas'>
    <cas:authenticationSuccess>
        <cas:user>jsmith</cas:user>
            <cas:proxyGrantingTicket>PGTIOU-84678-8a9d2sfa23casd</cas:proxyGrantingTicket>
    </cas:authenticationSuccess>
</cas:serviceResponse>
", $text_response);
		$this->assertType('DOMElement', $tree_response);
    }

	/**
     * Test that a service ticket can be successfully fails.
     * @expectedException CAS_AuthenticationException
     * @outputBuffering enabled
     */
    public function test_invalid_ticket_failure() {
		$this->object->setTicket('ST-1856339-aA5Yuvrxzpv8Tau1cYQ7');
		ob_start();
		$result = $this->object->validateCAS20($url, $text_response, $tree_response);
		ob_end_clean();
		$this->assertTrue($result);
		$this->assertEquals(
"<cas:serviceResponse xmlns:cas='http://www.yale.edu/tp/cas'>
    <cas:authenticationFailure code='INVALID_TICKET'>
        Ticket ST-1856339-aA5Yuvrxzpv8Tau1cYQ7 not recognized
    </cas:authenticationFailure>
</cas:serviceResponse>

", $text_response);
		$this->assertType('DOMElement', $tree_response);
    }

}
?>
