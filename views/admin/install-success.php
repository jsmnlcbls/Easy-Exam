<div id = "install-success-panel">
	<span class = "panel-title">Successfully Installed Easy Exam</span>
	<div>
		For security concerns, kindly take the time
		to change the administrator name and password.
	</div>
	<hr/>
	<form method = "post" action = "admin.php" id = "install-form">
		<input type = "hidden" name = "action" value = "editAdminCredentials"/>
		<table>
			<tr>
				<td>New Name</td>
				<td><input type = "text" name = "name"/></td>
			</tr>
			<tr>
				<td>Password</td>
				<td><input type = "password" name = "password1"/></td>
			</tr>
			<tr>
				<td>Confirm Password</td>
				<td><input type = "password" name = "password2"/></td>
			</tr>
			<tr>
				<td></td>
				<td><input type = "submit" value = "Submit"/></td>
			</tr>
		</table>
	</form>
</div>