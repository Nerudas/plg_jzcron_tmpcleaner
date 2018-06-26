<?php
/**
 * @package    JZ Cron - TmpCleaner Plugin
 * @version    1.0.1
 * @author     Nerudas  - nerudas.ru
 * @copyright  Copyright (c) 2013 - 2017 Nerudas. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://nerudas.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Language\Text;

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

class plgJZCronTmpCleaner extends CMSPlugin
{
	/**
	 * @param JObject $options subtask options
	 * @param JObject $params  plguin params
	 *
	 * @return int
	 *
	 * @since 1.0.0
	 */
	public function runSubtask($options, $params)
	{
		$count = 0;
		if ($options->get('path', false))
		{
			$path      = JPATH_ROOT . '/' . trim($options->get('path'), '/');
			$mode      = $options->get('mode', 'directories');
			$filter    = $options->get('filter');
			$time      = (!empty($options->get('time_number', 0))) ?
				'-' . $options->get('time_number') . ' ' . $options->get('time_value') : '';
			$date      = new JDate('now' . $time);
			$date      = $date->__toString();
			$recursive = $options->get('recursive', false);

			if ($mode == 'directories' || $mode == 'all')
			{
				$count = $count + $this->deleteDirectories($path, $filter, $date, $recursive);
			}

			if ($mode == 'files' || $mode == 'all')
			{
				$count = $count + $this->deleteFiles($path, $filter, $date);
			}
		}

		return $count;
	}

	/** Delete files
	 *
	 * @param string $path   path to directories
	 * @param string $filter mathc case
	 * @param  JDate $date   date check
	 *
	 * @return int
	 *
	 * @since 1.0.0
	 */
	protected function deleteFiles($path, $filter, $date)
	{
		$count = 0;
		$files = JFolder::files($path, $filter, true, true);
		foreach ($files as $file)
		{
			$stat  = filemtime($file);
			$mdate = new JDate($stat);
			$mdate = $mdate->__toString();
			if ($mdate < $date && JFile::delete($file))
			{
				$count++;
			}
		}

		return $count;
	}

	/** Delete directories
	 *
	 * @param string $path      path to directories
	 * @param string $filter    mathc case
	 * @param  JDate $date      date check
	 * @param  bool  $recursive recursive delete
	 *
	 * @return int
	 *
	 * @since 1.0.0
	 */
	protected function deleteDirectories($path, $filter, $date, $recursive)
	{

		$count = 0;

		$directories = JFolder::folders($path, $filter, $recursive, true);
		if (count($directories) > 0)
		{
			foreach ($directories as $directory)
			{
				$stat      = stat($directory);
				$mdate     = new JDate($stat['mtime']);
				$mdate     = $mdate->__toString();
				if ($mdate < $date && JFolder::delete($directory))
				{
					$count++;
				}
			}
		}

		return $count;
	}

	/**
	 * @param int     $data    count deletes
	 * @param JObject $options subtask options
	 * @param JObject $params  plguin params
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public function getNotification($data = 0, $options, $params)
	{
		$notification = '';
		if ($data > 0)
		{
			$notification = Text::sprintf('PLG_JZCRON_TMPCLEANER_DELETES', $data);
		}

		return $notification;

	}
}