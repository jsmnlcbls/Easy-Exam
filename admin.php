<?php
include "functions/common.php";

allowOnlyIfInstalled();

initialize();
allowLoggedInUserOnly();
allowOnlyUserRoles(array(EXAMINER_ROLE, ADMINISTRATOR_ROLE));
$requestMethod = $_SERVER['REQUEST_METHOD'];
if ($requestMethod == "GET") {
	include "functions/views.php";
	$args = array();
	$view = getUrlQuery('view', '');
	if (!_checkOwnership(getUrlQuery())) {
		_displayResultNotification(errorMessage(AUTHORIZATION_ERROR, 'Not Allowed.'));
		return;
	}
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
	$data['owner'] = getLoggedInUser('id');
	return addQuestionCategory($data);
}

function _addQuestionAction($data)
{
	include '/functions/question.php';
	$data['owner'] = getLoggedInUser('id');
	return addQuestion($data);
}

function _addUserAction($data)
{
	include "functions/user.php";
	$data['owner'] = getLoggedInUser('id');
	if (getLoggedInUser('role') == EXAMINER_ROLE) {
		$data['role'] = EXAMINEE_ROLE;
	}
	return addUser($data);
}

function _addUserGroupAction($data)
{
	include "functions/user.php";
	$data['owner'] = getLoggedInUser('id');
	return addUserGroup($data);
}

function _addExamAction($data)
{
	include "functions/exam.php";
	include "functions/question.php";
	$step = isset($data['step']) ? $data['step'] : null;
	$data['owner'] = getLoggedInUser('id');
	$result = addExam($data);
	if ($step == 1 && !isErrorMessage($result)) {
		return function() use ($result) {
				redirect('admin.php?view=exam-add-questions&exam-id=' . $result);
		};
	}
	return $result;
}

function _editQuestionCategoryAction($data)
{
	include '/functions/question.php';
	
	if (!isAllowedByOwnership(QUESTION_CATEGORY_RESOURCE, $data['category_id'])) {
		return errorMessage(AUTHORIZATION_ERROR, 'Not allowed');
	}
	return editQuestionCategory($data); 
}

function _editQuestionAction($data)
{
	include '/functions/question.php';
	
	if (!isAllowedByOwnership(QUESTION_RESOURCE, $data['question_id'])) {
		return errorMessage(AUTHORIZATION_ERROR, 'Not allowed');
	}
	return updateQuestion($data);
}

function _editExamAction($data)
{
	include "functions/exam.php";
	include "functions/question.php";
	
	if (!isAllowedByOwnership(EXAM_RESOURCE, $data['exam_id'])) {
		return errorMessage(AUTHORIZATION_ERROR, 'Not allowed');
	}
	
	$id = $data["exam_id"];
	$step = isset($data['step']) ? $data['step'] : 1;
	$result = updateExam($data);
	if ($step == 1 && !isErrorMessage ($result)) {
		return function() use ($id) {
				redirect('admin.php?view=exam-edit-questions&exam-id=' . $id);
			};
	}
	return $result;
}

function _editUserAction($data)
{
	include "functions/user.php";
	if (getLoggedInUser('role') == EXAMINER_ROLE) {
		$data['role'] = EXAMINEE_ROLE;
	}
	
	if (!isAllowedByOwnership(ACCOUNT_RESOURCE, $data['id'])) {
		return errorMessage(AUTHORIZATION_ERROR, 'Not allowed');
	}
	return updateUser($data);
}

function _editUserGroupAction($data)
{
	include "functions/user.php";
	
	if (!isAllowedByOwnership(ACCOUNT_GROUP_RESOURCE, $data['group_id'])) {
		return errorMessage(AUTHORIZATION_ERROR, 'Not allowed');
	}
	return updateUserGroup($data);
}

function _deleteQuestionAction($data)
{
	include "functions/question.php";
	
	if (!isAllowedByOwnership(QUESTION_RESOURCE, $data['question_id'])) {
		return errorMessage(AUTHORIZATION_ERROR, 'Not allowed');
	}
	return deleteQuestion($data);
}

function _deleteQuestionCategoryAction($data)
{
	include "functions/question.php";
	
	if (!isAllowedByOwnership(QUESTION_CATEGORY_RESOURCE, $data['category_id'])) {
		return errorMessage(AUTHORIZATION_ERROR, 'Not allowed');
	}
	return deleteQuestionCategory($data);
}

function _deleteExamAction($data)
{
	include "functions/exam.php";
	
	if (!isAllowedByOwnership(EXAM_RESOURCE, $data['exam_id'])) {
		return errorMessage(AUTHORIZATION_ERROR, 'Not allowed');
	}
	return deleteExam($data);
}

function _deleteUserAction($data)
{
	include "functions/user.php";
	
	if (!isAllowedByOwnership(ACCOUNT_RESOURCE, $data['id'])) {
		return errorMessage(AUTHORIZATION_ERROR, 'Not allowed');
	}
	return deleteUser($data);
}

function _deleteUserGroupAction($data)
{
	include "functions/user.php";
	if (!isAllowedByOwnership(ACCOUNT_GROUP_RESOURCE, $data['group_id'])) {
		return errorMessage(AUTHORIZATION_ERROR, 'Not allowed');
	}
	return deleteUserGroup($data);
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

function _checkOwnership($query)
{
	if (isset($query['exam-id']) && !empty($query['exam-id']) &&
		!isAllowedByOwnership(EXAM_RESOURCE, $query['exam-id'])) {
		return false;
	}
	if (isset($query['question-category-id']) && !empty($query['question-category-id']) &&
		!isAllowedByOwnership(QUESTION_CATEGORY_RESOURCE, $query['question-category-id'])) {
		return false;
	}
	if (isset($query['question-id']) && !empty($query['question-id']) &&
		!isAllowedByOwnership(QUESTION_RESOURCE, $query['question-id'])) {
		return false;
	}
	if (isset($query['id']) && !empty($query['id']) &&
		!isAllowedByOwnership(ACCOUNT_RESOURCE, $query['id'])) {
		return false;
	}
	if (isset($query['user-group-id']) && !empty($query['user-group-id']) &&
		!isAllowedByOwnership(ACCOUNT_GROUP_RESOURCE, $query['user-group-id'])) {
		return false;
	}
	return true;
}