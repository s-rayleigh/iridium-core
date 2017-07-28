<main class="parts-container">
	<h1>Управление пользователями</h1>
	<div class="controls-container">
		<section class="control">

			<input type="text" id="login-search" placeholder="Поиск по имени пользователя">
			<input type="text" id="email-search" placeholder="Поиск по email">

			<hr>

			<input type="checkbox" id="only-banned">
			<label for="only-banned">Только заблокированные</label>

			<input type="checkbox" id="only-unbanned">
			<label for="only-unbanned">Только не заблокированные</label>

			<hr>

			<div class="page-control">
				<button id="prev-page" class="tooltip icon-button" data-tooltip="Предыдущая страница" data-tt-pos="bottom" data-tt-pos-margin="20">
					<img src="{gl_images_path}/prev_page_cleaned.svg">
				</button>
				<span></span>
				<button id="next-page" class="tooltip icon-button" data-tooltip="Следующая страница" data-tt-pos="bottom" data-tt-pos-margin="20">
					<img src="{gl_images_path}/next_page_cleaned.svg">
				</button>
			</div>
		</section>
		<section class="info">
			<p>Для того чтобы заблокировать или разблокировать пользователя необходимо нажать на кнопку с иконкой банана.</p>
			<p>Изменение группы пользователя вступит в силу <span class="highlight">только</span> после того как пользователь выполнит переавторизацию.</p>
		</section>
	</div>
	<div class="blocks-container" id="users-list"></div>
</main>