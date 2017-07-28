/**
 * Модуль управления главной страницей
 * @module index
 */

window.addEventListener('load', function()
{
	var episodesContainer   = document.getElementsByClassName('episodes-container')[0],
		inputField          = document.getElementById('episodes-search-field'),
		lastEpTime,
		loadMoreEp          = false,
		episodesList        = new DataList({
			opUrl: 'index.php?op=eslist.episodesList',
			onLoad: function(result)
			{
				if(loadMoreEp)
				{
					loadMoreEp = false;
				}
				else
				{
					while(episodesContainer.children.length > 0)
					{
						episodesContainer.removeChild(episodesContainer.firstChild);
					}
				}

				if(!result.count)
				{ return; }

				result.list.forEach(function(ep)
				{
					var plank = createEpisodeObject(ep);
					episodesContainer.appendChild(plank);
				});

				lastEpTime = result.list[result.list.length - 1].time;
			}
		}),
		categoriesContainer = document.getElementById('categories'),
		selectedCategory    = document.getElementById('all-videos'),
		allCategoriesLink   = document.getElementById('all-categories'),
		categoriesDataList  = new DataList({
			opUrl: 'index.php?op=eslist.categoriesList',
			onLoad: function(result)
			{
				result.list.forEach(function(cat)
				{
					var catObj = document.createElement('a');
					catObj.appendChild(document.createTextNode(cat.name));

					catObj.addEventListener('click', function(e)
					{
						e.preventDefault();

						catObj.className           = 'selected';
						selectedCategory.className = '';
						selectedCategory           = catObj;

						episodesList.updateLoadParameters({catid: cat.id});
						episodesList.load();
					});

					categoriesContainer.appendChild(catObj);
				});
			}
		});

	//Ссылка на показ видеороликов из всех категорий
	selectedCategory.addEventListener('click', function(e)
	{
		e.preventDefault();

		selectedCategory.className = '';
		selectedCategory           = this;
		this.className             = 'selected';
		episodesList.updateLoadParameters({catid: null});
		episodesList.load();
	});

	//Ссылка на показ списка всех категорий
	allCategoriesLink.addEventListener('click', function(e)
	{
		e.preventDefault();

		var catListObj       = document.createElement('div');
		catListObj.className = 'categories';

		(new Popup({
			title: 'Список категорий',
			content: '',
			overlay: true,
			closeCross: true
		})).show();
	});

	episodesList.load();
	categoriesDataList.load();

	/**
	 * Поиск эпизода по имени
	 */
	function searchEpisodes()
	{
		episodesList.updateLoadParameters({name: inputField.value});
		episodesList.load();
	}

	document.getElementById('search-button').addEventListener('click', searchEpisodes);
	inputField.addEventListener('keypress', function(event)
	{
		if(event.keyCode === 13)
		{ searchEpisodes(); }
	});

	document.getElementById('load-more-episodes').addEventListener('click', function()
	{
		loadMoreEp = true;
		episodesList.updateLoadParameters({before_time: lastEpTime});
		episodesList.load(function()
		{
			episodesList.updateLoadParameters({before_time: null});
		});
	});

});