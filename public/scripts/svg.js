/**
 * Управление SVG графикой.
 * @author rayleigh <rayleigh@protonmail.com>
 */
var SVG = {};

/**
 * Загружает изображение SVG по URL
 * @param url		URL изображения
 * @param callback
 */
SVG.loadFromURL = function(url, callback)
{
	sendRequest('GET', url, '', function(svgDoc) { callback(svgDoc.firstChild); }, false);
};

/**
 * Загружает все SVG изображения из тегов img с классом inline-svg на страницу
 */
SVG.loadOnPage = function()
{
	var svgs = document.getElementsByClassName('inline-svg');

	for(var i = 0; i < svgs.length; i++)
	{
		(function(i)
		{
			var img = svgs[i];
			SVG.loadFromURL(img.src, function(svg)
			{
				svg.setAttribute('class', img.className);
				img.parentNode.replaceChild(svg, img);
			}, false);
		})(i);
	}
};

SVG.loadOnPage();