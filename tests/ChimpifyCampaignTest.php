<?php

use \Mockery as Mockery;
use \DrewM\MailChimp\MailChimp;

class ChimpifyCampaignTest extends SapphireTest
{
    public function tearDown()
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testGetMailChimpTemplates()
    {
        $chimpifyCampaign = new ChimpifyCampaign();

        $mailChimp = $this->getNewMailChimpMock();
        $mailChimp
            ->shouldReceive('get')
            ->with('templates')
            ->once();
        $mailChimp
            ->shouldReceive('success')
            ->withNoArgs()
            ->once();

        try {
            $chimpifyCampaign->getMailChimpTemplates($mailChimp);
        } catch (Exception $e) {
            $this->assertEquals('Error connecting to MailChimp API', $e->getMessage());
        }

        $mailChimp = $this->getNewMailChimpMock();
        $mailChimp
            ->shouldReceive('get')
            ->with('templates')
            ->once()
            ->andReturn([
                'templates' => [
                    [
                        'type' => 'user',
                        'name' => 'Template One',
                    ],
                    [
                        'type' => 'preset',
                        'name' => 'Template Two',
                    ],
                ],
            ]);
        $mailChimp
            ->shouldReceive('success')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $result = $chimpifyCampaign->getMailChimpTemplates($mailChimp);

        $this->assertInstanceOf('ArrayList', $result);
        $this->assertEquals(1, $result->count());
    }

    protected function getNewMailChimpMock()
    {
        return Mockery::mock(
            '\DrewM\MailChimp\MailChimp',
            ['abc123abc123abc123abc123abc123-us1']
        );
    }
}
