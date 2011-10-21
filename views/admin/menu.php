<span class="submenu-title">Question</span>
<ul class = "sub-menu">
	<li>Add Question
		<ul class ="sub-menu">
			<li><a href="?view=question-multiple-choice-add">Multiple Choice</a></li>
			<li><a href="?view=question-true-or-false-add">True/False</a></li>
			<li><a href="?view=question-essay-add">Essay</a></li>
			<li><a href="?view=question-objective-add">Objective</a></li>
		</ul>
	</li>
	<li><a href = "?view=question-category-add">Add Category</a></li>
	<li><a href = "?view=question-category-edit">Edit Category</a></li>
	<li><a href = "?view=question-category-delete">Delete Category</a></li>
	<li><a href = "?view=question-search">Search Questions</a></li>
</ul>
<br/>
<span class="submenu-title">Exam</span>
<ul class = "sub-menu">
	<li><a href="?view=exam-add-properties">Add</a></li>
	<li><a href="?view=exam-edit">Edit</a></li>
	<li><a href="?view=exam-delete">Delete</a></li>
</ul>
<br/>
<span class="submenu-title">User</span>
<ul class = "sub-menu">
	<li><a href="?view=user-add">Add User</a></li>
	<li><a href="?view=user-group-add">Add Group</a></li
	<li><a href="?view=user-list">List Users</a></li>
	<li><a href="?view=user-group-list">List Groups</a></li>
</ul>
<br/>
<span class="submenu-title">My Account</span>
<ul>
	<li>
		<form action = "login.php" method ="post" class="hidden-form">
			<input type = "hidden" name ="action" value ="logout"/>
			<button id ="logout-button">Logout</button>
		</form>
	</li>
</ul>
