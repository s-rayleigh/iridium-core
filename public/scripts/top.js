
window.addEventListener('load', function()
{
	var episodesContainer = document.getElementById('episodes-container'),
		totalEpsCount = 0,
		episodesDataList  = new DataList({
			opUrl: 'index.php?op=eslist.episodesList',
			loadParameters: {sort_type: 0},
			onLoad: function(result)
			{
				if(!result.list)
				{ return; }

				result.list.forEach(function(ep)
				{
					episodesContainer.appendChild(createEpisodeObject(ep));
				});

				totalEpsCount += result.list.length;

				episodesDataList.updateLoadParameters({ skip: totalEpsCount });
			}
		});

	episodesDataList.load();

	document.getElementById('load-more-episodes').addEventListener('click', function()
	{
		episodesDataList.load();
	});
});