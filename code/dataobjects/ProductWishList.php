<?php

/**
 * Class ProductWishList
 *
 * @property string $Title
 * @property bool $Private
 * @property int $MemberID
 * @method Member $Member
 */
class ProductWishList extends DataObject implements PermissionProvider, Dynamic\ViewableDataObject\VDOInterfaces\ViewableDataObjectInterface, ManageableDataObjectInterface
{

    /**
     * @var array
     */
    private static $db = [
        'Title' => 'Varchar(100)',
        'Private' => 'Boolean',
    ];

    /**
     * @var array
     */
    private static $has_one = [
        'Member' => 'Member',
    ];

    /**
     *
     */
    public function onBeforeWrite()
    {
        parent::onBeforeWrite();

        if (!$this->MemberID > 0) {
            $this->MemberID = Member::currentUserID();
        }
    }

    /**
     * @param null $params
     *
     * @return FieldList
     */
    public function getFrontEndFields($params = null)
    {
        $fields = parent::getFrontEndFields($params);

        $fields->removeByName([
            'MemberID',
            'MenuTitle',
            'URLSegment',
            'MetaTitle',
            'MetaDescription',
        ]);

        return $fields;
    }

    /**
     * @param bool $showCancel
     *
     * @return FieldList
     */
    public function getFrontEndActions($showCancel = false)
    {
        $processTitle = ($this->getIsEditing()) ? "Update {$this->i18n_singular_name()}" : "Create {$this->i18n_singular_name()}";
        $actions = FieldList::create(
            FormAction::create('doSaveObject')
                      ->setTitle($processTitle)
        );

        if ($showCancel === true) {
            $actions->insertBefore('action_doSaveObject', CancelFormAction::create('Cancel'));
        }

        $this->extend('updateFrontEndActions', $actions);

        return $actions;
    }

    /**
     * @return RequiredFields
     */
    public function getFrontEndRequiredFields()
    {
        $fields = RequiredFields::create([
            'Title',
        ]);

        $this->extend('updateFrontEndRequiredFields', $fields);

        return $fields;
    }

    /**
     * @return bool
     */
    public function getIsEditing()
    {
        $params = Controller::curr()->getRequest()->latestParams();

        return isset($params['Action']) && $params['Action'] == 'edit' && isset($params['ID']);
    }

    /**
     * set ParentPage for ViewableDataobject
     *
     * @return string
     */
    public function getParentPage()
    {
        $class = $this->config()->get('listing_page_class');
        return $class::get()->first();
    }

    /**
     * @return String|bool
     */
    public function getUpdateLink()
    {
        return ($this->ID > 0) ? Controller::join_links($this->getParentPage()->Link(), 'edit', $this->ID) : false;
    }

    /**
     * set ViewAction for ViewableDataobject
     *
     * @return string
     */
    public function getViewAction()
    {
        return 'view';
    }

    /**
     * @return array
     */
    public function providePermissions()
    {
        return [
            'WishList_EDIT' => [
                'name' => 'Edit a Wish List',
                'category' => 'Wish List Permissions',
            ],
            'WishList_DELETE' => [
                'name' => 'Delete a Wish List',
                'category' => 'Wish List Permissions',
            ],
            'WishList_CREATE' => [
                'name' => 'Create a Wish List',
                'category' => 'Wish List Permissions',
            ],
            'WishList_VIEW' => [
                'name' => 'View a Wish List',
                'category' => 'Wish List Permissions',
            ],
        ];
    }

    /**
     * @param Member|null $member
     *
     * @return bool|int
     */
    public function canEdit($member = null)
    {
        $member = ($member === null) ? Member::currentUser() : $member;

        return ((Permission::check('WishList_EDIT', 'any',
                        $member) && $member->ID == $this->MemberID) || Permission::check('ADMIN'));
    }

    /**
     * @param Member|null $member
     *
     * @return bool|int
     */
    public function canDelete($member = null)
    {
        $member = ($member === null) ? Member::currentUser() : $member;

        return ((Permission::check('WishList_DELETE', 'any',
                        $member) && $member->ID == $this->MemberID) || Permission::check('ADMIN'));
    }

    /**
     * @param Member|null $member
     *
     * @return bool|int
     */
    public function canCreate($member = null)
    {
        return Member::currentUser() && Permission::check('WishList_CREATE', 'any', $member);
    }

    /**
     * @param Member|null $member
     *
     * @return bool
     */
    public function canView($member = null)
    {
        $member = ($member === null) ? Member::currentUser() : $member;

        return (!$this->Private) || ((Permission::check('WishList_VIEW', 'any',
                        $member) && $member->ID == $this->MemberID) || Permission::check('ADMIN'));
    }

}