<?php
include "functions/common.php";

allowOnlyIfInstalled();

initialize();
allowLoggedInUserOnly();

$requestMethod = $_SERVER['REQUEST_METHOD'];
if ($requestMethod == "GET") {
	include "functions/views.php";
	$args = array();	
	$view = getUrlQuery('view', '');
	if ('' != $view) {
		$mainPanel = renderView($view);
		$args = array('mainPanel' => $mainPanel);
	}
	output(_renderAdminPage($args));
	return;
} else if ($requestMethod == "POST") {
	$postData = getPost();
	$action = $postData['action'];
	unset($postData['action']);
	$function = "_{$action}Action";
	if (_isInActionWhitelist($action) && function_exists($function)) {
		$result = call_user_func($function, $postData);
		if (is_callable($result)) {
			call_user_func($result);
		} else {
			_displayResultNotification($result);	
		}
	} else {
		_displayResultNotification(false);
	}

	return;
}

function _addQuestionCategoryAction($data)
{
	include '/functions/question.php';
	return addQuestionCategory($data);
}

function _addQuestionAction($data)
{
	include '/functions/question.php';
	return addQuestion($data);
}

function _addUserAction($data)
{
	include "functions/user.php";
	$userData = getArrayValues($data, getAccountsTableColumns());
	return addUser($userData);
}

function _addUserGroupAction($data)
{
	include "functions/user.php";
	$userData = getArrayValues($data, array('name'));
	return addUserGroup($userData);
}

function _addExamAction($data)
{
	include "functions/exam.php";
	include "functions/question.php";
	$step = isset($data['step']) ? $data['step'] : 1;
	unset($data['step']);
	if ($step == 1) {
		$examData = getArrayValues($data, getExamTableColumns());
		$result = addExam($examData, $step);
		if (!isErrorMessage ($result)) {
			return function() use ($result) {
				redirect('admin.php?view=exam-add-step-two&examId=' . $result);
			};
		} else {
			return $result;
		}
	} else if ($step == 2) {
		return addExam($data, $step);
	}
}

function _editQuestionCategoryAction($data)
{
	include '/functions/question.php';	
	return editQuestionCategory($data); 
}

function _editQuestionAction($data)
{
	include '/functions/question.php';
	return updateQuestion($data);
}

function _editExamAction($data)
{
	include "functions/exam.php";
	include "functions/question.php";
	
	$id = $data["exam_id"];
	$step = isset($data['step']) ? $data['step'] : 1;
	unset($data['step'], $data['exam_id']);
	if ($step == 1) {
		$examData = getArrayValues($data, getExamTableColumns());
		$result = updateExam($id, $examData, $step);
		if (!isErrorMessage ($result)) {
			return function() use ($id) {
				redirect('admin.php?view=exam-edit-questions&examId=' . $id);
			};
		} else {
			return $result;
		}
	} else if ($step == 2) {
		return updateExam($id, $data, $step);
	}
}

function _editUserAction($data)
{
	include "functions/user.php";
	$userData = getArrayValues($data, getAccountsTableColumns());
	$id = $data['id'];
	return updateUser($id, $userData);
}

function _editUserGroupAction($data)
{
	include "functions/user.php";
	$groupData = getArrayValues($data, array('name'));
	$id = $data['group_id'];
	return updateUserGroup($id, $groupData);
}

function _deleteQuestionAction($data)
{
	include "functions/question.php";
	return deleteQuestion($data);
}

function _deleteQuestionCategoryAction($data)
{
	include "functions/question.php";
	return deleteQuestionCategory($data);
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

function _deleteUserGroupAction($data)
{
	include "functions/user.php";
	return deleteUserGroup($data['group_id']);
}

function _displayResultNotification($result)
{
	$notification = '';
	if (is_string($result) && isErrorMessage($result)) {
		$message = json_decode($result, true);
		$notification = '<h2>Error</h2>';
		if (is_array($message) && isset($message['ERROR'])) {
			$errorMessage = nl2br($message['ERROR']['text']);
			$notification .= '<div>'.$errorMessage.'</div>';
		} elseif (is_array($message) && isset($message['OK'])) {
			$notification .= '<div>' . $message['ERROR']['text'] . '</div>';
		}
	} elseif (is_bool($result)) {
		if ($result === true) {
		$notification = "<h2>Success!</h2>";
		} elseif ($result === false) {
			$notification = "<h2>Error. Please try again.</h2>";
		}
	}
	$args = array('mainPanel' => $notification);
	$content = _renderAdminPage($args);
	output($content);
}

function _renderAdminPage($args)
{
	$menu = renderView('admin-menu');
	$args = array_merge(array('menu' => $menu), $args);
	return renderView('admin-main', $args);
}

function _isInActionWhitelist($action)
{
	$list = array('addQuestionCategory', 'addQuestion', 'addUser', 'addExam',
				'editQuestionCategory', 'editQuestion', 'editUser', 'editExam',
				'deleteQuestionCategory', 'deleteQuestion', 'deleteUser', 'deleteExam',
				'editAdminCredentials', 
				'addUserGroup', 'editUserGroup', 'deleteUserGroup');
	
	if (in_array($action, $list)) {
		return true;
	}
	return false;
}