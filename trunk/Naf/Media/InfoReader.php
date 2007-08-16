<?php

interface Naf_Media_InfoReader {
	/**
	 * Read media file information.
	 *
	 * @param string filename
	 * @return Naf_Media_Info
	 */
	function info($filename);
}