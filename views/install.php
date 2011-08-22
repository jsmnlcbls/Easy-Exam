<div id = "install-panel">
	<span class = "panel-title">Install Easy Exam Database</span>
	<div><em>Initial Database Connection Parameters To Use</em></div>
	<form method = "post" action = "admin.php" id = "install-form">
		<input type = "hidden" name = "action" value = "install"/>
		<table>
			<tr>
				<td>Username</td>
				<td><input type = "text" name = "databaseUser"/></td>
			</tr>
			<tr>
				<td>Password</td>
				<td><input type = "text" name = "databasePassword"/></td>
			</tr>
			<tr>
				<td>Host</td>
				<td><input type = "text" name = "databaseHost"/></td>
			</tr>
			<tr>
				<td></td>
				<td><input type = "submit" value = "Install"/></td>
			</tr>
		</table>
	</form>
</div>
		