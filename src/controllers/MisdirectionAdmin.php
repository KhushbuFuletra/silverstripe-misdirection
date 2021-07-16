<?php

namespace symbiote\misdirection;

use SilverStripe\Admin\ModelAdmin;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Forms\GridField\GridFieldSortableHeader;
use SilverStripe\Security\Member;
use SilverStripe\Security\Permission;

/**
 *	@author Nathan Glasl <nathan@symbiote.com.au>
 */

class MisdirectionAdmin extends ModelAdmin
{
    private static $managed_models = LinkMapping::class;

    private static $menu_title = 'Misdirection';

    private static $menu_description = 'Create, manage and test customisable <strong>link redirection</strong> mappings.';

    private static $menu_icon_class = 'font-icon-switch';

    private static $url_segment = 'misdirection';

    private static $allowed_actions = array(
        'getMappingChain'
    );

    /**
     *	Update the custom summary fields to be sortable.
     */

    public function getEditForm($ID = null, $fields = null)
    {
        $form = parent::getEditForm($ID, $fields);
        $gridfield = $form->Fields()->fieldByName($this->sanitiseClassName($this->modelClass));
        $gridfield->getConfig()->getComponentByType(GridFieldSortableHeader::class)->setFieldSorting(array(
            'RedirectTypeSummary' => 'RedirectType'
        ));

        // Allow extension customisation.

        $this->extend('updateMisdirectionAdminEditForm', $form);
        return $form;
    }

    /**
     *	Retrieve the JSON link mapping recursion stack for the testing interface.
     *
     *	@URLparameter map <{TEST_URL}> string
     *	@return JSON
     */

    public function getMappingChain()
    {

        // Restrict this functionality to administrators.

        $user = Member::currentUserID();
        if (Permission::checkMember($user, 'ADMIN')) {

            // Instantiate a request to handle the link mapping.

            $request = new HTTPRequest('GET', $this->getRequest()->getVar('map'));

            // Retrieve the link mapping recursion stack JSON.

            $testing = true;
            $mappings = singleton(MisdirectionService::class)->getMappingByRequest($request, $testing);
            $this->getResponse()->addHeader('Content-Type', 'application/json');

            // JSON_PRETTY_PRINT.

            return json_encode($mappings, 128);
        } else {
            return $this->httpError(404);
        }
    }
}
