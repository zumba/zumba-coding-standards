<?php

namespace Zumba\CodingStandards\Test;

class RunAllTests extends \PHPUnit\Framework\TestCase {

	protected function setUp() {
		chdir(__DIR__ . '/../');
	}

	/**
	 * @dataProvider provideData
	 */
	public function testAllTests($file) {
		$contents = file_get_contents($this->dataDir() . $file);
		list($test, $expected) = $this->splitExpectedAndTest($contents);
		$processedPath = $this->processedDir() . $file;
		file_put_contents($processedPath, $test);
		$phpcs = $this->vendorBin() . '/phpcs';
		$args = ' --standard=Zumba ' . escapeshellarg($processedPath);
		$cmd = $phpcs . $args;
		$output = shell_exec($cmd);
		$output = $this->filterPhpCsOutput($output);
		$this->assertEquals(trim($expected), $output);
	}

	public function provideData() {
		$d = opendir($this->dataDir());
		if ($d === false) {
			throw new \RuntimeException("Failed to open dir");
		}
		$files = array();
		while ($file = readdir($d)) {
			if (is_dir($file)) {
				continue;
			}
			$files[] = array($file);
		}
		return $files;
	}

	/**
	 * @return string
	 */
	protected function dataDir() {
		return __DIR__ . '/data/';
	}

	protected function filterPhpCsOutput($output) {
		return trim(preg_replace('/FILE:.*/', "", $output));
	}

	/**
	 * @param string $contents
	 * @return array
	 */
	protected function splitExpectedAndTest($contents) {
		list($php, $expect) = explode('--EXPECT--', $contents);
		$php = preg_replace("/\?>\$/", "", $php); // remove closing tag
		return array($php, $expect);
	}

	/**
	 * @return string
	 */
	protected function vendorBin() {
		return __DIR__ . '/../vendor/bin/';
	}

	/**
	 * We run the tests from the processed dir because our ruleset has an exclude for dirs that have 'test'
	 * in them, so that would exclude our tests:
	 *
	 * @return string
	 */
	protected function processedDir() {
		return __DIR__ . '/../processed/';
	}
}
