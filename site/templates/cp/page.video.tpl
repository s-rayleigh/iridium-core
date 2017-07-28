<main>
	<div class="tabs-buttons" data-tabs-id="hierarchy-tabs">
		<button data-tab-callback="onChannelsTabOpened">Каналы</button>
		<button data-tab-callback="onSeasonsTabOpened">Сезоны</button>
		<button data-tab-callback="onVideoTabOpened">Эпизоды</button>
	</div>
	<div class="tabs" id="hierarchy-tabs">

		<!-- BEGIN: Вкладка каналов -->
		<div class="parts-container">
			<h1>Управление каналами</h1>
			<div class="controls-container">
				<section class="control">
					<button id="add-channel">
						<img src="{gl_images_path}plus_icon_cleaned.svg">
						Добавить
					</button>
					<button id="delete-selected-channels">
						<img src="{gl_images_path}delete_cleaned.svg">
						Удалить выбранные
					</button>

					<hr>

					<input id="channel-name-search" type="text" placeholder="Поиск по названию">

					<hr>
					
					<div class="page-control">
						<button id="channel-prev-page" class="tooltip icon-button" data-tooltip="Предыдущая страница" data-tt-pos="bottom" data-tt-pos-margin="20">
							<img src="{gl_images_path}prev_page_cleaned.svg">
						</button>
						<span></span>
						<button id="channel-next-page" class="tooltip icon-button" data-tooltip="Следующая страница" data-tt-pos="bottom" data-tt-pos-margin="20">
							<img src="{gl_images_path}next_page_cleaned.svg">
						</button>
					</div>
				</section>
			</div>
			<div class="blocks-container"></div>
		</div>
		<!-- END: Вкладка каналов -->

		<!-- BEGIN: Вкладка сезонов -->
		<div class="parts-container">
			<h1>Управление сезонами</h1>
			<div class="controls-container">
				<section class="control">
					<button id="add-season">
						<img src="{gl_images_path}plus_icon_cleaned.svg">
						Добавить
					</button>
					<button id="delete-selected-seasons">
						<img src="{gl_images_path}delete_cleaned.svg">
						Удалить выбранные
					</button>

					<hr>

					<input id="season-name-search" type="text" placeholder="Поиск по названию">
					<input id="season-num-search" type="number" placeholder="Поиск по номеру" min="1">

					<hr>

					<div class="page-control">
						<button id="season-prev-page" class="tooltip icon-button" data-tooltip="Предыдущая страница" data-tt-pos="bottom" data-tt-pos-margin="20">
							<img src="{gl_images_path}prev_page_cleaned.svg">
						</button>
						<span></span>
						<button id="season-next-page" class="tooltip icon-button" data-tooltip="Следующая страница" data-tt-pos="bottom" data-tt-pos-margin="20">
							<img src="{gl_images_path}next_page_cleaned.svg">
						</button>
					</div>
				</section>
			</div>
			<div class="blocks-container"></div>
		</div>
		<!-- END: Вкладка сезонов -->

		<!-- BEGIN: Вкладка эпизодов -->
		<div class="parts-container">
			<h1>Управление эпизодами</h1>
			<div class="controls-container">
				<section class="control">
					<button id="add-episode">
						<img src="{gl_images_path}plus_icon_cleaned.svg">
						Добавить
					</button>
					<button id="delete-selected-episodes">
						<img src="{gl_images_path}delete_cleaned.svg">
						Удалить выбранные
					</button>

					<hr>

					<input id="episode-name-search" type="text" placeholder="Поиск по названию">
					<input id="episode-num-search" type="number" placeholder="Поиск по номеру" min="1">

					<hr>

					<div class="page-control">
						<button id="episode-prev-page" class="tooltip icon-button" data-tooltip="Предыдущая страница" data-tt-pos="bottom" data-tt-pos-margin="20">
							<img src="{gl_images_path}prev_page_cleaned.svg">
						</button>
						<span></span>
						<button id="episode-next-page" class="tooltip icon-button" data-tooltip="Следующая страница" data-tt-pos="bottom" data-tt-pos-margin="20">
							<img src="{gl_images_path}next_page_cleaned.svg">
						</button>
					</div>
				</section>
			</div>
			<div class="blocks-container"></div>
		</div>
		<!-- END: Вкладка эпизодов -->

	</div>
</main>