<?php

/**
 * Plugin Name:       Ecran connecté AMU
 * Plugin URI:        https://github.com/Nicolas-Rohrbach/plugin-ecran-connecte
 * Description:       Plugin écrans connectées de l'AMU, ce plugin permet de générer des fichiers ICS. Ces fichiers sont ensuite lus pour pouvoir afficher l'emploi du temps de la personne connectée. Ce plugin permet aussi d'afficher la météo, des informations, des alertes. Tant en ayant une gestion des utilisateurs et des informations.
 * Version:           1.2.9
 * Author:            Léa Arnaud & Nicolas Rohrbach
 * License:           GNU General Public License v2
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       ecran-connecte
 * GitHub Plugin URI: https://github.com/Nicolas-Rohrbach/plugin-ecran-connecte
 */

define('TV_PLUG_PATH', '/wp-content/plugins/plugin-ecran-connecte/');
define('TV_UPLOAD_PATH', '/wp-content/uploads/media/');
define('TV_ICSFILE_PATH', '/wp-content/uploads/fileICS/');

//On inclut tous les fichiers du plugin
include_once 'install_DB_Tv.php';

include_once 'controllers/ControllerG.php';
include_once 'models/Model.php';
include_once 'views/ViewG.php';

include_once 'controllers/fileR34ICS/R34ICS.php';
include_once 'views/ICSView.php';
include_once 'controllers/Schedule.php';
include_once 'widgets/WidgetSchedule.php';

include_once 'controllers/User.php';
include_once 'models/UserModel.php';
include_once 'views/UserView.php';

include_once 'controllers/CodeAde.php';
include_once 'models/CodeAdeManager.php';
include_once 'views/CodeAdeView.php';

include_once 'controllers/Student.php';
include_once 'models/StudentModel.php';
include_once 'views/StudentView.php';

include_once 'controllers/Teacher.php';
include_once 'models/TeacherModel.php';
include_once 'views/TeacherView.php';

include_once 'controllers/Television.php';
include_once 'models/TelevisionModel.php';
include_once 'views/TelevisionView.php';

include_once 'controllers/Secretary.php';
include_once 'models/SecretaryModel.php';
include_once 'views/SecretaryView.php';

include_once 'controllers/Technician.php';
include_once 'models/TechnicianModel.php';
include_once 'views/TechnicianView.php';

include_once 'controllers/StudyDirector.php';
include_once 'models/StudyDirectorModel.php';
include_once 'views/StudyDirectorView.php';

include_once 'widgets/WidgetWeather.php';

include_once 'controllers/Information.php';
include_once 'models/InformationModel.php';
include_once 'views/InformationView.php';
include_once 'widgets/WidgetInformation.php';

include_once 'controllers/Alert.php';
include_once 'models/AlertManager.php';
include_once 'views/AlertView.php';
include_once 'widgets/WidgetAlert.php';

//Blocks
include_once 'blocks/schedule/schedule.php';
include_once 'blocks/schedules/schedules.php';
include_once 'blocks/student/student.php';
include_once 'blocks/teacher/teacher.php';
include_once 'blocks/television/television.php';
include_once 'blocks/secretary/secretary.php';
include_once 'blocks/technician/technician.php';
include_once 'blocks/userManage/managementUser.php';
include_once 'blocks/userModify/userModify.php';
include_once 'blocks/myAccountPass/myAccountPass.php';
include_once 'blocks/myAccountDelete/myAccountDelete.php';
include_once 'blocks/alert/alert.php';
include_once 'blocks/alertManage/alertManage.php';
include_once 'blocks/alertModify/alertModify.php';
include_once 'blocks/information/information.php';
include_once 'blocks/informationManage/informationManage.php';
include_once 'blocks/informationModify/informationModify.php';
include_once 'blocks/codeAde/codeAde.php';
include_once 'blocks/codeAdeManage/codeAdeManage.php';
include_once 'blocks/codeAdeModify/codeAdeModify.php';
include_once 'blocks/subscriptionPush/subscriptionPush.php';
include_once 'blocks/studyDirector/studyDirector.php';
include_once 'blocks/myAccountCode/myAccountCode.php';
include_once 'blocks/userCreation/userCreation.php';
include_once 'blocks/myAccountChoose/myAccountChoose.php';
include_once 'blocks/inscription/inscription.php';
include_once 'blocks/adminInterface/adminInterface.php';

require( 'Excel/vendor/autoload.php' );

/**
 * Create all directory
 */
if (!file_exists($_SERVER['DOCUMENT_ROOT'] . TV_UPLOAD_PATH)) {
    mkdir($_SERVER['DOCUMENT_ROOT'] . TV_UPLOAD_PATH);
}

if (!file_exists($_SERVER['DOCUMENT_ROOT'] . TV_ICSFILE_PATH)) {
    mkdir($_SERVER['DOCUMENT_ROOT'] . TV_ICSFILE_PATH, 0777);
}

if (!file_exists($_SERVER['DOCUMENT_ROOT'] . TV_ICSFILE_PATH . 'file0')) {
    mkdir($_SERVER['DOCUMENT_ROOT'] . TV_ICSFILE_PATH . 'file0', 0777);
}

if (!file_exists($_SERVER['DOCUMENT_ROOT'] . TV_ICSFILE_PATH . 'file1')) {
    mkdir($_SERVER['DOCUMENT_ROOT'] . TV_ICSFILE_PATH . 'file1', 0777);
}

if (!file_exists($_SERVER['DOCUMENT_ROOT'] . TV_ICSFILE_PATH . 'file2')) {
    mkdir($_SERVER['DOCUMENT_ROOT'] . TV_ICSFILE_PATH . 'file2', 0777);
}

if (!file_exists($_SERVER['DOCUMENT_ROOT'] . TV_ICSFILE_PATH . 'file3')) {
    mkdir($_SERVER['DOCUMENT_ROOT'] . TV_ICSFILE_PATH . 'file3', 0777);
}

// Initialize plugin
add_action('init', function () {
    if (class_exists(R34ICS::class)) {
        global $R34ICS;
        $R34ICS = new R34ICS();
    }
});

$dl = $_POST['dlEDT'];
if (isset($dl)) {
    downloadFileICS_func();
}

function displaySchedule() {
    $current_user = wp_get_current_user();
    if (in_array("enseignant", $current_user->roles)) {
        $controller = new Teacher();
        $controller->displayMySchedule();
    }

    if (in_array("etudiant", $current_user->roles)) {
        $controller = new Student();
        $controller->displayMySchedule();
    }

    if (in_array("television", $current_user->roles)) {
        $controller = new Television();
        $controller->displayMySchedule();
    }

    if (in_array("technicien", $current_user->roles)) {
        $controller = new Technician();
        $controller->displayMySchedule();
    }

    if (in_array("administrator", $current_user->roles) || in_array("secretary", $current_user->roles)) {
        $controller = new Secretary();
        $view = new SecretaryView();
        $view->displayWelcomeAdmin();
    }
}

/**
 * Fonction pour la Cron de WordPress
 * Cette fonction télécharge tous les fichiers ICS des codes ADE enregistrés dans la base de données
 */
function downloadFileICS_func() {
    move_fileICS_schedule();
    $model = new CodeAdeManager();
    $allCodes = $model->getAllCode();
    $controllerAde = new CodeAde();
    foreach ($allCodes as $code) {
        $path = $controllerAde->getFilePath($code['code']);
        $controllerAde->addFile($code['code']);
        if (file_exists($path)) {
            if (filesize($path) < 200) {
                $controllerAde->addFile($code['code']);
            }
        }
    }
    $teachers = $model->getUsersByRole('enseignant');
    dlSchedule($teachers);
    $studyDirector = $model->getUsersByRole('directeuretude');
    dlSchedule($studyDirector);
}

add_action('downloadFileICS', 'downloadFileICS_func');

/**
 * Télécharge les emplois du temps des utilisateurs
 * @param $users    User[]
 */
function dlSchedule($users) {
    $controllerAde = new CodeAde();
    if (isset($users)) {
        foreach ($users as $user) {
            $codes = unserialize($user['code']);
            if (is_array($codes)) {
                foreach ($codes as $code) {
                    $path = $controllerAde->getFilePath($code);
                    $controllerAde->addFile($code);
                    if (file_exists($path)) {
                        if (file_get_contents($path) == '') {
                            $controllerAde->addFile($codes);
                        }
                    } else {
                        $controllerAde->addFile($codes);
                    }
                }
            } else {
                $path = $controllerAde->getFilePath($codes);
                $controllerAde->addFile($codes);
                if (file_exists($path)) {
                    if (file_get_contents($path) == '') {
                        $controllerAde->addFile($codes);
                    }
                } else {
                    $controllerAde->addFile($codes);
                }
            }
        }
    }
}

/**
 * Déplace les fichier ICS afin d'avoir 3 jours de fichiers sauvegardés
 */
function move_fileICS_schedule() {
    if ($myFiles = scandir($_SERVER['DOCUMENT_ROOT'] . TV_ICSFILE_PATH . 'file3')) {
        foreach ($myFiles as $myFile) {
            if (is_file($_SERVER['DOCUMENT_ROOT'] . TV_ICSFILE_PATH . 'file3/' . $myFile)) {
                wp_delete_file($_SERVER['DOCUMENT_ROOT'] . TV_ICSFILE_PATH . 'file3/' . $myFile);
            }
        }
    }
    if ($myFiles = scandir($_SERVER['DOCUMENT_ROOT'] . TV_ICSFILE_PATH . 'file2')) {
        foreach ($myFiles as $myFile) {
            if (is_file($_SERVER['DOCUMENT_ROOT'] . TV_ICSFILE_PATH . 'file2/' . $myFile)) {
                copy($_SERVER['DOCUMENT_ROOT'] . TV_ICSFILE_PATH . 'file2/' . $myFile, $_SERVER['DOCUMENT_ROOT'] . TV_ICSFILE_PATH . 'file3/' . $myFile);
                wp_delete_file($_SERVER['DOCUMENT_ROOT'] . TV_ICSFILE_PATH . 'file2/' . $myFile);
            }
        }
    }

    if ($myFiles = scandir($_SERVER['DOCUMENT_ROOT'] . TV_ICSFILE_PATH . 'file1')) {
        foreach ($myFiles as $myFile) {
            if (is_file($_SERVER['DOCUMENT_ROOT'] . TV_ICSFILE_PATH . 'file1/' . $myFile)) {
                copy($_SERVER['DOCUMENT_ROOT'] . TV_ICSFILE_PATH . 'file1/' . $myFile, $_SERVER['DOCUMENT_ROOT'] . TV_ICSFILE_PATH . 'file2/' . $myFile);
                wp_delete_file($_SERVER['DOCUMENT_ROOT'] . TV_ICSFILE_PATH . 'file1/' . $myFile);
            }
        }
    }

    if ($myFiles = scandir($_SERVER['DOCUMENT_ROOT'] . TV_ICSFILE_PATH . 'file0')) {
        foreach ($myFiles as $myFile) {
            if (is_file($_SERVER['DOCUMENT_ROOT'] . TV_ICSFILE_PATH . 'file0/' . $myFile)) {
                copy($_SERVER['DOCUMENT_ROOT'] . TV_ICSFILE_PATH . 'file0/' . $myFile, $_SERVER['DOCUMENT_ROOT'] . TV_ICSFILE_PATH . 'file1/' . $myFile);
                wp_delete_file($_SERVER['DOCUMENT_ROOT'] . TV_ICSFILE_PATH . 'file0/' . $myFile);
            }
        }
    }
}

/**
 * Inclut tous les fichiers CSS et les fichiers JS
 */
function wpdocs_plugin_ecran_connectee_scripts() {
	//CSS
    wp_enqueue_style('plugin-bootstrap-style', 'https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css', array(), true);
    wp_enqueue_style('weather-style', TV_PLUG_PATH . 'css/weather.css', array(), '1.0');
    wp_enqueue_style('style-style', TV_PLUG_PATH . 'css/style.css', array(), '1.0');
    wp_enqueue_style('alert-style', TV_PLUG_PATH . 'css/alert.css', array(), '1.0');
    wp_enqueue_style('info-style', TV_PLUG_PATH . 'css/information.css', array(), '1.0');
    wp_enqueue_style('schedule-style', TV_PLUG_PATH . 'css/schedule.css', array(), '1.0');

    // JQUERY
	wp_enqueue_script('plugin-jquerymin', TV_PLUG_PATH . 'js/jquery.min.js', array('jquery'), '', true);
	wp_enqueue_script('plugin-JqueryEzMin', TV_PLUG_PATH . 'js/jquery.easing.min.js', array('jquery'), '', true);
	wp_enqueue_script('plugin-jqueryEzTic', TV_PLUG_PATH . 'js/jquery.easy-ticker.js', array('jquery'), '', true);
	wp_enqueue_script('plugin-jqueryEzMinTic', TV_PLUG_PATH . 'js/jquery.easy-ticker.min.js', array('jquery'), '', true);
	wp_enqueue_script('plugin-marquee', TV_PLUG_PATH . 'js/jquery.marquee.js', array('jquery'), '', true);
	wp_enqueue_script('plugin-ticker', TV_PLUG_PATH . 'js/jquery.tickerNews.js', array('jquery'), '', true);

	// SCRIPT
	wp_enqueue_script('pdf-js', 'https://cdn.jsdelivr.net/npm/pdfjs-dist@2.2.228/build/pdf.min.js', array(), '', false);
	wp_enqueue_script('plugin-addCheckBox', TV_PLUG_PATH . 'js/addAllCheckBox.js', array('jquery'), '1.0', true);
	wp_enqueue_script('plugin-addCodeAlert', TV_PLUG_PATH . 'js/addOrDeleteAlertCode.js', array('jquery'), '1.0', true);
	wp_enqueue_script('plugin-addCodeTv', TV_PLUG_PATH . 'js/addOrDeleteTvCode.js', array('jquery'), '1.0', true);
	wp_enqueue_script('plugin-alertTicker', TV_PLUG_PATH . 'js/alertTicker.js', array('jquery'), '', true);
	wp_enqueue_script('plugin-scroll', TV_PLUG_PATH . 'js/scroll.js', array('plugin-jquerymin', 'plugin-jqueryEzTic', 'plugin-jqueryEzMinTic', 'plugin-JqueryEzMin'), '', true);
	wp_enqueue_script('plugin-confPass', TV_PLUG_PATH . 'js/confirmPass.js', array('jquery'), '1.0', true);
	wp_enqueue_script('plugin-showModal', TV_PLUG_PATH . 'js/modal.js', array('jquery'), '1.0', true);
	wp_enqueue_script('plugin-OneSignal', TV_PLUG_PATH . 'js/oneSignalPush.js', array('jquery'), '', true);
	wp_enqueue_script('plugin-slideshow', TV_PLUG_PATH . 'js/slideshow.js', array('jquery'), '2.0', true);
	wp_enqueue_script('plugin-weather', TV_PLUG_PATH . 'js/weather.js', array('jquery'), '1.0', true);
	wp_enqueue_script('plugin-weatherTime', TV_PLUG_PATH . 'js/weather_and_time.js', array('jquery'), '1.0', true);
}

add_action('wp_enqueue_scripts', 'wpdocs_plugin_ecran_connectee_scripts');


function manageStudent() {
    $current_user = wp_get_current_user();
    if (in_array('etudiant', $current_user->roles)) {
        $codes = unserialize($current_user->code);
        if (is_array($codes)) {
            $size = sizeof($codes);
        }
        if (empty($size)) {
            $model = new StudentModel();
            $years = $model->getCodeYear();
            $groups = $model->getCodeGroup();
            $halfgroups = $model->getCodeHalfgroup();
            selectSchedules($years, $groups, $halfgroups);
            $action = $_POST['addSchedules'];
            $year = filter_input(INPUT_POST, 'selectYears');
            $group = filter_input(INPUT_POST, 'selectGroups');
            $halfgroup = filter_input(INPUT_POST, 'selectHalfgroups');
            if ($action) {
                $current_user = wp_get_current_user();
                $codes = [$year, $group, $halfgroup];
                $model->modifyStudent($current_user->ID, $codes);
            }
        }
    }
}

function selectSchedules($years, $groups, $halfgroups) {
    echo '
        <div class="modal" id="myModal" tabindex="-1" role="dialog" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title"> Choix des emplois du temps</h5>
              </div>
              <div class="modal-body">
              <form method="post">
                <select class="form-control firstSelect" name="selectYears" required="">
                <option value="0">Aucun</option>
                        <optgroup label="Année">';
    if (is_array($years)) {
        foreach ($years as $year) {
            echo '<option value="' . $year['code'] . '">' . $year['title'] . '</option >';
        }
    } else {
        echo '<option value="' . $years['code'] . '">' . $years['title'] . '</option >';
    }
    echo '</optgroup>
    </select>
    
                <select class="form-control firstSelect" name="selectGroups" required="">
                <option value="0">Aucun</option>
                    <optgroup label="Groupe">';
    if (is_array($groups)) {
        foreach ($groups as $group) {
            echo '<option value="' . $group['code'] . '">' . $group['title'] . '</option>';
        }
    } else {
        echo '<option value="' . $groups['code'] . '">' . $groups['title'] . '</option>';
    }
    echo '</optgroup>
    </select>
    <select class="form-control firstSelect" name="selectHalfgroups" required="">
    <option value="0">Aucun</option>
          <optgroup label="Demi groupe">';
    if (is_array($halfgroups)) {
        foreach ($halfgroups as $halfgroup) {
            echo '<option value="' . $halfgroup['code'] . '">' . $halfgroup['title'] . '</option>';
        }
    } else {
        echo '<option value="' . $halfgroups['code'] . '">' . $halfgroups['title'] . '</option>';
    }
    echo '</optgroup>
                </select>
                <input type="submit" name="addSchedules">
                </form>
                </div>
            </div>
          </div>
        </div>
        
        <script> $("#myModal").show() </script>';
}

add_action('check', 'manageStudent');

function displayParticipant() {
    $url = "https://www.nuitdelinfo.com/inscription/sites/55";
    $result = file_get_contents($url);
    $result = explode('<li class="list-group-item list-group-item-info">', $result);
    $result1 = substr($result[1], 0, -60);
    $result2 = substr($result[2], 0, 70);
    return '<p class="info-text"> - '.$result1.'</p><p class="info-text"> - '.$result2.'</p>';
}