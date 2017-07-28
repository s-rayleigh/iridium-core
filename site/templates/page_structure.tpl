<!DOCTYPE html>
<!--
  ~ Page structure template.
  ~ This file is part of Iridium Core project.
  ~
  ~ Iridium Core is free software: you can redistribute it and/or modify
  ~ it under the terms of the GNU Lesser General Public License as published by
  ~ the Free Software Foundation, either version 3 of the License, or
  ~ (at your option) any later version.
  ~
  ~ Iridium Core is distributed in the hope that it will be useful,
  ~ but WITHOUT ANY WARRANTY; without even the implied warranty of
  ~ MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
  ~ GNU Lesser General Public License for more details.
  ~
  ~ You should have received a copy of the GNU General Public License
  ~ along with Iridium Core. If not, see <http://www.gnu.org/licenses/>.
  ~
  ~ @author rayleigh <rayleigh@protonmail.com>
  ~ @copyright 2017 Vladislav Pashaiev
  ~ @license LGPL-3.0+
  -->

<html lang="ru">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>{title}</title>
		{foreach css_file in css}
			<link rel="stylesheet" href="{css_file}">
		{/foreach}
		<link rel="icon" href="{icon_path}">
	</head>
	<body>
		{page}
		{js_vars}
		{foreach js_file in js}
			<script src="{js_file}"></script>
		{/foreach}
	</body>
</html>