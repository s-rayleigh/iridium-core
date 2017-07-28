window.addEventListener('load', function()
{
	var seasonsList  = document.getElementById('seasons-list'),
		episodesList = document.getElementById('last-episodes-list');

	var seasonsDataList = new DataList({
		opUrl: 'index.php?op=eslist.seasonsList',
		loadParameters: { sort: 4, chanid: channelId }, //Сортировка по номеру сезона
		onLoad: function(result)
		{
			if(!result.list)
			{ return; }

			result.list.forEach(function(seasonData)
			{
				seasonsList.appendChild(createSeasonObject(seasonData));
			});
		}
	});

	var episodesDataList = new DataList({
		opUrl: 'index.php?op=eslist.episodesList',
		loadParameters: { chanid: channelId, m_count: 10 },
		onLoad: function(result)
		{
			if(!result.list)
			{ return; }

			result.list.forEach(function(episodeData)
			{
				episodesList.appendChild(createEpisodeObject(episodeData));
			});
		}
	});

	seasonsDataList.load();
	episodesDataList.load();
});

/**
 * Создает объект DOM для отображения информации о сезоне.
 * @param {object} seasonData Данные сезона.
 * @returns {Element} Объект сезона.
 */
function createSeasonObject(seasonData)
{
	var plank           = document.createElement('div'),
		numberDiv       = document.createElement('div'),
		nameLink        = document.createElement('a'),
		descriptionSpan = document.createElement('span');

	plank.className           = 'season';
	numberDiv.className       = 'number';
	nameLink.className        = 'name';
	descriptionSpan.className = 'description';

	nameLink.href = 'index.php?page=season&id=' + seasonData.id;

	numberDiv.appendChild(document.createTextNode(seasonData.number));
	nameLink.appendChild(document.createTextNode(seasonData.name));
	descriptionSpan.appendChild(document.createTextNode(seasonData.description));

	plank.appendChild(numberDiv);
	plank.appendChild(nameLink);
	plank.appendChild(document.createElement('br'));
	plank.appendChild(descriptionSpan);

	return plank;
}