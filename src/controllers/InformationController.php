<?php

namespace Controllers;

use Models\Information;
use Views\InformationView;

/**
 * Class InformationController
 *
 * Manage information (create, update, delete, display)
 *
 * @package Controllers
 */
class InformationController extends Controller
{

	/**
	 * @var Information
	 */
	private $model;

	/**
	 * @var InformationView
	 */
	private $view;

	/**
	 * Constructor of InformationController
	 */
	public function __construct()
	{
		$this->model = new Information();
		$this->view  = new InformationView();
	}

	/**
	 * Create information and add it into the database
	 *
	 * @return string
	 * @throws \PhpOffice\PhpSpreadsheet\Exception
	 * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
	 */
	public function insertInformation() {

		// The current user who want to create the information
		$current_user = wp_get_current_user();

		// All forms
		$actionText  = $_POST['createText'];
		$actionImg   = $_POST['createImg'];
		$actionTab   = $_POST['createTab'];
		$actionPDF   = $_POST['createPDF'];
		$actionEvent = $_POST['createEvent'];

		// Variables
		$title        = filter_input( INPUT_POST, 'titleInfo' );
		$content      = filter_input( INPUT_POST, 'contentInfo' );
		$endDate      = filter_input( INPUT_POST, 'endDateInfo' );
		$creationDate = date('Y-m-d');

		// If the title is empty
		if ($title == '') {
			$title = 'Sans titre';
		}

		// Set the base of all information
		$this->model->setTitle($title);
		$this->model->setAuthor($current_user->ID);
		$this->model->setCreationDate($creationDate);
		$this->model->setEndDate($endDate);

		if ($actionText) {   // If the information is a text
			$this->model->setContent($content);
			$this->model->setType("text");

			// Try to insert the information
			if($this->model->create()) {
				$this->view->displayCreateValidate();
			} else {
				$this->view->displayErrorInsertionInfo();
			}
		} elseif ($actionImg) {  // If the information is an image
			$type = "img";
			$this->model->setType($type);
			$filename    = $_FILES['contentFile']['name'];
			$fileTmpName = $_FILES['contentFile']['tmp_name'];
			$explodeName = explode('.', $filename);
			$goodExtension = ['jpg', 'jpeg', 'gif', 'png', 'svg'];
			if(in_array(end($explodeName), $goodExtension)) {
				$this->registerFile($filename, $fileTmpName);
			} else {
				echo 'image non valide';
			}
		} elseif ($actionTab) { // If the information is a table
			$type = "tab";
			$this->model->setType($type);
			$filename    = $_FILES['contentFile']['name'];
			$fileTmpName = $_FILES['contentFile']['tmp_name'];
			$explodeName = explode('.', $filename);
			$goodExtension = ['xls', 'xlsx', 'ods'];
			if(in_array(end($explodeName), $goodExtension)) {
				$this->registerFile($filename, $fileTmpName);
			}
		} else if ($actionPDF) {
			$type = "pdf";
			$this->model->setType($type);
			$filename    = $_FILES['contentFile']['name'];
			$explodeName = explode('.', $filename);
			if(end($explodeName) == 'pdf') {
				$fileTmpName = $_FILES['contentFile']['tmp_name'];
				$this->registerFile($filename, $fileTmpName);
			} else {
				echo 'PDF non valide';
			}
		} else if ($actionEvent) {
			$type       = "event";
			$this->model->setType($type);

			// Register all files
			$countFiles = count( $_FILES['contentFile']['name'] );
			for ( $i = 0; $i < $countFiles; $i ++ ) {
				$this->model->setId(null);
				$filename    = $_FILES['contentFile']['name'][$i];
				$fileTmpName = $_FILES['contentFile']['tmp_name'][$i];
				$explodeName = explode('.', $filename);
				$goodExtension = ['jpg', 'jpeg', 'gif', 'png', 'svg', 'pdf'];
				if(in_array(end($explodeName), $goodExtension)) {
					$this->registerFile($filename, $fileTmpName);
				}
			}
		}

		// Return a selector with all forms
		return
			$this->view->displayStartMultiSelect() .
			$this->view->displayTitleSelect('text','Texte', true) .
			$this->view->displayTitleSelect('image','Image') .
			$this->view->displayTitleSelect('table','Tableau') .
			$this->view->displayTitleSelect('pdf','PDF') .
			$this->view->displayTitleSelect('event', 'Événement') .
			$this->view->displayEndOfTitle() .
			$this->view->displayContentSelect('text', $this->view->displayFormText(), true) .
			$this->view->displayContentSelect('image', $this->view->displayFormImg()) .
			$this->view->displayContentSelect('table', $this->view->displayFormTab()) .
			$this->view->displayContentSelect('pdf', $this->view->displayFormPDF()) .
			$this->view->displayContentSelect('event', $this->view->displayFormEvent()) .
			$this->view->displayEndDiv();

	} //insertInformation()


	/**
	 * Upload a file in a directory and in the database
	 *
	 * @param $filename     string
	 * @param $tmpName      string
	 */
	public function registerFile($filename, $tmpName)
	{
		$current_user = wp_get_current_user();
		$id               = "temporary";
		$extension_upload = strtolower(substr(strrchr($filename, '.'), 1));
		$name              = $_SERVER['DOCUMENT_ROOT'] . TV_UPLOAD_PATH . $id . "." . $extension_upload;

		// Upload the file
		if ($result = move_uploaded_file($tmpName, $name)) {
			$this->model->setContent("temporary content");
			if($this->model->getId() == null) {
				$id = $this->model->create();
			} else {
				$this->model->update();
				$id = $this->model->getId();
			}
		} else {
			$this->view->displayErrorInsertionInfo();
		}

		// If the file upload and the upload of the information in the database works
		if ($id != 0) {

			$this->model->setId($id);

			$md5Name = $id.md5_file($name);
			rename($name, $_SERVER['DOCUMENT_ROOT'] . TV_UPLOAD_PATH. $md5Name . '.' . $extension_upload);

			$content = $md5Name. '.' . $extension_upload;

			$this->model->setContent($content);
			$this->model->update();
			$this->view->displayCreateValidate();
		}
	}

	/**
	 * Modify the information
	 *
	 * @return string
	 * @throws \PhpOffice\PhpSpreadsheet\Exception
	 * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
	 */
	public function modifyInformation()
	{
		// Id of the information
		$id = $this->getMyIdUrl();
		if(!is_numeric($id)) {
			return;
		}

		$this->model  = $this->model->get($id);
		if(is_null($this->model->getId())) {
			return;
		}

		$action = filter_input(INPUT_POST, 'submit');

		if ($action) {

			$title   = filter_input(INPUT_POST, 'titleInfo');
			$content = filter_input(INPUT_POST, 'contentInfo');
			$endDate = $_POST['endDateInfo'];

			if($this->model->getType() === 'text') {
				// Set new information
				$this->model->setTitle($title);
				$this->model->setContent($content);
				$this->model->setEndDate($endDate);
			} else {

					$this->model->setTitle($title);
				$this->model->setEndDate($endDate);

				// Change the content
				if ($_FILES["contentFile"]['size'] != 0 ) { // If it's a new file

					$filename = $_FILES["contentFile"]['name'];

					if($this->model->getType() == 'img') {
						$explodeName = explode('.', $filename);
						$goodExtension = ['jpg', 'jpeg', 'gif', 'png', 'svg'];
						if(in_array(end($explodeName), $goodExtension)) {
							$this->deleteFile($this->model->getId());   //$_SERVER['DOCUMENT_ROOT'].$this->model->getContent()
							$this->registerFile($filename, $_FILES["contentFile"]['tmp_name']);
						}

					} else if($this->model->getType() == 'pdf') {
						$explodeName = explode('.', $filename);
						if(end($explodeName) == 'pdf') {
							$this->deleteFile($this->model->getId());   //$_SERVER['DOCUMENT_ROOT'].$this->model->getContent()
							$this->registerFile($filename, $_FILES["contentFile"]['tmp_name']);
						}

					} else if($this->model->getType() == 'tab') {
						$explodeName = explode('.', $filename);
						$goodExtension = ['xls', 'xlsx', 'ods'];
						if(in_array(end($explodeName), $goodExtension)) {
							$this->deleteFile($this->model->getId());   //$_SERVER['DOCUMENT_ROOT'].$this->model->getContent()
							$this->registerFile($filename, $_FILES["contentFile"]['tmp_name']);
						}
					}
				}
			}

			$this->model->update();

			$this->view->displayModifyValidate();
		}

		// Display the view / the form
		return $this->view->displayModifyInformationForm($this->model->getTitle(), $this->model->getContent(), $this->model->getEndDate(), $this->model->getType());
	} //modifyInformation()


	/**
	 * Delete the information
	 */
	public function deleteInformations()
	{
		$actionDelete = $_POST['Delete'];
		if ($actionDelete) {
			if (isset($_REQUEST['checkboxstatusinfo'])) {
				// Take all checkbox
				$checked_values = $_REQUEST['checkboxstatusinfo'];
				foreach ($checked_values as $id) {
					$this->model = $this->model->get($id);
					$type  = $this->model->getType();
					$types = ["img", "pdf", "tab", "event"];
					if (in_array($type, $types)) {
						$this->deleteFile($id);
					}
					$this->model->delete();
				}
			}
			$this->view->refreshPage();
		}
	} //deleteInformations()

	/**
	 * Delete the file who's link to the id
	 *
	 * @param $id int Code
	 */
	public function deleteFile($id)
	{
		$this->model = $this->model->get($id);
		$source = $_SERVER['DOCUMENT_ROOT'] . TV_UPLOAD_PATH . $this->model->getContent();
		wp_delete_file($source);
	}

	/**
	 * Display a table with all informations from the database
	 */
	function informationManagement()
	{
		$current_user = wp_get_current_user();
		$user         = $current_user->ID;
		if (in_array( "administrator", $current_user->roles)) {
			$informations = $this->model->getAll();
		} else {
			$informations = $this->model->getAuthorListInformation($user);
		}

		return $this->view->displayAllInformation($informations);
	} // informationManagement()



	/**
	 * Check if the end date is today or less
	 * And delete the file if the date is past
	 *
	 * @param $id
	 * @param $endDate
	 */
	public function endDateCheckInfo($id, $endDate)
	{
		if ($endDate <= date("Y-m-d")) {
			$information = $this->model->get($id);
			$this->deleteFile($id);
			$information->delete();
		}
	} //endDateCheckInfo()


	/**
	 * Display a slideshow
	 * The slideshow display all the informations
	 *
	 * @throws \PhpOffice\PhpSpreadsheet\Exception
	 * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
	 */
	public function informationMain()
	{
		// Get all informations
		$informations = $this->model->getAll();

		// Slideshow
		$this->view->displayStartSlideshow();
		foreach ($informations as $information) { // Create a slide for each information
			if ($information->getType() == 'tab') {
					$list = $this->readSpreadSheet(TV_UPLOAD_PATH  . $information->getContent());
					$content = "";
					foreach ($list as $table) {
						$content .= $table;
					}
					$information->setContent($content);
			}
			$endDate = date( 'Y-m-d', strtotime($information->getEndDate()));
			$this->endDateCheckInfo($information->getId(), $endDate);
			$this->view->displaySlide($information->getTitle(), $information->getContent(), $information->getType());
		}
		$this->view->displayEndDiv();
	} // informationMain()

	/**
	 *  Display a slideshow of event information in full screen
	 */
	public function displayEvent()
	{
		// Get all event informations
		$events = $this->model->getListInformationEvent();

		// Slideshow
		$this->view->displayStartSlideEvent();
		foreach ($events as $event) {
			$this->view->displaySlideBegin();
			$extension = explode('.', $event->getContent());
			$extension = $extension[1];
			if($extension == "pdf") {
				echo '
				<div class="canvas_pdf" id="'.$event->getContent().'">
				</div>';
				//echo do_shortcode('[pdf-embedder url="'.$event->getContent().'"]');
			} else {
				echo '<img src="'. TV_UPLOAD_PATH . $event->getContent() . '" alt="'.$event->getTitle().'">';
			}
			echo $this->view->displayEndDiv();
		}
		$this->view->displayEndDiv();
	}

	/**
	 * Read an excel file
	 *
	 * @param $content
	 *
	 * @return array
	 * @throws \PhpOffice\PhpSpreadsheet\Exception
	 * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
	 */
	public function readSpreadSheet($content)
	{
		$file = $_SERVER['DOCUMENT_ROOT'] . $content;

		$extension = ucfirst(strtolower(end(explode(".", $file))));
		$reader    = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($extension);
		$reader->setReadDataOnly(true);
		$spreadsheet = $reader->load($file);

		$worksheet  = $spreadsheet->getActiveSheet();
		$highestRow = $worksheet->getHighestRow();

		$contentList = array();
		$content     = "";
		$mod         = 0;

		for ($i = 0; $i < $highestRow; ++ $i) {
			$mod = $i % 10;
			if ($mod == 0) {
				$content .= '<table class ="table table-bordered tablesize">';
			}
			foreach ($worksheet->getRowIterator($i + 1, 1) as $row) {
				$content      .= '<tr scope="row">';
				$cellIterator = $row->getCellIterator();
				$cellIterator->setIterateOnlyExistingCells(false);
				foreach ($cellIterator as $cell) {
					$content .= '<td class="text-center">' .
					            $cell->getValue() .
					            '</td>';
				}
				$content .= '</tr>';
			}
			if ($mod == 9) {
				$content .= '</table>';
				array_push($contentList, $content);
				$content = "";
			}
		}
		if ($mod != 9 && $i > 0) {
			$content .= '</table>';
			array_push($contentList, $content);
		}

		return $contentList;
	}
}