<?php
/**
 * Module for support of the multiple languages.
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
 * @copyright 2018 Vladislav Pashaiev
 * @license LGPL-3.0+
 */

namespace Iridium\Modules\Lang;


use Iridium\Core\Http\Filter\ValueType;
use Iridium\Core\Http\HTTP;
use Iridium\Core\Module\IModule;

require 'GroupParseData.php';
require 'Group.php';
require 'Dictionary.php';
require 'FilteredDictionary.php';
require 'Cache.php';

/**
 * Language module.
 * @package Iridium\Modules\Lang
 */
final class Lang implements IModule
{
	/**
	 * Extension of the language file.
	 */
	const LANG_FILE_EXT = 'lng';

	/**
	 * Extension of the phrase file.
	 */
	const PHRASE_FILE_EXT = 'phr';

	/**
	 * Multibyte encoding of the language files.
	 */
	const ENCODING = 'UTF-8';

	/**
	 * Name of the cookie in which active language code is stored.
	 */
	const COOKIE_NAME = 'lang';

	/**
	 * @var \stdClass Configuration of the module.
	 */
	public static $conf;

	/**
	 * @var Dictionary Active language.
	 */
	private static $active;

	/**
	 * @var Dictionary[] Loaded languages.
	 */
	private static $languages;

	/**
	 * @var int Timestamp of the loaded cache creation.
	 */
	private static $cacheTimestamp;

	/**
	 * Initializes the language module.
	 * @param array $conf Config of the module.
	 * @throws \Exception If some error is occurred.
	 */
	public static function Init(array $conf)
	{
		self::$conf = (object)$conf;

		if(self::$conf->cache && Cache::IsExists())
		{
			// Load languages from cache

			try
			{
				$cache                = Cache::Load();
				self::$languages      = $cache->GetDictionaries();
				self::$cacheTimestamp = $cache->GetTimestamp();
			}
			catch(\Exception $e)
			{
				throw new \Exception("Canot load languages cache.", 1, $e);
			}
		}
		else
		{
			// Load languages from file

			self::$languages = [];

			foreach(self::$conf->languages as $langCode)
			{
				$path = self::GetLangPath($langCode);

				if($path === null)
				{
					throw new \Exception("Cannot find language with code '{$langCode}'.");
				}

				self::$languages[] = self::ReadLanguage($langCode, $path);
			}

			// Save loaded languages to the cache
			if(self::$conf->cache && !Cache::IsExists())
			{
				(new Cache(self::$languages))->Save();
			}
		}

		// Set fallbacks
		foreach(self::$languages as $lang)
		{
			$fallbackCode = $lang->GetFallbackCode();

			if(empty($fallbackCode) && !empty(self::$conf->fallback) && self::$conf->fallback !== $lang->GetCode())
			{
				// Set fallback code to the default fallback language if it defined in the config of the module
				$fallbackCode = self::$conf->fallback;
			}

			if(!empty($fallbackCode))
			{
				if($fallbackCode === $lang->GetCode())
				{
					throw new \Exception("Language with code '{$lang->GetCode()}' cannot have fallback code that equals to it's code.");
				}

				foreach(self::$languages as $l)
				{
					if($l->GetCode() === $fallbackCode)
					{
						$lang->SetFallbackLang($l);
					}
				}
			}
		}

		if(self::$conf->use_cookies)
		{
			$activeCode = HTTP::GetCookie(self::COOKIE_NAME, ValueType::STRING, '');
		}

		if(empty($activeCode) && self::$conf->autodetect)
		{
			$activeCode = HTTP::GetUserLangCode();
		}

		if(empty($activeCode) && !empty(self::$conf->default))
		{
			$activeCode = self::$conf->default;
		}

		if(!empty($activeCode) && self::IsLoaded($activeCode))
		{
			try
			{
				self::SetActive($activeCode);
			}
			catch(\Exception $e)
			{
				throw new \Exception("Cannot set the active language with code '{$activeCode}'.", 0, $e);
			}
		}
	}

	/**
	 * Returns an array of required modules.
	 * @return array Required modules.
	 */
	public static function GetRequiredModules(): array { return []; }

	/**
	 * Searches for the <lang_code>.<ext> or the <lang_code>/main.<lang_ext> file and returns path.
	 * @param string $code Code of the language.
	 * @return null|string Path or null if file not found.
	 */
	private static function GetLangPath(string $code)
	{
		// Just one file
		if(file_exists(self::BuildLangFilePath($code)))
		{
			return $code;
		}

		// Directory
		$dirFilePath = $code . DIRECTORY_SEPARATOR . 'main';
		if(file_exists(self::BuildLangFilePath($dirFilePath)))
		{
			return $dirFilePath;
		}

		return null;
	}

	/**
	 * Returns true if language with specified code is loaded.
	 * @param string $code Code of the language.
	 * @return bool True if language with specified code is loaded.
	 */
	public static function IsLoaded(string $code): bool
	{
		foreach(self::$languages as $lang)
		{
			if($lang->GetCode() === $code)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Sets the active language by code.
	 * @param string $code Code of the language that is needed to be setted as active.
	 * @throws \Exception If language with the specified code is not found.
	 */
	public static function SetActive(string $code)
	{
		$exist = false;

		foreach(self::$languages as $lang)
		{
			if($lang->GetCode() === $code)
			{
				self::$active = $lang;
				$exist        = true;

				if(self::$conf->use_cookies)
				{
					HTTP::SetCookie(self::COOKIE_NAME, $code,
						self::$conf->cookie_lifetime === 0 ? 0 : time() + self::$conf->cookie_lifetime);
				}

				break;
			}
		}

		if(!$exist)
		{
			throw new \Exception("Language with the specified code '{$code}' is not found.");
		}
	}

	/**
	 * @return string Code of the active language or empty string if active language is not setted.
	 */
	public static function GetActiveCode(): string
	{
		return empty(self::$active) ? '' : self::$active->GetCode();
	}

	/**
	 * Returns dictionary of the language with the specified code. If the code is not specified (or empty), the dictionary of the active language will be returned.
	 * @param string $code Code of the language, dictionary of which should be returned.
	 * @return Dictionary Dictionary of the language.
	 * @throws \Exception If the code is not specified and the is no active language or the language of the specified code does not exist.
	 */
	public static function GetDictionary($code = ''): Dictionary
	{
		if(empty($code))
		{
			if(empty(self::$active))
			{
				throw new \Exception('Code of the language should not be empty or you should set active language.');
			}

			return self::$active;
		}

		foreach(self::$languages as $lang)
		{
			if($lang->GetCode() === $code)
			{
				return $lang;
			}
		}

		throw new \Exception("Language with specified code was not found.");
	}

	/**
	 * @return Dictionary[] Dinctionaries of the loaded languages.
	 */
	public static function GetDictionaries(): array
	{
		return self::$languages;
	}

	/**
	 * @return bool True if the cache is enabled.
	 */
	public static function IsCacheEnabled(): bool
	{
		return self::$conf->cache;
	}

	/**
	 * @return int Timestamp of the loaded cache or zero if cache is not loaded.
	 */
	public static function GetCacheTimestamp(): int
	{
		return empty(self::$cacheTimestamp) ? 0 : self::$cacheTimestamp;
	}

	/**
	 * Reads language with the specified code and path and returns it's dictionary.
	 * @param string $code Code of the language.
	 * @param string $path Short path to the language file.
	 * @return Dictionary Dictionary of the language.
	 * @throws \Exception If an error is occurred while reading or parsing the file or parameter 'name' is not setted in the main file of the language.
	 */
	private static function ReadLanguage(string $code, string $path) : Dictionary
	{
		try
		{
			$mainFileContent = self::ReadFile(self::BuildLangFilePath($path));
		}
		catch(\Exception $e)
		{
			throw new \Exception("Error trying to read the main file of the language with code {$code}.", 0, $e);
		}

		$parseData = new GroupParseData($mainFileContent);
		$parseData->SetPath($path);

		$langParams = new \stdClass;
		$mainGroup  = self::ParseGroup($parseData, $langParams);

		if(empty($langParams->name))
		{
			throw new \Exception("Parameter 'name' is required in the language main file.");
		}

		$dict = new Dictionary($code, $langParams->name, $mainGroup);
		if(!empty($langParams->fallback))
		{
			$dict->SetFallbackCode($langParams->fallback);
		}

		try
		{
			self::ProcessInlineCommands($dict);
		}
		catch(\Exception $e)
		{
			throw new \Exception("Error occurred while processing inline commands for the language with code '{$code}'.", 3, $e);
		}

		return $dict;
	}

	/**
	 * Process group content text and returns parsed group data.
	 * @param GroupParseData $pd Data for parsing the group.
	 * @param \stdClass|null $mainParams Reference to the stdClass object that should be filled with main
	 * file parameters. Used only if group is a main file.
	 * @return Group Data of the group.
	 * @throws \Exception If error occured while processing the group.
	 */
	private static function ParseGroup(GroupParseData $pd, \stdClass &$mainParams = null): Group
	{
		// Flags
		$newLine          = true;
		$cmdOrParam       = false;
		$comment          = false;
		$groupConstr      = false;
		$phrase           = false;
		$namelessGrpCount = 0; // Count of the embedded nameless groups

		$group               = new Group; // Current group
		$ri                  = 0; // Remember '$i'
		$gi                  = 0; // Group start '$i'
		$line                = $pd->GetLine(); // Number of the line
		$file                = $pd->GetRelativePath(); // Relative path to the current file without the extension
		$groupLine           = 0; // Line number of the subgroup beginning
		$availableMainParams = ['name', 'fallback'];
		$subgroupData        = null;
		$len                 = mb_strlen($pd->GetContent(), self::ENCODING); // Length of the content

		/**
		 * Generates place text based on line number and path to the file.
		 * @return string Place.
		 */
		$genPlace = static function() use(&$line, $file)
		{
			$result = 'at line ' . $line;
			if(!empty($file)) { $result .= " in file '{$file}'"; }
			return $result;
		};

		$group->SetDir($pd->GetDir());

		// Iterate through symbols
		for($i = 0; $i < $len; $i++)
		{
			$char = mb_substr($pd->GetContent(), $i, 1, self::ENCODING);

			// Detect comment
			if($char === '#') { $comment = true; }

			// Detect new line or comment or EOF (last symbol)
			if($char === "\n" || $char === "\r" || $comment || $i === $len - 1)
			{
				// End of the comment
				if($comment && ($char === "\n" || $char === "\r"))
				{
					$comment = false;
					continue;
				}

				// New line detected
				if(!$comment)
				{
					$newLine = true;
					$line++;
				}

				// Current symbol is last
				$lastSymbol = $i === $len - 1;

				// Parse command or parameter
				if($cmdOrParam)
				{
					$cmdOrParam = false;

					// Try parse command or parameter
					try
					{
						// Skip '>' and get rest of the line except EOL char
						$data = self::ParseCommandOrParam(mb_substr($pd->GetContent(), $ri + 1, $i - $ri - ($lastSymbol ? 0 : 1)));
					}
					catch(\Exception $e)
					{
						throw new \Exception("Error trying to parse command or parameter {$genPlace()}.", 1, $e);
					}

					if($data->type === 0) // Parameter
					{
						$isMainParam = in_array($data->name, $availableMainParams);

						if(isset($mainParams))
						{
							if($isMainParam)
							{
								$mainParams->{$data->name} = $data->param;
							}
							else
							{
								throw new \Exception("Parameter '{$data->name}'' is not available as main file parameter {$genPlace()}.");
							}
						}
						else
						{
							if($isMainParam)
							{
								throw new \Exception("Parameter '{$data->name}' is only allowed as main file parameter {$genPlace()}.");
							}

							switch($data->name)
							{
								case 'keywords': // Keywords parameter
									$keywords = array_map(
										function($kw) { return trim($kw); },
										explode(',', $data->param)
									);

									if(empty($keywords))
									{
										throw new \Exception("Value is expected for the 'keywords' parameter {$genPlace()}.");
									}

									// Validate keywords
									foreach($keywords as $kw)
									{
										if(!self::CheckKeyword($kw))
										{
											throw new \Exception("Keyword '{$kw}' is invalid {$genPlace()}.");
										}
									}

									$group->AddKeywords($keywords);
									break;
								case 'group': // Group name parameter
									if(empty($file))
									{
										throw new \Exception("Parameter 'group' is only allowed in included file {$genPlace()}.");
									}

									if(!self::CheckGroupName($data->param))
									{
										throw new \Exception("Invalid group name '{$data->param}' {$genPlace()}.");
									}

									$group->SetName($data->param);
									break;
								default:
									throw new \Exception("No parameter exist with name {$data->name} {$genPlace()}.");
									break;
							}
						}
					}
					else if($data->type === 1) // Command
					{
						switch($data->name)
						{
							case 'include': // File include command
								if(strlen($data->param) === 0)
								{
									throw new \Exception("Command 'include' requires path to the file {$genPlace()}.");
								}

								$incParceData = new GroupParseData(self::ReadFile(self::BuildLangFilePath($pd->GetDir() . DIRECTORY_SEPARATOR . $data->param)));
								$incParceData->SetPath($pd->GetDir() . DIRECTORY_SEPARATOR . $data->param);

								try
								{
									$incGroup = self::ParseGroup($incParceData);
								}
								catch(\Exception $e)
								{
									throw new \Exception("Error trying to read and parse file specified in the include command {$genPlace()}.", 6, $e);
								}

								$group->AddSubgroup($incGroup);
								break;
							default:
								throw new \Exception("No command exist with name '{$data->name}' {$genPlace()}.");
								break;
						}
					}
				}

				// Parse group construction
				if($groupConstr)
				{
					$groupConstr = false;

					$text = trim(mb_substr($pd->GetContent(), $ri + 1, $i - $ri - ($lastSymbol ? 0 : 1)));

					if(mb_substr($text, -1) !== ']')
					{
						throw new \Exception("Symbol ']' at the end of the group beginning construction expected {$genPlace()}.");
					}

					try
					{
						$data = self::ParseGroupConstruction(mb_substr($text, 0, -1));
					}
					catch(\Exception $e)
					{
						throw new \Exception("Error trying to parse group beginning or ending {$genPlace()}.", 2, $e);
					}

					if($data->end)
					{
						// This is the ending of the group

						if(empty($data->name)) { $namelessGrpCount--; }

						// End of the current subgroup
						if(empty($data->name) && empty($subgroupData->name) && $namelessGrpCount === 0 || !empty($data->name) && !empty($subgroupData->name) && $subgroupData->name === $data->name)
						{
							try
							{
								$subgrouParseData = new GroupParseData(mb_substr($pd->GetContent(), $gi, $ri - $gi - 1));
								$subgrouParseData->SetLine($groupLine);
								$subgrouParseData->SetPath($pd->GetRelativePath());
								$subgroup = self::ParseGroup($subgrouParseData);
							}
							catch(\Exception $e)
							{
								throw new \Exception("Cannot process subgroup.", 3, $e);
							}

							$subgroup->SetName(empty($subgroupData->name) ? '' : $subgroupData->name);
							$group->AddSubgroup($subgroup);
							$subgroupData = null;
						}
					}
					else
					{
						// This is the beginning of the group

						if(empty($subgroupData))
						{
							// Remember where the group starts
							$gi = $i + 1;
							$groupLine = $line;

							// Remember subgroup data
							$subgroupData = $data;
						}

						if(empty($data->name))
						{
							// Increment nameless groups count
							$namelessGrpCount++;
						}
						else
						{
							if(!self::CheckGroupName($data->name))
							{
								throw new \Exception("Invalid group name '{$data->name}' {$genPlace()}.");
							}
						}
					}
				}

				// Parse phrase
				if($phrase)
				{
					$phrase = false;

					try
					{
						$data = self::ParsePhrase(mb_substr($pd->GetContent(), $ri, $i - $ri + ($lastSymbol ? 1 : 0)));
					}
					catch(\Exception $e)
					{
						throw new \Exception("Error trying to parse phrase {$genPlace()}.", 4, $e);
					}

					if(!self::CheckPhraseId($data->id))
					{
						throw new \Exception("Phrase identifier '{$data->id}' is invalid {$genPlace()}.");
					}

					$group->AddPhrase($data->id, $data->phrase);
				}

				continue;
			}

			// Skip comment
			if($comment) { continue; }

			// Starts with new line
			if($newLine)
			{
				// Skip tabs and spaces
				if($char === "\t" || $char === " ")
				{
					continue;
				}

				$newLine = false;

				// Group construction
				if($char === '[')
				{
					$groupConstr = true;
					$ri          = $i; // Remember start of the group construction

					continue;
				}

				// Parse everything else only if subgroup isn't found
				if(empty($subgroupData))
				{
					// Command or parameter
					if($char === '>')
					{
						$cmdOrParam = true;
						$ri         = $i; // Remember start of the command or parameter

						continue;
					}

					// Phrase
					$phrase = true;
					$ri     = $i;
				}
			}
		}

		// End of file

		if(!empty($subgroupData))
		{
			throw new \Exception(
				(empty($subgroupData->name) ?
					'Nameless group constraction' :
					"Group construction with name '{$subgroupData->name}'")
				. ' should be closed.'
			);
		}

		return $group;
	}

	/**
	 * Reads file and returns it's content.
	 * @param string $filePath Path to the file.
	 * @return string Content of the file.
	 * @throws \Exception If error occured while reading the file.
	 */
	private static function ReadFile(string $filePath): string
	{
		if(($rootContent = @file_get_contents($filePath)) === false)
		{
			throw new \Exception("Cannot read file {$filePath}.");
		}

		return $rootContent;
	}

	/**
	 * Parses command or parameter and returns it's data.
	 * @param string $text Text of the command or parameter.
	 * @return \stdClass Data of the command or parameter.
	 * @throws \Exception If error occured while parsing the command or parameter.
	 */
	private static function ParseCommandOrParam(string $text): \stdClass
	{
		$result = new \stdClass();
		$text = trim($text);

		for($i = 0; $i < mb_strlen($text, self::ENCODING); $i++)
		{
			$symb = mb_substr($text, $i, 1, self::ENCODING);

			if($symb === ' ' || $symb === ':')
			{
				// Parameter
				if($symb === ':')
				{
					$result->type = 0;
				}

				// Command
				if($symb === ' ')
				{
					$result->type = 1;
				}

				$result->name  = trim(mb_substr($text, 0, $i, self::ENCODING));
				$result->param = trim(mb_substr($text, $i + 1, null, self::ENCODING));

				break;
			}
		}

		if(!isset($result->name))
		{
			throw new \Exception("Command or parameter expected.");
		}

		return $result;
	}

	/**
	 * Parses group beginning or ending and returns it's data.
	 * @param string $text Text of the group beginning or ending.
	 * @return \stdClass Data of the command beginning or ending.
	 * @throws \Exception If error occured while parsing the group beginning or ending.
	 */
	private static function ParseGroupConstruction(string $text): \stdClass
	{
		$result = new \stdClass;

		for($i = 0; $i < mb_strlen($text, self::ENCODING); $i++)
		{
			$symb = mb_substr($text, $i, 1, self::ENCODING);

			if($symb === ':')
			{
				$command      = trim(mb_substr($text, 0, $i, self::ENCODING));
				$result->name = trim(mb_substr($text, $i + 1, null, self::ENCODING));

				if(empty($result->name))
				{
					throw new \Exception("Name of the group is required after the ':' symbol.");
				}

				break;
			}
		}

		if(empty($command))
		{
			$command = trim($text);
		}

		switch($command)
		{
			case 'group':
				$result->end = false;
				break;
			case 'end':
				$result->end = true;
				break;
			default:
				throw new \Exception("Must be 'group' or 'end' in square brackets.");
				break;
		}

		return $result;
	}

	/**
	 * Parses phrases identifier and text.
	 * @param string $text Text that should be parsed.
	 * @return \stdClass Parsed phrase identifier and text.
	 * @throws \Exception If error occured while parsing the phrase.
	 */
	private static function ParsePhrase(string $text): \stdClass
	{
		$result = new \stdClass;

		for($i = 0; $i < mb_strlen($text, self::ENCODING); $i++)
		{
			$symb = mb_substr($text, $i, 1, self::ENCODING);

			if($symb === ':')
			{
				$result->id     = trim(mb_substr($text, 0, $i, self::ENCODING));
				$result->phrase = trim(mb_substr($text, $i + 1, null, self::ENCODING));
				break;
			}
		}

		if(empty($result->id))
		{
			throw new \Exception("Phrase should have identifier.");
		}

		if(empty($result->phrase))
		{
			throw new \Exception("Text of the phrase should not be empty.");
		}

		return $result;
	}

	/**
	 * Parses and processes inline commands.
	 * @param Dictionary $dict Dictionary of the language.
	 */
	private static function ProcessInlineCommands(Dictionary $dict)
	{
		$prInlCmd = function(Group $group) use(&$prInlCmd, $dict)
		{
			foreach($group->GetPhrases() as $id => $phrase)
			{
				$len     = mb_strlen($phrase, self::ENCODING);
				$command = false; // Command flag
				$cmdBeg  = 0; // Command start index

				for($i = 0; $i < $len; $i++)
				{
					$symb = mb_substr($phrase, $i, 1, self::ENCODING);

					if($symb === '\\')
					{
						// Skip next
						$i++;
						continue;
					}

					if($symb === '{')
					{
						$command = true;
						$cmdBeg  = $i;
						continue;
					}

					if($command)
					{
						if($symb === '{')
						{
							throw new \Exception("Unexpected symbol '{' in the inline command construction. Expected symbol '}'.");
						}

						if($symb === '}')
						{
							$command  = false;
							$cmdLen   = $i - $cmdBeg - 1;
							$cmdText  = trim(mb_substr($phrase, $cmdBeg + 1, $cmdLen));
							$cmdParts = explode(' ', $cmdText);
							$partsLen = count($cmdParts);

							if($partsLen === 1)
							{
								$path      = $cmdParts[0];
								$subPhrase = $dict->FindPhrase($path);

								if($subPhrase === null)
								{
									throw new \Exception("Cannot fild phrase with path '{$path}' specified in the inline command.'");
								}

								$phrase = substr_replace($phrase, $subPhrase, $cmdBeg, $cmdLen + 2);
								$len    = mb_strlen($phrase, self::ENCODING);
								$i      = $cmdBeg + mb_strlen($subPhrase, self::ENCODING);
							}
							else if($partsLen === 2)
							{
								$command = $cmdParts[0];
								$value   = $cmdParts[1];


								switch($command)
								{
									case 'include':
										$grpPath = $group->GetDir();

										$path = ROOT_PATH . DIRECTORY_SEPARATOR . self::$conf->lang_path . DIRECTORY_SEPARATOR;

										if(!empty($grpPath))
										{
											$path .= $grpPath . DIRECTORY_SEPARATOR;
										}

										$path .= $value . '.' . self::PHRASE_FILE_EXT;

										if(file_exists($path))
										{
											$fileContent = @file_get_contents($path);

											if($fileContent === false)
											{
												throw new \Exception("Cannot read file of the phrase with path '{$path}'.");
											}

											$phrase = substr_replace($phrase, $fileContent, $cmdBeg, $cmdLen + 2);
											$len    = mb_strlen($phrase, self::ENCODING);
											$i      = $cmdBeg + mb_strlen($fileContent, self::ENCODING);
										}
										else
										{
											throw new \Exception("File of the phrase with path '{$path}' in not exists.");
										}

										break;
									default:
										throw new \Exception("Unknown inline command '{$command}'.");
										break;
								}
							}
							else
							{
								throw new \Exception("Unknown inline command.");
							}
						}

					}
				}

				$group->AddPhrase($id, str_replace('\{', '{', $phrase));
			}

			foreach($group->GetSubgroups() as $subgroup)
			{
				$prInlCmd($subgroup);
			}
		};

		$prInlCmd($dict->GetRootGroup());
	}

	/**
	 * Checks if passed group name is valid.
	 * @param string $groupName Name of the group.
	 * @return bool True, if name is valid.
	 */
	private static function CheckGroupName(string $groupName): bool
	{
		return preg_match('/^[A-Za-z_]{2,}$/', $groupName) === 1;
	}

	/**
	 * Checks if passed keyword is valid.
	 * @param string $keyword Keyword.
	 * @return bool True, if keyword is valid.
	 */
	private static function CheckKeyword(string $keyword): bool
	{
		return preg_match('/^[A-Za-z_ ]{2,}$/', $keyword) === 1;
	}

	/**
	 * Checks if passed phrase identifier is valid.
	 * @param string $id Phrase identifier.
	 * @return bool True, if phrase identifier is valid.
	 */
	private static function CheckPhraseId(string $id): bool
	{
		return preg_match('/^[A-Za-z][A-Za-z0-9_]+$/', $id) === 1;
	}

	/**
	 * Builds and returns absolute path to the language file.
	 * @param string $lng Language file relative (to the language files folder) path without extension.
	 * @return string Builded absolute path.
	 */
	private static function BuildLangFilePath(string $lng)
	{
		return ROOT_PATH . DIRECTORY_SEPARATOR . self::$conf->lang_path . DIRECTORY_SEPARATOR . $lng . '.' . self::LANG_FILE_EXT;
	}
}