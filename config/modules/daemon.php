<?php
/**
 * Daemon module parameters.
 * This file is part of Iridium Core project.
 *
 * Iridium Core is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Iridium Core is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Iridium Core. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author rayleigh <rayleigh@protonmail.com>
 * @copyright 2017 Vladislav Pashaiev
 * @license LGPL-3.0+
 * @version 0.1-indev
 */

// Do not remove any parameter, it might cause errors.
return
[
	// Lock file parameters
	'lock' => [

		// Path to the directory for the lock files.
		// If empty, php tmp directory will be used.
		// In case of using /tmp as php tmp directory and if you want to control daemon process from multiple users,
		// make sure that you have disabled temp directory isolation in the systemd settings
		// If directory does not exist, it will be created
		'path' => '',

		// Unique id for the lock files.
		// Used as file extension.
		// Should be unique in case any other application uses similar lock system
		'unique_id' => 'ir_lock'
	],

	// Debug mode
	// If enabled, stdout and stderr streams will be stored in files in the php tmp directory
	'debug' => true
];