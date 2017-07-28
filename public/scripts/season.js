
window.addEventListener('load', function()
{
	var episodesList = document.getElementById('episodes-list');

	var episodesDataList = new DataList({
		opUrl: 'index.php?op=eslist.episodesList',
		loadParameters: { seaid: seasonId, sort: 4, m_count: 0 },
		onLoad: function(result)
		{
			if(!result.list) { return; }

			document.getElementById('episodes-count').innerHTML = '(' + result.count + ')';

			result.list.forEach(function(episodeData)
			{
				episodesList.appendChild(createEpisodeObject(episodeData, true));
			});
		}
	});

	episodesDataList.load();
});