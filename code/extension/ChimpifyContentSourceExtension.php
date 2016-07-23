<?php

class ChimpifyContentSourceExtension extends DataExtension
{
    private static $belongs_many_many = [
        'ChimpifyCampaigns' => 'ChimpifyCampaign',
    ];
}
