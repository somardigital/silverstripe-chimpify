<?php

use \Mockery as Mockery;
use \DrewM\MailChimp\MailChimp;

class ChimpifyRequestHandlerTest extends SapphireTest
{
    public function tearDown()
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testHandleMailChimpResponse()
    {
        $chimpifyRequestHandler = new ChimpifyRequestHandler(null, null, null, null, null);

        $mailChimp = $this->getNewMailChimpMock();
        $mailChimp
            ->shouldReceive('getLastResponse')
            ->withNoArgs()
            ->once();
        $mailChimp
            ->shouldReceive('success')
            ->withNoArgs()
            ->once();

        try {
            $chimpifyRequestHandler->handleMailChimpResponse($mailChimp);
        } catch (Exception $e) {
            $this->assertEquals('Error connecting to MailChimp API', $e->getMessage());
        }

        $mailChimp = $this->getNewMailChimpMock();
        $mailChimp
            ->shouldReceive('getLastResponse')
            ->withNoArgs()
            ->once()
            ->andReturn([
                'body' => json_encode(['items' => [['id' => 1]]]),
            ]);
        $mailChimp
            ->shouldReceive('success')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $result = $chimpifyRequestHandler->handleMailChimpResponse($mailChimp);

        $this->assertEquals(true, is_array($result));
        $this->assertEquals(1, count($result['items']));
        $this->assertEquals(1, $result['items'][0]['id']);
    }

    protected function getNewMailChimpMock()
    {
        return Mockery::mock(
            '\DrewM\MailChimp\MailChimp',
            ['abc123abc123abc123abc123abc123-us1']
        );
    }
}
