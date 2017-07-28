<main>
	<form class="unselectable block-shadow" id="reg_form" method="POST" action="index.php?op=register">
		<table>
			<caption>Регистрация</caption>
			<tr>
				<td><label for="r_login" class="tooltip information" data-tooltip="От 4 до 15 символов: буквы латинского алфавита, цифры от 0 до 9, а также символы _ и -.">Имя пользователя</label></td>
				<td><input type="text" name="login" id="r_login" pattern="{login_regexpr}" title="От 4 до 15 символов: буквы латинского алфавита, цифры от 0 до 9, а также символы _ и -." required></td>
			</tr>

			<tr>
				<td><label for="r_password" class="tooltip information" data-tooltip="От 4 до 128 символов.">Пароль</label></td>
				<td><input type="password" name="password" id="r_password" pattern="{password_regexpr}" title="От 4 до 128 символов." required></td>
			</tr>

			<tr>
				<td><label for="rpassword">Повторите пароль</label></td>
				<td><input type="password" name="rpassword" id="rpassword" required></td>
			</tr>

			<tr>
				<td><label for="email">E-mail</label></td>
				<td><input type="email" id="email" name="email" pattern="{email_regexpr}" required></td>
			</tr>

			<tr>
				<td><label for="first_name">Имя</label></td>
				<td><input type="text" id="first_name" name="first_name" pattern="{name_regexpr}"></td>
			</tr>

			<tr>
				<td><label for="second_name">Фамилия</label></td>
				<td><input type="text" id="second_name" name="second_name" pattern="{name_regexpr}"></td>
			</tr>

			<tr>
				<td colspan="2"><input type="submit" value="Зарегистрироваться"></td>
			</tr>
		</table>
	</form>
	<div class="forms_separator"></div>
	<form class="unselectable block-shadow" id="login_form" method="POST" action="index.php?op=login">
		<table>
			<caption>Вход</caption>
			<tr>
				<td><label for="l_login">Имя пользователя</label></td>
				<td><input name="login" id="l_login" type="text" required autofocus></td>
			</tr>
			<tr>
				<td><label for="l_password">Пароль</label></td>
				<td><input name="password" id="l_password" type="password" required></td>
			</tr>
			<tr>
				<td colspan="2"><input type="submit" value="Войти"></td>
			</tr>
		</table>
	</form>
</main>