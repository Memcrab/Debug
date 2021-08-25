<?php declare (strict_types = 1);
namespace Memcrab\Debug;

class Debug {
	private static $enviroment = null;
	private static $filename = null;
	private static $result = null;

	public static function toConsole() {self::$enviroment = "console";}
	public static function toBrowser() {self::$enviroment = "browser";}

	public static function toFile($filename = null) {
		if ($filename != null) {
			self::$filename = $filename;
		} else {
			self::$filename = "dump" . time() . ".txt";
		}

		self::$enviroment = "file";

		file_put_contents(self::$filename, "\n");
	}

	private static function start() {
		switch (self::$enviroment) {
		case 'browser':echo "<pre>";
			break;
		case 'console':echo "==> start\n";
			break;
		case 'file':ob_start();
			break;
		default:echo "<pre>";
			break;
		}
	}

	private static function end() {
		switch (self::$enviroment) {
		case 'browser':echo "</pre>";
			break;
		case 'console':echo "<== end\n";
			break;
		case 'file':
			$result = ob_get_clean();
			file_put_contents(self::$filename, "\n" . $result, FILE_APPEND);
			break;
		default:echo "</pre>";
			break;
		}
	}

	private static function getDateTimeMicro() {
		return date_create_from_format('U.u', sprintf('%.f', microtime(true)))->format('Y-m-d\TH:i:s.u \o\f\f\s\e\t:O');
	}

	public static function me(...$params) {
		self::start();
		call_user_func_array('var_dump', $params);
		self::end();
	}
	public static function xme(...$params) {call_user_func_array(array('self', 'me'), $params);exit();}
	public static function arr(...$params) {
		self::start();
		print_r($params, false);
		self::end();}
	public static function xarr(...$params) {call_user_func_array(array('self', 'arr'), $params);exit();}
	public static function ferr($file, $array) {
		file_put_contents(
			$file,
			"###################################################################\n" .
			date("Y-m-d H:i:s") . " Europe/Kiev\n" .
			print_r($array, true),
			FILE_APPEND | LOCK_EX);
	}

	public static function startTimeCounter(&$startTime, &$counter) {
		list($usec, $sec) = explode(" ", microtime());
		$startTime = (float) $usec + (float) $sec;
		if (is_int($counter)) {
			$counter++;
		} else {
			$counter = 1;
		}

	}

	public static function finishTimeCounter($startTime, $counter) {
		list($usec, $sec) = explode(" ", microtime());
		$finishTime = (float) $usec + (float) $sec;
		self::xme(array("time" . $counter => ($finishTime - $startTime)));
	}

	public static function getLogsFolder($logsFolder) {
		$nowDate = new \DateTime('now', new \DateTimeZone('UTC'));
		$day = $nowDate->format('d');

		if (!file_exists($logsFolder)) {
			mkdir($logsFolder, 0755, true);
		}

		if (!file_exists($logsFolder . $day)) {
			mkdir($logsFolder . $day, 0755, true);
		}

		self::removeOldFiles($logsFolder . $day, $nowDate);

		return $logsFolder . $day;
	}

	/**
	 * Remove files which is old then 1 month
	 * @param  [type] $dir [description]
	 * @param  [type] $now [description]
	 * @return [type]      [description]
	 * @author Sukhov Igor
	 */
	public static function removeOldFiles($dir, $now) {
		if (is_dir($dir)) {
			$files = array_diff(scandir($dir), array('.', '..'));
			$critical = clone $now;
			$critical->modify('- 28 days');
			$criticalDate = $critical->getTimestamp();
			foreach ($files as $file) {
				$fileinfo = stat($dir . "/" . $file);
				$fileTime = new \DateTime('@' . $fileinfo['mtime'], new \DateTimeZone('UTC'));
				$fileTimestamp = $fileTime->getTimestamp();
				if ($fileTimestamp <= $criticalDate && is_file($dir . "/" . $file)) {
					unlink($dir . "/" . $file);
				}

			}
		}
	}
}
