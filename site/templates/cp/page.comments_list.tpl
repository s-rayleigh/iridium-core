<main class="parts-container">
	<h1>
		Список комментариев&nbsp;
		{if type == comment_type_episode}
			эпизода "{object_data['name']}"
		{else if type == comment_type_season}
			сезона "{object_data['name']}"
		{else if type == comment_type_channel}
			канала "{object_data['name']}"
		{/if}
	</h1>
	<div class="controls-container">
		<section class="control">
			<button id="delete-selected">
				<img src="{gl_images_path}delete_cleaned.svg">
				Удалить выбраные
			</button>

			<hr>

			<div class="page-control">
				<button id="prev-page" class="tooltip icon-button" data-tooltip="Предыдущая страница" data-tt-pos="bottom" data-tt-pos-margin="20">
					<img src="{gl_images_path}prev_page_cleaned.svg">
				</button>
				<span></span>
				<button id="next-page" class="tooltip icon-button" data-tooltip="Следующая страница" data-tt-pos="bottom" data-tt-pos-margin="20">
					<img src="{gl_images_path}next_page_cleaned.svg">
				</button>
			</div>

		</section>
	</div>
	<div class="blocks-container" id="comments-list"></div>
</main>