<main class="parts-container">
	<h1>Управление списком групп пользователей</h1>
	<div class="controls-container">
		<section class="control">
			<button id="add-button">
				<img src="{gl_images_path}plus_icon_cleaned.svg">
				Добавить
			</button>

			<hr>

			<input type="text" id="name-search" placeholder="Поиск по названию">

			<hr>

			<div class="page-control">
				<button id="prev-page" class="tooltip icon-button" data-tooltip="Предыдущая страница" data-tt-pos="bottom"
						data-tt-pos-margin="20">
					<img src="{gl_images_path}/prev_page_cleaned.svg">
				</button>
				<span id="page"></span>
				<button id="next-page" class="tooltip icon-button" data-tooltip="Следующая страница" data-tt-pos="bottom"
						data-tt-pos-margin="20">
					<img src="{gl_images_path}/next_page_cleaned.svg">
				</button>
			</div>
		</section>
		<section class="info">
			<p>
				Для выполнения каких-либо операций на этой страницы требуется <span class="highlight">полный доступ</span>.
			</p>
			<p>
				Полный доступ включает в себя все типы прав доступа.
			</p>
		</section>
	</div>
	<div class="blocks-container" id="users-groups-list"></div>
</main>