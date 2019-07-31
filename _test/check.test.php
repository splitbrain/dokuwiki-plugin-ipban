<?php

/**
 * General tests for the ipban plugin
 *
 * @group plugin_ipban
 * @group plugins
 */
class check_plugin_ipban_test extends DokuWikiTest
{
    /**
     * Ban configuration
     *
     * @var array
     */
    protected $banconfig = [
        "127.0.0.1\t1400000000\tandi\tspam",
        "192.168.1.0/24\t1400000000\tandi\tspam",
        "192.168.2.*\t1400000000\tandi\tspam",
        "broken\t1400000000\tandi\tspam",
    ];

    /**
     * IPs to test for above config
     *
     * @return array
     */
    public function data()
    {
        return [
            ['127.0.0.1', true],
            ['127.0.0.2', false],
            ['192.168.1.13', true],
            ['192.168.2.13', true],
            ['192.168.3.13', false],
        ];
    }

    /**
     * Simple test to make sure the plugin.info.txt is in correct format
     *
     * @dataProvider data
     * @param string $ip
     * @param bool $result
     * @throws ReflectionException
     */
    public function test_banning($ip, $result)
    {
        $action = new action_plugin_ipban();

        $this->assertSame(
            $result,
            (bool) $this->callInaccessibleMethod($action,'isBanned', [$ip, $this->banconfig])
        );
    }

}
