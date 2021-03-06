<?php
class CDashboardController extends CBaseController {
	public function __construct() {
		if (!CSession::isAuth()) {
			$this->redirectNoAccess();
		}
	
		$this->_smartyEnabled = true;
		$this->setPageTitle("Рабочий стол");
	
		parent::__construct();
	}
	public function actionIndex() {
        $this->addCSSInclude("_modules/_dashboard/style.css");
        $items = new CArrayList();
        /**
         * Показываем пункты рабочего стола в зависимости от
         * личных настроек пользователя
         */
        if (!is_null(CSession::getCurrentUser()->getPersonalSettings())) {
            $settings = CSession::getCurrentUser()->getPersonalSettings();
            /**
             * Показ дней рождения на этой недел
             */
            if ($settings->isShowBirthdays()) {
                if (CStaffManager::getBirthdaysThisWeek()->getCount() > 0) {
                    $cnt = 0;
                    $item = new CDashboardItem();
                    $item->title = "Дни рождения";
                    $item->icon = "mimetypes/text-x-java.png";
                    $items->add("_".$items->getCount(), $item);
                    foreach (CStaffManager::getBirthdaysThisWeek()->getItems() as $person) {
                        $child = new CDashboardItem();
                        $child->id = "person_".$person->getId();
                        $child->title = $person->getName();
                        $item->addChild($child);
                        $cnt++;
                        if ($cnt == 3) {
                            $child = new CDashboardItem();
                            $child->id = "person_q";
                            $child->title = "Всего ".CStaffManager::getBirthdaysThisWeek()->getCount();
                            $item->addChild($child);
                            break;
                        }
                    }
                }
            }
            /**
             * Показываем сообщения
             */
            if ($settings->isShowMessages()) {
                $item = new CDashboardItem();
                $item->title = "Сообщения";
                $item->icon = "apps/evolution.png";
                $item->addChild(null);
                if (CSession::getCurrentUser()->getUnreadMessages()->getCount() > 0) {
                    $child = new CDashboardItem();
                    $child->id = "inbox";
                    $child->title = "Входящие (".CSession::getCurrentUser()->getUnreadMessages()->getCount().")";
                    $child->link = WEB_ROOT."mail.php?folder=in";
                    $item->addChild($child);
                } else {
                    $child = new CDashboardItem();
                    $child->id = "inbox";
                    $child->title = "Нет непрочитанных сообщений";
                    $child->link = WEB_ROOT."mail.php?folder=in";
                    $item->addChild($child);
                }
                $child = new CDashboardItem();
                $child->id = "new";
                $child->title = "Написать сообщение";
                $child->link = WEB_ROOT."mail.php?compose=1";
                $item->addChild($child);
                $items->add("_".$items->getCount(), $item);
            }
        }
        $set = CActiveRecordProvider::getWithCondition(TABLE_DASHBOARD, "user_id = ".CSession::getCurrentUser()->getId()." and parent_id = 0");
        foreach ($set->getItems() as $ar) {
            $item = new CDashboardItem($ar);
            $items->add($item->getId(), $item);
        }
		$this->setData("items", $items);
		$this->renderView("_dashboard/index.tpl");
	}
	public function actionList() {
		$set = CActiveRecordProvider::getWithCondition(TABLE_DASHBOARD, "user_id = ".CSession::getCurrentUser()->getId()." and parent_id = 0");
        $items = new CArrayList();
        foreach ($set->getPaginated()->getItems() as $ar) {
            $item = new CDashboardItem($ar);
            $items->add($item->getId(), $item);
        }
        $this->setData("paginator", $set->getPaginator());
        $this->setData("items", $items);
		$this->renderView("_dashboard/list.tpl");		
	}
	public function actionAdd() {
		$parents = new CArrayList();
		foreach (CActiveRecordProvider::getWithCondition(TABLE_DASHBOARD, "user_id = ".CSession::getCurrentUser()->getId()." and parent_id = 0")->getItems() as $ar) {
			$item = new CDashboardItem($ar);
			$parents->add($item->getId(), $item->title);
			foreach ($item->children->getItems() as $child) {
				$parents->add($child->getId(), " - ".$child->title);
			}
		}
        $icons = new CArrayList();
        $dirs = array(
            "actions",
            "apps",
            "categories",
            "devices",
            "emblems",
            "emotes",
            "mimetypes",
            "places",
            "status"
        );
        foreach ($dirs as $dir) {
            if ($h = opendir(CORE_CWD."/images/tango/16x16/".$dir."/")) {
                while ($file = readdir($h)) {
                    if (strpos($file, ".png")) {
                        $icons->add($dir."/".$file, $dir."/".$file);
                    }
                }
                closedir($h);
            }
        }
        $item = new CDashboardItem();
        $item->user_id = CSession::getCurrentUser()->getId();
        $this->addJSInclude("_core/jDropdown/jquery.dd.js");
        $this->addCSSInclude("_core/jDropdown/dd.css");
        $this->setData("icons", $icons);
		$this->setData("parents", $parents);
		$this->setData("item", $item);
		$this->renderView("_dashboard/add.tpl");
	}
	public function actionEdit() {
		$parents = new CArrayList();
		foreach (CActiveRecordProvider::getWithCondition(TABLE_DASHBOARD, "user_id = ".CSession::getCurrentUser()->getId()." and parent_id = 0")->getItems() as $ar) {
			$item = new CDashboardItem($ar);
			$parents->add($item->getId(), $item->title);
			foreach ($item->children->getItems() as $child) {
				$parents->add($child->getId(), " - ".$child->title);
			}
		}
        $icons = new CArrayList();
        $dirs = array(
            "actions",
            "apps",
            "categories",
            "devices",
            "emblems",
            "emotes",
            "mimetypes",
            "places",
            "status"
        );
        foreach ($dirs as $dir) {
            if ($h = opendir(CORE_CWD."/images/tango/16x16/".$dir."/")) {
                while ($file = readdir($h)) {
                    if (strpos($file, ".png")) {
                        $icons->add($dir."/".$file, $dir."/".$file);
                    }
                }
                closedir($h);
            }
        }
        $item = CDashboardManager::getDashboardItem(CRequest::getInt("id"));
        $this->addJSInclude("_core/jDropdown/jquery.dd.js");
        $this->addCSSInclude("_core/jDropdown/dd.css");
        $this->setData("icons", $icons);
		$this->setData("parents", $parents);
		$this->setData("item", $item);
		$this->renderView("_dashboard/edit.tpl");		
	}
	public function actionSave() {
		$item = new CDashboardItem();
		$item->setAttributes(CRequest::getArray($item::getClassName()));
		if ($item->validate()) {
			$item->save();
			$this->redirect("?action=list");
			return true;
		}
		$parents = new CArrayList();
		foreach (CActiveRecordProvider::getWithCondition(TABLE_DASHBOARD, "user_id = ".CSession::getCurrentUser()->getId()." and parent_id = 0")->getItems() as $ar) {
			$item = new CDashboardItem($ar);
			$parents->add($item->getId(), $item->title);
			foreach ($item->children->getItems() as $child) {
				$parents->add($child->getId(), " - ".$child->title);
			}
		}
		$this->setData("parents", $item);		
		$this->setData("item", $item);
		$this->renderView("_dashboard/edit.tpl");		
	}
	public function actionDelete() {
		$item = CDashboardManager::getDashboardItem(CRequest::getInt("id"));
		$item->remove();
		$this->redirect("?action=list");
	}
}