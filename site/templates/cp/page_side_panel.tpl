<menu class="unselectable">
	<a href="index.php?page=cp.index" class="logo" title="Перейти на главную страницу панели администратора">
		<img src="{gl_images_path}beleme_logotype_cleaned.svg" alt="Логотип">
		<span>Панель управления</span>
	</a>
	<div class="menu">
		{foreach link in links}
			<a href="index.php?page=cp.{link['href']}"{if link['selected']} class="active"{/if}>
				<img src="{gl_images_path}{link['img']}.svg" class="inline-svg">
				<span>{link['name']}</span>
			</a>
		{/foreach}
	</div>
	<span class="admin-access">
	{if admin_access}
		У вас есть доступ администратора. Если он более вам не нужен, вы можете <a href="#" id="disable-access">отключить</a> его.
	{else}
		<a href="index.php?page=cp.getAccess">Получить</a> доступ администратора
	{/if}
	</span>
</menu>