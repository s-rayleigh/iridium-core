<menu class="unselectable">
	<a href="/" class="logo" title="Перейти на главную страницу">
		<img src="{gl_images_path}beleme_logotype_cleaned.svg" alt="Логотип">
		<span style="position: absolute;left:-500px;">a</span> <!-- не убирать! это костыль чтоб в chromium (а может и в chrome тоже) под линуксом нормально шрифты отображались  -->
		<span class="logo-label">{site_name}</span>
	</a>
	
	<div class="menu">
		{foreach link in links}
			<a href="?page={link['page']}"{if link['selected']} class="active"{/if}>
				<img class="inline-svg" src="{gl_images_path}/{link['img']}_cleaned.svg">
				<span>{link['name']}</span>
			</a>
		{/foreach}
	</div>

	{if cp_link}
	<div class="cp-link">
		<a href="index.php?page=cp.index">Панель управления</a>
	</div>
	{/if}

	<div class="copyright">
		{site_name} &copy; 2016
	</div>
</menu>