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

    protected $_table;

    function getLinkedUsers($userId) {
        return $this->getDi()->db->select("SELECT * FROM ?_user WHERE user_id in (SELECT user_id_a AS user_id FROM ?_user_links WHERE user_id_b = ?d UNION SELECT user_id_b AS user_id FROM ?_user_links WHERE user_id_a = ?d)", $userId, $userId);
    }

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

    function getTable()
    {
        if (!$this->_table) {
            $this->_table = $this->getDi()->userLinkTable;
        }
        return $this->_table;
    }

    function onGetPermissionsList(Am_Event $event)
    {
        $event->addReturn(___("ENTS: Link Users"), self::ADMIN_PERM_ID);
    }

    static function getDbXml()
    {
        return <<<CUT
<schema version="4.0.0">
    <table name="user_links">
        <field name="link_id" type="int" notnull="1" extra="auto_increment"/>
        <field name="user_id_a" type="int"/>
        <field name="user_id_b" type="int"/>
        <index name="PRIMARY" unique="1">
            <field name="link_id" />
        </index>
    </table>
</schema>
CUT;
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
        $linkedUsers = $this->getDi()->plugins_misc->loadGet("ents-linked-users")->getLinkedUsers($this->getInt('user_id'));
        $this->view->content .= "<h1>" . ___("Linked Users") . "</h1>";
        if (count($linkedUsers) > 0) {
            $this->view->content .= "<ul>";
            foreach($linkedUsers as $user) {
                $this->view->content .= "<li style='margin-left: 20px; list-style: disc'>${user['name_f']} ${user['name_l']} (Fob: ${user['fob']})</li>";
            }
            $this->view->content .= "</ul>";
        } else {
            $this->view->content .= "<i>No users linked.</i>";
        }

        $this->view->display("admin/user-layout.phtml");
    }
}

class UserLink extends Am_Record {
    protected $_key = 'link_id';
    protected $_table = '?_user_links';
}

class UserLinkTable extends Am_Table {
    protected $_key = 'link_id';
    protected $_table = '?_user_links';
    protected $_recordClass = 'UserLink';
}