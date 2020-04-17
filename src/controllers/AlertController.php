<?php

namespace Controllers;

use Models\Alert;
use Models\CodeAde;
use Models\User;
use Views\AlertView;

/**
 * Class AlertController
 *
 * Manage alerts (create, update, delete, display)
 *
 * @package Controllers
 */
class AlertController extends Controller
{

	/**
	 * @var Alert
	 */
    private $model;

	/**
	 * @var AlertView
	 */
    private $view;

    /**
     * AlertController constructor
     */
    public function __construct()
    {
        $this->model = new Alert();
        $this->view  = new AlertView();
    }

	/**
	 * Insert an alert in the database
	 */
	public function insert()
	{
		$codeAde    = new CodeAde();
        $action = filter_input(INPUT_POST, 'submit');
		if (isset($action)) {
			$codes   = $_POST['selectAlert'];
			$content = filter_input(INPUT_POST, 'content');
			$endDate = filter_input(INPUT_POST, 'expirationDate');

			$creationDate       = date('Y-m-d');
			$endDateString      = strtotime($endDate);
			$creationDateString = strtotime(date('Y-m-d',time()));

			$this->model->setForEveryone(0);

			$codesAde = array();
			foreach ($codes as $code) {
				if($code != 'all' && $code != 0) {
					if(is_null($codeAde->getByCode($code)->getId())) {
					    $this->view->errorMessageInvalidForm();
						return;
					} else {
						$codesAde[] = $codeAde->getByCode($code);
					}
				} else if($code == 'all') {
					$this->model->setForEveryone(1);
				}
			}

			if(is_string($content) && strlen($content) >= 4 && strlen($content) <= 280 && $this->isRealDate($endDate) && $creationDateString < $endDateString) {
				$current_user = wp_get_current_user();

				// Set the alert
				$this->model->setAuthor($current_user->ID);
				$this->model->setContent($content);
				$this->model->setCreationDate($creationDate);
				$this->model->setExpirationDate($endDate);
				$this->model->setCodes($codesAde);

				// Insert
				if($id = $this->model->insert()) {
					$this->view->displayAddValidate();
					//$this->sendAlert($id);
				} else {
                    $this->view->errorMessageCantAdd();
                }
			} else {
                $this->view->errorMessageInvalidForm();
            }
		}

		$years      = $codeAde->getAllFromType('year');
		$groups     = $codeAde->getAllFromType('group');
		$halfGroups = $codeAde->getAllFromType('halfGroup');

		return $this->view->creationForm($years, $groups, $halfGroups);
	}

	/**
	 * Modify an alert
	 */
	public function modify()
	{
        $id = $this->getPartOfUrl()[2];
        if(!is_numeric($id) || !$this->model->get($id)) {
            return $this->view->noAlert();
		}
        $current_user = wp_get_current_user();
        $alert = $this->model->get($id);
        if(!in_array('administrator', $current_user->roles) && !in_array('secretaire', $current_user->roles) && $alert->getAuthor()->getId() != $current_user->ID) {
            return $this->view->noAlert();
        }

		$codeAde    = new CodeAde();

        $submit = filter_input(INPUT_POST, 'submit');
		if (isset($submit)) {
			// Get value
            $content = filter_input(INPUT_POST, 'content');
            $expirationDate = filter_input(INPUT_POST, 'expirationDate');
			$codes   = $_POST['selectAlert'];

            $alert->setForEveryone(0);

            $codesAde = array();
            foreach ($codes as $code) {
                if($code != 'all' && $code != 0) {
                    if(is_null($codeAde->getByCode($code)->getId())) {
                        $this->view->errorMessageInvalidForm();
                        return;
                    } else {
                        $codesAde[] = $codeAde->getByCode($code);
                    }
                } else if($code == 'all') {
                    $alert->setForEveryone(1);
                }
            }

            // Set the alert
            $alert->setContent($content);
            $alert->setExpirationDate($expirationDate);
            $alert->setCodes($codesAde);

            if($alert->update()) {
                $this->view->displayModifyValidate();
            } else {
                $this->view->errorMessageCantAdd();
            }
		}

        $delete = filter_input(INPUT_POST, 'delete');
        if(isset($delete)) {
            $alert->delete();
            $this->view->displayModifyValidate();
        }

		$years      = $codeAde->getAllFromType('year');
		$groups     = $codeAde->getAllFromType('group');
		$halfGroups = $codeAde->getAllFromType('halfGroup');

		return $this->view->modifyForm($alert, $years, $groups, $halfGroups);
	}


    public function displayAll()
    {
        $numberAllEntity = $this->model->countAll();
        $url = $this->getPartOfUrl();
        $number = filter_input(INPUT_GET, 'number');
        $pageNumber = 1;
        if(sizeof($url) >= 2 && is_numeric($url[1])) {
            $pageNumber = $url[1];
        }
        if(isset($number) && !is_numeric($number) || empty($number)) {
            $number = 25;
        }
        $begin = ($pageNumber - 1) * $number;
        $maxPage = ceil($numberAllEntity / $number);
        if($maxPage <= $pageNumber && $maxPage >= 1) {
            $pageNumber = $maxPage;
        }
        $current_user = wp_get_current_user();
        if(in_array('administrator', $current_user->roles) || in_array('secretaire', $current_user->roles)) {
            $alertList = $this->model->getList($begin, $number);
        } else {
            $alertList = $this->model->getAuthorListAlert($current_user->ID, $begin, $number);
        }
        $name = 'Alert';
        $header = ['Contenu', 'Date de création', 'Date d\'expiration', 'Auteur', 'Modifier'];
        $dataList = [];
        $row = $begin;
        foreach ($alertList as $alert) {
            ++$row;
            $dataList[] = [$row, $this->view->buildCheckbox($name, $alert->getId()), $alert->getContent(), $alert->getCreationDate(), $alert->getExpirationDate(), $alert->getAuthor()->getLogin(), $this->view->buildLinkForModify(esc_url(get_permalink(get_page_by_title('Modifier une alerte'))).$alert->getId())];
        }

        $submit = filter_input(INPUT_POST, 'delete');
        if(isset($submit)) {
            if (isset($_REQUEST['checkboxStatusAlert'])) {
                $checked_values = $_REQUEST['checkboxStatusAlert'];
                foreach ($checked_values as $id) {
                    $entity = $this->model->get($id);
                    $entity->delete();
                }
                $this->view->refreshPage();
            }
        }
        if($pageNumber == 1) {
            $returnString = $this->view->contextDisplayAll();
        }
        return $returnString.$this->view->displayAll($name, 'Alertes', $header, $dataList).$this->view->pageNumber($maxPage, $pageNumber, esc_url(get_permalink(get_page_by_title('Gestion des alertes'))), $number);
    }


    /**
     * Display all alerts link to the user
     */
    public function alertMain()
    {
        // Get codes from current user
        $current_user = wp_get_current_user();
        $alertsUser = $this->model->getForUser($current_user->ID);
	    //$alertsUser = array_unique($alertsUser); // Delete duplicate

	    foreach ($this->model->getForEveryone() as $alert) {
	    	$alertsUser[] = $alert;
	    }

        $contentList = array();
        foreach ($alertsUser as $alert) {
            $endDate = date('Y-m-d', strtotime($alert->getEndDate()));
            $this->endDateCheckAlert($alert->getId(), $endDate); // Check alert

	        $content = $alert->getContent() . '&emsp;&emsp;&emsp;&emsp;';
            array_push($contentList, $content);
        }

        if (isset($content)) {
            $this->view->displayAlertMain($contentList);
        }
    } // alertMain()

    public function registerNewAlert()
    {
        $informationList = $this->model->getFromAdminWebsite();
        $myInformationList = $this->model->getAdminWebsiteAlert();
        foreach ($myInformationList as $information) {
            if($adminInfo = $this->model->getAlertFromAdminSite($information->getId())) {
                if($information->getTitle() != $adminInfo->getTitle()) {
                    $information->setTitle($adminInfo->getTitle());
                }
                if($information->getContent() != $adminInfo->getContent()) {
                    $information->setContent($adminInfo->getContent());
                }
                if($information->getExpirationDate() != $adminInfo->getExpirationDate()) {
                    $information->setExpirationDate($adminInfo->getExpirationDate());
                }
                $information->update();
            } else {
                $information->delete();
            }
        }
        foreach ($informationList as $information) {
            $exist = 0;
            foreach ($myInformationList as $myInformation) {
                if($information->getId() == $myInformation->getAdminId()) {
                    ++$exist;
                }
            }
            if($exist == 0) {
                $information->setAdminId($information->getId());
                $information->insert();
            }
        }
    }


    /**
     * ONESIGNAL NOTIFICATIONS PUSH
     *
     */
    public function sendAlert($id)
    {
        $alert = $this->model->get($id);
        $message = $alert->getContent();

        if ($alert->isForEveryone()) {
            $this->sendMessage("all", $message);
        } else {
        	$user = new User();
            $students = $user->getUsersByRole("etudiant");
            $studentLoginListToSend = array();
            foreach ($students as $student) {
                foreach ($alert->getCodes() as $code) {
                    if (in_array($code, $student->getCodes())) {
                        array_push($studentLoginListToSend, $student->getLogin());
                    }
                }
            }
            $studentLoginListToSend = array_unique($studentLoginListToSend);

            foreach ($studentLoginListToSend as $login) {
                $this->sendMessage($login, $message);
            }
        }
    }

    function sendMessage($login, $message)
    {
        $content = array(
            "en" => $message
        );
        $hashes_array = array();
        if ($login == "all") {
            $fields = array(
                'app_id' => "317b1068-1f28-4e19-81e4-9a3553c449ea",
                'included_segments' => array(
                    'All'
                ),
                'data' => array(
                    "foo" => "bar"
                ),
                'contents' => $content,
                'web_buttons' => $hashes_array
            );
        } else {
            $fields = array(
                'app_id' => "317b1068-1f28-4e19-81e4-9a3553c449ea",
                'included_segments' => array(
                    'All'
                ),
                'data' => array(
                    "foo" => "bar"
                ),
                'contents' => $content,
                'web_buttons' => $hashes_array,
                'filters' => array(array("field" => "tag", "key" => "login", "relation" => "=", "value" => $login))
            );
        }

        $fields = json_encode($fields);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json; charset=utf-8',
            'Authorization: Basic YjY1ZGUxMDktYjNhZi00NTYxLWIwZjYtNWEwMmZhNzQ2ZGY1'
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

	/**
	 * Check the end date of the alert
	 *
	 * @param $id
	 * @param $endDate
	 */
	public function endDateCheckAlert($id, $endDate)
	{
		if ($endDate <= date("Y-m-d")) {
			$alert = $this->model->get($id);
			$alert->delete();
		}
	} //endDateCheckAlert()
}