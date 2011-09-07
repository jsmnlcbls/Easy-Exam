<?php
include "/functions/common.php";
initialize();

$requestMethod = $_SERVER['REQUEST_METHOD'];

if ($requestMethod == "GET") {
	$isDatabaseInstalled = getDatabase();
	$viewArgs = array();
	if (!$isDatabaseInstalled) {
		$viewArgs['isInstalled'] = false;
	} else {
		$viewArgs['isInstalled'] = true;
		$viewArgs['view'] = filterGet("view");
	}
	echo renderView(getViewFile('adminView'), $viewArgs);
} else if ($requestMethod == "POST") {
	$action = filterPOST('action');
	if ($action == "addCategory") {
		$name = filterPOST("categoryName", "");
		$parent = intval(getPOST("parentCategory", 0));
		$menuVisibility = intval(getPOST("menuVisibility"));
		
		$data = array('name' => $name, 'parent' => $parent, 'showOnMenu' => $menuVisibility);
		include '/functions/category.php';
		$result = addCategory($data);
		displayResultNotification($result);
	} else if ($action == "addQuestion") {
		$postKeys = array('question', 'answer', 'category', 'type') 
				  + getChoicesLetterColumns();
		$data = getPOST($postKeys);
		
		include '/functions/question.php';
		$result = addQuestion($data);
		displayResultNotification($result);
	}else if ($action == "editCategory") {
		$categoryId = intval(filterPOST("categoryId"));
		$categoryName = filterPOST("categoryName");
		$parentCategory = intval(filterPOST("parentCategory"));
		$menuVisibility = intval(getPOST("menuVisibility"));
		
		$data = array('name' => $categoryName, 'parent' => $parentCategory, 'menuVisibility' => $menuVisibility);
		include '/functions/category.php';
		$result = editCategory($categoryId, $data);
		displayResultNotification($result);
	} else if ($action == "editQuestion") {
		$questionId = intval(filterPOST("questionId"));
		$postKeys = array('question', 'answer', 'category', 'type') 
				  + getChoicesLetterColumns();
		$data = getPOST($postKeys);

		include '/functions/question.php';
		$result = updateQuestion($questionId, $data);
		
		$examId = intval(getPOST('examId', ''));
		if (empty($examId)) {
			displayResultNotification($result);
		} else {
			//this does not seem to work
			//$location = array('view' => 'editExam', 'examId' => $examId, 'examView' => 'questions');
			//redirect($_SERVER['REQUEST_URI'] . "?" . http_build_query($location));
			
			//workaround
			redirect($_SERVER['REQUEST_URI'] . "?" . "view=editExamQuestions&examId=$examId");
		}
	} else if ($action == "addExam" || $action == "editExam") {
		$name = filterPOST("examName");
		$category = intval(getPOST("category"));
		$startDateTime = filterPOST("startDate") . " " . filterPOST("startTime");
		$endDateTime = filterPOST("endDate") . " " . filterPOST("endTime");
		$timeLimit = filterPOST("timeLimit");
		$passingScore = filterPOST("passingScore");
		
		$data = array('name' => $name, 'category' => $category, 
					  'startDateTime' => $startDateTime, 
					  'endDateTime' => $endDateTime, 'timeLimit' => $timeLimit, 
					  'passingScore' => $passingScore);
		
		include "functions/exam.php";
		if ($action == "addExam") {
			$result = addExam($data);
		} else if ($action == "editExam") {
			$id = intval(getPOST("examId"));
			$result = updateExam($id, $data);
		}
		displayResultNotification($result);
	} else if ($action == "deleteQuestion") {
		$id = intval(getPOST("questionId"));
		
		include "functions/question.php";
		$result = deleteQuestion($id);
		displayResultNotification($result);
	} else if ($action == "deleteQuestionFromExam") {
		$id = intval(getPOST("questionId"));
		$examId = intval(getPOST("examId"));
		
		include "functions/question.php";
		$data = getQuestionData($id);
		$data['type'] = getQuestionTypeId('Unassigned');
		
		$result = updateQuestion($id, $data);
		if ($result) {
			redirect($_SERVER['REQUEST_URI'] . "?" . "view=editExamQuestions&examId=$examId");
		} else {
			displayResultNotification(false);
		}
	} else if ($action == "deleteExam") {
		$id = intval(getPOST("examId"));
		
		include "functions/exam.php";
		$result = deleteExam($id);
		displayResultNotification($result);
	} else if ($action == "addUser") {
		include "functions/user.php";
		$name = filterPOST('username');
		$password = filterPOST('password');
		$role = getPost('role');
		
		$data = array('name' => $name, 'password' => $password, 'role' => $role);
		$result = addUser($data);
		displayResultNotification($result);
	} else if ($action == "editUser") {
		include "functions/user.php";
		$name = filterPOST("username");
		$password = filterPOST("password");
		$role = getPOST('role');
		$id = intval(getPOST('id'));
	
		$data = array('name' => $name, 'password' => $password, 'role' => $role, 'id' => $id);
		$result = updateUser($id, $data);
		displayResultNotification($result);
	} else if ($action == "deleteUser") {
		include "functions/user.php";
		$id = intval(getPOST('id'));
	
		$result = deleteUser($id);
		displayResultNotification($result);
	} elseif ($action == "install") {
		include "functions/install.php";
		$username = filterGET("databaseUser");
		$password = filterGET('databasePassword');
		$host = filterGET("databaseHost");
		$result = installDatabase($host, $username, $password);
		displayResultNotification($result);
	}
}


