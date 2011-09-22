<?php
include "/functions/common.php";
initialize();

$requestMethod = $_SERVER['REQUEST_METHOD'];

if ($requestMethod == "GET") {
	include "functions/views.php";
	$isDatabaseInstalled = getDatabase();
	$args = array();
	if (!$isDatabaseInstalled) {
		$mainPanel = renderView('admin-install');
		$args = array('mainPanel' => $mainPanel, 'menu' => '');
	} else {
		$view = filterGET('view', '');
		if ('' != $view) {
			$mainPanel = renderView($view);
			$args = array('mainPanel' => $mainPanel);
		}
	}
	echo _renderAdminPage($args);
	return;
} else if ($requestMethod == "POST") {
	$action = $_POST['action'];
	unset($_POST['action']);
	$function = "_{$action}Action";
	
	if (_isInActionWhitelist($action) && function_exists($function)) {
		$success = call_user_func($function, $_POST);
		if (is_bool($success)) {
			_displayResultNotification($success);
		}
	} else {
		_displayResultNotification(false);
	}
	return;
}

function _addCategoryAction($data)
{
	$name = filterPOST("categoryName", "");
	$parent = intval(getPOST("parentCategory", 0));

	$data = array('name' => $name, 'parent' => $parent);
	include '/functions/category.php';
	return addCategory($data);
}

function _addQuestionAction($data)
{
	$type = $data['type'];
	$data = getPOST();

	include '/functions/question.php';
	return addQuestion($type, $data);
}

function _addUserAction($data)
{
	include "functions/user.php";
	$name = $data['username'];
	$password = $data['password'];
	$role = $data['role'];

	$data = array('name' => $name, 'password' => $password, 'role' => $role);
	return addUser($data);
}

function _addExamAction($data)
{
	$name = $data["examName"];
	$category = $data["category"];
	$startDateTime = $data["startDate"] . " " . $data["startTime"];
	$endDateTime = $data["endDate"] . " " . $data["endTime"];
	$timeLimit = $data["timeLimit"];
	$passingScore = $data["passingScore"];

	$data = array('name' => $name, 'category' => $category, 
				  'startDateTime' => $startDateTime, 
				  'endDateTime' => $endDateTime, 'timeLimit' => $timeLimit, 
				  'passingScore' => $passingScore);

	include "functions/exam.php";
	return addExam($data);
}

function _editCategoryAction($data)
{
	$categoryId = $data["categoryId"];
	$categoryName = $data["categoryName"];
	$parentCategory = $data["parentCategory"];
	
	$data = array('name' => $categoryName, 'parent' => $parentCategory);
	include '/functions/category.php';
	return editCategory($categoryId, $data); 
}

function _editQuestionAction($data)
{
	$id = $data['question_id'];
	include '/functions/question.php';
	$result = updateQuestion($id, $data);
	$examId = intval(getPOST('examId', ''));
	if (empty($examId)) {
		_displayResultNotification($result);
	} else {
		//this does not seem to work
		//$location = array('view' => 'editExam', 'examId' => $examId, 'examView' => 'questions');
		//redirect($_SERVER['REQUEST_URI'] . "?" . http_build_query($location));

		//workaround
		redirect($_SERVER['REQUEST_URI'] . "?" . "view=editExamQuestions&examId=$examId");
	} 
}

function _editExamAction($data)
{
	$name = $data["examName"];
	$category = $data["category"];
	$startDateTime = $data["startDate"] . " " . $data["startTime"];
	$endDateTime = $data["endDate"] . " " . $data["endTime"];
	$timeLimit = $data["timeLimit"];
	$passingScore = $data["passingScore"];

	$data = array('name' => $name, 'category' => $category, 
				  'startDateTime' => $startDateTime, 
				  'endDateTime' => $endDateTime, 'timeLimit' => $timeLimit, 
				  'passingScore' => $passingScore);

	include "functions/exam.php";

	$id = intval(getPOST("examId"));
	return updateExam($id, $data);
}

function _editUserAction($data)
{
	include "functions/user.php";
	$name = $data["username"];
	$password = $data["password"];
	$role = $data['role'];
	$id = $data['id'];

	$data = array('name' => $name, 'password' => $password, 'role' => $role, 'id' => $id);
	return updateUser($id, $data);
}

function _deleteQuestionAction($data)
{
	include "functions/question.php";
	return deleteQuestion($data["questionId"]);
}

function _deleteExamAction($data)
{
	include "functions/exam.php";
	return deleteExam($data["examId"]);
}

function _deleteUserAction($data)
{
	include "functions/user.php";
	return deleteUser($data['id']);
}

function _installAction($data)
{
	include "functions/install.php";
	$username = $data["databaseUser"];
	$password = $data['databasePassword'];
	$host = $data["databaseHost"];
	return installDatabase($host, $username, $password);
}

function _displayResultNotification($success)
{
	$notification = '';
	if ($success === true) {
		$notification = "<h2>Success!</h2>";
	} elseif ($success === false) {
		$notification = "<h2>Error. Please try again.</h2>";
	}
	$args = array('mainPanel' => $notification);
	echo _renderAdminPage($args);
}

function _renderAdminPage($args)
{
	$menu = renderView('admin-menu');
	$args = array_merge(array('menu' => $menu), $args);
	return renderView('admin-main', $args);
}

function _isInActionWhitelist($action)
{
	$list = array('addCategory', 'addQuestion', 'addUser', 'addExam',
				'editCategory', 'editQuestion', 'editUser', 'editExam',
				'deleteCategory', 'deleteQuestion', 'deleteUser', 'deleteExam', 'install');
	
	if (in_array($action, $list)) {
		return true;
	}
	return false;
}