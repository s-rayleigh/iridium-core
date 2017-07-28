<main>
	<section class="block block-shadow">
		<div class="images">
			<img src="{pg_image_path}" class="page-img">
			<img src="{logo_path}" class="logo-img">
		</div>
		<div class="name-container">
			<div class="name">
				{name}
				{if is_category}
					<br>
					<span class="small tooltip" data-tooltip="Категория">{category}</span>
				{/if}
			</div>
			<div id="subscribe-container" class="subscribe-container"></div>
		</div>
		<hr>
		<div class="description">
			{description}
		</div>
	</section>

	<section class="block block-shadow episodes">
		<h2>Последние эпизоды</h2>
		<hr>
		<div class="episodes-container" id="last-episodes-list"></div>
	</section>

	<section class="block block-shadow" id="seasons-list">
		<h2>Список сезонов</h2>
		<hr>
	</section>
</main>