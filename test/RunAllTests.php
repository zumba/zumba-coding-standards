<?php

namespace Zumba\CodingStandards\Test;

/**
 * This class loads all the tests in the data/*.php directory and tests
 * whether the output of the sniffer matches the expectation defined in
 * the test file.
 */
class RunAllTests extends \PHPUnit\Framework\TestCase
{
	protected function setUp()
	{
		chdir($this->topDir());
	}

	/**
	 * @dataProvider provideData
	 */
	public function testAllTests($file)
	{
		$contents = file_get_contents($this->dataDir() . $file);
		list($testFileContents, $expected) = $this->splitExpectedAndTest($contents);

		$phpcs = $this->vendorBin() . '/phpcs';
		$cmd = $phpcs . ' --standard=Zumba ';
		$output = $this->openProcessAndGetOutput($cmd, $testFileContents);
		$output = $this->filterPhpCsOutput($output);

		$this->assertEquals(trim($expected), $output);
	}

	public function provideData()
	{
		$d = opendir($this->dataDir());
		if ($d === false) {
			throw new \RuntimeException("Failed to open dir");
		}
		$files = array();
		while ($file = readdir($d)) {
			if (is_dir($file)) {
				continue;
			}
			$files[$file] = array($file);
		}
		ksort($files);
		return $files;
	}

	protected function topDir()
	{
		return __DIR__ . '/../';
	}

	/**
	 * @return string
	 */
	protected function dataDir()
	{
		return __DIR__ . '/data/';
	}

	protected function filterPhpCsOutput($output)
	{
		return trim(preg_replace('/FILE:.*/', "", $output));
	}

	/**
	 * @param string $contents
	 * @return array
	 */
	protected function splitExpectedAndTest($contents)
	{
		list($php, $expect) = explode('--EXPECT--', $contents);
		$php = preg_replace("/\?>\$/", "", $php); // remove closing tag
		return array($php, $expect);
	}

	/**
	 * @return string
	 */
	protected function vendorBin()
	{
		return __DIR__ . '/../vendor/bin/';
	}

	protected function openProcessAndGetOutput($cmd, $contents)
	{
		$descriptors = array(
			0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
			1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
			2 => array("file", "php://stderr", 'a') // stderr is a file to write to
		);
		try {
			$proc = proc_open($cmd, $descriptors, $pipes, $this->topDir());
			if ($proc === false) {
				throw new \RuntimeException("Error starting process");
			}
			if (fwrite($pipes[0], $contents) === false) {
				throw new \RuntimeException("Error writing to pipe");
			}
			if (fclose($pipes[0]) === false) {
				throw new \RuntimeException("Failed writing to pipe (on close)");
			}
			$result = stream_get_contents($pipes[1]);
			if (fclose($pipes[1]) === false) {
				throw new \RuntimeException("Error reading from pipe (on close)");
			}
			// Ignore the return value here (it is zero or non-zero depending on whether
			// the sniffer encountered problems):
			proc_close($proc);
			$proc = null;

			// Return the output of the command:
			return $result;

		} finally {
			if ($proc) {
				proc_close($proc);
			}
		}
	}
}
