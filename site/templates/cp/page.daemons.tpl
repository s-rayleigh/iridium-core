<main class="parts-container">
	<h1>Управление демонами <time id="load-time"></time></h1>
	<div class="controls-container">
		<section class="control">
			<button id="add-daemon">
				<img src="{gl_images_path}plus_icon_cleaned.svg">
				Добавить
			</button>
			<button id="stop-all">
				<img src="{gl_images_path}stop_all_icon_cleaned.svg">
				Остановить все
			</button>

			<hr>

			<input type="checkbox" id="show-enabled">
			<label for="show-enabled">Отобразить включенные</label>

			<input type="checkbox" id="show-disabled">
			<label for="show-disabled">Отобразить отключенные</label>

			<input type="checkbox" id="show-working">
			<label for="show-working">Отобразить работающие</label>

			<input type="checkbox" id="show-waiting">
			<label for="show-waiting">Отобразить ожидающие</label>

			<hr>

			<input type="text" placeholder="Поиск по названию" id="name-search">

			<hr>

			<button id="clean-filter">
				<img src="{gl_images_path}x_icon_cleaned.svg">
				Сбросить
			</button>
		</section>
		<section class="info">
			<p>Демоны &mdash; фоновые процессы, которые выполняют различные необходимые действия, которые невозможно выполнить во время загрузки страницы.</p>
			<p>Принцип работы здешних демонов таков: работа является бесконечным циклом, в котором за итерацию выполняется одной действие, после чего демон ждет указанное в его параметрах время и переходит к следующей итерации.<br>При подаче команды завершения работы демон должен закончить свою работу, после чего подождать указанное время. Только после этих действий демон может окончательно остановиться.</p>
			<p>Обновление списка происходит раз в 10 секунд.</p>
		</section>
	</div>
	<div class="blocks-container"></div>
</main>