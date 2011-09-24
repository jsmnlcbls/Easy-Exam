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
	$categoryData = getArrayValues($data, array('name', 'parent'));
	include '/functions/question.php';
	return addQuestionCategory($categoryData);
}

function _addQuestionAction($data)
{
	include '/functions/question.php';
	$type = $data['type'];
	$options = array('TYPE' => $type);
	$mainTableColumns = getQuestionTableColumns();
	$secondaryTableColumns = getQuestionTableColumns($options);
	$columns = array_merge($mainTableColumns, $secondaryTableColumns);
	$questionData = getArrayValues($data, $columns);
	return addQuestion($type, $questionData);
}

function _addUserAction($data)
{
	include "functions/user.php";
	$userData = getArrayValues($data, getAccountsTableColumns());
	return addUser($userData);
}

function _addExamAction($data)
{
	$examData = getArrayValues($data, array('name', 'category', 'timeLimit', 'passingScore'));
	$examData['startDateTime'] = $data["startDate"] . " " . $data["startTime"];
	$examData['endDateTime'] = $data["endDate"] . " " . $data["endTime"];
	
	include "functions/exam.php";
	return addExam($examData);
}

function _editCategoryAction($data)
{
	$categoryId = $data["categoryId"];
	$categoryData = getArrayValues($data, array('name', 'parent'));
	
	include '/functions/question.php';
	return editQuestionCategory($categoryId, $categoryData); 
}

function _editQuestionAction($data)
{
	include '/functions/question.php';
	$id = $data['question_id'];
	$type = $data['type'];
	$options = array('TYPE' => $type);
	$mainTableColumns = getQuestionTableColumns();
	$secondaryTableColumns = getQuestionTableColumns($options);
	$columns = array_merge($mainTableColumns, $secondaryTableColumns);
	$questionData = getArrayValues($data, $columns);
	
	$result = updateQuestion($id, $questionData);
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
	$examData = getArrayValues($data, array('name', 'category', 'timeLimit', 'passingScore'));
	$examData['startDateTime'] = $data["startDate"] . " " . $data["startTime"];
	$examData['endDateTime'] = $data["endDate"] . " " . $data["endTime"];
	
	include "functions/exam.php";
	$id = $data["examId"];
	return updateExam($id, $examData);
}

function _editUserAction($data)
{
	include "functions/user.php";
	$userData = getArrayValues($data, getAccountsTableColumns());
	$id = $data['id'];
	return updateUser($id, $userData);
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
	return installDatabase($data);
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