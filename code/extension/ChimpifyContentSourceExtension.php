<?php

class ChimpifyContentSourceExtension extends DataExtension {
    private static $db = [
        'SortOrder' => 'Int',
    ];

    private static $has_one = [
        'ChimpifyCampaign' => 'ChimpifyCampaign',
    ];

    public static $default_sort = 'SortOrder';

    public function updateCMSFields(FieldList $fields) {
        $fields->removeByName('SortOrder');
        $fields->removeByName('ChimpifyCampaignID');

        return $fields;
    }
}
