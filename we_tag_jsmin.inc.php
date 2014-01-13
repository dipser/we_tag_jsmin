<?php

/**
 * we-Tag we:jsmin
 *
 * File: we_tag_jsmin.inc.php
 * Path: webEdition/we/include/we_tags/custom_tags/
 *
 * @author Aurelian Hermand (aurel@hermand.de)
 * @version 1.0.0
 * @date 13.01.2014
 *
 *
 *
 * Beschreibung:
 *
 * Das Tag <we:jsmin src="" target="" /> erzeugt aus den angegebenen
 * JS-Dokumenten (src) in der Zieldatei (target) eine minimierte Version.
 * 
 * Ausgabe:
 * <script type="text/javascript" src="/script.min.js"></script>
 *
 * Attribute:
 * - src: IDs der Javascript-Dokumente
 *       - Pflicht
 *       - Integer bzw. Integers getrennt durch Kommas
 *       - Beispiele:
 *           <we:jsmin src="2" target="1" />
 *           <we:jsmin src="2,3,4" target="1" />
 * - target: ID der Zieldatei (script.min.js)
 *       - Pflicht
 *       - Integer
 *       - Beispiele:
 *           <we:jsmin src="2" target="1" />
 *           <we:jsmin src="2,3" target="1" />
 * - watch: Beobachtet den Zeitstempel der letzten Änderung
 *       - Optional
 *       - true/false (default: false)
 *       - Beispiele:
 *           <we:jsmin src="2" target="1" watch="true" />
 * - forceUpdate: Erstellt die minimierte Version bei jedem Aufruf neu
 *       - Optional
 *       - true/false (default: false)
 *       - Beispiele:
 *           <we:jsmin src="2" target="1" forceUpdate="true" />
 * - only: Liefert nur das src-Attribut zurück
 *       - Optional
 *       - src
 *       - Beispiele:
 *           <we:jsmin src="2" target="1" only="src" />
 *
 *
 * ---------
 * Diese Software benutzt: https://raw.github.com/mrclay/minify/master/min/lib/JSMin.php
 */

function we_tag_jsmin($attribs, $content) {

	// Fehlende Pflicht-Attribute abfangen
	if ($missingAttrib = attributFehltError($attribs, "src", __FUNCTION__)) {
		return $missingAttrib;
	}
	if ($missingAttrib = attributFehltError($attribs, "target", __FUNCTION__)) {
		return $missingAttrib;
	}

	// Attribute auslesen
	/*
 	* get an attribute from $attribs, and return its value according to default
 	* @param string $name attributes name
 	* @param array $attribs array containg the attributes
 	* @param mixed $default default value
 	* @param bool $isFlag determines if this is a flag (true/false -value)
 	* @param bool $useGlobal check if attribute value is a php-variable and is found in $GLOBALS
 	* @return mixed returns the attributes value or default if not set
 	*/
	$srcs = explode(',', weTag_getAttribute("src", $attribs));
	$target = weTag_getAttribute("target", $attribs);
	$watch = weTag_getAttribute("watch", $attribs, false, true); // default: false
	$forceUpdate = weTag_getAttribute("forceUpdate", $attribs, false, true); // default: false
	$only = weTag_getAttribute("only", $attribs); // Possibility: href


	// Überprüfen ob Zeitstempel der Quelldateien neuer sind => Update erzwingen
	if ( $watch ) {

		$targetObject = new we_textDocument();
		$targetObject->initByID($target);
		$targetTime = $targetObject->Published;

		$highestSrcTime = $targetTime;
		for ($i = 0; $i < count($srcs); $i++) {

			$src = intval($srcs[$i]);

			if ($src > 0) {
				$srcObject[$src] = new we_textDocument();
				$srcObject[$src]->initByID($src);
			}

			$srcTime = $srcObject[$src]->Published;
			$highestSrcTime = $highestSrcTime > $srcTime ? $highestSrcTime : $srcTime;
		}

		if ($targetTime < $highestSrcTime) {
			$forceUpdate = true;
		}
	}
	
	// Update ausführen
	if ( $forceUpdate ) {

		require_once('JSMin.php');
		//$minifiedJs = JSMin::minify($js);

		$jsmin = '';

		for ($i = 0; $i < count($srcs); $i++) {

			$src = intval($srcs[$i]);

			if($src > 0) {

				if ( !isset($srcObject[$src]) ) {
					$srcObject[$src] = new we_textDocument();
					$srcObject[$src]->initByID($src);
				}

				//$ico_lib->add_image( $_SERVER['DOCUMENT_ROOT'].$srcObject[$src]->Path );
				$jsmin .= implode('', file($_SERVER['DOCUMENT_ROOT'].$srcObject[$src]->Path))."\n";
			}
		}

		$result = $jsmin;

		try {
			// Sichern in $target
			if ( !isset($targetObj) ) {
				$targetObject = new we_textDocument();
				$targetObject->initByID($target);
			}
			$targetObject->setElement('data', $result);
			$targetObject->we_save();
		} catch (exception $e) {
			echo "fatal error: " . $e->getMessage();
		}
	}


	// Ziel-Pfad
	$targetPath = '';
	if ( isset($targetObject) ) { // Ziel-Pfad aus dem bestehenden Object übernehmen
		$targetPath = $targetObject->Path;
	} else { // Ziel-Pfad ermitteln
		$row = getHash('SELECT Path,IsFolder,IsDynamic FROM ' . FILE_TABLE . ' WHERE ID=' . intval($target), $GLOBALS['DB_WE']);
		if (!empty($row)) {
			$url = $row['Path'] . ($row['IsFolder'] ? '/' : '');
			$targetPath = $url;
		}
	}

	$srcObject = null;
	$targetObject = null;


	// Ausgabe
	if ($only == 'src') {
		return $targetPath;
	} else {
		return '<script type="text/javascript" src="'.$targetPath.'" /></script>'."\n";
	}


}

