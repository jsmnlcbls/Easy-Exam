<div id = "install-panel">
	<span class = "panel-title">Install Easy Exam Database</span>
	<form method = "post" action = "admin.php" id = "install-form">
		<input type = "hidden" name = "action" value = "install"/>
		<table>
			<tr>
				<td>Database Software</td>
				<td>
					<select name="dsnPrefix">
						<option value="mysql">MySQL</option>
					</select>
				</td>
			</tr>
			<tr>
				<td>Database Host</td>
				<td><input type = "text" name = "host" value ="127.0.0.1"/></td>
			</tr>
			<tr>
				<td>Database Name</td>
				<td>
					<input type = "text" name = "database"/>
				</td>
			</tr>
			<tr>
				<td>Database Username</td>
				<td><input type = "text" name = "user"/></td>
			</tr>
			<tr>
				<td>Database Password</td>
				<td><input type = "text" name = "password"/></td>
			</tr>
			<tr>
				<td></td>
				<td><input type = "submit" value = "Install"/></td>
			</tr>
		</table>
	</form>
</div>
		