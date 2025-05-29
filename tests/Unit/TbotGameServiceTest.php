<?php
use PHPUnit\Framework\TestCase;
// Assuming TbotGameService is autoloaded or required
// require_once __DIR__ . '/../../src/Service/TbotGameService.php';

class TbotGameServiceTest extends TestCase
{
    // You would need to mock makeCurlRequest for actual testing
    // For this example, let's assume a simplified test of constructor
    public function testConstructorSetsRefererCorrectly()
    {
        $service = new TbotGameService('testgame');
        // Use reflection to access private property $referer for testing, or add a getter if appropriate
        $reflection = new ReflectionClass($service);
        $refererProperty = $reflection->getProperty('referer');
        $refererProperty->setAccessible(true);
        $this->assertEquals('https://tbot.xyz/testgame/', $refererProperty->getValue($service));
    }

    // Add more tests for sendScore and getHighScores, likely involving mocking HTTP calls
}