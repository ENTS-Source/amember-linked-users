<?php

/**
 * Class Am_Plugin_EntsLinkedUsers
 */
class Am_Plugin_EntsLinkedUsers extends Am_Plugin
{
    const PLUGIN_STATUS = self::STATUS_PRODUCTION;
    const PLUGIN_COMM = self::COMM_FREE;
    const PLUGIN_REVISION = "1.0.0";
    const ADMIN_PERM_ID = "ents-link-users";

    function onUserTabs(Am_Event_UserTabs $e)
    {
        if ($e->getUserId() > 0) {
            $e->getTabs()->addPage(array(
                'id' => 'ents-linked-users',
                'controller' => 'admin-ents-linked-users',
                'action' => 'index',
                'params' => array(
                    'user_id' => $e->getUserId(),
                ),
                'label' => ___('Linked Users'),
                'order' => 1000,
                'resource' => self::ADMIN_PERM_ID
            ));
        }
    }

    function onGetPermissionsList(Am_Event $event)
    {
        $event->addReturn(___("ENTS: Link Users"), self::ADMIN_PERM_ID);
    }
}

class AdminEntsLinkedUsersController extends Am_Mvc_Controller
{
    const ADMIN_PERM_ID = "ents-link-users";

    public function checkAdminPermissions(Admin $admin)
    {
        return $admin->hasPermission(self::ADMIN_PERM_ID);
    }

    public function indexAction()
    {
        $this->view->content = "<h1>" . ___("Linked Users") . "</h1>";

        $this->view->display("admin/user-layout.phtml");
    }
}