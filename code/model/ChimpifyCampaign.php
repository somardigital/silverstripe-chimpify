<?php

use \DrewM\MailChimp\MailChimp;

class ChimpifyCampaign extends DataObject
{
    private static $api_key;

    private static $db = [
        'Title' => 'Varchar',
        'FromName' => 'Varchar',
        'ReplyTo' => 'Varchar',
        'TemplateID' => 'Int',
        'Intro' => 'Text',
        'ItemLimit' => 'Int',
    ];

    private static $has_many = [
        'ContentSources' => 'Blog',
    ];

    private static $defaults = array('ItemLimit' => 3);

    private static $singular_name = 'MailChimp Campaign';

    private static $plural_name = 'MailChimp Campaigns';

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName('ContentSources');

        $fields->addFieldsToTab(
            'Root.Main',
            [
                TextField::create(
                    'Title',
                    _t('Chimpify.FieldLabelSubjectLine', 'Subject line')
                ),
                TextField::create(
                    'FromName',
                    _t('Chimpify.FieldLabelFromName', 'From name')
                ),
                EmailField::create(
                    'ReplyTo',
                    _t('Chimpify.FieldLabelReplyTo', 'Reply to email address')
                ),
                DropdownField::create(
                    'TemplateID',
                    _t('Chimpify.FieldLabelMailChimpTemplate', 'MailChimp template'),
                    $this->getMailChimpTemplates()->map('id', 'name'))
                    ->setEmptyString(
                        _t('Chimpify.FieldPlaceholderMailChimpTemplate', 'Select...')
                    ),
                TextareaField::create(
                    'Intro',
                    _t('Chimpify.FieldLabelIntro', 'Introduction'))
                    ->setDescription(_t(
                        'Chimpify.FieldDescriptionIntro',
                        'Dispayled above the list of Blog posts.'
                    )),
                NumericField::create(
                    'ItemLimit',
                    _t('Chimpify.FieldLabelItemLimit', 'Number of posts'))
                    ->setDescription(_t(
                        'Chimpify.FieldDescriptionItemLimit',
                        'The number of posts to display for each source selected below.'
                    )),
            ]
        );

        $sourcesConfig = GridFieldConfig_RelationEditor::create();
        $sourcesConfig->removeComponentsByType('GridFieldEditButton');
        $sourcesConfig->removeComponentsByType('GridFieldAddNewButton');
        $sourcesConfig->addComponent(new GridFieldSortableRows('SortOrder'));

        $fields->addFieldToTab(
            'Root.Main',
            GridField::create(
                'ContentSources',
                _t('Chimpify.FieldLabelContentSources', 'Content sources'),
                $this->ContentSources(),
                $sourcesConfig
            )
        );

        $this->extend('updateCMSFields', $fields);

        return $fields;
    }

    public function getCMSActions()
    {
        $actions = parent::getCMSActions();

        $actions->push(
            FormAction::create(
                'doGenerateCampaign',
                _t('Chimpify.ButtonLabelGenerateCampaign', 'Create in MailChimp')
            )
        );

        $this->extend('updateCMSActions', $actions);

        return $actions;
    }

    public function getCMSValidator()
    {
        return new RequiredFields(
            'Title', 'FromName', 'ReplyTo', 'TemplateID', 'Intro', 'ItemLimit'
        );
    }

    /**
     * Fetches a list of email templates from MailChimp.
     *
     * @return ArrayList
     */
    private function getMailChimpTemplates()
    {
        if (!$api_key = $this->config()->get('api_key')) {
            user_error(
                'Add a MailChimp API key to config (ChimpifyCampaign::api_key)',
                E_USER_ERROR
            );
        }

        $templates = ArrayList::create();

        $mailChimp = new MailChimp($api_key);
        $response = $mailChimp->get('templates');

        if (!$mailChimp->success()) {
            $message = is_array($response['errors'])
                ? $response['errors'][0]['message']
                : 'Error connecting to MailChimp API';

            user_error($message, E_USER_ERROR);
        }

        foreach ($response['templates'] as $template) {
            if ($template['type'] == 'user') {
                $templates->push(ArrayData::create($template));
            }
        }

        $this->extend('updateMailChimpTemplates', $templates);

        return $templates;
    }

    /**
     * Generates HTML from ContentSources.
     *
     * @return String
     */
    public function getCampaignContent()
    {
        return $this->renderWith('ChimpifyCampaignContent')->Value;
    }
}
