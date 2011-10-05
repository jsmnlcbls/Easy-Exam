<div id = "install-panel">
	<span class = "panel-title">Install Easy Exam</span>
	<form method = "post" action = "install.php" id = "install-form">
		<input type = "hidden" name = "action" value = "install"/>
		<table>
			<tr>
				<td colspan="2"><hr/><em>Database Configuration</em></td>
			</tr>
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
				<td><input type = "text" name = "host" value ="localhost"/></td>
			</tr>
			<tr>
				<td>Database Name</td>
				<td>
					<input type = "text" name = "database"/>
				</td>
			</tr>
			<tr>
				<td>Database User</td>
				<td><input type = "text" name = "user"/></td>
			</tr>
			<tr>
				<td>Database Password</td>
				<td><input type = "text" name = "password"/></td>
			</tr>
			<tr>
				<td colspan="2"><hr/><em>Administrator Credentials</em></td>
			</tr>
			<tr>
				<td>Username</td>
				<td><input type = "text" name = "adminUsername"/></td>
			</tr>
			<tr>
				<td>Password</td>
				<td><input type = "password" name = "adminPassword"/></td>
			</tr>
			<tr>
				<td>Confirm Password</td>
				<td><input type = "password" name = "adminPasswordConfirmation"/></td>
			</tr>
			<tr>
				<td></td>
				<td><input type = "submit" value = "Install"/></td>
			</tr>
			<tr>
				<td colspan="2" style="font-size:80%; color: green;font-style: italic">
					<p>The database name and database user must be created first <br/>
					if they do not exist yet. The database user should also have <br/>
					full privileges on that new database.</p>
					<p>**For MySQL: When creating a new user, the host login part<br/>
						should be the same as the database host.
					</p>
				</td>
			</tr>
		</table>
	</form>
</div>
		