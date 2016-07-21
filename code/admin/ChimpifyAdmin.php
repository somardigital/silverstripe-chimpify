<?php

use \DrewM\MailChimp\MailChimp;

class ChimpifyAdmin extends ModelAdmin
{
    private static $managed_models = [
        'ChimpifyCampaign',
    ];

    private static $url_segment = 'mailchimp-campaigns';

    private static $menu_title = 'MailChimp Campaigns';

    public function getEditForm($id = null, $fields = null)
    {
        $form = parent::getEditForm($id, $fields);

        $gridFieldName = $this->sanitiseClassName($this->modelClass);
        $gridFieldConfig = $form->Fields()->fieldByName($gridFieldName)->getConfig();

        $gridFieldConfig->removeComponentsByType('GridFieldPrintButton');
        $gridFieldConfig->removeComponentsByType('GridFieldExportButton');
        $gridFieldConfig
            ->getComponentByType('GridFieldAddNewButton')
            ->setButtonName('Add MailChimp Campaign');
        $gridFieldConfig
            ->getComponentByType('GridFieldDetailForm')
            ->setItemRequestClass('ChimpifyRequestHandler');

        return $form;
    }
}

class ChimpifyRequestHandler extends GridFieldDetailForm_ItemRequest
{
    private static $allowed_actions = [
        'edit',
        'view',
        'ItemEditForm'
    ];

    public function ItemEditForm()
    {
        $form = parent::ItemEditForm();

        if (!$this->record->ID) {
            return $form;
        }

        $formActions = $form->Actions();

        if ($actions = $this->record->getCMSActions()) {
            foreach ($actions as $action) {
                $formActions->push($action);
            }
        }

        return $form;
    }

    /**
     * Handles responses from the MailChimp API.
     *
     * @param MailChimp $mailChimp
     * @return Array
     */
    private function handleMailChimpResponse($mailChimp)
    {
        if (!$mailChimp->success()) {
            $message = is_array($result['errors'])
                ? $result['errors'][0]['message']
                : 'Error connecting to MailChimp API';

            user_error($message, E_USER_ERROR);
        }

        $response = $mailChimp->getLastResponse();

        return Convert::json2array($response['body']);
    }

    /**
     * Creates a MailChimp campaign via the API.
     *
     * @param MailChimp $mailChimp
     * @return Array
     */
    private function createCampaign($mailChimp)
    {
        $mailChimp->post('campaigns', [
          'type' => 'regular',
          'settings' => [
            'subject_line' => $this->record->Title,
            'from_name' => $this->record->FromName,
            'reply_to' => $this->record->ReplyTo,
          ],
        ]);

        return $this->handleMailChimpResponse($mailChimp);
    }

    /**
     * Populates a MailChimp Campaign with Blog content via the API.
     *
     * @param MailChimp $mailChimp
     * @param Int $campaignID
     * @return Array
     */
    private function populateCampaignContent($mailChimp, $campaignID)
    {
        $mailChimp->put("campaigns/{$campaignID}/content", [
            'template' => [
                'id' => $this->record->TemplateID,
                'sections' => [
                    'chimpify' => $this->record->getCampaignContent(),
                ],
            ],
        ]);

        return $this->handleMailChimpResponse($mailChimp);
    }

    /**
     * Creates and populates a MailChimp Campaign with blog content via the API.
     *
     * @param Array $data
     * @param Form $form
     */
    public function doGenerateCampaign($data, $form)
    {
        if (!$api_key = $this->record->config()->get('api_key')) {
            user_error(
                'Add a MailChimp API key to config (ChimpifyCampaign::api_key)',
                E_USER_ERROR
            );
        }

        $controller = $this->getToplevelController();

        if (!$this->record || !$this->record->canEdit()) {
            return $controller->httpError(403);
        }

        $form->validate();

        $mailChimp = new MailChimp($api_key);

        $response = $this->createCampaign($mailChimp);
        $response = $this->populateCampaignContent($mailChimp, $response['id']);

        $form->sessionMessage(
            _t(
                'Chimpify.GenerateCampaignSuccessMessage',
                'Successfully created MailChimp Campaign'
            ),
            'good'
        );

        return $controller->redirectBack();
    }
}
