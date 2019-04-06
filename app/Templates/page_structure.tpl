<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>{title}</title>
		{foreach css_file in css}
			<link rel="stylesheet" href="{css_file}">
		{/foreach}
	</head>
	<body>
		{page}
		{js_page_data}
		{foreach js_file in js}
			<script src="{js_file}"></script>
		{/foreach}
	</body>
</html>